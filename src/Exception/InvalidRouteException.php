<?php

declare(strict_types=1);

namespace EbookMarket\Exception;

class InvalidRouteException extends ClientException
{
	public function __construct(string $route, ?string $message = null,
		?string $userMessage = null, int $code = 404,
		?\Throwable $previous = null)
	{
		if ($message === null)
			$message = 'Invalid route.';
		if ($userMessage === null)
			$userMessage = 'The requested page could not be found.';
		parent::__construct($message, $route, $userMessage, $code,
			$previous);
	}
}
