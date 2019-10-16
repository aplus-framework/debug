<?php namespace Tests\Debug;

use Framework\Debug\Timer;
use PHPUnit\Framework\TestCase;

class TimerTest extends TestCase
{
	/**
	 * @var Timer
	 */
	protected $timer;

	public function setUp() : void
	{
		$this->timer = new Timer();
	}

	public function testA()
	{
		$this->assertEquals(['debug[start]'], \array_keys($this->timer->getMarks()));
		$this->timer->addMark('1');
		$this->timer->addMark('2');
		$this->assertEquals(['debug[start]', '1', '2'], \array_keys($this->timer->getMarks()));
	}

	public function testMark()
	{
		$this->timer->addMark('1');
		$this->assertEquals(['memory', 'time'], \array_keys($this->timer->getMark('1')));
	}

	public function testDiff()
	{
		$this->timer->addMark('1');
		$this->timer->addMark('2');
		$this->assertEquals(['memory', 'time'], \array_keys($this->timer->diff('1', '2')));
	}

	public function testTest()
	{
		$this->timer->test(10, function () {
			\strpos('abc', 'b');
		});
		$this->timer->test(10, function () {
			\stripos('abc', 'b');
		});
		$this->assertEquals([
			'debug[start]',
			'test[2][start]',
			'test[2][end]',
			'test[3][start]',
			'test[3][end]',
		], \array_keys($this->timer->getMarks(true)));
	}
}
