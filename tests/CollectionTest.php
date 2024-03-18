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
use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
    protected Collection $collection;

    protected function setUp() : void
    {
        $this->collection = new Collection('Foo');
    }

    public function testIcon() : void
    {
        self::assertFalse($this->collection->hasIcon());
        self::assertSame('', $this->collection->getIcon());
        $this->collection->setIcon('xXx');
        self::assertTrue($this->collection->hasIcon());
        self::assertSame('xXx', $this->collection->getIcon());
    }

    public function testIconPath() : void
    {
        $collection = new class('Foo') extends Collection {
            protected string $iconPath = __DIR__ . '/../src/Views/debugbar/icons/info.svg';
        };
        self::assertTrue($collection->hasIcon());
    }

    public function testIconPathWithInvalidPath() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Icon path is invalid: /foo/bar');
        $collection = new class('Foo') extends Collection {
            protected string $iconPath = '/foo/bar';
        };
    }
}
