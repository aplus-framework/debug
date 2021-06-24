<?php namespace Framework\Debug;

/**
 * Class Timer.
 */
class Timer
{
	/**
	 * @var array<string,array>
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
	 * @return $this
	 */
	public function addMark(string $name)
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
	 * @return $this
	 */
	public function setMark(string $name, int $memoryUsage, float $microtime)
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
	 * @var bool $format
	 *
	 * @return array<string,array>
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
	public function diff(string $from, string $to) : array
	{
		$number = $this->marks[$to]['memory'] - $this->marks[$from]['memory'];
		$number = \number_format($number / 1024 / 1024, 3);
		$diff['memory'] = $number . ' MB';
		$number = $this->marks[$to]['time'] - $this->marks[$from]['time'];
		$number = \number_format($number, 3);
		$diff['time'] = $number . ' s';
		return $diff;
	}
}
