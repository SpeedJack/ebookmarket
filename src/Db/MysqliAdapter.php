<?php
namespace EbookMarket\Db;


class MysqliAdapter extends AbstractAdapter
{
	public function closeConnection()
	{
		if ($this->isConnected())
			$this->connection->close();
		$this->connecttion = null;
	}

	protected function getStatementClass()
	{
		return __NAMESPACE__ . '\MysqliStatement';
	}


	protected function connect()
	{
		if ($this->isConnected())
			return;

		$this->connection = new \mysqli($this->config['host'],
			$this->config['username'], $this->config['password'],
			$this->config['dbname'], $this->config['port']);

		if ($this->connection->connect_errno)
			throw new \RuntimeException(
				$this->connection->connect_error,
				$this->connection->connect_errno);

		$this->connection->set_charset($this->config['charset']);
	}


}
