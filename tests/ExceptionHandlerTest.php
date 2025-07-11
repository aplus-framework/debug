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
use Framework\Log\Log;
use Framework\Log\Logger;
use Framework\Log\Loggers\FileLogger;
use Framework\Log\Loggers\SysLogger;
use PHPUnit\Framework\TestCase;

final class ExceptionHandlerTest extends TestCase
{
    protected Logger $logger;

    protected function getLogger() : Logger
    {
        return $this->logger
            ??= new FileLogger(\sys_get_temp_dir() . '/tests.log');
    }

    public function testEnvironments() : void
    {
        $exceptions = new ExceptionHandler(ExceptionHandler::DEVELOPMENT);
        self::assertSame('development', $exceptions->getEnvironment());
        $exceptions = new ExceptionHandler(ExceptionHandler::PRODUCTION);
        self::assertSame('production', $exceptions->getEnvironment());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid environment: foo');
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

    public function testDevelopmentView() : void
    {
        $exceptions = new ExceptionHandler();
        $file = __DIR__ . '/../src/Views/exceptions/development.php';
        self::assertSame(\realpath($file), $exceptions->getDevelopmentView());
        $exceptions->setDevelopmentView($file);
        self::assertSame(\realpath($file), $exceptions->getDevelopmentView());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid exceptions view file: /unknown/foo.php');
        $exceptions->setDevelopmentView('/unknown/foo.php');
    }

    public function testProductionView() : void
    {
        $exceptions = new ExceptionHandler();
        $file = __DIR__ . '/../src/Views/exceptions/production.php';
        self::assertSame(\realpath($file), $exceptions->getProductionView());
        $exceptions->setProductionView($file);
        self::assertSame(\realpath($file), $exceptions->getProductionView());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid exceptions view file: /unknown/foo.php');
        $exceptions->setProductionView('/unknown/foo.php');
    }

    public function testCliException() : void
    {
        $exceptions = new ExceptionHandlerMock();
        Stderr::init();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = Stderr::getContents();
        self::assertStringContainsString('Exception: ', $contents);
        self::assertStringContainsString('Message: Foo', $contents);
        self::assertStringContainsString('File: ', $contents);
        self::assertStringContainsString('Line: ', $contents);
        self::assertStringContainsString('Trace: ', $contents);
        self::assertStringNotContainsString('Log id: ', $contents);
    }

    public function testCliExceptionWithLog() : void
    {
        $exceptions = new ExceptionHandlerMock(logger: $this->getLogger());
        Stderr::init();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = Stderr::getContents();
        self::assertStringContainsString('Log id: ', $contents);
    }

    /**
     * @dataProvider jsonHeadersProvider
     *
     * @runInSeparateProcess
     */
    public function testJsonExceptionOnProduction(string $header, string $value) : void
    {
        $_SERVER[$header] = $value;
        $exceptions = new ExceptionHandlerMock();
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        self::assertSame(500, \http_response_code());
        self::assertSame([
            'Content-Type: application/json; charset=UTF-8',
            'Content-Language: en',
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
     * @dataProvider jsonHeadersProvider
     *
     * @runInSeparateProcess
     */
    public function testJsonExceptionOnDevelopment(string $header, string $value) : void
    {
        $_SERVER[$header] = $value;
        $exceptions = new ExceptionHandlerMock(ExceptionHandler::DEVELOPMENT);
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        self::assertSame(500, \http_response_code());
        self::assertSame([
            'Content-Type: application/json; charset=UTF-8',
            'Content-Language: en',
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
     * @dataProvider jsonHeadersProvider
     *
     * @runInSeparateProcess
     */
    public function testJsonExceptionOnProductionWithLog(string $header, string $value) : void
    {
        $_SERVER[$header] = $value;
        $exceptions = new ExceptionHandlerMock(logger: $this->getLogger());
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        $contents = \json_decode($contents, true);
        self::assertSame($contents, [
            'status' => [
                'code' => 500,
                'reason' => 'Internal Server Error',
            ],
            'data' => [
                'message' => 'Something went wrong. Please, back later.',
                'log_id' => $this->getLogger()->getLastLog()->id,
            ],
        ]);
    }

    /**
     * @dataProvider jsonHeadersProvider
     *
     * @runInSeparateProcess
     */
    public function testJsonExceptionOnDevelopmentWithLog(string $header, string $value) : void
    {
        $_SERVER[$header] = $value;
        $exceptions = new ExceptionHandlerMock(ExceptionHandler::DEVELOPMENT, $this->getLogger());
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        $contents = \json_decode($contents, true);
        self::assertSame($contents['data']['log_id'], $this->getLogger()->getLastLog()->id);
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
            'Content-Language: en',
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
            'Content-Language: en',
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
     * @runInSeparateProcess
     */
    public function testExceptionOnDevelopmentWithHiddenInputs() : void
    {
        $_ENV = [];
        $_GET = ['foo' => 'bar'];
        $_POST = ['foo' => 'bar'];
        $exceptions = new ExceptionHandlerMock(ExceptionHandler::DEVELOPMENT);
        $exceptions->setHiddenInputs('$_POST', '$_GET', '$_ENV');
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        self::assertStringContainsString('<strong>$_GET</strong>', $contents);
        self::assertStringContainsString('<strong>$_POST</strong>', $contents);
        self::assertStringNotContainsString('<strong>$_ENV</strong>', $contents);
    }

    /**
     * @runInSeparateProcess
     *
     * @dataProvider environmentsProvider
     */
    public function testExceptionWithLogger(string $environment) : void
    {
        $exceptions = new ExceptionHandlerMock($environment, $this->getLogger());
        $exceptions->cli = false;
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        \ob_get_clean();
        self::assertNotEmpty($this->getLogger()->getLastLog());
    }

    /**
     * @dataProvider errorProvider
     *
     * @param int $error
     * @param string $type
     */
    public function testErrorHandler(int $error, string $type) : void
    {
        $exceptions = new ExceptionHandler();
        $exceptions->initialize();
        \error_reporting(\E_CORE_ERROR);
        \trigger_error('Error message', \E_USER_WARNING);
        \error_reporting(\E_ALL);
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage($type . ': Error message');
        \trigger_error('Error message', $error);
    }

    public function testShowLogId() : void
    {
        $exceptions = new ExceptionHandler();
        self::assertTrue($exceptions->isShowingLogId());
        $exceptions->setShowLogId(false);
        self::assertFalse($exceptions->isShowingLogId());
    }

    public function testJsonFlags() : void
    {
        $exceptions = new ExceptionHandler();
        self::assertSame(
            \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,
            $exceptions->getJsonFlags()
        );
        $flags = \JSON_PRETTY_PRINT | \JSON_OBJECT_AS_ARRAY;
        $exceptions->setJsonFlags($flags);
        self::assertSame($flags, $exceptions->getJsonFlags());
    }

    public function testGetLog() : void
    {
        $exceptions = new ExceptionHandler();
        self::assertNull($exceptions->getLog());
        $exceptions = new ExceptionHandler(logger: $this->getLogger());
        self::assertNull($exceptions->getLog());
        $this->getLogger()->logCritical('Ooops!');
        self::assertInstanceOf(Log::class, $exceptions->getLog());
        self::assertSame('Ooops!', $exceptions->getLog()->message);
        $exceptions->setShowLogId(false);
        self::assertNull($exceptions->getLog());
    }

    public function testGetSetHiddenInputs() : void
    {
        $exceptions = new ExceptionHandler();
        self::assertEmpty($exceptions->getHiddenInputs());
        $exceptions->setHiddenInputs('$_POST', '$_GET');
        self::assertNotEmpty($exceptions->getHiddenInputs());
        self::assertSame(['$_GET', '$_POST'], $exceptions->getHiddenInputs());
        $exceptions->setHiddenInputs('$_POST', '$_SERVER', '$_ENV');
        self::assertSame(['$_ENV', '$_POST', '$_SERVER'], $exceptions->getHiddenInputs());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input name: $_FOO');
        $exceptions->setHiddenInputs('$_POST', '$_FOO', '$_ENV');
    }

    public function testIsHiddenInput() : void
    {
        $exceptions = new ExceptionHandler();
        self::assertFalse($exceptions->isHiddenInput('$_GET'));
        $exceptions->setHiddenInputs('$_GET');
        self::assertTrue($exceptions->isHiddenInput('$_GET'));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input name: $_Bar');
        $exceptions->isHiddenInput('$_Bar');
    }

    public function testAddAndRemoveHiddenInput() : void
    {
        $exceptions = new ExceptionHandler();
        self::assertEmpty($exceptions->getHiddenInputs());
        $exceptions->addHiddenInputs('$_ENV');
        self::assertNotEmpty($exceptions->getHiddenInputs());
        self::assertSame(
            ['$_ENV'],
            $exceptions->getHiddenInputs()
        );
        $exceptions->addHiddenInputs('$_POST', '$_FILES');
        self::assertSame(
            ['$_ENV', '$_FILES', '$_POST'],
            $exceptions->getHiddenInputs()
        );
        $exceptions->removeHiddenInputs('$_FILES');
        self::assertSame(
            ['$_ENV', '$_POST'],
            $exceptions->getHiddenInputs()
        );
    }

    /**
     * @runInSeparateProcess
     *
     * @dataProvider environmentsProvider
     */
    public function testExceptionsWithLtrLanguage(string $environment) : void
    {
        $_POST = ['foo' => 'bar'];
        $language = new Language('en', [__DIR__ . '/../src/Languages']);
        $exceptions = new ExceptionHandlerMock(
            $environment,
            $this->getLogger(),
            $language
        );
        $exceptions->cli = false;
        $exceptions->setHiddenInputs('$_POST');
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        self::assertStringContainsString('dir="ltr"', $contents);
    }

    /**
     * @runInSeparateProcess
     *
     * @dataProvider environmentsProvider
     */
    public function testExceptionsWithRtlLanguage(string $environment) : void
    {
        $_POST = ['foo' => 'bar'];
        $language = new Language('he', [__DIR__ . '/../src/Languages']);
        $exceptions = new ExceptionHandlerMock(
            $environment,
            $this->getLogger(),
            $language
        );
        $exceptions->cli = false;
        $exceptions->setHiddenInputs('$_POST');
        \ob_start();
        $exceptions->exceptionHandler(new \Exception('Foo'));
        $contents = (string) \ob_get_clean();
        self::assertStringContainsString('dir="rtl"', $contents);
    }

    /**
     * @return array<array<string>>
     */
    public static function environmentsProvider() : array
    {
        return [
            [ExceptionHandler::DEVELOPMENT],
            [ExceptionHandler::PRODUCTION],
        ];
    }

    /**
     * @return array<array<int|string>>
     */
    public static function errorProvider() : array
    {
        return [
            [\E_USER_WARNING, 'User Warning'],
            [\E_USER_DEPRECATED, 'User Deprecated'],
            [\E_USER_NOTICE, 'User Notice'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public static function jsonHeadersProvider() : array
    {
        return [
            ['HTTP_ACCEPT', 'text/html; application/json'],
            ['HTTP_CONTENT_TYPE', 'application/json; charset=UTF-8'],
        ];
    }
}
