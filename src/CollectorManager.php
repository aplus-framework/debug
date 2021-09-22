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
 * Class CollectorManager.
 *
 * @package debug
 */
class CollectorManager
{
    /**
     * @var array<string,Collector>
     */
    protected array $collectors = [];

    public function addCollector( $collector) : static
    {
        $this->collectors[$collector] = new $collector;
        return $this;
    }

    /**
     * @return array<string,Collector>
     */
    public function getCollectors() : array
    {
        return $this->collectors;
    }
}
