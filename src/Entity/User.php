<?php

declare(strict_types=1);

namespace EbookMarket\Entity;

class User extends AbstractEntity
{
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
}
