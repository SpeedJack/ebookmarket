<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

class ErrorPage extends AbstractPage
{
	protected $code;

	public function __construct(int $code = 200)
	{
		parent::__construct();
		$this->code = $code;
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

	public function getMessage(): string
	{
		switch ($this->code) {
		case 401:
		case 402:
		case 403:
			return __('You don\'t have the rights to view this page.');
		case 404:
			return __('The requested page could not be found.');
		case 405:
			return __('Bad request. Please, try again.');
		case 501:
			return __('Service unavailable.');
		case 400:
		case 500:
		default:
			if ($this->code > 399) {
				if ($this->code < 500)
					return __('Invalid request. Please, try again.');
				else if ($this->code < 600)
					return __('Unexpected server error. Please, try again later.');
			}
			return __('Something wrong happened. Please, try again later.');
		}
	}

	public function showError(): void
	{
		$params = [
			'title' => parent::htmlEscape($this->getTitle()),
			'message' => parent::htmlEscape($this->getMessage()),
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
