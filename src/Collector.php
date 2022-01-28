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
    /**
     * @var array<mixed>
     */
    protected array $data = [];

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

    /**
     * @param array<mixed> $data
     *
     * @return static
     */
    public function addData(array $data) : static
    {
        $this->data[] = $data;
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getData() : array
    {
        return $this->data;
    }

    public function hasData() : bool
    {
        return ! empty($this->data);
    }

    /**
     * @return array<string,mixed>
     */
    public function getInfos() : array
    {
        return [];
    }

    abstract public function getContents() : string;
}
