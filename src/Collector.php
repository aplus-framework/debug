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
    protected string $name;

    public function __construct(string $name = 'default')
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getSafeName() : string
    {
        return Debugger::makeSafeName($this->getName());
    }

    abstract public function getContents() : string;
}
