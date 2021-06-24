<?php namespace Framework\Debug;

use ErrorException;
use Framework\CLI\CLI;
use Framework\Language\Language;
use Framework\Log\Logger;

/**
 * Class ExceptionHandler.
 */
class ExceptionHandler
{
	public const ENV_DEV = 'development';
	public const ENV_PROD = 'production';
	protected string $viewsDir = __DIR__ . '/Views/';
	protected ?Logger $logger = null;
	protected string $environment = 'production';
	protected Language $language;
	protected bool $handleErrors = true;

	/**
	 * ExceptionHandler constructor.
	 *
	 * @param string                            $environment One of ENV_* constants
	 * @param \Framework\Log\Logger|null        $logger
	 * @param \Framework\Language\Language|null $language
	 *
	 * @throws \InvalidArgumentException if environment is invalid
	 */
	public function __construct(
		string $environment = self::ENV_PROD,
		Logger $logger = null,
		Language $language = null
	) {
		if ($logger) {
			$this->logger = $logger;
		}
		$this->language = $language ?: new Language('en');
		$this->language->addDirectory(__DIR__ . '/Languages');
		if ( ! \in_array($environment, [
			static::ENV_DEV,
			static::ENV_PROD,
		], true)) {
			throw new \InvalidArgumentException("Invalid environment '{$environment}'");
		}
		$this->environment = $environment;
	}

	public function getViewsDir() : string
	{
		return $this->viewsDir;
	}

	/**
	 * @param string $dir
	 *
	 * @return $this
	 */
	public function setViewsDir(string $dir)
	{
		$path = \realpath($dir);
		if ( ! $path) {
			throw new \InvalidArgumentException('Invalid path to view dir "' . $dir . '"');
		}
		$this->viewsDir = \rtrim($path, '/') . \DIRECTORY_SEPARATOR;
		return $this;
	}

	public function initialize() : void
	{
		\set_exception_handler([$this, 'exceptionHandler']);
		if ($this->handleErrors) {
			\set_error_handler([$this, 'errorHandler']);
		}
	}

	public function exceptionHandler(\Throwable $exception) : void
	{
		if (\ob_get_length()) {
			\ob_end_clean();
		}
		$this->log($exception);
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
		$file = $this->environment !== static::ENV_DEV
			? 'exceptions/production.php'
			: 'exceptions/development.php';
		if (\is_file($this->viewsDir . $file)) {
			require $this->viewsDir . $file;
			return;
		}
		$error = 'Debug exception view "' . $this->viewsDir . $file . '" was not found';
		$this->log($error);
		throw new \LogicException($error);
	}

	protected function isJSON() : bool
	{
		return isset($_SERVER['HTTP_CONTENT_TYPE'])
			&& \str_starts_with($_SERVER['HTTP_CONTENT_TYPE'], 'application/json');
	}

	protected function sendJSON(\Throwable $exception) : void
	{
		if ($this->environment !== static::ENV_DEV) {
			$data = [
				'message' => $this->language->render('debug', 'exceptionDescription'),
			];
		} else {
			$data = [
				'exception' => $exception::class,
				'message' => $exception->getMessage(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => $exception->getTrace(),
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

	protected function cliError(\Throwable $exception) : void
	{
		$message = $this->language->render('debug', 'exception')
			. ': ' . \get_class($exception) . \PHP_EOL;
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
