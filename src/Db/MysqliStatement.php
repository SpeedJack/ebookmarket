<?php

declare(strict_types=1);

namespace EbookMarket\Db;

class MysqliStatement extends AbstractStatement
{
	protected $metaFields;
	protected $statement;
	protected $keys = [];
	protected $values = [];

	public function rowsAffected(): ?int
	{
		return $this->statement ? $this->statement->affected_rows : null;
	}

	public function prepare(): void
	{
		if ($this->statement)
			return;

		$connection = $this->adapter->getConnection();
		$this->statement = $connection->prepare($this->query);
		if (!$this->statement)
			throw $this->getException($connection->error,
				$connection->errno, $connection->sqlstate);
	}


	public function execute(): ?bool
	{
		if (!$this->statement)
			$this->prepare();

		$types = '';
		$bind = [];
		foreach ($this->params as &$param) {
			switch (gettype($param)) {
			case 'boolean':
			case 'integer':
				$types .= 'i';
				break;
			case 'double':
				$types .= 'd';
				break;
			case 'array':
			case 'object':
			case 'resource':
			case 'resource (closed)':
				$types .= 'b';
				break;
			case 'string':
			case 'unknown type':
			case 'NULL':
			default:
				$types .= 's';
			}
			$bind[] =& $param;
		}
		if (!empty($types)) {
			array_unshift($bind, $types);
			if (!call_user_func_array(
				[$this->statement, 'bind_param'], $bind))
				throw $this->getException(
					$this->statement->error,
					$this->statement->errno,
					$this->statement->sqlstate
				);
		}

		$success = $this->statement->execute();
		if (!$success)
			throw $this->getException($this->statement->error,
				$this->statement->errno,
				$this->statement->sqlstate);

		$meta = $this->statement->result_metadata();
		if (!$meta)
			return $success;

		$this->metaFields = $meta->fetch_fields();
		if (!$this->statement->store_result())
			throw $this->getException($this->statement->error,
				$this->statement->errno,
				$this->statement->sqlstate);

		$keys = [];
		$values = [];
		$refs = [];
		$i = 0;
		foreach ($this->metaFields as $field)
		{
			$keys[] =$field->name;
			$refs[] = null;
			$values[] =& $refs[$i];
			$i++;
		}

		$this->keys = $keys;
		$this->values = $values;

		if (!call_user_func_array([$this->statement, 'bind_result'],
			$this->values))
			throw $this->getException($this->statement->error,
				$this->statement->errno,
				$this->statement->sqlstate);

		return $success;
	}

	public function fetch(): array
	{
		if (!$this->statement)
			throw new \LogicException(
				__('Trying to fetch values from an unprepared statement.'));

		$success = $this->statement->fetch();
		if ($success === false)
			throw $this->getException($this->statement->error,
				$this->statement->errno,
				$this->statement->sqlstate);
		if ($success === null)
			return [];

		$values = [];
		foreach ($this->values as $v)
			$values[] = $v;
		/* Emulates PDO::FETCH_MODE fetch style */
		return array_merge($values,
			array_combine($this->keys, $values));
	}
}
