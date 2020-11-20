<?php
namespace EbookMarket\Db;

class PdoStatement extends AbstractStatement
{

	protected $_statement;

	public function rowsAffected()
	{
		return $this->_statement ? $this->_statement->rowCount() : null;
	}

	public function prepare()
	{
		if ($this->_statement)
			return;

		$connection = $this->_adapter->getConnection();
		try {
			$this->_statement = $connection->prepare($this->query);
		} catch (\PDOException $e) {
			throw $this->_getException($e->errorInfo[2],
				$e->errorInfo[1], $e->errorInfo[0]);
		}
	}

	public function execute()
	{
		if (!$this->_statement)
			$this->prepare();

		$index = 1;
		foreach ($this->_params as &$param) {
			switch (gettype($param)) {
			case 'boolean':
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
				$this->_statement->bindParam($index, $param, $type);
			} catch (\PDOException $e) {
				throw $this->_getException($e->errorInfo[2],
					$e->errorInfo[1], $e->errorInfo[0]);
			}
			$index++;
		}

		try {
			$this->_statement->execute();
		} catch (\PDOException $e) {
			throw $this->_getException($e->errorInfo[2],
				$e->errorInfo[1], $e->errorInfo[0]);
		}
		return true;
	}

	public function fetch()
	{
		if (!$this->_statement)
			throw new LogicException('Trying to fetch values from an unprepared statement.');

		try {
			$values = $this->_statement->fetch(\PDO::FETCH_BOTH);
		} catch (\PDOException $e) {
			throw $this->_getException($e->errorInfo[2],
				$e->errorInfo[1], $e->errorInfo[0]);
		}
		if ($values === false || $values === null)
			return [];
		foreach ($values as $key => $value)
			if (is_numeric($value))
				$values[$key] = ctype_digit($value) ? (int)$value : (float)$value;
			else if ($value === 'NULL')
				$values[$key] = null;

		return $values;
	}

}
