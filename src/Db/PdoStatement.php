<?php

declare(strict_types=1);

namespace EbookMarket\Db;

class PdoStatement extends AbstractStatement
{
	public function rowsAffected(): ?int
	{
		return $this->statement ? $this->statement->rowCount() : null;
	}

	public function prepare(): void
	{
		if ($this->statement)
			return;

		$connection = $this->adapter->getConnection();
		try {
			$this->statement = $connection->prepare($this->query);
		} catch (\PDOException $e) {
			throw $this->getException($e->errorInfo[2],
				$e->errorInfo[1], $e->errorInfo[0]);
		}
	}

	public function execute(): ?bool
	{
		if (!$this->statement)
			$this->prepare();

		$index = 1;
		foreach ($this->params as &$param) {
			switch (gettype($param)) {
			case 'bool':
				$type = \PDO::PARAM_BOOL;
				break;
			case 'integer':
				$type = \PDO::PARAM_INT;
				break;
			case 'double':
			case 'string':
				$type = \PDO::PARAM_STR;
				break;
			case 'array':
			case 'object':
			case 'resource':
			case 'resource (closed)':
			case 'unknown type':
				$type = \PDO::PARAM_LOB;
				break;
			case 'NULL':
			default:
				$type = \PDO::PARAM_NULL;
			}
			try {
				$this->statement->bindParam($index,
					$param, $type);
			} catch (\PDOException $e) {
				throw $this->getException($e->errorInfo[2],
					$e->errorInfo[1], $e->errorInfo[0]);
			}
			$index++;
		}

		try {
			$this->statement->execute();
		} catch (\PDOException $e) {
			throw $this->getException($e->errorInfo[2],
				$e->errorInfo[1], $e->errorInfo[0]);
		}
		return true;
	}

	public function fetch(): array
	{
		if (!$this->statement)
			throw new LogicException(
				__('Trying to fetch values from an unprepared statement.'));

		try {
			$values = $this->statement->fetch(\PDO::FETCH_BOTH);
		} catch (\PDOException $e) {
			throw $this->getException($e->errorInfo[2],
				$e->errorInfo[1], $e->errorInfo[0]);
		}
		if ($values === false || $values === null)
			return [];
		foreach ($values as $key => $value)
			if (is_numeric($value))
				$values[$key] = ctype_digit($value) ?
					(int)$value : (float)$value;
			else if ($value === 'NULL')
				$values[$key] = null;

		return $values;
	}
}
