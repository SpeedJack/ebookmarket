<?php
namespace EbookMarket\Entity;

class User extends AbstractEntity
{
	protected $username;
	protected $email;
	protected $passwordHash;

	const TABLE_NAME = 'users';

	const INVALID = 0;
	const ALREADY_IN_USE = 1;
	const VALID = 2;

	public function __construct(string $username, string $email,
		string $password)
	{
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getEmail()
	{
		return $this->_email;
	}

	public function getPasswordHash()
	{
		return $this->_passwordHash;
	}

	public function setUsername($username)
	{
		$validity = self::isValidUsername($username, true, $this->getId());
		if ($validity !== self::VALID)
			return $validity;
		$this->_set('username', $username);
		return self::VALID;
	}

	public function setEmail($email)
	{
		$validity = self::isValidEmail($email, true, $this->getId());
		if ($validity !== self::VALID)
			return $validity;
		$this->_set('email', $email);
		return self::VALID;
	}

	public function setPassword($password)
	{
		if (strlen($password) < $this->_app->config['min_password_length'])
			return false;
		$this->_set('passwordHash',
			password_hash($password, PASSWORD_DEFAULT));
		return true;
	}

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

	public function verifyPassword($password)
	{
		return password_verify($password, $this->_passwordHash);
	}
}
