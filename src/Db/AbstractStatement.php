<?php

declare(strict_types=1);

namespace EbookMarket\Db;

use EbookMarket\Exceptions\{
	DbException,
	DuplicateKeyException,
};

abstract class AbstractStatement
{
	protected $query;
	protected $adapter;
	protected $params;

	public function __construct(AbstractAdapter $adapter,
		string $query, ?array $params)
	{
		$this->adapter = $adapter;
		$this->query = $query;
		$this->params = $params ?? [];
	}

	public function fetchColumn(int $column = 0)
	{
		$values = $this->fetch();
		if (!$values)
			return false;
		return isset($values[$column]) ?? null;
	}

	public function fetchAll(): array
	{
		$output = [];
		while ($v = $this->fetch())
			$output[] = $v;
		return $output;
	}

	public function fetchAllColumn(int $column = 0): array
	{
		$output = [];
		while (($v = $this->fetchColumn($column)) !== false)
			$output[] = $v;
		return $output;
	}

	public function getQuery(): string
	{
		return $this->query;
	}

	protected function getException(string $message, int $code = 0,
		?string $sqlStateCode = null): DbException
	{
		if (!$sqlStateCode || $sqlStateCode === '00000')
			switch ($code) {
			case 1062:
				$sqlStateCode = '23000';
				break;
			default:
			}

		switch($sqlStateCode) {
		case '23000':
			return new DuplicateKeyException($message, null, $this);
		default:
			return new DbException($message, null, $sqlStateCode, $this);
		}
	}

	abstract public function prepare(): void;
	abstract public function fetch(): array;
	abstract public function execute(): ?bool;
	abstract public function rowsAffected(): ?int;
}
