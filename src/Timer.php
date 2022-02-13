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

use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Timer.
 *
 * @package debug
 */
class Timer
{
    /**
     * @var array<string,array<string,mixed>>
     */
    protected array $marks = [];
    protected int $testsCount = 1;

    /**
     * Timer constructor.
     */
    public function __construct()
    {
        $this->addMark('debug[start]');
    }

    /**
     * @param int $times
     * @param callable $function
     * @param bool $flush
     *
     * @return array<string,string> Two keys - "memory" in MB and "time" in seconds
     */
    #[ArrayShape(['memory' => 'string', 'time' => 'string'])]
    public function test(int $times, callable $function, bool $flush = false) : array
    {
        if ( ! $flush) {
            \ob_start();
        }
        $this->testsCount++;
        $this->addMark('test[' . $this->testsCount . '][start]');
        for ($i = 0; $i < $times; $i++) {
            $function();
        }
        $this->addMark('test[' . ($this->testsCount) . '][end]');
        if ( ! $flush) {
            \ob_end_clean();
        }
        return $this->diff(
            'test[' . $this->testsCount . '][start]',
            'test[' . $this->testsCount . '][end]'
        );
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function addMark(string $name) : static
    {
        $this->marks[$name] = [
            'memory' => \memory_get_usage(),
            'time' => \microtime(true),
        ];
        return $this;
    }

    /**
     * @param string $name
     * @param int $memoryUsage
     * @param float $microtime
     *
     * @return static
     */
    public function setMark(string $name, int $memoryUsage, float $microtime) : static
    {
        $this->marks[$name] = [
            'memory' => $memoryUsage,
            'time' => $microtime,
        ];
        return $this;
    }

    /**
     * @param string $name
     *
     * @return array<string,string>|false
     */
    public function getMark(string $name) : array | false
    {
        return $this->marks[$name] ?? false;
    }

    /**
     * @param bool $format
     *
     * @return array<string,array<string,mixed>>
     */
    public function getMarks(bool $format = false) : array
    {
        $marks = $this->marks;
        if ($format) {
            foreach ($marks as &$mark) {
                $mark['memory'] = \number_format($mark['memory'] / 1024 / 1024, 3) . ' MB';
                $mark['time'] = \number_format($mark['time'], 3) . ' s';
            }
        }
        return $marks;
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return array<string,string> Two keys: memory in MB and time in seconds
     */
    #[ArrayShape(['memory' => 'string', 'time' => 'string'])]
    public function diff(string $from, string $to) : array
    {
        $number = $this->marks[$to]['memory'] - $this->marks[$from]['memory'];
        $number = \number_format($number / 1024 / 1024, 3);
        $diff = [];
        $diff['memory'] = $number . ' MB';
        $number = $this->marks[$to]['time'] - $this->marks[$from]['time'];
        $number = \number_format($number, 3);
        $diff['time'] = $number . ' s';
        return $diff;
    }
}
