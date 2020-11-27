<?php

declare(strict_types=1);

namespace EbookMarket\Entity;

use \EbookMarket\App;

abstract class AbstractEntity
{
	protected const INT = 0;
	protected const UINT = 1;
	protected const BOOL = 2;
	protected const STR = 3;

	protected $values = [];
	protected $newvalues = [];
	protected $gettercache = [];
	protected $deleted = false;
	private $structure;
	protected $app;
	protected $db;

	public function __construct(?array $data = null)
	{
		$this->structure = static::getStructure();
		if (!$this->hasValidStructure())
			throw new \LogicException(__('Invalid structure.'));
		$this->app = App::getInstance();
		$this->db = $this->app->db();
		if (isset($data) && !empty($data))
			foreach ($this->structure['columns'] as $name => $col)
				if (array_key_exists($name, $data))
					$this->values[$name] = $data[$name];
	}

	private function hasValidStructure(): bool
	{
		if (!isset($this->structure['table'])
			|| !is_scalar($this->structure['table'])
			|| !isset($this->structure['columns'])
			|| !is_array($this->structure['columns'])
			|| empty($this->structure['columns']))
			return false;
		$hasid = false;
		foreach ($this->structure['columns'] as $name => $col) {
			if (!is_array($col) || !isset($col['type']))
				return false;
			if (strcmp($name, 'id') === 0)
				$hasid = true;
		}
		return $hasid;
	}

	public function __set(string $name, $value): void
	{
		if ($this->deleted)
			throw new \LogicException(
				__('Can not set the \'%s\' attribute on the deleted entity \'%s\'.',
				$name, get_class($this)));

		if (!$this->validateValue($name, $value))
			throw new \RuntimeException(
				__('Invalid value for \'%s\' in entity \'%s\'.',
				$name, get_class($this)));

		$setter = 'set' . ucfirst($name);
		if (method_exists($this, $setter)) {
			$this->$setter($value);
			return;
		}

		$this->setValue($name, $value);
	}

	protected function validateValue(string $name, $value): bool
	{
		$validator = 'validate' . ucfirst($name);
		if (!method_exists($this, $validator))
			return true;
		return $this->$validator($value);
	}

	protected function setValue(string $name, $value): void
	{
		if (!isset($this->structure['columns'][$name]))
			throw new \LogicException(
				__('Attribute \'%s\' does not exists in entity \'%s\'.',
				$name, get_class($this)));

		$column = $this->structure['columns'][$name];

		if (!array_key_exists($name, $this->values)
			|| $this->values[$name] !== $value)
			$this->newvalues[$name] = $value;
		else if (array_key_exists($name, $this->newvalues))
			unset($this->newvalues[$name]);
	}

	public function __get(string $name)
	{
		$getter = 'get' . ucfirst($name);
		if (method_exists($this, $getter)) {
			if (!array_key_exists($name, $this->gettercache))
				$this->gettercache[$name] = $this->$getter();
			return $this->gettercache[$name];
		}

		if (!isset($this->structure['columns'][$name]))
			throw new \LogicException(
				__('Attribute \'%s\' does not exists in entity \'%s\'.',
				$name, get_class($this)));
		return $this->getValue($name);
	}

	protected function getValue(string $name)
	{
		return array_key_exists($name, $this->newvalues)
			? $this->newvalues[$name] : $this->getExistingValue($name);
	}

	protected function getExistingValue(string $name)
	{
		return $this->values[$name] ?? null;
	}

	public function __isset(string $name): bool
	{
		return isset($this->newvalues[$name]) || isset($this->values[$name]);
	}

	public function __unset(string $name): void
	{
		if ($this->deleted)
			return;

		$setter = 'set' . ucfirst($name);
		if (method_exists($this, $setter))
			$this->$setter(null);

		if (array_key_exists($name, $this->newvalues)) {
			if (isset($this->values[$name]))
				$this->newvalues[$name] = null;
			else
				unset($this->newvalues[$name]);
		}
	}

	protected function preDelete(): void {}

	public function delete(): void
	{
		if ($this->deleted)
			return;
		if (!isset($this->values['id']))
			throw new \LogicException(
				__('Entity \'%s\' does not define an id.', get_class($this)));

		$this->preDelete();
		$this->db->query('DELETE FROM `' . $this->structure['table'] . '`'
		       . ' WHERE id=?', $this->values['id']);
		$this->deleted = true;
		$this->newvalues = [];
		$this->gettercache = [];
		$this->postDelete();
	}

	protected function postDelete(): void {}

	protected function preSave(): void {}

