<?php

declare(strict_types=1);

namespace EbookMarket\Db;

class MysqliAdapter extends AbstractAdapter
{
	protected function disconnect(): void
	{
		if (!$this->isConnected())
			return;
		$this->connection->close();
	}

	protected function getStatementClass(): string
	{
		return __NAMESPACE__ . '\MysqliStatement';
	}

	protected function connect(): void
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

		if (!$this->connection->set_charset('utf8'))
			return;
	}
}
