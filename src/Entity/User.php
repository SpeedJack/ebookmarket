<?php
namespace EbookMarket\Entity;

/**
 * @brief Represents a user.
 *
 * @author Niccolò Scatena <speedjack95@gmail.com>
 * @copyright GNU General Public License, version 3
 */
class User extends AbstractEntity
{

// Entity Properties {{{
	/**
	 * @var string $_username
	 * The user's username.
	 */
	protected $_username;
	/**
	 * @var string $_email
	 * The user's email.
	 */
	protected $_email;
	/**
	 * @var string $_passwordHash
	 * The user's hashed password.
	 */
	protected $_passwordHash;
	
// }}}

// Other Properties {{{
	/**
	 * @internal
	 * @var array $_getters
	 * An array of getter functions for each property/column.
	 */
	protected $_getters = [
		'username' => 'getUsername',
		'email' => 'getEmail',
		'passwordHash' => 'getPasswordHash',
		'isAdmin' => 'isAdmin',
		'points' => 'getPoints'
	];

	/**
	 * @var string TABLE_NAME
	 * The name of the database's table associated with the entity.
	 */
	const TABLE_NAME = 'users';
	
	/**
	 * @var int INVALID
	 * Returned by isValid* and set* functions when the username/email is
	 * invalid.
	 */
	const INVALID = 0;
	/**
	 * @var int ALREADY_IN_USE
	 * Returned by isValid* and set* functions when the username/email is
	 * already in use by another user.
	 */
	const ALREADY_IN_USE = 1;
	/**
	 * @var int VALID
	 * Returned by isValid* and set* functions when the username/email is
	 * valid.
	 */
	const VALID = 2;

	
// }}}

// Getters {{{
	/**
	 * @brief Returns the user's username.
	 *
	 * @retval string	The user's username.
	 */
	public function getUsername()
	{
		return $this->_username;
	}

	/**
	 * @brief Returns the user's email.
	 *
	 * @retval string	The user's email.
	 */
	public function getEmail()
	{
		return $this->_email;
	}

	/**
	 * @brief Returns the user's hashed password.
	 *
	 * @retval string	The user's hashed password.
	 */
	public function getPasswordHash()
	{
		return $this->_passwordHash;
	}

	
	// }}}

// Setters {{{
	/**
	 * @brief Sets the user's username.
	 *
	 * @param[in] string $username	The username.
	 * @retval int			Returns VALID if username is valid;
	 * 				INVALID if invalid; ALREADY_IN_USE if
	 * 				already in use by another user.
	 */
	public function setUsername($username)
	{
		$validity = self::isValidUsername($username, true, $this->getId());
		if ($validity !== self::VALID)
			return $validity;
		$this->_set('username', $username);
		return self::VALID;
	}

	/**
	 * @brief Sets the user's email.
	 *
	 * @param[in] string $email	The email.
	 * @retval int			Returns VALID if email is valid;
	 * 				INVALID if invalid; ALREADY_IN_USE if
	 * 				already in use by another user.
	 */
	public function setEmail($email)
	{
		$validity = self::isValidEmail($email, true, $this->getId());
		if ($validity !== self::VALID)
			return $validity;
		$this->_set('email', $email);
		return self::VALID;
	}

	/**
	 * @brief Sets the user's password.
	 *
	 * @param[in] string $password	The password.
	 * @retval bool			TRUE if the password is valid; FALSE if
	 * 				the password is too short.
	 */
	public function setPassword($password)
	{
		if (strlen($password) < $this->_app->config['min_password_length'])
			return false;
		$this->_set('passwordHash',
			password_hash($password, PASSWORD_DEFAULT));
		return true;
	}

	/** @brief Gives the user admin's rights. */
	public function promoteAdmin()
	{
		$this->_set('isAdmin', true);
	}

	/** @brief Removes the user admin's rights. */
	public function demoteAdmin()
	{
		$this->_set('isAdmin', false);
	}

	
// }}}

// Entity Methods {{{
	/**
	 * @brief Retrives the user with the specified username from the
	 * database.
	 *
	 * @param[in] string $username	The username of the user to retrive.
	 * @retval self|false		The retrived user or FALSE if no user
	 * 				was found.
	 */
	public static function getByUsername($username)
	{
		if (self::isValidUsername($username) !== self::VALID)
			return false;
		$db = \EbookMarket\App::getInstance()->db();
		$data = $db->fetchRow('SELECT * FROM `' . self::TABLE_NAME . '` WHERE username=?;', $username);
		return self::createFromData($data);
	}

	/**
	 * @brief Retrives the user with the specified email from the database.
	 *
	 * @param[in] string $email	The email of the user to retrive.
	 * @retval self|false		The retrived user or FALSE if no user
	 * 				was found.
	 */
	public static function getByEmail($email)
	{
		if (self::isValidEmail($email) !== self::VALID)
			return false;
		$db = \EbookMarket\App::getInstance()->db();
		$data = $db->fetchRow('SELECT * FROM `' . self::TABLE_NAME . '` WHERE email=?;', $email);
		return self::createFromData($data);
	}

	/**
	 * @brief Retrives all users that solved a challenge.
	 *
	 * @param[in] Challenge|int $chall	The challenge or challenge's id.
	 * @retval array|false	Array containing all users that solved the
	 * 			challenge.
	 */
	