	public function save(): void
	{
		if ($this->deleted)
			throw new \LogicException(
				__('Trying to save the deleted entity \'%s\'.',
				get_class($this)));

		if (!$this->isInsert() && !$this->isUpdate())
			return;

		$this->preSave();
		if ($this->isUpdate())
			$this->update();
		else if ($this->isInsert())
			$this->insert();
		$this->postSave();
	}

	protected function postSave(): void {}

	protected function insert(): void
	{
		if ($this->deleted)
			throw new \LogicException(
				__('Can not insert deleted entity \'%s\'.', get_class($this)));

		$query = 'INSERT INTO `' . $this->structure['table'] . '`(';
		$placeholders = '';
		$values = [];
		$i = 0;
		foreach ($this->structure['columns'] as $name => $col) {
			if (isset($col['auto_increment'])
				&& !array_key_exists($name, $this->newvalues))
				continue;
			if (!array_key_exists($name, $this->newvalues)) {
				if (!array_key_exists('default', $col)) {
					if (isset($col['required']))
						throw new \LogicException(
							__('Required field \'%s\' not set for entity \'%s\'.',
							$name, get_class($this)));
					continue;
				}
				$values[] = $col['default'];
			} else {
				$values[] = $this->newvalues[$name];
			}

			if ($i > 0) {
				$query .= ', ';
				$placeholders .= ', ';
			}
			$query .= "`$name`";
			$placeholders .= '?';
			$i++;
		}
		if ($i == 0)
			return;
		$query .= ") VALUES($placeholders)";

		$this->db->query($query, ...$values);

		$this->values = $this->newvalues;
		$this->newvalues = [];
		$this->gettercache = [];
	}

	protected function update(): void
	{
		if ($this->deleted)
			throw new \LogicException(
				__('Can not update deleted entity \'%s\'.', get_class($this)));

		$query = 'UPDATE `' . $this->structure['table'] . '` SET ';
		$values = [];
		$i = 0;
		foreach ($this->structure['columns'] as $name => $col) {
			if (!array_key_exists($name, $this->newvalues))
				continue;
			$values[] = $this->newvalues[$name];
			if ($i > 0)
				$query .= ', ';
			$query .= "`$name` = ?";
			$i++;
		}
		if ($i == 0)
			return;
		if (!isset($this->values['id']))
			throw new \LogicException(
				__('Entity \'%s\' does not define an id.', get_class($this)));
		$query .= ' WHERE id = ?';
		$values[] = $this->values['id'];

		$this->db->query($query, ...$values);

		$this->values = array_replace($this->values, $this->newvalues);
		$this->newvalues = [];
		$this->gettercache = [];
	}

	protected function isInsert(): bool
	{
		return !$this->deleted
			&& !empty($this->newvalues) && empty($this->values);
	}

	protected function isUpdate(): bool
	{
		return !$this->deleted
			&& !empty($this->newvalues) && !empty($this->values);
	}

	public function isDeleted(): bool
	{
		return $this->deleted;
	}

	public function equals(self $entity): bool
	{
		if (!isset($this->id) || !isset($entity->id))
			return false;
		return $this->id === $entity->id;
	}

	protected function cacheValue(string $name, $value): void
	{
		$this->gettercache[$name] = $value;
	}

	abstract public static function getStructure(): array;

	public static function get($name = null, $value = null,
		bool $or = false, bool $multirow = false)
	{
		$query = 'SELECT * FROM `' . static::getStructure()['table'] . '`';
		if (is_scalar($name) && !isset($value)) {
			$query .= ' WHERE id = ?';
			$params = [ $name ];
		} else if (is_string($name)) {
			$query .= " WHERE `$name` = ?";
			$params = [ $value ];
		} else if (is_array($name) && !empty($name)) {
			$i = 0;
			$query .= ' WHERE ';
			$params = [];
			foreach ($name as $key => $val) {
				if ($i > 0)
					$query .= $or ? ' OR ' : ' AND ';
				$query .= "`$key` = ?";
				$params[] = $val;
			}
		} else if (is_null($name)) {
			$params = [];
		} else {
			throw new \InvalidArgumentException(
				__('Parameter 1 of AbstractEntity::get is invalid.'));
		}

		$db = App::getInstance()->db();
		if (!$multirow) {
			$data = $db->fetchRow($query, ...$params);
			if (empty($data))
				return null;
			return new static($data);
		}

		$data = $db->fetchAll($query, ...$params);
		$entities = [];
		foreach ($data as $row)
			$entities[] = new static($row);
		return $entities;
	}

	public static function getOr($name, $value = null,
		bool $multirow = false)
	{
		return static::get($name, $value, true, $multirow);
	}

	public static function getAll($name = null, $value = null,
		bool $or = false): array
	{
		return static::get($name, $value, $or, true);
	}

	public static function getAllOr($name = null, $value = null): array
	{
		return static::getAll($name, $value, true);
	}
}
