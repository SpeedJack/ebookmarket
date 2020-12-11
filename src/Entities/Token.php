<?php

declare(strict_types=1);

namespace EbookMarket\Entities;

class Token extends AbstractEntity
{
	public const SESSION = 'SESSION';
	public const VERIFY = 'VERIFY';
	public const RECOVERY = 'RECOVERY';
	public const CSRF = 'CSRF';
	public const BUYSTEP1 = 'BUYSTEP1';
	public const BUYSTEP2 = 'BUYSTEP2';

	private $usertoken;

	public function __construct(?array $data = null)
	{
		parent::__construct($data);
		if (isset($data) && !empty($data))
			return;
		$token = explode(':', self::generateToken(), 2);
		$this->id = $token[0];
		$this->token = $token[1];
	}

	public static function getStructure(): array
	{
		return [
			'table' => 'tokens',
			'columns' => [
				'id' => [ 'required' => true ],
				'token' => [ 'required' => true ],
				'userid' => [],
				'bookid' => [],
				'expiretime' => [ 'required' => true ],
				'type' => [ 'required' => true ],
			]
		];
	}

	public static function createNew(User $user, string $type, ?Book $book = null): self
	{	
		if(($type === self::BUYSTEP1 || $type === self::BUYSTEP2) && !$book)
			return null;
		$token = new Token();
		$token->user = $user;
		$token->type = $type;
		if(($type === self::BUYSTEP1 || $type === self::BUYSTEP2)) {
			$token->book = $book;
		}
		return $token;
	}

	public static function createNewCsrf(?User $user = null): self
	{
		if (isset($user))
			return static::createNew($user, self::CSRF);
		$token = new Token();
		$token->setValue('userid', null);
		$token->type = self::CSRF;
		$token->resetExpireTime();
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

	public function setBook(Book $book): void
	{
		$this->setValue('bookid', $book->id);
	}

	public function getBook(): ?Book
	{
		return Book::get($this->bookid);
	}

	public function validateType(string $type): bool
	{
		switch ($type) {
		case self::SESSION:
		case self::VERIFY:
		case self::RECOVERY:
		case self::CSRF:
		case self::BUYSTEP1:
		case self::BUYSTEP2:
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
		switch ($this->type) {
		case self::SESSION:
			$time = $this->app->config('session_token_expire_time');
			break;
		case self::VERIFY:
			$time = $this->app->config('verify_token_expire_time');
			break;
		case self::RECOVERY:
			$time = $this->app->config('recovery_token_expire_time');
			break;
		case self::CSRF:
			$time = $this->app->config('csrf_token_expire_time');
			break;
		case self::BUYSTEP1:
		case self::BUYSTEP2:
			$time = $this->app->config('buystep_token_expire_time');
			break;
		default:
			return;
		}
		$this->expiretime = time() + $time;
	}

	protected function preSave(): void
	{
		$this->resetExpireTime();
	}

	public function verifyToken(string $token): bool
	{
		return password_verify($token, $this->token);
	}

	public function authenticate(string $token,
		string $type = self::SESSION): ?User
	{
		if ($this->isExpired()) {
			$this->delete();
			return null;
		}
		if ($this->type !== $type)
			return null;
		$token = strstr($token, ':') ?: $token;
		$this->usertoken = ltrim($token, ':');
		if (!$this->verifyToken($this->usertoken))
			return null;
		return $this->user;
	}

	public function deleteOthers(): void
	{
		$this->db->query('DELETE FROM `' . $this->structure['table'] . '`'
			. ' WHERE (id <> ? AND type = ?) OR expiretime <= ?',
			$this->id, $this->type, time());
	}

	private static function generateToken(): string
	{
		if (function_exists('random_bytes'))
			try {
				$bytes = random_bytes(32);
				$token = hash('sha256', $bytes);
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

		throw new \RuntimeException('Unable to create a secure token.');
	}

	public static function get($name = null, $value = null,
		bool $or = false, bool $multirow = false,
		?string $orderby = null)
	{
		if (is_scalar($name) && !isset($value))
			$name = strstr($name, ':', true) ?: $name;
		return parent::get($name, $value, $or, $multirow, $orderby);
	}
}
