<?php
namespace EbookMarket\Db;

abstract class AbstractStatement
{
	public $query;
	protected $_adapter;
	protected $_params;
	public function __construct(AbstractAdapter $adapter, $query, $params = [])
	{
		$this->_adapter = $adapter;
		$this->query = $query;
		$this->_params = is_array($params) ? $params : [$params];
	}

	public function fetchColumn($column = 0)
	{
		$values = $this->fetch();
		if (!$values)
			return false;
		return isset($values[$column]) ? $values[$column] : null;
	}

	public function fetchAll()
	{
		$output = [];
		while ($v = $this->fetch())
			$output[] = $v;
		return $output;
	}

	public function fetchAllColumn($column = 0)
	{
		$output = [];
		while (($v = $this->fetchColumn($column)) !== false)
			$output[] = $v;
		return $output;
	}
	protected function _getException($message, $code = 0, $sqlStateCode = null)
	{
		if (!$sqlStateCode || $sqlStateCode === '00000')
			switch ($code) {
			case 1062: $sqlStateCode = '23000'; break; // duplicate key
			}

		switch($sqlStateCode) {
		case '23000': $exClass = 'DuplicateKeyException'; break; // duplicate key
		default: $exClass = 'Exception'; break;
		}

		return new Exception($message, $code, $sqlStateCode, $this);

	}
	abstract public function prepare();

	abstract public function fetch();

	abstract public function execute();

	abstract public function rowsAffected();

}
