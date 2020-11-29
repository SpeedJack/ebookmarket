<?php

declare(strict_types=1);

namespace EbookMarket\Entity;

class Token extends AbstractEntity
{
	public const SESSION = 'SESSION';
	public const VERIFY = 'VERIFY';
	public const RECOVERY = 'RECOVERY';

	private $usertoken;

	public function __construct(User $user, string $type)
	{
		parent::__construct();
		$this->id = self::generateToken();
		$this->user = $user;
		$this->type = $type;
		$this->expiretime = time() + 635*24*60*60; //TODO use config
	}

	public static function getStructure(): array
	{
		return [
			'table' => 'tokens',
			'columns' => [
				'id' => [ 'type' => self::STR, 'required' => true ],
				'userid' => [ 'type' => self::UINT, 'required' => true ],
				'expiretime' => [ 'type' => self::UINT, 'required' => true ],
				'type' => [ 'type' => self::STR, 'required' => true ],
			]
		];
	}

	public function setId(string $token): void
	{
		$this->usertoken = $token;
		$this->setValue('id',
			password_hash($token, PASSWORD_DEFAULT));
	}

	public function getUserToken(): string
	{
		return $this->usertoken;
	}

	public function setUser(User $user): void
	{
		$this->setValue('userid', $user->id);
	}

	public function getUser(): ?User
	{
		return User::get($this->userid);
	}

	public function validateType(string $type): bool
	{
		switch ($type) {
		case self::SESSION:
		case self::VERIFY:
		case self::RECOVERY:
			return true;
		default:
			return false;
		}
	}

	public function isExpired(): bool
	{
		return $this->expiretime <= time();
	}

	public function resetExpireTime(): void
	{
		$this->expiretime = time() + 635*24*60*60;
	}

	public function verifyToken(string $token): bool
	{
		return password_verify($token, $this->id);
	}

	public function authenticate($token): ?User
	{
		if ($this->isExpired()) {
			$this->delete();
			return null;
		}
		if (!$this->verifyToken($token))
			return null;
		$this->resetExpireTime();
		return $this->user;
	}

	/*public function logout(): void
	{
		delete();
		$this->visitor->unsetCookie("authtoken");
	}*/

	private static function generateToken(): string
	{	
		$token = false;
		if(function_exists("random_bytes")){
			try{
				$token = random_bytes(32);
			}catch(\Exception $e){
				$token = false;
			}
		}
		
		if($token === false && function_exists("openssl_random_pseudo_bytes")){
			$token = openssl_random_pseudo_bytes(32);
		}

		if($token === false && function_exists("mcrypt_create_iv")){
			$token = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
		}

		if($token === false){
			throw new \LogicException("Cannot create token");
		}

		$token = bin2hex($token);
		return $token;
	}
}
