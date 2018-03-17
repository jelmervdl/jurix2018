<?php

class FormatterCallback
{
	public $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function __invoke($match) {
		$path = explode('[', $match['key']);

		// remove ] from all 1..n components
		for ($i = 1; $i < count($path); ++$i)
			$path[$i] = substr($path[$i], 0, -1);

		// Step 0
		$value = $this->data;

		foreach ($path as $step) {
			if (isset($value[$step])) {
				$value = $value[$step];
			} else {
				$value = null;
				break;
			}
		}

		// If there is a modifier, apply it
		if (isset($match['formatter'])) {
			$args = !empty($match['args']) ? eval('return array(' . $match['args'] . ');') : array();
			array_unshift($args, $value);
			$value = call_user_func_array($match['formatter'], $args);
		}

		return $value;
	}
}

function format_string($format, $params)
{
	if (!(is_array($params) || $params instanceof ArrayAccess))
		throw new InvalidArgumentException('$params has to behave like an array');

	return preg_replace_callback(
		'/\$(?<key>[a-z][a-z0-9_]*(?:\[[a-z0-9_]+\])*)(?:\|(?<formatter>[a-z_]+)(\((?<args>[^\)]*)\))?)?/i',
		new FormatterCallback($params),
		$format);
}

function _if($test, $true, $false) {
	return $test ? $true : $false;
}

// test case:
//var_dump(format_string('This is a test $test|_if("true", "false") etceter(a)', ['test' => 'd"ata']));
