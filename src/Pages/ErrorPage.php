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
		$params = [
			'title' => parent::htmlEscape($this->getTitle()),
			'message' => parent::htmlEscape($this->message),
		];
		http_response_code($this->code);
		$this->setTitle($this->code . ' - ' . $this->getTitle());
		$this->show('error', $params);
	}

	public function actionIndex(): void
	{
		$this->redirectHomePermanently();
	}
}
