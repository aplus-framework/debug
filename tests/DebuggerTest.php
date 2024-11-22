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

    public function testDebugbarView() : void
    {
        self::assertIsString($this->debugger->getDebugbarView());
        $file = __DIR__ . '/../src/Views/debugbar/debugbar.php';
        $this->debugger->setDebugbarView($file);
        self::assertSame(\realpath($file), $this->debugger->getDebugbarView());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid debugbar view file: /unknown/foo.php');
        $this->debugger->setDebugbarView('/unknown/foo.php');
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

    public function testDebugbarStatus() : void
    {
        self::assertTrue($this->debugger->isDebugbarEnabled());
        self::assertNotSame('', $this->debugger->renderDebugbar());
        $this->debugger->disableDebugbar();
        self::assertFalse($this->debugger->isDebugbarEnabled());
        self::assertSame('', $this->debugger->renderDebugbar());
        $this->debugger->enableDebugbar();
        self::assertTrue($this->debugger->isDebugbarEnabled());
        self::assertNotSame('', $this->debugger->renderDebugbar());
    }

    public function testActivities() : void
    {
        $microtime = \microtime(true);
        $activities = [
            [
                'collector' => 'default',
                'class' => 'Class name',
                'description' => 'Collected data 1',
                'start' => $microtime,
                'end' => $microtime + .5,
            ],
            [
                'collector' => 'default',
                'class' => 'Class name',
                'description' => 'Collected data 2',
                'start' => $microtime + .1,
                'end' => $microtime + 1.0,
            ],
        ];
        self::assertSame([
            'min' => .0,
            'max' => .0,
            'total' => .0,
            'collected' => [],
        ], $this->debugger->getActivities());
        $collector = new CollectorMock();
        $collector->activities[0] = $activities[0];
        $this->debugger->addCollector($collector, 'Foo');
        self::assertSame([
            'min' => $microtime,
            'max' => $activities[0]['end'],
            'total' => .5,
            'collected' => [
                [
                    'collection' => 'Foo',
                    'collector' => 'default',
                    'class' => 'Class name',
                    'description' => 'Collected data 1',
                    'start' => $microtime,
                    'end' => $microtime + .5,
                    'total' => .5,
                    'left' => .0,
                    'width' => 100.0,
                ],
            ],
        ], $this->debugger->getActivities());
        self::assertStringContainsString('1 activity', $this->debugger->renderDebugbar());
        $collector->activities = $activities;
        self::assertSame([
            'min' => $microtime,
            'max' => $activities[1]['end'],
            'total' => 1.0,
            'collected' => [
                [
                    'collection' => 'Foo',
                    'collector' => 'default',
                    'class' => 'Class name',
                    'description' => 'Collected data 1',
                    'start' => $microtime,
                    'end' => $microtime + .5,
                    'total' => .5,
                    'left' => .0,
                    'width' => 50.0,
                ],
                [
                    'collection' => 'Foo',
                    'collector' => 'default',
                    'class' => 'Class name',
                    'description' => 'Collected data 2',
                    'start' => $microtime + .1,
                    'end' => $microtime + 1.0,
                    'total' => $activities[1]['end'] - $activities[1]['start'],
                    'left' => 10.0,
                    'width' => 90.0,
                ],
            ],
        ], $this->debugger->getActivities());
        $debugbar = $this->debugger->renderDebugbar();
        self::assertStringContainsString('2 activities', $debugbar);
        self::assertGreaterThan(
            \strpos($debugbar, 'Collected data 1'),
            \strpos($debugbar, 'Collected data 2')
        );
    }

    public function testOptions() : void
    {
        self::assertEmpty($this->debugger->getOptions());
        $this->debugger->setOptions(['color' => 'royalblue']);
        self::assertSame(['color' => 'royalblue'], $this->debugger->getOptions());
        self::assertStringContainsString('royalblue', $this->debugger->renderDebugbar());
    }

    public function testMakeSafeName() : void
    {
        self::assertSame(
            'foo-bar--baz-',
            Debugger::makeSafeName('Foo Bar <small>(Baz)</small>  ')
        );
    }

    public function testConvertSize() : void
    {
        self::assertSame('0 B', Debugger::convertSize(0));
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

    public function testRoundVersion() : void
    {
        self::assertSame('1.2.3', Debugger::roundVersion('1.2.3'));
        self::assertSame('1.0.3', Debugger::roundVersion('1.0.3'));
        self::assertSame('1.2', Debugger::roundVersion('1.2.0'));
        self::assertSame('1', Debugger::roundVersion('1.0.0'));
    }
}
