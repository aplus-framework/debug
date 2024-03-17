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

use RuntimeException;

/**
 * Class Collection.
 *
 * @package debug
 */
class Collection
{
    protected string $name;
    /**
     * @var array<Collector>
     */
    protected array $collectors = [];
    /**
     * @var array<string>
     */
    protected array $actions = [];
    protected string $icon = '';
    protected string $iconPath;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->prepare();
    }

    protected function prepare() : void
    {
        if (isset($this->iconPath)) {
            if (!\is_file($this->iconPath)) {
                throw new RuntimeException(
                    'Icon path is invalid: ' . $this->iconPath
                );
            }
            $this->setIcon((string) \file_get_contents($this->iconPath));
        }
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getSafeName() : string
    {
        return Debugger::makeSafeName($this->getName());
    }

    public function addCollector(Collector $collector) : static
    {
        $this->collectors[] = $collector;
        return $this;
    }

    /**
     * @return array<Collector>
     */
    public function getCollectors() : array
    {
        return $this->collectors;
    }

    public function addAction(string $action) : static
    {
        $this->actions[] = $action;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getActions() : array
    {
        return $this->actions;
    }

    public function hasCollectors() : bool
    {
        return !empty($this->collectors);
    }

    /**
     * @return array<int,array<int,array<string,mixed>>>
     */
    public function getActivities() : array
    {
        $result = [];
        foreach ($this->getCollectors() as $collector) {
            $activities = $collector->getActivities();
            if ($activities) {
                foreach ($activities as &$activity) {
                    $activity = \array_merge([
                        'collection' => $this->getName(),
                    ], $activity);
                }
                unset($activity);
                $result[] = $activities;
            }
        }
        return $result;
    }

    public function getIcon() : string
    {
        return $this->icon;
    }

    public function setIcon(string $icon) : static
    {
        $this->icon = $icon;
        return $this;
    }

    public function hasIcon() : bool
    {
        return $this->icon !== '';
    }
}
