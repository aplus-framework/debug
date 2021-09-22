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
 * Class Toolbar.
 *
 * @package debug
 */
class Toolbar extends CollectorManager
{
    public function render() : string
    {
        $content = '<div>';
        $content .= '<h1>Toolbar</h1>';
        $content .= '<div>';
        foreach ($this->getCollectors() as $class => $collector) {
            $content .= '<div>';
            $content .= $collector::render();
            $content .= '</div>';
        }
        $content .= '</div>';
        $content .= '</div>';
        return $content;
    }
}
