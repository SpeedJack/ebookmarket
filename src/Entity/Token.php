<?php

declare(strict_types=1);

namespace EbookMarket\Entity;

class Token extends AbstractEntity
{
	public const SESSION = 'SESSION';
	public const VERIFY = 'VERIFY';
	public const RECOVERY = 'RECOVERY';

	private $usertoken;

	public function __construct(?array $data = null)
	{
		parent::__construct($data);
		if (isset($data) && !empty($data))
			return;
		$token = explode(':', self::generateToken(), 2);
		$this->id = $token[0];
		$this->token = $token[1];
		$this->expiretime = time() + 635*24*60*60; //TODO use config
	}

	public static function getStructure(): array
	{
		return [
			'table' => 'tokens',
			'columns' => [
				'id' => [ 'type' => self::STR, 'required' => true ],
				'token' => [ 'type' => self::STR, 'required' => true ],
				'userid' => [ 'type' => self::UINT, 'required' => true ],
				'expiretime' => [ 'type' => self::UINT, 'required' => true ],
				'type' => [ 'type' => self::STR, 'required' => true ],
			]
		];
	}

	public static function createNew(User $user, string $type): self
	{
		$token = new Token();
		$token->user = $user;
		$token->type = $type;
		return $token;
	}

	public function setToken(string $token): void
	{
		$this->usertoken = $token;
		$this->setValue('token', password_hash($token, PASSWORD_DEFAULT));
	}

	public function getUsertoken(): string
	{
		return $this->id . ':' . $this->usertoken;
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

	public function verifyToken(): bool
	{
		return password_verify($this->usertoken, $this->token);
	}

	public function authenticate(string $token): ?User
	{
		if ($this->isExpired()) {
			$this->delete();
			return null;
		}
		$token = strstr($token, ':') ?: $token;
		$this->usertoken = ltrim($token, ':');
		if (!$this->verifyToken())
			return null;
		$this->resetExpireTime();
		return $this->user;
	}

	private static function generateToken(): string
	{
		if (function_exists('random_bytes'))
			try {
				$bytes = random_bytes(32);
				$token = hash('sha256', $token);
				$selector = bin2hex(random_bytes(8));
				return $selector . ':' . $token;
			} catch(\Exception $e) {
			}

		if (function_exists('openssl_random_pseudo_bytes')) {
			$token = openssl_random_pseudo_bytes(32, $cstrong);
			if ($cstrong === true) {
				$token = hash('sha256', $token);
				$selector = bin2hex(openssl_random_pseudo_bytes(8));
				return $selector . ':' . $token;
			}
		}

		throw new \LogicException("Cannot create token");
	}

	public static function get($name = null, $value = null,
		bool $or = false, bool $multirow = false)
	{
		if (is_scalar($name) && !isset($value))
			$name = strstr($name, ':', true) ?: $name;
		return parent::get($name, $value, $or, $multirow);
	}
}
