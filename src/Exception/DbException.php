<?php

declare(strict_types=1);

namespace EbookMarket\Exception;

class DbException extends ServerException
{
	protected $sqlStateCode;
	protected $statement;

	public function __construct(string $message, ?string $route = null,
		?string $sqlStateCode = null,
		?AbstractStatement $statement = null,
		?string $userMessage = 'There was an error with the database. Please, try again later.',
		int $code = 502, ?\Throwable $previous = null)
	{
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
