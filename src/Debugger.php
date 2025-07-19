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
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Debugger.
 *
 * @package debug
 */
class Debugger
{
    /**
     * Contains the Collections.
     * Keys are the names and values are the Collections.
     *
     * @var array<string,Collection>
     */
    protected array $collections = [];
    /**
     * Contains the debug options.
     * The keys are the names of the options.
     *
     * @var array<string,mixed>
     */
    protected array $options = [];
    /**
     * Contains the path to the debugbar view file.
     *
     * @var string
     */
    protected string $debugbarView = __DIR__ . '/Views/debugbar/debugbar.php';
    /**
     * Tells if the debugbar is enabled.
     *
     * @var bool
     */
    protected bool $debugbarEnabled = true;

    /**
     * Add a new Collection.
     *
     * @param Collection $collection
     *
     * @return static
     */
    public function addCollection(Collection $collection) : static
    {
        $this->collections[$collection->getName()] = $collection;
        return $this;
    }

    /**
     * Get all Collections.
     *
     * @return array<string,Collection> An array where the keys are the names of
     * the collections and the values are the collections
     */
    public function getCollections() : array
    {
        return $this->collections;
    }

    /**
     * Get a Collection by name.
     *
     * @param string $name The name of the Collection
     *
     * @return Collection|null The collection or null if it does not exist
     */
    public function getCollection(string $name) : ?Collection
    {
        return $this->getCollections()[$name] ?? null;
    }

    /**
     * Add a collector to a collection.
     *
     * If a collection with the given name does not exist, a new one will be
     * created.
     *
     * @param Collector $collector The collector
     * @param string $collectionName The name of the collection
     *
     * @return static
     */
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
     * Set a debug option.
     *
     * @since 4.5
     *
     * @param string $name The name of the option
     * @param mixed $value The value of the option
     *
     * @return static
     */
    public function setOption(string $name, mixed $value) : static
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Get the value of a debug option.
     *
     * @since 4.5
     *
     * @param string $name The name of the option
     *
     * @return mixed The value of the option or null
     */
    public function getOption(string $name) : mixed
    {
        return $this->options[$name] ?? null;
    }

    /**
     * Set all debug options.
     *
     * @param array<string,mixed> $options All options
     *
     * @return static
     */
    public function setOptions(array $options) : static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get all debug options.
     *
     * @return array<string,mixed>
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Get an array with the minimum, maximum, and total execution times for
     * all activities. Also, returns an array with all collected activities.
     *
     * @return array<string,mixed>
     */
    #[ArrayShape([
        'min' => 'float',
        'max' => 'float',
        'total' => 'float',
        'collected' => 'array',
    ])]
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
     * Updates the $activity variable.
     *
     * Adds the "total" key representing the total execution time.
     *
     * Adds the "left" and "width" keys representing the CSS `margin-left` and
     * `width` properties used to create the time bar.
     *
     * @param array<string,mixed> $activity Current activity
     * @param float $min The minimum time of the collected activities
     * @param float $max The maximum time of collected activities
     */
    protected function addActivityValues(array &$activity, float $min, float $max) : void
    {
        $total = $max - $min;
        $activity['total'] = $activity['end'] - $activity['start'];
        if ($total > 0) {
            $activity['left'] = \round(($activity['start'] - $min) * 100 / $total, 3);
            $activity['width'] = \round($activity['total'] * 100 / $total, 3);
            return;
        }
        $activity['left'] = .0;
        $activity['width'] = .0;
    }

    /**
     * Set the path of the debugbar view.
     *
     * @param string $file The view file path
     *
     * @return static
     */
    public function setDebugbarView(string $file) : static
    {
        $realpath = \realpath($file);
        if (!$realpath || !\is_file($realpath)) {
            throw new InvalidArgumentException(
                'Invalid debugbar view file: ' . $file
            );
        }
        $this->debugbarView = $realpath;
        return $this;
    }

    /**
     * Get the path of the debugbar view.
     *
     * @return string
     */
    public function getDebugbarView() : string
    {
        return $this->debugbarView;
    }

    /**
     * Render the debug bar, if it is enabled.
     *
     * @return string The debug bar (if enabled) or a blank string
     */
    public function renderDebugbar() : string
    {
        if (!$this->isDebugbarEnabled()) {
            return '';
        }
        \ob_start();
        Isolation::require($this->getDebugbarView(), [
            'collections' => $this->getCollections(),
            'activities' => $this->getActivities(),
            'options' => $this->getOptions(),
        ]);
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    /**
     * Tells if debugbar rendering is enabled.
     *
     * @since 4.2
     *
     * @return bool
     */
    public function isDebugbarEnabled() : bool
    {
        return $this->debugbarEnabled;
    }

    /**
     * Enables debugbar rendering.
     *
     * @since 4.2
     *
     * @return static
     */
    public function enableDebugbar() : static
    {
        $this->debugbarEnabled = true;
        return $this;
    }

    /**
     * Disables debugbar rendering.
     *
     * @since 4.2
     *
     * @return static
     */
    public function disableDebugbar() : static
    {
        $this->debugbarEnabled = false;
        return $this;
    }

    /**
     * Replace a list of characters with hyphens.
     *
     * @param string $name The name to be updated
     *
     * @return string Returns the name with replacements
     */
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
            '"' => '-',
            '\'' => '-',
        ]);
    }

    /**
     * Convert size to unit of measurement.
     *
     * @param float|int $size The size in bytes
     *
     * @return string Returns the size with unit of measurement
     */
    public static function convertSize(float | int $size) : string
    {
        if (empty($size)) {
            return '0 B';
        }
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $index = \floor(\log($size, 1024));
        return \round($size / (1024 ** $index), 3) . ' ' . $unit[$index];
    }

    /**
     * Make a value into a debug value.
     *
     * @param mixed $value Any value
     *
     * @return string The value made
     */
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

    /**
     * Remove dots and zeros from the end of the version.
     *
     * @param string $version The version
     *
     * @return string The updated version
     */
    public static function roundVersion(string $version) : string
    {
        if (\str_ends_with($version, '.0')) {
            $version = \substr($version, 0, -2);
            return static::roundVersion($version);
        }
        return $version;
    }

    /**
     * Round seconds to milliseconds.
     *
     * @param float|int $seconds The number of seconds
     * @param int $precision The number of decimal digits to round
     *
     * @return float Returns the value in milliseconds
     */
    public static function roundSecondsToMilliseconds(float | int $seconds, int $precision = 3) : float
    {
        return \round($seconds * 1000, $precision);
    }
}
