<?php

declare(strict_types=1);

namespace EbookMarket\Db;

class PdoAdapter extends AbstractAdapter
{
	public function disconnect(): void
	{
	}

	protected function getStatementClass(): string
	{
		return __NAMESPACE__ . '\PdoStatement';
	}

	protected function connect(): void
	{
		if ($this->isConnected())
			return;

		$this->connection = new \PDO('mysql:host=' . $this->config['host'] .
			';port=' . $this->config['port'] . ';dbname=' .
			$this->config['dbname'] . ';charset=utf8',
			$this->config['username'], $this->config['password'],
			[\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
	}
}
