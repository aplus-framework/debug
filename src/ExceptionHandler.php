<?php declare(strict_types=1);
/*
 * This file is part of The Framework Debug Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\Debug;

use ErrorException;
use Framework\CLI\CLI;
use Framework\Helpers\Isolation;
use Framework\Language\Language;
use Framework\Log\Logger;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Class ExceptionHandler.
 */
class ExceptionHandler
{
	/**
	 * Development environment.
	 */
	public const DEVELOPMENT = 'development';
	/**
	 * Production environment.
	 */
	public const PRODUCTION = 'production';
	protected string $viewsDir = __DIR__ . '/Views/';
	protected ?Logger $logger = null;
	protected string $environment = ExceptionHandler::PRODUCTION;
	protected Language $language;

	/**
	 * ExceptionHandler constructor.
	 *
	 * @param string $environment
	 * @param Logger|null $logger
	 * @param Language|null $language
	 *
	 * @throws InvalidArgumentException if environment is invalid
	 */
	public function __construct(
		string $environment = ExceptionHandler::PRODUCTION,
		Logger $logger = null,
		Language $language = null
	) {
		if ( ! \in_array($environment, [
			static::DEVELOPMENT,
			static::PRODUCTION,
		], true)) {
			throw new InvalidArgumentException("Invalid environment '{$environment}'");
		}
		$this->environment = $environment;
		if ($logger) {
			$this->logger = $logger;
		}
		$this->language = $language ?? new Language('en');
		$this->language->addDirectory(__DIR__ . '/Languages');
	}

	/**
	 * @return Logger|null
	 */
	public function getLogger() : ?Logger
	{
		return $this->logger;
	}

	/**
	 * @return Language
	 */
	public function getLanguage() : Language
	{
		return $this->language;
	}

	public function getViewsDir() : string
	{
		return $this->viewsDir;
	}

	/**
	 * @param string $dir
	 *
	 * @return static
	 */
	public function setViewsDir(string $dir) : static
	{
		$path = \realpath($dir);
		if ( ! $path) {
			throw new InvalidArgumentException('Invalid path to view dir "' . $dir . '"');
		}
		$this->viewsDir = \rtrim($path, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
		return $this;
	}

	public function initialize(bool $handleErrors = true) : void
	{
		\set_exception_handler([$this, 'exceptionHandler']);
		if ($handleErrors) {
			\set_error_handler([$this, 'errorHandler']);
		}
	}

	/**
	 * Exception handler.
	 *
	 * @param Throwable $exception The Throwable, exception, instance
	 *
	 * @throws RuntimeException if view file is not found
	 */
	public function exceptionHandler(Throwable $exception) : void
	{
		if (\ob_get_length()) {
			\ob_end_clean();
		}
		$this->log((string) $exception);
		if (\PHP_SAPI === 'cli') {
			$this->cliError($exception);
			return;
		}
		\http_response_code(500);
		if ( ! \headers_sent()) {
			$this->sendHeaders();
		}
		if ($this->isJSON()) {
			$this->sendJSON($exception);
			return;
		}
		$file = $this->environment === static::DEVELOPMENT
			? 'development.php'
			: 'production.php';
		$file = $this->viewsDir . $file;
		if (\is_file($file)) {
			Isolation::require($file, [
				'handler' => $this,
				'exception' => $exception,
			]);
			return;
		}
		$error = 'Debug exception view "' . $file . '" was not found';
		$this->log($error);
		throw new RuntimeException($error);
	}

	protected function isJSON() : bool
	{
		return isset($_SERVER['HTTP_CONTENT_TYPE'])
			&& \str_starts_with($_SERVER['HTTP_CONTENT_TYPE'], 'application/json');
	}

	protected function sendJSON(Throwable $exception) : void
	{
		if ($this->environment === static::DEVELOPMENT) {
			$data = [
				'exception' => $exception::class,
				'message' => $exception->getMessage(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => $exception->getTrace(),
			];
		} else {
			$data = [
				'message' => $this->language->render('debug', 'exceptionDescription'),
			];
		}
		echo \json_encode($data);
	}

	protected function sendHeaders() : void
	{
		$content_type = 'text/html';
		if ($this->isJSON()) {
			$content_type = 'application/json';
		}
		\header('Content-Type: ' . $content_type . '; charset=UTF-8');
	}

	protected function cliError(Throwable $exception) : void
	{
		$message = $this->language->render('debug', 'exception')
			. ': ' . $exception::class . \PHP_EOL;
		$message .= $this->language->render('debug', 'message')
			. ': ' . $exception->getMessage() . \PHP_EOL;
		$message .= $this->language->render('debug', 'file')
			. ': ' . $exception->getFile() . \PHP_EOL;
		$message .= $this->language->render('debug', 'line')
			. ': ' . $exception->getLine() . \PHP_EOL;
		$message .= $this->language->render('debug', 'trace')
			. ': ' . $exception->getTraceAsString();
		CLI::error($message);
	}

	protected function log(string $message) : void
	{
		if ($this->logger) {
			$this->logger->critical($message);
		}
	}

	/**
	 * Error handler.
	 *
	 * @param int $errno The level of the error raised
	 * @param string $errstr The error message
	 * @param string|null $errfile The filename that the error was raised in
	 * @param int|null $errline The line number where the error was raised
	 *
	 * @see http://php.net/manual/en/function.set-error-handler.php
	 *
	 * @throws ErrorException if the error is included in the error_reporting
	 *
	 * @return bool
	 */
	public function errorHandler(
		int $errno,
		string $errstr,
		string $errfile = null,
		int $errline = null
	) : bool {
		if ( ! (\error_reporting() & $errno)) {
			return true;
		}
		$type = match ($errno) {
			\E_ERROR => 'Error',
			\E_WARNING => 'Warning',
			\E_PARSE => 'Parse',
			\E_NOTICE => 'Notice',
			\E_CORE_ERROR => 'Core Error',
			\E_CORE_WARNING => 'Core Warning',
			\E_COMPILE_ERROR => 'Compile Error',
			\E_COMPILE_WARNING => 'Compile Warning',
			\E_USER_ERROR => 'User Error',
			\E_USER_WARNING => 'User Warning',
			\E_USER_NOTICE => 'User Notice',
			\E_STRICT => 'Strict',
			\E_RECOVERABLE_ERROR => 'Recoverable Error',
			\E_DEPRECATED => 'Deprecated',
			\E_USER_DEPRECATED => 'User Deprecated',
			\E_ALL => 'All',
			default => '',
		};
		throw new ErrorException(
			($type ? $type . ': ' : '') . $errstr,
			0,
			$errno,
			$errfile,
			$errline
		);
	}
}
