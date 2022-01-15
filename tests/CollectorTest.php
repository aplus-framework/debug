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

use PHPUnit\Framework\TestCase;

final class CollectorTest extends TestCase
{
    protected CollectorMock $collector;

    protected function setUp() : void
    {
        $this->collector = new CollectorMock();
    }

    public function testName() : void
    {
        self::assertSame('default', $this->collector->getName());
        self::assertSame('default', $this->collector->getSafeName());
        $collector = new CollectorMock('foo');
        self::assertSame('foo', $collector->getName());
        self::assertSame('foo', $collector->getSafeName());
    }

    public function testData() : void
    {
        self::assertFalse($this->collector->hasData());
        $item1 = ['foo'];
        $item2 = ['bar'];
        $this->collector->addData($item1);
        self::assertTrue($this->collector->hasData());
        $this->collector->addData($item2);
        self::assertSame([$item1, $item2], $this->collector->getData());
    }

    public function testGetContents() : void
    {
        self::assertStringContainsString(
            '<p>Collector: default</p>',
            $this->collector->getContents()
        );
    }
}
