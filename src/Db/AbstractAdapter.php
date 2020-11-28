<?php

declare(strict_types=1);

namespace EbookMarket\Db;

abstract class AbstractAdapter extends \EbookMarket\AbstractSingleton
{
	protected $connection;
	protected $config;

	protected function __construct(array $config)
	{
		$this->config = $config;
		$this->connect();
	}

	public function __destruct()
	{
		$this->closeConnection();
	}

	public function isConnected(): bool
	{
		return $this->connection !== null;
	}

	public function getConnection(): ?object
	{
		if (!$this->isConnected())
			$this->connect();
		return $this->connection;
	}

	public function closeConnection(): void
	{
		$this->disconnect();
		$this->connction = null;
	}

	public function query(string $query, ...$params): ?int
	{
		return $this->execute($query, $params)->rowsAffected();
	}

	public function fetchRow(string $query, ...$params): array
	{
		return $this->execute($query, $params)->fetch();
	}

	public function fetchColumn(string $query, array $params = [],
		int $column = 0)
	{
		return $this->execute($query, $params)->fetchColumn($column);
	}

	public function fetchAll(string $query, ...$params): array
	{
		return $this->execute($query, $params)->fetchAll();
	}

	public function fetchAllColumn(string $query, array $params = [],
		int $column = 0): array
	{
		return $this->execute($query, $params)->fetchAllColumn($column);
	}

	protected function execute(string $query,
		array $params = []): AbstractStatement
	{
		$this->connect();

		$statement = $this->createStatement($query, $params);
		$statement->execute();

		return $statement;
	}

	abstract protected function disconnect(): void;
	abstract protected function connect(): void;
	abstract protected function createStatement(string $query,
		?array $params): AbstractStatement;
}
