<?php

declare(strict_types=1);

namespace EbookMarket\Exception;

class InvalidMethodException extends ClientException
{
	public function __construct(string $message, ?string $route = null,
		?string $userMessage = 'Invalid method.',
		int $code = 405, ?\Throwable $previous = null)
	{
		parent::__construct($message, $route, $userMessage, $code,
			$previous);
	}
}
