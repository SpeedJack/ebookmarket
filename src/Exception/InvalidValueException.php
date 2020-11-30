<?php

declare(strict_types=1);

namespace EbookMarket\Exception;

class InvalidValueException extends ClientException
{
	public function __construct(string $message, ?string $route = null,
		?string $userMessage = 'Invalid request.', int $code = 400,
		?\Throwable $previous = null)
	{
		parent::__construct($message, $route, $userMessage, $code,
			$previous);
	}
}