	/**
	 * @brief Retrives all users which username contains the given pattern.
	 *
	 * @param[in] string $username	The username pattern.
	 * @retval array|false		Array containing all users which
	 * 				username contains the given pattern.
	 */
	public static function getAllByUsernameLike($username)
	{
		$em = EntityManager::getInstance();
		$db = \EbookMarket\App::getInstance()->db();
		$data = $db->fetchAll('SELECT * FROM `' . self::TABLE_NAME . '` WHERE username LIKE ?;', '%' . $username . '%');
		return parent::createFromDataArray($data);
	}

	/**
	 * @brief Retrives all users which email contains the given pattern.
	 *
	 * @param[in] string $email	The email pattern.
	 * @retval array|false		Array containing all users which email
	 * 				contains the given pattern.
	 */
	public static function getAllByEmailLike($email)
	{
		$em = EntityManager::getInstance();
		$db = \EbookMarket\App::getInstance()->db();
		$data = $db->fetchAll('SELECT * FROM `' . self::TABLE_NAME . '` WHERE email LIKE ?;', '%' . $email . '%');
		return parent::createFromDataArray($data);
	}

	
	/**
	 * @brief Creates a new User from the data passed as array.
	 *
	 * @param[in] array $data	Associative array of key-value pairs
	 * 				where the key is the property/column's
	 * 				name.
	 * @retval self|false	The entity created or FALSE if no data is
	 * 			provided.
	 */
	public static function createFromData(array $data)
	{
		if (isset($data['isAdmin']))
			$data['isAdmin'] = ($data['isAdmin'] ? true : false);
		return parent::createFromData($data);
	}
// }}}

// Entity Life-cycle {{{
	/**
	 * @brief Deletes every auth tokens and solved challenge associated with
	 * this user.
	 */
	protected function _preDelete()
	{
		$userid = $this->getId();
	}
// }}}

// Public Methods {{{
	/**
	 * @brief Checks if the provided username is valid.
	 *
	 * @param[in] string $username	The username to check.
	 * @param[in] bool $checkInUse	Set to TRUE to check if the username is
	 * 				already in use by another user.
	 * @param[in] int|null $userid	When $checkInUse is TRUE, if the
	 * 				username is already in use by a user
	 * 				that has this id this function still
	 * 				returns VALID.
	 * @retval int			Returns VALID if username is valid;
	 * 				INVALID if invalid; ALREADY_IN_USE if
	 * 				already in use by another user.
	 */
	public static function isValidUsername($username, $checkInUse = false, $userid = null)
	{
		if (!is_string($username))
			return self::INVALID;
		$app = \EbookMarket\App::getInstance();
		$res = preg_match($app->config['username_regex'], $username);
		if ($res === false)
			throw new \Exception(__('Error parsing regular expression: \'%s\'.',
				$app->config['username_regex']));
		if (!$res)
			return self::INVALID;
		if (!$checkInUse)
			return self::VALID;
		$em = EntityManager::getInstance();
		$inUse = $em->getFromDbBy('User', 'getByUsername', $username);
		if ($inUse === false)
			return self::VALID;
		if (!isset($userid))
			return self::ALREADY_IN_USE;
		return $inUse->getId() === $userid ? self::VALID : self::ALREADY_IN_USE;
	}

	/**
	 * @brief Checks if the provided email is valid.
	 *
	 * @param[in] string $email	The email to check.
	 * @param[in] bool $checkInUse	Set to TRUE to check if the email is
	 * 				already in use by another user.
	 * @param[in] int|null $userid	When $checkInUse is TRUE, if the
	 * 				email is already in use by a user that
	 * 				has this id this function still returns
	 * 				VALID.
	 * @retval int			Returns VALID if email is valid;
	 * 				INVALID if invalid; ALREADY_IN_USE if
	 * 				already in use by another user.
	 */
	public static function isValidEmail($email, $checkInUse = false, $userid = null)
	{
		// FIXME: FILTER_VALIDATE_EMAIL sometimes rejects RFC5321 valid
		// email addresses. Better use a custom regex.
		if (!is_string($email)
			|| filter_var($email, FILTER_VALIDATE_EMAIL) === false)
			return self::INVALID;
		if (!$checkInUse)
			return self::VALID;
		$em = EntityManager::getInstance();
		$inUse = $em->getFromDbBy('User', 'getByEmail', $email);
		if ($inUse === false)
			return self::VALID;
		if (!isset($userid))
			return self::ALREADY_IN_USE;
		return $inUse->getId() === $userid ? self::VALID : self::ALREADY_IN_USE;
	}

	/**
	 * @brief Verifies that the provided password equals with this user's
	 * password.
	 *
	 * @param[in] string $password	The password to check.
	 * @retval bool			TRUE if the password matches this user's
	 * 				password; FALSE otherwise.
	 */
	public function verifyPassword($password)
	{
		return password_verify($password, $this->_passwordHash);
	}

	/**
	 * @brief Checks if the user has solved the specified challenge.
	 *
	 * @param[in] int $chall	The challenge or challenge's id.
	 * @retval bool		TRUE if the user has solved the challenge;
	 * 			FALSE otherwise.
	 */
	// }}}

}
