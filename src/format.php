<?php


function format_evaluate($match, array $data)
{
	$path = explode('[', $match['key']);

	// remove ] from all 1..n components
	for ($i = 1; $i < count($path); ++$i)
		$path[$i] = substr($path[$i], 0, -1);

	// Step 0
	$value = $data;

	// Step 1..n (if there are any)
	foreach ($path as $step) {
		if (isset($value[$step])) {
			$value = $value[$step];
		} else {
			$value = null;
			break;
		}
	}

	// If there is a modifier, apply it
	if (!empty($match['formatter'])) {
		$args = !empty($match['args']) ? eval('return array(' . $match['args'] . ');') : array();
		array_unshift($args, $value);
		$value = call_user_func_array($match['formatter'], $args);
	}

	return $value;
}

class FormatterCallback
{
	public $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function __invoke($match)
	{
		return format_evaluate($match, $this->data);	
	}
}

class FormatterSectionCallback
{
	public $data;

	public $sections;

	public function __construct(array $data)
	{
		$this->data = $data;

		$this->sections = array();
	}

	public function __invoke($match)
	{
		$index = sprintf('@@%s@@', uniqid());

		$test = format_evaluate($match, $this->data);

		$this->sections[$index] = $test
			? format_string($match['body'], $this->data)
			: '';

		return $index;
	}

	public function finish($formatted)
	{
		return str_replace(
			array_keys($this->sections),
			array_values($this->sections),
			$formatted);
	}
}

function format_string($format, $params)
{
	if (!(is_array($params) || $params instanceof ArrayAccess))
		throw new InvalidArgumentException('$params has to behave like an array');

	// Find and remove the {% if $.. %} .. {% endif %} sections.
	$format = preg_replace_callback('/\{%\s*if \$(?<key>[a-z][a-z0-9_]*(?:\[[a-z0-9_]+\])*)(?:\|(?<formatter>[a-z_]+)(\((?<args>[^\)]*)\))?)?\s*%\}(?<body>.+?)\{%\s*endif\s*%\}/is',
		$if_sections = new FormatterSectionCallback($params),
		$format);

	// Now replace all $xx|yyy placeholders
	$formatted = preg_replace_callback(
		'/\$(?<key>[a-z][a-z0-9_]*(?:\[[a-z0-9_]+\])*)(?:\|(?<formatter>[a-z_]+)(\((?<args>[^\)]*)\))?)?/i',
		new FormatterCallback($params),
		$format);

	// Return the sections we removed earlier, and are now also parsed (or left out)
	return $if_sections->finish($formatted);
}

function _if($test, $true, $false) {
	return $test ? $true : $false;
}

// test case:
// var_dump(format_string('This is a test $test|_if("true", "false") etceter(a)', ['test' => 'd"ata']));

// var_dump(format_string('This is another test that{% if $test %} should be $value{% endif %}?', ['test' => false, 'value' => 'five']));
