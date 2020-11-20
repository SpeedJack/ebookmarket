<?php

declare(strict_types=1);

namespace EbookMarket\Db;

class Exception extends \Exception
{
	public $sqlStateCode;
	public $statement;

	public function __construct(string $message, int $code,
		?string $sqlStateCode, $statement, \Throwable $previous = null)
	{
		$this->sqlStateCode = $sqlStateCode;
		$this->statement = $statement;
		$message .= __("\nSQL State Code: %s\nQuery: %s",
			$sqlStateCode, $statement->query);
		parent::__construct($message, 500, $previous);
	}
}
