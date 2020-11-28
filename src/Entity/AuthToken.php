<?php
namespace EbookMarket\Entity;

use DateTime;
use \EbookMarket\Entity\User;

class AuthToken extends AbstractEntity
{
	public static function getStructure(): array
	{
		return [
			'table' => 'authtoken',
			'columns' => [
				'id' => [ 'type' => self::STR, 'auto_increment' => true ],
				'expire_time' => [ 'type' => self::UINT, 'required' => true ],
				'type' => [ 'type' => self::STR, 'required' => true ],
				'user' => [ 'type' => self::UINT, 'required' => true ]
			]
		];
	}

    public function isExpired() : bool
	{
		return $this->expire_time <= time();
	}

	public function resetExpireTime() : void
	{
		$this->expire_time = time() + $this->app->config['auth_token_duration'];
    }

    public function verifyToken($token) : bool
	{
		return password_verify($token, $this->id);
	}

    public function authenticate($token) : User
	{
		if ($this->isExpired() || !$this->verifyToken($token))
			return null;
		$this->resetExpireTime();
		return User::get($this->user);
	}
}
