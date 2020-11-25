<?php
namespace EbookMarket\Entity;

require_once 'string-functions.php';

/**
 * @brief The Entity Manager class.
 *
 * This class maintains a cache of all entities used by the app. Entities can be
 * saved to the database (ie. have an associated record in a database's table)
 * or not (eg. the Visitor entity has no entry in the db). This class handles
 * the creation and life-cycle of every entity. It also ensures that, for each
 * entity, only a single instance exists (if the app requests the user with id
 * 3, and it requests it again after some time, the entity manager returns the
 * same instance).
 *
 * @author Niccolò Scatena <speedjack95@gmail.com>
 * @copyright GNU General Public License, version 3
 */
class EntityManager extends \EbookMarket\AbstractSingleton
{

// Protected Properties {{{
	/**
	 * @internal
	 * @var	array $_savedEntities
	 * Array of entities fetched from the database or that will be saved to
	 * the database.
	 */
	protected $_savedEntities = [];
	/**
	 * @internal
	 * @var	array $_cachedEntities
	 * Array of entities that are created by the application and are
	 * intended to be deleted when the application exits.
	 */
	protected $_cachedEntities = [];

	/**
	 * @internal
	 * @var array $_doNotSave
	 * Array of hashes of entities that do not must be saved when the
	 * application exits.
	 */
	protected $_doNotSave = [];
// }}}

	/**
	 * @brief This class must be instantiated using getInstance().
	 */
	protected function __construct() { }

	/**
	 * @brief Flushes all the saved entities to the database and destroys
	 * the EntityManager.
	 */
	public function __destruct()
	{
		$this->flush();
	}

// Public Methods {{{
	/**
	 * @brief Creates the specified entity, or returns it from the cache if
	 * it was already created.
	 *
	 * @param[in] string $entityName	The entity's name to create.
	 * @param[in] int $id			The entity's id.
	 * @param[in] mixed $params		The list of parameters to pass
	 * 					to the entity's constructor.
	 * @retval AbstractEntity		The entity created.
	 */
	public function create($entityName, $id = 0, ...$params)
	{
		$found = $this->findCached($entityName, $id);
			if ($found !== false)
				return $found;
		$entityName = $this->_getEntityFullName($entityName);
		$entity = new $entityName($id, ...$params);
		$entity->insert();
		$hash = $this->_getEntityHash($entity);
		$this->_cachedEntities[$hash] = $entity;
		return $entity;
	}

	/**
	 * @brief Creates the specified entity assigning to it the next
	 * available entity id.
	 *
	 * @param[in] string $entityName	The entity's name to create.
	 * @param[in] mixed $params		The list of parameters to pass
	 * 					to the entity's constructor.
	 * @retval AbstractEntity		The entity created.
	 */
	public function createNew($entityName, ...$params)
	{
		$id = $this->_nextEntityId($entityName);
		return $this->create($entityName, $id, ...$params);
	}

	/**
	 * @brief Returns a cached entity.
	 *
	 * @param[in] string $entityName	The name of the entity to find.
	 * @param[in] int $id			The id of the entity to find.
	 * @retval AbstractEntity|false		The entity found or FALSE if no
	 * 					entity was found.
	 */
	public function findCached($entityName, $id = 0)
	{
		$hash = $this->_getEntityHash($entityName);
		if (isset($this->_cachedEntities[$hash]))
			return $this->_cachedEntities[$hash];
		return false;
	}

	/**
	 * @brief Returns a saved entity.
	 *
	 * @param[in] string $entityName	The name of the entity to find.
	 * @param[in] int $id			The id of the entity to find.
	 * @retval AbstractEntity|false		The entity found or FALSE if no
	 * 					entity was found.
	 */
	public function findSaved($entityName, $id)
	{
		$hash = $this->_getEntityHash($entityName);
		if (isset($this->_savedEntities[$hash]))
			return $this->_savedEntities[$hash];
		return false;
	}

