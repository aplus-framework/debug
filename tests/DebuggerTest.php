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

use Framework\Debug\Collection;
use Framework\Debug\Debugger;
use PHPUnit\Framework\TestCase;

final class DebuggerTest extends TestCase
{
    protected Debugger $debugger;

    protected function setUp() : void
    {
        $this->debugger = new Debugger();
    }

    public function testCollections() : void
    {
        self::assertEmpty($this->debugger->getCollections());
        self::assertNull($this->debugger->getCollection('Foo'));
        $this->debugger->addCollection(new Collection('Foo'));
        self::assertNull($this->debugger->getCollection('foo'));
        self::assertInstanceOf(Collection::class, $this->debugger->getCollection('Foo'));
        self::assertNotEmpty($this->debugger->getCollections());
    }

    public function testAddCollector() : void
    {
        $this->debugger->addCollector(new CollectorMock(), 'Foo');
        self::assertInstanceOf(Collection::class, $this->debugger->getCollection('Foo'));
        self::assertNull($this->debugger->getCollection('Bar'));
        $collection = new Collection('Bar');
        $this->debugger->addCollection($collection);
        self::assertEmpty($collection->getCollectors());
        $this->debugger->addCollector(new CollectorMock(), 'Bar');
        self::assertNotEmpty($collection->getCollectors());
    }

    public function testRenderDebugbar() : void
    {
        $debugbar = $this->debugger->renderDebugbar();
        self::assertStringContainsString('<style>', $debugbar);
        self::assertStringContainsString('id="debugbar"', $debugbar);
        self::assertStringContainsString('<script>', $debugbar);
        self::assertStringNotContainsString('foo-collection', $debugbar);
        self::assertStringNotContainsString('bar-collection', $debugbar);
        self::assertStringNotContainsString('collector-c1', $debugbar);
        self::assertStringNotContainsString('collector-c2', $debugbar);
        self::assertStringNotContainsString('collector-c3', $debugbar);
        self::assertStringNotContainsString('<button>Foo</button> xx', $debugbar);
        $fooCollection = new Collection('Foo');
        $fooCollection->addAction('<button>Foo</button>')->addAction('xx');
        $this->debugger->addCollection($fooCollection);
        $this->debugger->addCollector(new CollectorMock('c1'), 'Foo');
        $this->debugger->addCollector(new CollectorMock('c2'), 'Bar');
        $this->debugger->addCollector(new CollectorMock('c3'), 'Bar');
        $debugbar = $this->debugger->renderDebugbar();
        self::assertStringContainsString('foo-collection', $debugbar);
        self::assertStringContainsString('bar-collection', $debugbar);
        self::assertStringContainsString('collector-c1', $debugbar);
        self::assertStringContainsString('collector-c2', $debugbar);
        self::assertStringContainsString('collector-c3', $debugbar);
        self::assertStringContainsString('<button>Foo</button> xx', $debugbar);
    }

    public function testMakeSafeName() : void
    {
        self::assertSame('foo-bar-baz', Debugger::makeSafeName('Foo Bar <small>Baz</small>  '));
    }

    public function testConvertSize() : void
    {
        self::assertSame('1 B', Debugger::convertSize(1));
        self::assertSame('2 KB', Debugger::convertSize(1024 * 2));
        self::assertSame('3 MB', Debugger::convertSize(1024 * 1024 * 3));
        self::assertSame('4 GB', Debugger::convertSize(1024 * 1024 * 1024 * 4));
        self::assertSame('5 TB', Debugger::convertSize(1024 * 1024 * 1024 * 1024 * 5));
        self::assertSame('6 PB', Debugger::convertSize(1024 * 1024 * 1024 * 1024 * 1024 * 6));
        self::assertSame('3.37 MB', Debugger::convertSize(1024 * 1024 * 3.36999));
    }

    public function testMakeDebugValue() : void
    {
        self::assertSame('array', Debugger::makeDebugValue([]));
        self::assertSame('false', Debugger::makeDebugValue(false));
        self::assertSame('1', Debugger::makeDebugValue(1));
        self::assertSame('1.2', Debugger::makeDebugValue(1.2));
        self::assertSame('null', Debugger::makeDebugValue(null));
        self::assertSame("'\\'Ok'", Debugger::makeDebugValue("'Ok"));
        self::assertSame('instanceof stdClass', Debugger::makeDebugValue(new \stdClass()));
    }
}
