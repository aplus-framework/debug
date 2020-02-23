<?php namespace Framework\Debug;

/**
 * Class Timer.
 */
class Timer
{
	protected array $marks = [];
	protected int $tests_count = 1;

	/**
	 * Timer constructor.
	 */
	public function __construct()
	{
		$this->addMark('debug[start]');
	}

	/**
	 * @param int      $times
	 * @param callable $function
	 * @param bool     $flush
	 *
	 * @return array Two indexes - "memory" in MB and "time" in seconds
	 */
	public function test(int $times, callable $function, bool $flush = false) : array
	{
		if ( ! $flush) {
			\ob_start();
		}
		$this->tests_count++;
		$this->addMark('test[' . $this->tests_count . '][start]');
		for ($i = 0; $i < $times; $i++) {
			$function();
		}
		$this->addMark('test[' . ($this->tests_count) . '][end]');
		if ( ! $flush) {
			\ob_end_clean();
		}
		return $this->diff(
			'test[' . $this->tests_count . '][start]',
			'test[' . $this->tests_count . '][end]'
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
			'memory' => \memory_get_usage(), // / 1024 / 1024,
			'time' => \microtime(true),
		];
		return $this;
	}

	public function setMark(string $name, int $memory_get_usage, float $microtime)
	{
		$this->marks[$name] = [
			'memory' => $memory_get_usage, // / 1024 / 1024,
			'time' => $microtime,
		];
		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return array|false
	 */
	public function getMark(string $name)
	{
		return $this->marks[$name] ?? false;
	}

	/**
	 * @var bool $format
	 *
	 * @return array
	 */
	public function getMarks(bool $format = false) : array
	{
		$marks = $this->marks;
		if ($format) {
			foreach ($marks as &$mark) {
				$mark['memory'] = \number_format(($mark['memory']) / 1024 / 1024, 3) . ' MB';
				$mark['time'] = \number_format($mark['time'], 3) . ' s';
			}
		}
		return $marks;
	}

	/**
	 * @param string $from
	 * @param string $to
	 *
	 * @return array Two indexes - memory in MB and time in seconds
	 */
	public function diff(string $from, string $to) : array
	{
		return [
			'memory' => \number_format(
				(
					$this->marks[$to]['memory'] - $this->marks[$from]['memory']
				) / 1024 / 1024,
				3
			) . ' MB',
			'time' => \number_format(
				$this->marks[$to]['time'] - $this->marks[$from]['time'],
				3
			) . ' s',
		];
	}
}
