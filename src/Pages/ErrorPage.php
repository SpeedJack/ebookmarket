<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

class ErrorPage extends AbstractPage
{
	protected $code;
	protected $message;

	public function __construct(int $code = 200, string $message = '')
	{
		parent::__construct();
		$this->code = $code;
		$this->message = $message;
	}

	public function getTitle(): string
	{
		switch ($this->code) {
		case 401:
			return __('Unauthorized');
		case 402:
			return __('Payment Required');
		case 403:
			return __('Forbidden');
		case 404:
			return __('Not Found');
		case 405:
			return __('Method Not Allowed');
		case 501:
			return __('Not Implemented');
		case 400:
		case 500:
		default:
			if ($this->code > 399) {
				if ($this->code < 500)
					return __('Bad Request');
				else if ($this->code < 600)
					return __('Internal Server Error');
			}
			return __('Unknown Error');
		}
	}

	public function showError(): void
	{
		/* TODO: use template */
		$title = $this->getTitle();
		http_response_code($this->code);
		echo <<<EOT
<!DOCTYPE html>
<html>
	<head>
		<title>{$this->code} - {$title}</title>
	</head>
	<body>
		<h1>{$title}</h1>
		<p>{$this->message}</p>
	</body>
</html>
EOT;
	}

	public function actionIndex(): void
	{
		/* TODO: reroute to home */
		throw new \BadMethodCallException('Not Implemented');
	}
}
