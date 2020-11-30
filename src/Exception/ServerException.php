<?php

declare(strict_types=1);

namespace EbookMarket\Exception;

class ServerException extends Exception
{
	public function __construct(string $message, ?string $route = null,
		?string $userMessage = 'Unexpected server error. Please, try again later.',
		int $code = 500, ?\Throwable $previous = null)
	{
		parent::__construct($message, $route, $userMessage, $code,
			$previous);
	}
}
