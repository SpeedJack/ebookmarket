<?php
namespace EbookMarket\Db;

class Exception extends \Exception
{

	public $sqlStateCode;
	
	public $statement;

	public function __construct($message, $code, $sqlStateCode, $statement,
		Exception $previous = null)
	{
		$this->sqlStateCode = $sqlStateCode;
		$this->statement = $statement;
		$message .= __("\nSQL State Code: %s\nQuery: %s",
			$sqlStateCode, $statement->query);
		parent::__construct($message, 500, $previous);
	}
}
