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
use InvalidArgumentException;

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
    /**
     * @var array<string,mixed>
     */
    protected array $options = [];
    protected string $debugbarView = __DIR__ . '/Views/debugbar/debugbar.php';

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

    /**
     * @param array<string,mixed> $options
     *
     * @return static
     */
    public function setOptions(array $options) : static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * @return array<string,mixed>
     */
    public function getActivities() : array
    {
        $collected = [];
        foreach ($this->getCollections() as $collection) {
            foreach ($collection->getActivities() as $activities) {
                $collected = [...$collected, ...$activities];
            }
        }
        $min = .0;
        $max = .0;
        if ($collected) {
            \usort($collected, static function ($c1, $c2) {
                return $c1['start'] <=> $c2['start'];
            });
            $min = \min(\array_column($collected, 'start'));
            $max = \max(\array_column($collected, 'end'));
            foreach ($collected as &$activity) {
                $this->addActivityValues($activity, $min, $max);
            }
        }
        return [
            'min' => $min,
            'max' => $max,
            'total' => $max - $min,
            'collected' => $collected,
        ];
    }

    /**
     * @param array<string,mixed> $activity
     * @param float $min
     * @param float $max
     */
    protected function addActivityValues(array &$activity, float $min, float $max) : void
    {
        $total = $max - $min;
        $activity['total'] = $activity['end'] - $activity['start'];
        $activity['left'] = \round(($activity['start'] - $min) * 100 / $total, 3);
        $activity['width'] = \round($activity['total'] * 100 / $total, 3);
    }

    public function setDebugbarView(string $file) : static
    {
        $realpath = \realpath($file);
        if ( ! $realpath || ! \is_file($realpath)) {
            throw new InvalidArgumentException(
                'Invalid debugbar view file: ' . $file
            );
        }
        $this->debugbarView = $realpath;
        return $this;
    }

    public function getDebugbarView() : string
    {
        return $this->debugbarView;
    }

    public function renderDebugbar() : string
    {
        \ob_start();
        Isolation::require($this->getDebugbarView(), [
            'collections' => $this->getCollections(),
            'activities' => $this->getActivities(),
            'options' => $this->getOptions(),
        ]);
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    public static function makeSafeName(string $name) : string
    {
        return \strtr(\trim(\strip_tags(\strtolower($name))), [
            '·' => '-',
            ':' => '-',
            '(' => '-',
            ')' => '-',
            '/' => '-',
            '\\' => '-',
            ' ' => '-',
        ]);
    }

    public static function convertSize(float | int $size) : string
    {
        if (empty($size)) {
            return '0 B';
        }
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $index = \floor(\log($size, 1024));
        return \round($size / (1024 ** $index), 3) . ' ' . $unit[$index];
    }

    public static function makeDebugValue(mixed $value) : string
    {
        $type = \get_debug_type($value);
        return (string) match ($type) {
            'array' => 'array',
            'bool' => $value ? 'true' : 'false',
            'float', 'int' => $value,
            'null' => 'null',
            'string' => "'" . \strtr($value, ["'" => "\\'"]) . "'",
            default => 'instanceof ' . $type,
        };
    }
}
