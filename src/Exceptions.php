<?php namespace Framework\Debug;

use Framework\CLI\CLI;
use Framework\Language\Language;

/**
 * Class Exceptions.
 */
class Exceptions
{
	public const ENV_DEV = 'development';
	public const ENV_PROD = 'production';
	/**
	 * @var string
	 */
	protected $viewsDir = __DIR__ . '/Views/';
	/**
	 * @var bool
	 */
	protected $cleanBuffer = true;
	protected $logger;
	protected $environment = 'production';
	/**
	 * @var Language
	 */
	protected $language;
	protected $handleErrors = true;

	public function __construct(
		$environment = self::ENV_PROD,
		$clearBuffer = true,
		string $viewsDir = null,
		Language $language = null
	) {
		$this->environment = $environment;
		$this->initialize($clearBuffer);
		if ($viewsDir) {
			$this->setViewsDir($viewsDir);
		}
		$this->language = $language ?: new Language('pt-br');
		$this->language
			->addDirectory(__DIR__ . '/Languages');
	}

	public function getViewsDir() : string
	{
		return $this->viewsDir;
	}

	public function setViewsDir(string $dir)
	{
		$path = \realpath($dir);
		if ( ! $path) {
			throw new \InvalidArgumentException('Invalid path to view dir "' . $dir . '"');
		}
		$this->viewsDir = \rtrim($path, '/') . \DIRECTORY_SEPARATOR;
		return $this;
	}

	public function initialize(bool $clean_buffer = true)
	{
		$this->cleanBuffer = $clean_buffer;
		\set_exception_handler([$this, 'exceptionHandler']);
		if ($this->handleErrors) {
			\set_error_handler([$this, 'errorHandler']);
		}
		return $this;
	}

	public function exceptionHandler(\Throwable $exception)
	{
		if ($this->cleanBuffer && \ob_get_length()) {
			\ob_end_clean();
		}
		//logger()->log('CRITICAL', $exception);
		if (\PHP_SAPI === 'cli') {
			$message = $this->language->render('debug', 'exception')
				. ': ' . \get_class($exception) . \PHP_EOL;
			$message .= $this->language->render('debug', 'message')
				. ': ' . $exception->getMessage() . \PHP_EOL;
			$message .= $this->language->render('debug', 'file')
				. ': ' . $exception->getFile() . \PHP_EOL;
			$message .= $this->language->render('debug', 'line')
				. ': ' . $exception->getLine();
			CLI::error($message);
			return;
		}
		\http_response_code(500);
		if ( ! \headers_sent()) {
			\header('Content-Type: text/html; charset=UTF-8');
		}
		$file = $this->environment !== static::ENV_DEV
			? 'exceptions/production.php'
			: 'exceptions/development.php';
		if (\is_file($this->viewsDir . $file)) {
			require $this->viewsDir . $file;
			return;
		}
		$error = 'Debug exception view "' . $this->viewsDir . $file . '" was not found.';
		//logger()->log('CRITICAL', $error);
		throw new \LogicException($error);
	}

	/**
	 * @param int         $severity
	 * @param string      $message
	 * @param string|null $file
	 * @param int|null    $line
	 * @param null        $context
	 *
	 * @see http://php.net/manual/en/function.set-error-handler.php
	 *
	 * @throws \ErrorException
	 */
	public function errorHandler(
		int $severity,
		string $message,
		string $file = null,
		int $line = null,
		$context = null
	) {
		switch ($severity) {
			case \E_ERROR:
				$type = 'Error';
				break;
			case \E_WARNING:
				$type = 'Warning';
				break;
			case \E_PARSE:
				$type = 'Parse';
				break;
			case \E_NOTICE:
				$type = 'Notice';
				break;
			case \E_CORE_ERROR:
				$type = 'Core Error';
				break;
			case \E_CORE_WARNING:
				$type = 'Core Warning';
				break;
			case \E_COMPILE_ERROR:
				$type = 'Compile Error';
				break;
			case \E_COMPILE_WARNING:
				$type = 'Compile Warning';
				break;
			case \E_USER_ERROR:
				$type = 'User Error';
				break;
			case \E_USER_WARNING:
				$type = 'User Warning';
				break;
			case \E_USER_NOTICE:
				$type = 'User Notice';
				break;
			case \E_STRICT:
				$type = 'Strict';
				break;
			case \E_RECOVERABLE_ERROR:
				$type = 'Recoverable Error';
				break;
			case \E_DEPRECATED:
				$type = 'Deprecated';
				break;
			case \E_USER_DEPRECATED:
				$type = 'User Deprecated';
				break;
			case \E_ALL:
				$type = 'All';
				break;
			default:
				$type = '';
		}
		if ( ! (\error_reporting() & $severity)) {
			// This error code is not included in error_reporting
			return;
		}
		// http://php.net/manual/en/function.set-exception-handler.php#95170
		throw new \ErrorException(
			($type ? $type . ': ' : '') . $message,
			0,
			$severity,
			$file,
			$line
		);
	}
}
