<?php

declare(strict_types=1);

namespace EbookMarket\Db;

use EbookMarket\Exceptions\DbException;
use EbookMarket\Logger;

class MysqliAdapter extends AbstractAdapter
{
	protected function disconnect(): void
	{
		if (!$this->isConnected())
			return;
		$this->connection->close();
	}

	protected function createStatement(string $query,
		?array $params): AbstractStatement
	{
		return new MysqliStatement($this, $query, $params);
	}

	protected function connect(): void
	{
		if ($this->isConnected())
			return;

		$this->connection = new \mysqli($this->config['host'],
			$this->config['username'], $this->config['password'],
			$this->config['dbname'], $this->config['port']);

		if ($this->connection === false
			|| $this->connection->connect_error)
			throw new DbException('Unable to connect to the database ('
			. $this->connection->connect_errno . '): '
			. $this->connection->connect_error);

		if (!$this->connection->set_charset('utf8mb4'))
			Logger::notice('Unable to set MySQL server charset to utf8mb4.');
	}
}
