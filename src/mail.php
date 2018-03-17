<?php

require_once 'src/format.php';

require_once 'src/class.phpmailer.php';
require_once 'src/class.smtp.php';

function get_mailer()
{
	$auth = require '../data/smtp.php';
	
	$mailer = new PHPMailer(true);

	$mailer->SMTPDebug = 0;

	$mailer->isSMTP();
	$mailer->Host = 'smtp.gmail.com';
	$mailer->Port = 587;
	$mailer->SMTPSecure = 'tls';
	$mailer->SMTPAuth = true;
	$mailer->Username = $auth['username'];
	$mailer->Password = $auth['password'];
	
	return $mailer;
}

function parse_mail_address($address) {
	return preg_match('/^(.+?)\s+\<(.+?)\>$/', $address, $match)
		? array('address' => $match[2], 'name' => $match[1])
		: array('address' => $address, 'name' => '');
}

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
			$message->headers[] = array($name, format_string($value, $data));
		}

		$message->body = format_string($body, $data);

		return $message;
	}

	public $headers = array();

	public $body = '';

	public function send()
	{
		$mailer = get_mailer();

		foreach ($this->headers as $header) {
			list($name, $value) = $header;
			switch ($name) {
				case 'Subject':
					$mailer->Subject = $value;
					break;
				case 'From':
					$from = parse_mail_address($value);
					$mailer->setFrom($from['address'], $from['name']);
					break;
				case 'To':
					$to = parse_mail_address($value);
					$mailer->addAddress($to['address'], $to['name']);
					break;
				case 'CC':
					$cc = parse_mail_address($value);
					$mailer->addCC($cc['address'], $cc['name']);
					break;
				case 'BCC':
					$bcc = parse_mail_address($value);
					$mailer->addBCC($bcc['address'], $bcc['name']);
					break;
			}
		}

		$mailer->isHTML(true);
		$mailer->CharSet = 'UTF-8';
		$mailer->Body = $this->body;

		return $mailer->send();
	}
}