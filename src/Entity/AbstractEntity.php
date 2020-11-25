<?php
namespace EbookMarket\Entity;

require_once 'string-functions.php';

abstract class AbstractEntity
{


	protected $_id;
	/**
	 * @internal
	 * @var array $_changedValues
	 * Associative array of changed columns of the entity.
	 */
	protected $_changedValues = [];
	/**
	 * @internal
	 * @var bool $_toDelete
	 * Indicates if the entity is marked from deletion from the database.
	 */
	protected $_toDelete = false;
	/**
	 * @internal
	 * @var bool $_toInsert
	 * Indicates if the entity must be inserted on the database.
	 */
	protected $_toInsert = false;
	/**
	 * @internal
	 * @var bool $_deleted
	 * Indicates if the entity has been deleted from the database.
	 */
	protected $_deleted = false;
	/**
	 * @var EbookMarket::App $_app
	 * The application instance.
	 */
	protected $_app;
	/**
	 * @var EntityManager $_em
	 * The Entity Manager instance.
	 */
	protected $_em;
	/**
	 * @var EbookMarket::Db::AbstractAdapter $_db
	 * The database adapter.
	 */
	protected $_db;
	/**
	 * @internal
	 * @var array $_getters
	 * An array of getter functions for each property/column.
	 */
	protected $_getters = [];
// }}}

	/**
	 * @var string|null TABLE_NAME
	 * The name of the database's table associated with the entity.
	 */
	const TABLE_NAME = null;

	/**
	 * @brief Creates the entity.
	 *
	 * @param[in] int $id		The id of this entity.
	 * @return			The entity instance.
	 */
	public function __construct($id)
	{
		$this->_id = $id;
		$this->_app = \EbookMarket\App::getInstance();
		$this->_db = $this->_app->getDb();
		$this->_em = EntityManager::getInstance();
	}

// Getters {{{
	/**
	 * @brief Returns the fully qualified name of the entity.
	 *
	 * @retval string	The fully qualified name of the entity.
	 */
	public function getClassName()
	{
		return get_class($this);
	}

	/**
	 * @brief Returns the id of this entity on the database.
	 *
	 * @retval int		The id of this entity on the database.
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @brief Generates the entity's unique hash.
	 *
	 * @param[in] int $id	The id of the entity.
	 * @retval string	The entity's unique hash.
	 */
	public static function generateHash($id)
	{
		return static::class . '-' . $id;
	}

	/**
	 * @brief Returns the entity's unique hash.
	 *
	 * @retval int		The hash of this entity.
	 */
	public function getHash()
	{
		return static::generateHash($this->getId());
	}
// }}}

// Setters {{{
	/**
	 * @brief Sets a property which corresponds to a column in the database.
	 *
	 * @throws LogicException	If the property specified does not
	 * 				exists of if the entity has been
	 * 				deleted.
	 *
	 * @param[in] string $propertyName	The property/column name.
	 * @param[in] mixed $value		The property/column value.
	 */
	protected function _set($propertyName, $value)
	{
		$realName = "_$propertyName";
		if ($this->_deleted || $this->_toDelete)
			throw new \LogicException(
				__('Trying to set property %s in deleted entity %s.',
					$realName, $this->getClassName())
				);
		if (!property_exists($this, $realName))
			throw new \LogicException(
				__('Trying to set an non-existent property %s for entity %s.',
					$realName, $this->getClassName())
			);

		if (!isset($this->_changedValues[$propertyName]))
			$this->_changedValues[$propertyName] = $this->$realName;
		if ($this->_changedValues[$propertyName] === $value)
			unset($this->_changedValues[$propertyName]);

		$this->$realName = $value;
	}
// }}}

// Entity Methods {{{
	/**
	 * @brief Retrives an instance of this entity from the database with the
	 * specified id.
	 *
	 * @param[in] int $id	The id of the entity to search.
	 * @retval self|false	The entity retrived, or FALSE if the entity was
	 * 			not found.
	 */
	public static function getByid($id)
	{
		$db = \EbookMarket\App::getInstance()->getDb();
		$data = $db->fetchRow('SELECT * FROM `' . static::TABLE_NAME . '` WHERE id=?;', $id);
		return static::createFromData($data);
	}

