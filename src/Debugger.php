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

use Framework\Helpers\Isolation;

/**
 * Class Debugger.
 *
 * @package debug
 */
class Debugger
{
    /**
     * @var array<string,Collection>
     */
    protected array $collections = [];

    public function addCollection(Collection $collection) : static
    {
        $this->collections[$collection->getName()] = $collection;
        return $this;
    }

    /**
     * @return array<string,Collection>
     */
    public function getCollections() : array
    {
        return $this->collections;
    }

    public function getCollection(string $name) : ?Collection
    {
        return $this->getCollections()[$name] ?? null;
    }

    public function addCollector(Collector $collector, string $collectionName) : static
    {
        $collection = $this->getCollection($collectionName);
        if ($collection === null) {
            $collection = new Collection($collectionName);
            $this->addCollection($collection);
        }
        $collection->addCollector($collector);
        return $this;
    }

    public function renderDebugbar() : string
    {
        \ob_start();
        Isolation::require(__DIR__ . '/Views/debugbar.php', [
            'collections' => $this->getCollections(),
        ]);
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    public static function makeSafeName(string $name) : string
    {
        return \strtr(\trim(\strip_tags(\strtolower($name))), [' ' => '-']);
    }
}
