<?php

declare(strict_types=1);

namespace EbookMarket\Db;

class PdoAdapter extends AbstractAdapter
{
	protected function disconnect(): void
	{
		$this->connection = null;
	}

	protected static function getStatementClass(): string
	{
		return __NAMESPACE__ . '\\PdoStatement';
	}

	protected function createStatement(string $query,
		?array $params): AbstractStatement
	{
		return new PdoStatement($this, $query, $params);
	}

	protected function connect(): void
	{
		if ($this->isConnected())
			return;

		$this->connection = new \PDO(
			'mysql:host=' . $this->config['host'] .
			';port=' . $this->config['port'] . ';dbname=' .
			$this->config['dbname'] . ';charset=utf8mb4',
			$this->config['username'], $this->config['password'],
			[\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
	}
}
