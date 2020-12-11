<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\Exceptions\Exception;

class ErrorPage extends AbstractPage
{
	protected $exception;

	public function __construct(\Throwable $exception)
	{
		parent::__construct();
		$this->exception = $exception;
	}

	protected static function getTitle(int $code): string
	{
		switch ($code) {
		case 401:
			return 'Unauthorized';
		case 402:
			return 'Payment Required';
		case 403:
			return 'Forbidden';
		case 404:
			return 'Not Found';
		case 405:
			return 'Method Not Allowed';
		case 501:
			return 'Not Implemented';
		case 400:
		case 500:
		default:
			if ($code > 399) {
				if ($code < 500)
					return 'Bad Request';
				else if ($code < 600)
					return 'Internal Server Error';
			}
			return 'Unknown Error';
		}
	}

	protected static function getMessage(int $code): string
	{
		switch ($code) {
		case 401:
		case 402:
		case 403:
			return 'You don\'t have the rights to view this page.';
		case 404:
			return 'The requested page could not be found.';
		case 405:
			return 'Bad request. Please, try again.';
		case 501:
			return 'Service unavailable.';
		case 400:
		case 500:
		default:
			if ($code > 399) {
				if ($code < 500)
					return 'Invalid request. Please, try again.';
				else if ($code < 600)
					return 'Unexpected server error. Please, try again later.';
			}
			return 'Something wrong happened. Please, try again later.';
		}
	}

	public function showError(): void
	{
		$this->exitOnShow = false;
		$code = 500;
		if ($this->exception instanceof Exception) {
			$code = $this->exception->getCode();
			$message = $this->exception->getUserMessage()
				?? static::getMessage($code);
		} else {
			$message = static::getMessage($code);
		}
		$title = parent::htmlEscape(static::getTitle($code));
		$message = parent::htmlEscape($message);
		$this->setTitle($code . ' - ' . $title);
		if ($this->visitor->isAjax()) {
			$this->modalMessage('Error', $message, false);
			return;
		}
		http_response_code($code);
		$this->show('message', [
			'title' => $title,
			'message' => $message,
		]);
	}

	public function actionIndex(): void
	{
		$this->redirectHomePermanently();
	}

	protected function buildSidebar(): ?string
	{
		return null;
	}
}