	/**
	 * @brief Fetches an entity from the database.
	 *
	 * @param[in] string $entityName	The name of the entity to fetch.
	 * @param[in] int $id			The id of the entity to fetch.
	 * @param[in] mixed $params		Additional parameters to pass to
	 * 					the getById() method.
	 * @retval AbstractEntity|false		The entity fetched or false if
	 * 					the entity was not found.
	 */
	public function getFromDb($entityName, $id, ...$params)
	{
		$found = $this->findSaved($entityName, $id);
		if ($found !== false)
			return $found;
		return $this->getFromDbBy($entityName, 'getById', $id, ...$params);
	}

	/**
	 * @brief Fetches all entities of the specified type from the database.
	 *
	 * @param[in] string $entityName	The name of the entity to fetch.
	 * @param[in] mixed $params		The parameters to pass to the
	 * 					getAll() method.
	 * @retval array|false			The entities fetched or FALSE if
	 * 					no entity was found.
	 */
	public function getAllFromDb($entityName, ...$params)
	{
		return $this->getFromDbBy($entityName, 'getAll', ...$params);
	}

	/**
	 * @brief Fetches a page of entities of the specified type from the
	 * database.
	 *
	 * @param[in] string $entityName	The name of the entity to fetch.
	 * @param[in] array $orderBy		Array that for each element has
	 * 					a 'column' with the column name
	 * 					and 'ascending'.
	 * @param[in] int $page			Page number.
	 * @param[in] int|null $perPage		The number of entries per page.
	 * 					If NULL, it defaults to the
	 * 					value set in the application's
	 * 					config.
	 * @param[in] array $params		Additional parameters to pass to
	 * 					the entity's getAllPaged()
	 * 					method.
	 * @retval array|false			The entities fetched or FALSE if
	 * 					no entity was found.
	 */
	public function getAllPagedFromDb($entityName, $orderBy, $page,
		$perPage = null, ...$params)
	{
		return $this->getFromDbBy($entityName, 'getAllPaged', $orderBy,
			$page, $perPage, ...$params);
	}

	/**
	 * @brief Fetches one or more entities from the database using the
	 * specified method.
	 *
	 * @throws BadMethodCallException	If the $byFunction method does
	 * 					not exists.
	 *
	 * @param[in] string $entityName	The name of the entity to fetch.
	 * @param[in] string $byFunction	The name to the method to call.
	 * @param[in] mixed $params		The parameters to pass to the
	 * 					method.
	 * @retval AbstractEntity|array|false	The entities fetched or FALSE if
	 * 					no entity was found.
	 */
	public function getFromDbBy($entityName, $byFunction, ...$params)
	{
		$entityName = $this->_getEntityFullName($entityName);
		if (!method_exists($entityName, $byFunction))
			throw new \BadMethodCallException(
				__('The method %s::%s does not exists.',
					$entityName, $byFunction)
			);
		$entities = $entityName::$byFunction(...$params);
		if (empty($entities))
			return false;
		$entities = is_array($entities) ? $entities : [ $entities ];
		foreach ($entities as $key => $entity) {
			$hash = $entity->getHash();
			if (isset($this->_savedEntities[$hash])) {
				$this->_savedEntities[$hash]->merge($entity);
				$entities[$key] = $this->_savedEntities[$hash];
			} else {
				$this->addToSaved($entity);
			}
		}
		return count($entities) > 1 ? $entities : array_pop($entities);
	}

	/**
	 * @brief Returns the number of entities of the specified type saved in
	 * the database.
	 *
	 * @param[in] string $entityName	The name of the entity to count.
	 * @retval int		The number of entities in the database.
	 */
	public function countDbEntities($entityName)
	{
		$entityName = $this->_getEntityFullName($entityName);
		return $entityName::count();
	}

	/**
	 * @brief Adds an entity to the $_savedEntities array.
	 *
	 * @throws LogicException	If $entity already exists in the
	 * 				$_savedEntities array.
	 *
	 * @param[in] AbstractEntity $entity	The entity.
	 */
	public function addToSaved($entity)
	{
		$this->_assertValidEntity($entity);
		$hash = $entity->getHash();
		if (isset($this->_savedEntities[$hash]))
			throw new \LogicException(
				__('Can not add entity with hash %s since another entity of the same type already uses this id.',
				$hash)
			);
		$this->_savedEntities[$hash] = $entity;
	}