	/**
	 * @brief Retrives all entities of the type of this instance from the
	 * database.
	 *
	 * @param[in] string $appendQuery	SQL query part to append.
	 * @retval array			The array of entities retrived.
	 */
	public static function getAll($appendQuery = "")
	{
		$db = \EbookMarket\App::getInstance()->getDb();
		$data = $db->fetchAll('SELECT * FROM `' . static::TABLE_NAME . '`' . $appendQuery . ';');
		return static::createFromDataArray($data);
	}

	/**
	 * @brief Retrives from the database an ordered page of entities of the
	 * type of this instance.
	 *
	 * @param[in] array $orderBy	Array that for each element has a
	 * 				'column' with the column name and
	 * 				'ascending'.
	 * @param[in] int $page		Page number.
	 * @param[in] int|null $perPage	The number of entries per page. If NULL,
	 * 				it defaults to the value set in the
	 * 				application's config.
	 * @retval array		The array of entities retrived.
	 */
	public static function getAllPaged($orderBy, $page, $perPage = null)
	{
		$query = 'ORDER BY ';
		foreach ($orderBy as $ob)
			$query .= $ob['column'] . ' ' . ($ob['ascending'] ? 'ASC' : 'DESC') . ',';
		$query = trim_suffix($query, ',');
		$perPage = isset($perPage) ? $perPage : $this->_app->config['default_per_page'];
		return self::getAll($query . " LIMIT $perPage OFFSET " . ($page - 1)*$perPage);
	}

	/**
	 * @brief Returns the number of entities of the type of this instance
	 * saved in the database.
	 *
	 * @retval int	The number of entities in the database.
	 */
	public static function count()
	{
		$db = \EbookMarket\App::getInstance()->getDb();
		return $db->fetchColumn('SELECT COUNT(*) FROM `' . static::TABLE_NAME . '`;');
	}

	/**
	 * @brief Merges this entity with another entity of the same type.
	 *
	 * @param[in] self $entity	The entity to merge with this entity.
	 */
	public function merge(self $entity)
	{
		foreach ($this->_getters as $colName => $getter) {
			$propertyName = "_$colName";
			$this->$propertyName = isset($this->_changedValues[$colName])
				? $this->$propertyName : $entity->$getter();
		}
	}

	/**
	 * @brief Creates a new entity from the data passed as array.
	 *
	 * @param[in] array $data	Associative array of key-value pairs
	 * 				where the key is the property/column's
	 * 				name.
	 * @retval self|false		The entity created or FALSE if no data
	 * 				is provided.
	 */
	public static function createFromData(array $data)
	{
		if (empty($data))
			return false;
		$instance = new static(0);
		$instance->_fillData($data);
		return $instance;
	}

	/**
	 * @brief Creates multiple entities from the data passed as array.
	 *
	 * @param[in] array $data	Array of associative arrays of key-value
	 * 				pairs where the key is the
	 * 				property/column's name.
	 * @retval array		The entities created or FALSE if no data
	 * 				is provided.
	 */
	public static function createFromDataArray(array $data)
	{
		if (!is_array($data))
			return false;
		$entities = [];
		foreach ($data as $row)
			$entities[] = static::createFromData($row);
		return count($entities) === 1 ? array_pop($entities) : $entities;
	}

	/**
	 * @brief Marks this entity for deletion from the database.
	 */
	public function delete()
	{
		$this->_toDelete = true;
	}

	/**
	 * @brief Marks this entity for insertion on the database.
	 */
	public function insert()
	{
		$this->_toInsert = true;
	}
// }}}

// Entity Life-cycle {{{
	/**
	 * @brief This function is executed before the entity is inserted on the
	 * database.
	 */
	protected function _preInsert() {}

