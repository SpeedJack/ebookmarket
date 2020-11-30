<?php

declare(strict_types=1);

namespace EbookMarket\Db;

use \EbookMarket\AppException;

class Exception extends \Exception implements AppException
{
	protected $sqlStateCode;
	protected $statement;

	public function __construct(string $message, int $code,
		?string $sqlStateCode, AbstractStatement $statement,
		?\Throwable $previous = null)
	{
		$this->sqlStateCode = $sqlStateCode;
		$this->statement = $statement;
		$message .= PHP_EOL . "SQL State Code: $sqlStateCode" . PHP_EOL
			. 'Query: ' . $statement->getQuery();
		parent::__construct($message, 500, $previous);
	}

	public function getSqlStateCode(): string
	{
		return $this->sqlStateCode;
	}

	public function getStatement(): AbstractStatement
	{
		return $this->statement;
	}
}
