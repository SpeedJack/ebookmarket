<?php
namespace EbookMarket\Db;

class PdoAdapter extends AbstractAdapter
{

	public function closeConnection()
	{
	}

	protected function getStatementClass()
	{
		return __NAMESPACE__ . '\PdoStatement';
	}

	protected function connect()
	{
		if ($this->isConnected())
			return;

		$this->connection = new \PDO('mysql:host=' . $this->config['host'] .
			';port=' . $this->config['port'] . ';dbname=' .
			$this->config['dbname'] . ';charset=' .
			$this->config['charset'], $this->config['username'],
			$this->config['password'],
			[\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
	}

}
