<?php

declare(strict_types=1);

namespace EbookMarket\Exception;

use EbookMarket\Db\AbstractStatement;

class DuplicateKeyException extends ServerException
{
	public function __construct(string $message, ?string $route = null,
		?AbstractStatement $statement = null,
		?string $userMessage = null, int $code = 502,
		?\Throwable $previous = null)
	{
		parent::__construct($message, $route, '23000', $statement,
			$userMessage, $code, $previous);
	}
}
