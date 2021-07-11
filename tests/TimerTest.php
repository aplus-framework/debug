<?php
/*
 * This file is part of The Framework Debug Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Debug;

use Framework\Debug\Timer;
use PHPUnit\Framework\TestCase;

final class TimerTest extends TestCase
{
    protected Timer $timer;

    public function setUp() : void
    {
        $this->timer = new Timer();
    }

    public function testA() : void
    {
        self::assertSame(['debug[start]'], \array_keys($this->timer->getMarks()));
        $this->timer->addMark('a');
        $this->timer->addMark('b');
        self::assertSame(['debug[start]', 'a', 'b'], \array_keys($this->timer->getMarks()));
    }

    public function testMark() : void
    {
        $this->timer->addMark('1');
        // @phpstan-ignore-next-line
        self::assertSame(['memory', 'time'], \array_keys($this->timer->getMark('1')));
        $this->timer->setMark('foo', \memory_get_usage(), \microtime(true));
        // @phpstan-ignore-next-line
        self::assertSame(['memory', 'time'], \array_keys($this->timer->getMark('foo')));
    }

    public function testDiff() : void
    {
        $this->timer->addMark('1');
        $this->timer->addMark('2');
        self::assertSame(['memory', 'time'], \array_keys($this->timer->diff('1', '2')));
    }

    public function testTest() : void
    {
        $this->timer->test(10, static function () : void {
            \strpos('abc', 'b'); // @phpstan-ignore-line
        });
        $this->timer->test(10, static function () : void {
            \stripos('abc', 'b'); // @phpstan-ignore-line
        });
        self::assertSame([
            'debug[start]',
            'test[2][start]',
            'test[2][end]',
            'test[3][start]',
            'test[3][end]',
        ], \array_keys($this->timer->getMarks(true)));
    }
}
