<?php

class FormatterCallback
{
	public $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function __invoke($match) {
		$path = explode('[', $match[1]);

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
		if (isset($match[2]))
			$value = call_user_func($match[2], $value);

		return $value;
	}
}

function format_string($format, $params)
{
	if (!(is_array($params) || $params instanceof ArrayAccess))
		throw new InvalidArgumentException('$params has to behave like an array');

	return preg_replace_callback(
		'/\$([a-z][a-z0-9_]*(?:\[[a-z0-9_]+\])*)(?:\|([a-z_]+))?/i',
		new FormatterCallback($params),
		$format);
}