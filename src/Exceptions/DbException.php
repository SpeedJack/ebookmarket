<?php

declare(strict_types=1);

namespace EbookMarket\Exceptions;

use EbookMarket\Db\AbstractStatement;

class DbException extends ServerException
{
	protected $sqlStateCode;
	protected $statement;

	public function __construct(string $message, ?string $route = null,
		?string $sqlStateCode = null,
		?AbstractStatement $statement = null,
		?string $userMessage = null, int $code = 502,
		?\Throwable $previous = null)
	{
		if ($userMessage === null)
			$userMessage = 'There was an error with the database. Please, try again later.';
		parent::__construct($message, $route, $userMessage, $code,
			$previous);
		$this->sqlStateCode = $sqlStateCode;
		$this->statement = $statement;
	}

	public function __toString(): string
	{
		$str = parent::__toString();
		if ($this->sqlStateCode !== null)
			$str .= 'SQL State Code: ' . $this->sqlStateCode . PHP_EOL;
		if ($this->statement !== null)
			$str .= 'Query: ' . $this->statement->getQuery() . PHP_EOL;
		return $str;
	}
}
