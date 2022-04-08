<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Debug Library.
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
 *
 * @package debug
 */
class ExceptionHandler
{
    /**
     * Development environment.
     *
     * @var string
     */
    public const DEVELOPMENT = 'development';
    /**
     * Production environment.
     *
     * @var string
     */
    public const PRODUCTION = 'production';
    protected string $developmentView = __DIR__ . '/Views/exceptions/development.php';
    protected string $productionView = __DIR__ . '/Views/exceptions/production.php';
    protected ?Logger $logger = null;
    protected string $environment = ExceptionHandler::PRODUCTION;
    protected Language $language;
    protected bool $testing = false;

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
        $this->setEnvironment($environment);
        if ($logger) {
            $this->logger = $logger;
        }
        if ($language) {
            $this->setLanguage($language);
        }
    }

    public function setEnvironment(string $environment) : static
    {
        if ( ! \in_array($environment, [
            static::DEVELOPMENT,
            static::PRODUCTION,
        ], true)) {
            throw new InvalidArgumentException('Invalid environment: ' . $environment);
        }
        $this->environment = $environment;
        return $this;
    }

    public function getEnvironment() : string
    {
        return $this->environment;
    }

    /**
     * @return Logger|null
     */
    public function getLogger() : ?Logger
    {
        return $this->logger;
    }

    public function setLanguage(Language $language = null) : static
    {
        $this->language = $language ?? new Language();
        $this->language->addDirectory(__DIR__ . '/Languages');
        return $this;
    }

    /**
     * @return Language
     */
    public function getLanguage() : Language
    {
        if ( ! isset($this->language)) {
            $this->setLanguage();
        }
        return $this->language;
    }

    protected function validateView(string $file) : string
    {
        $realpath = \realpath($file);
        if ( ! $realpath || ! \is_file($realpath)) {
            throw new InvalidArgumentException(
                'Invalid exceptions view file: ' . $file
            );
        }
        return $realpath;
    }

    public function setDevelopmentView(string $file) : static
    {
        $this->developmentView = $this->validateView($file);
        return $this;
    }

    public function getDevelopmentView() : string
    {
        return $this->developmentView;
    }

    public function setProductionView(string $file) : static
    {
        $this->productionView = $this->validateView($file);
        return $this;
    }

    public function getProductionView() : string
    {
        return $this->productionView;
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
        if ($this->isCli()) {
            $this->cliError($exception);
            return;
        }
        \http_response_code(500);
        if ( ! \headers_sent()) {
            $this->sendHeaders();
        }
        if ($this->isJson()) {
            $this->sendJson($exception);
            return;
        }
        $file = $this->getEnvironment() === static::DEVELOPMENT
            ? $this->getDevelopmentView()
            : $this->getProductionView();
        Isolation::require($file, [
            'handler' => $this,
            'exception' => $exception,
        ]);
    }

    protected function isCli() : bool
    {
        return \PHP_SAPI === 'cli' || \defined('STDIN');
    }

    protected function isJson() : bool
    {
        return isset($_SERVER['HTTP_CONTENT_TYPE'])
            && \str_starts_with($_SERVER['HTTP_CONTENT_TYPE'], 'application/json');
    }

    protected function sendJson(Throwable $exception) : void
    {
        $data = $this->getEnvironment() === static::DEVELOPMENT
            ? [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]
            : [
                'message' => $this->getLanguage()->render('debug', 'exceptionDescription'),
            ];
        echo \json_encode([
            'status' => [
                'code' => 500,
                'reason' => 'Internal Server Error',
            ],
            'data' => $data,
        ], \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
    }

    protected function sendHeaders() : void
    {
        $contentType = 'text/html';
        if ($this->isJson()) {
            $contentType = 'application/json';
        }
        \header('Content-Type: ' . $contentType . '; charset=UTF-8');
    }

    protected function cliError(Throwable $exception) : void
    {
        $language = $this->getLanguage();
        $message = $language->render('debug', 'exception')
            . ': ' . $exception::class . \PHP_EOL;
        $message .= $language->render('debug', 'message')
            . ': ' . $exception->getMessage() . \PHP_EOL;
        $message .= $language->render('debug', 'file')
            . ': ' . $exception->getFile() . \PHP_EOL;
        $message .= $language->render('debug', 'line')
            . ': ' . $exception->getLine() . \PHP_EOL;
        $message .= $language->render('debug', 'trace')
            . ': ' . $exception->getTraceAsString();
        CLI::error($message, $this->testing ? null : 1);
    }

    protected function log(string $message) : void
    {
        $this->getLogger()?->logCritical($message);
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
