<?php

namespace EbookMarket\Db;

abstract class AbstractAdapter extends \EbookMarket\AbstractSingleton
{

	protected $connection;
	protected $statementClass;
	protected $config;

	protected function __construct(array $config)
	{
		$this->statementClass = $this->getStatementClass();

		$this->config = $config;
		$this->connect();
	}

	public function isConnected()
	{
		return $this->connection !== null;
	}

	public function getConnection()
	{
		if (!$this->connection)
			$this->connect();
		return $this->connection;
	}

	public function query($query, ...$params)
	{
		return $this->_query($query, $params)->rowsAffected();
	}

	public function fetchRow($query, ...$params)
	{
		return $this->_query($query, $params)->fetch();
	}

	public function fetchColumn($query, $params = [], $column = 0)
	{
		return $this->_query($query, $params)->fetchColumn($column);
	}
	
	public function fetchAll($query, ...$params)
	{
		return $this->_query($query, $params)->fetchAll();
	}

	public function fetchAllColumn($query, $params = [], $column = 0)
	{
		return $this->_query($query, $params)->fetchAllColumn($column);
	}

	private function _query($query, $params = [])
	{
		$this->connect();

		$class = $this->statementClass;

		$statement = new $class($this, $query, $params);
		$statement->execute();

		return $statement;
	}

	abstract public function closeConnection();

	abstract protected function connect();

	abstract protected function getStatementClass();

}
