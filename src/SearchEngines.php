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

use InvalidArgumentException;

/**
 * Class SearchEngines.
 *
 * @package debug
 */
class SearchEngines
{
    /**
     * @var array<string,string>
     */
    protected array $engines = [
        'ask' => 'https://www.ask.com/web?q=',
        'baidu' => 'https://www.baidu.com/s?wd=',
        'bing' => 'https://www.bing.com/search?q=',
        'duckduckgo' => 'https://duckduckgo.com/?q=',
        'google' => 'https://www.google.com/search?q=',
        'yahoo' => 'https://search.yahoo.com/search?p=',
        'yandex' => 'https://yandex.com/search/?text=',
    ];
    protected string $current = 'google';

    public function __construct(?string $current = null)
    {
        if (isset($current)) {
            $this->setCurrent($current);
        }
    }

    /**
     * @return array<string,string>
     */
    public function getAll() : array
    {
        return $this->engines;
    }

    public function add(string $name, string $url) : static
    {
        $this->engines[$name] = $url;
        return $this;
    }

    public function getUrl(string $name) : string
    {
        if (!isset($this->engines[$name])) {
            throw new InvalidArgumentException('Invalid search engine name: ' . $name);
        }
        return $this->engines[$name];
    }

    public function setCurrent(string $name) : static
    {
        if (!isset($this->engines[$name])) {
            throw new InvalidArgumentException('Invalid search engine name: ' . $name);
        }
        $this->current = $name;
        return $this;
    }

    public function getCurrent() : string
    {
        return $this->current;
    }

    public function getCurrentUrl() : string
    {
        return $this->getUrl($this->getCurrent());
    }

    public function makeLink(string $query, ?string $name = null) : string
    {
        $link = isset($name) ? $this->getUrl($name) : $this->getCurrentUrl();
        return $link . \urlencode($query);
    }
}
