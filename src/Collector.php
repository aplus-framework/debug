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

/**
 * Class Collector.
 *
 * @package debug
 */
abstract class Collector
{
    /**
     * @var array<mixed>
     */
    protected static array $data = [];

    /**
     * @param array<mixed> $item
     */
    public static function add(array $item) : void
    {
        static::$data[] = [
            'microtime' => \microtime(true),
            'memory' => \memory_get_usage(),
            'item' => $item,
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function getData() : array
    {
        return static::$data;
    }

    public static function render() : string
    {
        return <<<'EOL'
            <h2>Database</h2>
            <div>
            <table>
            <thead>
            <tr>
            <th>Query</th>
            </tr>
            <tbody>
            <tr>
            <td><pre><code class="language-sql">SELECT * FROM `foo`</code></pre></td>
            </tr>
            </tbody>
            </thead>
            </table>
            </div>
            EOL;
    }
}
