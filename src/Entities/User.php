<?php

declare(strict_types=1);

namespace EbookMarket\Entities;

class User extends AbstractEntity
{
	private $token;

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
