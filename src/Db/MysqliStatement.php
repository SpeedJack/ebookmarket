<?php
namespace EbookMarket\Db;

class MysqliStatement extends AbstractStatement
{

	protected $_metaFields;

	protected $_statement;
	
	protected $_keys = [];
	
	protected $_values = [];

	public function rowsAffected()
	{
		return $this->_statement ? $this->_statement->affected_rows : null;
	}

	public function prepare()
	{
		if ($this->_statement)
			return;

		$connection = $this->_adapter->getConnection();
		$this->_statement = $connection->prepare($this->query);
		if (!$this->_statement)
			throw $this->_getException($connection->error,
				$connection->errno, $connection->sqlstate);
	}

	
	public function execute()
	{
		if (!$this->_statement)
			$this->prepare();

		$types = '';
		$bind = [];
		foreach ($this->_params as &$param) {
			switch (gettype($param)) {
			case 'boolean':
			case 'integer':
			case 'NULL':
				$types .= 'i';
				break;
			case 'double':
				$types .= 'd';
				break;
			case 'array':
			case 'object':
			case 'resource':
			case 'resource (closed)':
			case 'unknown type':
				$types .= 'b';
				break;
			case 'string':
			case 'NULL':
			default:
				$types .= 's';
			}
			$bind[] =& $param;
		}
		if (!empty($types)) {
			array_unshift($bind, $types);
			if (!call_user_func_array([$this->_statement, 'bind_param'], $bind))
				throw $this->_getException(
					$this->_statement->error,
					$this->_statement->errno,
					$this->_statement->sqlstate
				);
		}

		$success = $this->_statement->execute();
		if (!$success)
			throw $this->_getException($this->_statement->error,
				$this->_statement->errno,
				$this->_statement->sqlstate);

		$meta = $this->_statement->result_metadata();
		if (!$meta)
			return $success;

		$this->_metaFields = $meta->fetch_fields();
		if (!$this->_statement->store_result())
			throw $this->_getException($this->_statement->error,
				$this->_statement->errno,
				$this->_statement->sqlstate);

		$keys = [];
		$values = [];
		$refs = [];
		$i = 0;
		foreach ($this->_metaFields as $field)
		{
			$keys[] =$field->name;
			$refs[] = null;
			$values[] =& $refs[$i];
			$i++;
		}

		$this->_keys = $keys;
		$this->_values = $values;

		if (!call_user_func_array([$this->_statement, 'bind_result'],
			$this->_values))
			throw $this->_getException($this->_statement->error,
				$this->_statement->errno,
				$this->_statement->sqlstate);

		return $success;
	}

	public function fetch()
	{
		if (!$this->_statement)
			throw new \LogicException('Trying to fetch values from an unprepared statement.');

		$success = $this->_statement->fetch();
		if ($success === false)
			throw $this->_getException($this->_statement->error,
				$this->_statement->errno,
				$this->_statement->sqlstate);
		if ($success === null)
			return [];

		$values = [];
		foreach ($this->_values as $v)
			$values[] = $v;
		/* Emulates PDO::FETCH_MODE fetch style */
		return array_merge($values, array_combine($this->_keys, $values));
	}

}
