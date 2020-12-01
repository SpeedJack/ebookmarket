<?php

declare(strict_types=1);

namespace EbookMarket\Exceptions;

class UserAuthenticationException extends ClientException
{
	public function __construct(string $route, ?string $message = null,
		?string $userMessage = null, int $code = 401,
		?\Throwable $previous = null)
	{
		if ($message === null)
			$message = 'User not authenticated.';
		if ($userMessage === null)
			$userMessage = 'You must log-in to view this page.';
		parent::__construct($message, $route, $userMessage, $code,
			$previous);
	}
}
