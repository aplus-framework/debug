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
 * Class Activity.
 *
 * @since 5
 */
class Activity
{
    public string $collection;
    public string $collector;
    public string $class;
    public string $description;
    public float $start;
    public float $end;
    public float $width;
    public float $left;

    public function __construct(
        string $collector,
        string $class,
        string $description,
        float $start,
        float $end
    ) {
        $this->collector = $collector;
        $this->class = $class;
        $this->description = $description;
        $this->start = $start;
        $this->end = $end;
    }

    public function total() : float
    {
        return $this->end - $this->start;
    }

    public function title() : string
    {
        $title = $this->width . '%';
        $title .= ' · From ' . $this->left . '% to ' . $this->left + $this->width . '%';
        $title .= ' · From ' . \round($this->start, 6) . ' to ' . \round($this->end, 6) . ' second';
        return $title;
    }

    /**
     * Set the Activity "left" and "width" representing the CSS `margin-left`
     * and `width` properties used to create the time bar.
     *
     * @param float $min The minimum time of all collected activities
     * @param float $max The maximum time of all collected activities
     *
     * @return static
     */
    public function setTimeBarValues(float $min, float $max) : static
    {
        $total = $max - $min;
        if ($total > 0) {
            $this->left = \round(($this->start - $min) * 100 / $total, 3);
            $this->width = \round($this->total() * 100 / $total, 3);
            return $this;
        }
        $this->left = .0;
        $this->width = .0;
        return $this;
    }
}
