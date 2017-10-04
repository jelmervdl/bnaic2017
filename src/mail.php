<?php

require_once 'src/format.php';

class Email
{
	static public function fromTemplate($path, array $data)
	{
		$template = file_get_contents($path);

		if ($template === false)
			throw new Exception('Could not read email template');

		list($headers, $body) = preg_split('/\R{2,}/', $template, 2);

		$message = new self();

		foreach (explode("\n", $headers) as $header) {
			list($name, $value) = preg_split('/\s*:\s*/', $header, 2);
			$message->headers[$name] = format_string($value, $data);
		}

		$message->body = format_string($body, $data);

		return $message;
	}

	public $headers = array();

	public $body = '';

	public function __construct()
	{

	}

	public function send($address)
	{
		$subject = null;

		$headers = array();

		foreach ($this->headers as $name => $value) {
			switch ($name) {
				case 'subject':
					$subject = $value;
					break;
				default:
					$headers[] = sprintf('%s: %s', $name, $value);
					break;
			}
		}

		return mail($address, $subject, $body, implode("\r\n", $headers));
	}
}