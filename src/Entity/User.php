<?php

declare(strict_types=1);

namespace EbookMarket\Entity;

class User extends AbstractEntity
{
	public static function getStructure(): array
	{
		return [
			'table' => 'user',
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

	protected function validateUsername(string $username): bool
	{
		return preg_match('/^[A-Za-z0-9_\-.]{3,32}$/', $username);
	}

	protected function validateEmail(string $email): bool
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	protected function validatePassword(string $password): bool
	{
		/* Just check for a decent passwd len here */
		return strlen($password) > 7;
	}
}
