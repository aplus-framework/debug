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
use JetBrains\PhpStorm\Deprecated;

/**
 * Class SearchEngines.
 *
 * @package debug
 */
class SearchEngines
{
    /**
     * Associative array with search engine names as key and their base URLs as
     * value.
     *
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
    /**
     * Name of currently selected search engine.
     *
     * @var string
     */
    protected string $current = 'google';

    /**
     * Instantiate the class and allows you to define the current search engine.
     *
     * @param string|null $current Set the current search engine name or null to
     * use the default
     * @param array<string,string> $engines Custom search engines
     */
    public function __construct(?string $current = null, array $engines = [])
    {
        if (isset($current)) {
            $this->setCurrent($current);
        }
        if ($engines) {
            $this->setMany($engines);
        }
    }

    /**
     * Returns the array of search engines.
     *
     * @return array<string,string> search engine names as keys and URLs as
     * values
     */
    public function getAll() : array
    {
        return $this->engines;
    }

    /**
     * @deprecated since version 4.5, use setEngine() instead
     *
     * @codeCoverageIgnore
     */
    #[Deprecated(
        reason: 'since version 4.5, use setEngine() instead',
        replacement: '%class%->setEngine(%parameter0%, %parameter1%)'
    )]
    public function add(string $name, string $url) : static
    {
        \trigger_error(
            'This method is deprecated, use setEngine() instead',
            \E_USER_DEPRECATED
        );
        $this->engines[$name] = $url;
        return $this;
    }

    /**
     * Sets a search engine.
     *
     * @since 4.5
     *
     * @param string $name The search engine name
     * @param string $url The search engine base URL
     *
     * @return static
     */
    public function set(string $name, string $url) : static
    {
        if (\trim($name) === '') {
            throw new InvalidArgumentException('Engine name cannot be empty');
        }
        if (\filter_var($url, \FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid URL: ' . $url);
        }
        $this->engines[$name] = $url;
        return $this;
    }

    /**
     * Set multiple search engines at once.
     *
     * @since 4.5
     *
     * @param array<string,string> $engines
     */
    public function setMany(array $engines) : static
    {
        foreach ($engines as $name => $url) {
            $this->set($name, $url);
        }
        return $this;
    }

    /**
     * Returns the base URL of an engine; throws exception if it does not exist.
     *
     * @param string $name The search engine name
     *
     * @return string The search engine base URL
     */
    public function getUrl(string $name) : string
    {
        if (!isset($this->engines[$name])) {
            throw new InvalidArgumentException('Invalid search engine name: ' . $name);
        }
        return $this->engines[$name];
    }

    /**
     * Sets the current search engine; validates existence.
     *
     * @param string $name The search engine name
     *
     * @return static
     */
    public function setCurrent(string $name) : static
    {
        if (!isset($this->engines[$name])) {
            throw new InvalidArgumentException('Invalid search engine name: ' . $name);
        }
        $this->current = $name;
        return $this;
    }

    /**
     * Returns the name of the current engine.
     *
     * @return string
     */
    public function getCurrent() : string
    {
        return $this->current;
    }

    /**
     * Returns the base URL of the current engine.
     *
     * @return string
     */
    public function getCurrentUrl() : string
    {
        return $this->getUrl($this->getCurrent());
    }

    /**
     * Generates a search link with the given query, using the current engine or a specific name.
     *
     * @param string $query A string to be URL-encoded
     * @param string|null $name the search engine name or null to use the current
     *
     * @return string Returns the link to search for the exception
     */
    public function makeLink(string $query, ?string $name = null) : string
    {
        $link = isset($name) ? $this->getUrl($name) : $this->getCurrentUrl();
        return $link . \urlencode($query);
    }
}
