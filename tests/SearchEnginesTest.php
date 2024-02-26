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

use Framework\Debug\SearchEngines;
use PHPUnit\Framework\TestCase;

final class SearchEnginesTest extends TestCase
{
    protected SearchEngines $searchEngines;

    protected function setUp() : void
    {
        $this->searchEngines = new SearchEngines();
    }

    public function testDefaultSearchEngine() : void
    {
        self::assertSame('google', $this->searchEngines->getCurrent());
    }

    public function testConstructor() : void
    {
        $searchEngines = new SearchEngines('bing');
        self::assertSame('bing', $searchEngines->getCurrent());
    }

    public function testGetAll() : void
    {
        foreach ($this->searchEngines->getAll() as $name => $link) {
            self::assertIsString($name);
            self::assertIsString($link);
            self::assertStringStartsWith('https://', $link);
        }
    }

    public function testAdd() : void
    {
        $name = 'foo';
        $url = 'https://foo.tld/?q=';
        $this->searchEngines->add('foo', $url);
        self::assertSame($url, $this->searchEngines->getUrl($name));
    }

    public function testGetUrl() : void
    {
        self::assertIsString($this->searchEngines->getUrl('google'));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid search engine name: foo');
        $this->searchEngines->getUrl('foo');
    }

    public function testCurrent() : void
    {
        self::assertIsString($this->searchEngines->getCurrent());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid search engine name: foo');
        $this->searchEngines->setCurrent('foo');
    }

    public function testMakeLink() : void
    {
        $link = $this->searchEngines->makeLink('foo bar');
        self::assertStringContainsString('foo+bar', $link);
        self::assertStringStartsWith('https://www.google.com/search?q=', $link);
        $link = $this->searchEngines->makeLink('foo bar', 'bing');
        self::assertStringContainsString('foo+bar', $link);
        self::assertStringStartsWith('https://www.bing.com/search?q=', $link);
    }
}
