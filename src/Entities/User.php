<?php

declare(strict_types=1);

namespace EbookMarket\Entities;

class User extends AbstractEntity
{
	private $token;
	public const EMAIL_IN_USE = 1;
	public const USERNAME_IN_USE = 2;
	public const BOTH_IN_USE = 3;
	public const FREE = 0; 
	public static function getStructure(): array
	{
		return [
			'table' => 'users',
			'columns' => [
				'id' => [ 'type' => self::UINT, 'auto_increment' => true ],
				'username' => [ 'type' => self::STR, 'required' => true ],
				'email' => [ 'type' => self::STR, 'required' => true ],
				'passwordhash' => [ 'type' => self::STR, 'required' => true ],
				'valid' => [ 'type' => self::BOOL, 'default' => false ],
			],
		];
	}

	public function setPassword(string $password): void
	{
		$this->setValue('passwordhash',
			password_hash($password, PASSWORD_DEFAULT));
	}

	public static function validateUsername(string $username): bool
	{
		return preg_match('/^[A-Za-z0-9_\-.]{3,32}$/', $username) === 1;
	}

	public static function validateEmail(string $email): bool
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL) != false;
	}

	public static function validatePassword(string $password): bool
	{
		/* Just check for a decent passwd len here */
		return strlen($password) > 7;
	}

	public function verifyPassword(string $password): bool
	{
		return password_verify($password, $this->passwordhash);
	}

	public function hasAuthtoken(): bool
	{
		return $this->token !== null;
	}

	public function getAuthtoken(): ?Token
	{
		return $this->token;
	}

	public function checkCredentials(string $username, string $email) : int{
		$query = "SELECT ". 
		"CASE ".  
        	"WHEN email = ? AND username = ? OR count(*) > 1 THEN ". static::BOTH_IN_USE ." ".
			"WHEN email = ? THEN ". static::EMAIL_IN_USE ." ".
			"WHEN username = ? THEN ". static::USERNAME_IN_USE ." ".
        	"ELSE ".static::FREE." ".
    	"END as result ". 
		"FROM ".static::getStructure()['table']." ".
		"WHERE username = ? ".
		"GROUP BY email, username ".
		"OR email = ? ;" ;

		$db = $this->app->db();
		$params = [$email, $username,  $email, $username, $username, $email];
		$data = $db->fetchRow($query, ...$params);
		return $data["result"];
	}

	public function setAuthtoken(?Token $token): void
	{
		$this->token = $token;
	}

	public function login(): void
	{
		$this->token = Token::createNew($this, Token::SESSION);
		$this->token->save();
	}
}
