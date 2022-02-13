<?php
/*
 * This file is part of Aplus Framework Debug Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Debug;

use Framework\CLI\Streams\Stderr;
use Framework\Debug\ExceptionHandler;
use Framework\Language\Language;
use Framework\Log\Loggers\FileLogger;
use Framework\Log\Loggers\SysLogger;
use PHPUnit\Framework\TestCase;

final class ExceptionHandlerTest extends TestCase
{
    public function testEnvironments() : void
    {
        $exceptions = new ExceptionHandler(ExceptionHandler::DEVELOPMENT);
        self::assertSame('development', $exceptions->getEnvironment());
        $exceptions = new ExceptionHandler(ExceptionHandler::PRODUCTION);
        self::assertSame('production', $exceptions->getEnvironment());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid environment 'foo'");
        new ExceptionHandler('foo');
    }

    public function testConstructorInstances() : void
    {
        $exceptions = new ExceptionHandler();
        self::assertNull($exceptions->getLogger());
        self::assertInstanceOf(Language::class, $exceptions->getLanguage());
        $logger = new SysLogger();
        $language = new Language();
        $exceptions = new ExceptionHandler(logger: $logger, language: $language);
        self::assertSame($logger, $exceptions->getLogger());
        self::assertSame($language, $exceptions->getLanguage());
    }

    public function testViewsDir() : void
    {
        $exceptions = new ExceptionHandler();
        $dir = __DIR__ . '/../src/Views/';
        $expectedDir = \realpath($dir) . '/';
        self::assertSame($expectedDir, $exceptions->getViewsDir());
        $exceptions->setViewsDir($dir);
        self::assertSame($expectedDir, $exceptions->getViewsDir());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid path to view dir "/foobar"');
        $exceptions->setViewsDir('/foobar');
    }

    public function testCliException() : void
    {
        Stderr::init();
        $exceptions = new ExceptionHandlerMock();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = Stderr::getContents();
        self::assertStringContainsString('Exception: ', $contents);
        self::assertStringContainsString('Message: Foo', $contents);
        self::assertStringContainsString('File: ', $contents);
        self::assertStringContainsString('Line: ', $contents);
        self::assertStringContainsString('Trace: ', $contents);
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonExceptionOnProduction() : void
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json ';
        $exceptions = new ExceptionHandlerMock();
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        self::assertSame(500, \http_response_code());
        self::assertSame([
            'Content-Type: application/json; charset=UTF-8',
        ], xdebug_get_headers());
        $contents = \json_decode($contents, true);
        self::assertSame($contents, [
            'status' => [
                'code' => 500,
                'reason' => 'Internal Server Error',
            ],
            'data' => [
                'message' => 'Something went wrong. Please, back later.',
            ],
        ]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonExceptionOnDevelopment() : void
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json ';
        $exceptions = new ExceptionHandlerMock(ExceptionHandler::DEVELOPMENT);
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        self::assertSame(500, \http_response_code());
        self::assertSame([
            'Content-Type: application/json; charset=UTF-8',
        ], xdebug_get_headers());
        $contents = \json_decode($contents, true);
        self::assertSame($contents['status'], [
            'code' => 500,
            'reason' => 'Internal Server Error',
        ]);
        self::assertSame($contents['data']['exception'], \Exception::class);
        self::assertSame($contents['data']['message'], 'Foo');
        self::assertArrayHasKey('file', $contents['data']);
        self::assertArrayHasKey('line', $contents['data']);
        self::assertArrayHasKey('trace', $contents['data']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionOnProduction() : void
    {
        $exceptions = new ExceptionHandlerMock();
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        self::assertSame(500, \http_response_code());
        self::assertSame([
            'Content-Type: text/html; charset=UTF-8',
        ], xdebug_get_headers());
        self::assertStringContainsString(
            'Error 500 - Internal Server Error',
            $contents
        );
        self::assertStringContainsString(
            'Something went wrong. Please, back later.',
            $contents
        );
        self::assertStringNotContainsString('Log Id:', $contents);
    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionOnDevelopment() : void
    {
        $exceptions = new ExceptionHandlerMock(ExceptionHandler::DEVELOPMENT);
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        self::assertSame(500, \http_response_code());
        self::assertSame([
            'Content-Type: text/html; charset=UTF-8',
        ], xdebug_get_headers());
        self::assertStringContainsString(
            'Error 500 - Internal Server Error',
            $contents
        );
        self::assertStringContainsString('Exception: Foo', $contents);
        self::assertStringContainsString('Message:', $contents);
        self::assertStringNotContainsString('Log:', $contents);
    }

    /**
     * @return array<array<string>>
     */
    public function environmentsProvider() : array
    {
        return [
            [ExceptionHandler::DEVELOPMENT],
            [ExceptionHandler::PRODUCTION],
        ];
    }

    /**
     * @runInSeparateProcess
     * @dataProvider environmentsProvider
     */
    public function testExceptionWithLogger(string $environment) : void
    {
        $logger = new FileLogger(\sys_get_temp_dir() . '/tests.log');
        $exceptions = new ExceptionHandlerMock($environment, $logger);
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        \ob_get_clean();
        self::assertNotEmpty($logger->getLastLog());
    }

    public function testViewNotFound() : void
    {
        $exceptions = new ExceptionHandlerMock();
        $exceptions->cli = false;
        $dir = __DIR__ . '/unknown/';
        $exceptions->setTestViewsDir($dir);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Debug exception view "' . $dir . 'production.php" was not found'
        );
        $exceptions->exceptionHandler(new \Exception('Foo'));
    }

    public function testErrorHandler() : void
    {
        $exceptions = new ExceptionHandler();
        $exceptions->initialize();
        \error_reporting(\E_CORE_ERROR);
        \trigger_error('Error message', \E_USER_WARNING);
        \error_reporting(\E_ALL);
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('User Warning: Error message');
        \trigger_error('Error message', \E_USER_WARNING);
    }
}
