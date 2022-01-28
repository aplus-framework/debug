<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Debug Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Debug;

use Framework\Debug\Collector;

class CollectorMock extends Collector
{
    public array $activities = [];

    public function getContents() : string
    {
        return '<p>Collector: ' . $this->getName() . '</p>';
    }
}