	/** @brief Inserts the entity on the database. */
	protected function _insert()
	{
		$query = 'INSERT INTO `' . static::TABLE_NAME . '`(';
		$placeholders = '';
		$values = [];
		foreach ($this->_getters as $colName => $getter) {
			$query .= "$colName, ";
			$placeholders .= '?, ';
			$values[] = $this->$getter();
		}
		$placeholders = trim_suffix($placeholders, ', ');
		$query = trim_suffix($query, ', ') . ") VALUES($placeholders);";
		try {
			$this->_db->query($query, ...$values);
		} catch (\EbookMarket\Db\DuplicateKeyException $e) {
			if (!empty($this->_changedValues)) {
				$this->_preUpdate();
				$this->_update();
				$this->_postUpdate();
				$this->_changedValues = [];
			}
		}
	}

	/**
	 * @brief This function is executed after the entity is inserted on the
	 * database.
	 */
	protected function _postInsert() {}

	/**
	 * @brief This function is executed before the entity is deleted from
	 * the database.
	 */
	protected function _preDelete() {}

	/** @brief Deletes the entity from the database. */
	protected function _delete()
	{
		$this->_db->query('DELETE FROM `' . static::TABLE_NAME . '` WHERE id=?;',
			$this->_id);
		$this->_deleted = true;
	}

	/**
	 * @brief This function is executed after the entity is deleted from the
	 * database.
	 */
	protected function _postDelete() {}

	/**
	 * @brief This function is executed before the entity is updated on the
	 * database.
	 */
	protected function _preUpdate() {}

	/** @brief Updates the entity on the database. */
	protected function _update()
	{
		$query = 'UPDATE `' . static::TABLE_NAME . '` SET ';
		$realNames = [];
		foreach (array_keys($this->_changedValues) as $name) {
			$realName = "_$name";
			$values[] = $this->$realName;
			$query .= "$name = ?, ";
		}
		$query = trim_suffix($query, ', ') . ' WHERE id=?;';
		$values[] = $this->_id;
		$rowsAffected = $this->_db->query($query, ...$values);

	}

	/**
	 * @brief This function is executed after the entity is updated on the
	 * database.
	 */
	protected function _postUpdate() {}

	/**
	 * @brief This function is executed before the entity is saved (i.e.
	 * inserted, updated or deleted) on the database.
	 */
	protected function _preSave() {}

	/**
	 * @brief Saves (i.e. inserts, deletes or updates) the entity on the
	 * database.
	 */
	public function save()
	{
		if ($this->_deleted || ($this->_toInsert && $this->_toDelete))
			goto ClearChangesAndExit;

		$this->_preSave();
		if ($this->_toInsert) {
			$this->_preInsert();
			$this->_insert();
			$oldHash = $this->getHash();
			$this->_id = $this->_db->fetchColumn('SELECT LAST_INSERT_ID();');
			$this->_em->moveToSaved($this, $oldHash);
			$this->_postInsert();
			$this->_toInsert = false;
		} else if ($this->_toDelete) {
			$this->_preDelete();
			$this->_delete();
			$this->_postDelete();
			$this->_toDelete = false;
		} else if (!empty($this->_changedValues)) {
			$this->_preUpdate();
			$this->_update();
			$this->_postUpdate();
		}
		$this->_postSave();

	ClearChangesAndExit:
		$this->_changedValues = [];
	}

	/**
	 * @brief This function is executed after the entity is saved (i.e.
	 * inserted, updated or deleted) on the database.
	 */
	protected function _postSave() {}
// }}}

// Protected Entity Methods {{{
	/**
	 * @internal
	 * @brief Fills the properties/columns of this entity with the values
	 * passed as array.
	 *
	 * @param[in] array $data	Associative array of key-value pairs
	 * 				where the key is the property/column's
	 * 				name.
	 */
	protected function _fillData(array $data)
	{
		foreach ($data as $name => $value) {
			$realName = "_$name";
			if (property_exists($this, $realName))
				$this->$realName = $value;
		}
		$this->_entityId = $this->_id;
	}
// }}}

}