	/**
	 * @brief Moves an entity from the $_cachedEntities to the
	 * $_savedEntities array.
	 *
	 * @throws LogicException	If $entity does not exists in the
	 * 				$_cachedEntities array.
	 *
	 * @param[in] AbstractEntity $entity	The entity.
	 * @param[in] string $oldHash		The old hash of the entity.
	 */
	public function moveToSaved($entity, $oldHash)
	{
		$this->_assertValidEntity($entity);
		if (!isset($this->_cachedEntities[$oldHash]))
			throw new \LogicException(
				__('Entity hash \'%s\' has changed unexpectedly.',
				$oldHash)
			);
		$this->addToSaved($entity);
		unset($this->_cachedEntities[$oldHash]);
		$entity->insert();
	}

	/**
	 * @brief Marks an entity to no be saved when the application exits.
	 *
	 * @param[in] AbstractEntity $entity	The entity that do not must
	 * 					be saved.
	 */
	public function doNotSave($entity)
	{
		$this->_assertValidEntity($entity);
		$this->_doNotSave[] = $entity->getHash();
	}

	/** @brief Flushes all the saved entities to the database. */
	public function flush()
	{
		foreach ($this->_savedEntities as $entity)
			if (!in_array($entity->getHash(), $this->_doNotSave, true))
				$entity->save();
	}
// }}}

// Private Methods {{{
	/**
	 * @internal
	 * @brief Gets the next available id in the _cachedEntities array.
	 *
	 * @param[in] string $entityName	The name of the entity.
	 * @retval int				The next available id for the
	 * 					specified entity.
	 */
	private function _nextEntityId($entityName)
	{
		$entityName = $this->_getEntityFullName($entityName);
		for ($id = 0; isset($this->_cachedEntities[$this->_getEntityHash($entityName, $id)]); $id++)
			;
		return $id;
	}

	/**
	 * @internal
	 * @brief Returns the entity's unique hash.
	 *
	 * @param[in] string|AbstractEntity $entity	The entity or entity's
	 * 						name.
	 * @param[in] int $id				The entity's id.
	 * @retval string				The entity's unique
	 * 						hash.
	 */
	private function _getEntityHash($entity, $id = 0)
	{
		if (is_string($entity)) {
			$entity = $this->_getEntityFullName($entity);
			return $entity::generateHash($id);
		}
		return $entity->getHash();
	}

	/**
	 * @internal
	 * @brief Asserts that the specified entity is a valid entity.
	 *
	 * @throws InvalidArgumentException	If $entity is not a valid
	 * 					entity.
	 *
	 * @param[in] object|string $entity	The entity or entity's name to
	 * 					check.
	 */
	private function _assertValidEntity($entity)
	{
		if (is_string($entity) && !starts_with($entity, __NAMESPACE__))
			$entity = __NAMESPACE__ . "\\$entity";
		$parentName = __NAMESPACE__ . "\\AbstractEntity";
		if (!is_subclass_of($entity, $parentName))
			throw new \InvalidArgumentException(
				__('Invalid Entity %s.', $entity)
			);
	}

	/**
	 * @internal
	 * @brief Returns the entity's fully qualified name.
	 *
	 * @param[in] object|string $entity	The entity or entity's name.
	 * @retval string			The entity's fully qualified
	 * 					name.
	 */
	private function _getEntityFullName($entity)
	{
		$this->_assertValidEntity($entity);
		if (is_object($entity))
			$entityName = $entity->getClassName();
		else if (!starts_with($entity, __NAMESPACE__))
			$entityName = __NAMESPACE__ . "\\$entity";
		else
			$entityName = $entity;
		return $entityName;
	}

	/**
	 * @internal
	 * @brief Returns the entity's short name (i.e. the name of the class).
	 *
	 * @param[in] object|string $entity	The entity or entity's name.
	 * @retval string			The entity's short name.
	 */
	private function _getEntityShortName($entity)
	{
		$this->_assertValidEntity($entity);
		if (is_object($entity))
			$entityName = $entity->getClassName();
		else
			$entityName = $entity;
		return trim_prefix($entityName, __NAMESPACE__ . '\\');
	}
// }}}

}
