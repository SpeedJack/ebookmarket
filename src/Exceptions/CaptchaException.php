<?php

declare(strict_types=1);

namespace EbookMarket\Exceptions;

class CaptchaException extends ClientException
{
	public function __construct(string $route, ?string $message = null,
		?string $userMessage = null, int $code = 403,
		?\Throwable $previous = null)
	{
		if ($message === null)
			$message = 'CAPTCHA validation failed.';
		if ($userMessage === null)
			$userMessage = 'CAPTCHA validation failed. Please, try again.';
		parent::__construct($message, $route, $userMessage, $code,
			$previous);
	}
}
