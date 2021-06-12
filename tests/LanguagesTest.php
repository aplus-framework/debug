<?php namespace Tests\Debug;

use PHPUnit\Framework\TestCase;

final class LanguagesTest extends TestCase
{
	protected string $langDir = __DIR__ . '/../src/Languages/';

	protected function getCodes()
	{
		$codes = \array_filter(\glob($this->langDir . '*'), 'is_dir');
		$length = \strlen($this->langDir);
		foreach ($codes as &$dir) {
			$dir = \substr($dir, $length);
		}
		return $codes;
	}

	public function testKeys() : void
	{
		$rules = require $this->langDir . 'en/debug.php';
		$rules = \array_keys($rules);
		\sort($rules);
		foreach ($this->getCodes() as $code) {
			$lines = require $this->langDir . $code . '/debug.php';
			$lines = \array_keys($lines);
			\sort($lines);
			$this->assertEquals($rules, $lines, 'Language: ' . $code);
		}
	}
}
