<?php

declare(strict_types=1);

namespace EbookMarket\Entities;

class User extends AbstractEntity
{
	private $token;

	public function __construct(?array $data = null)
	{
		parent::__construct($data);
		if(isset($data) && !empty($data))
			return;
		$this->remainingattempts = $this->app->config('max_login_attempts');
	}
	public static function getStructure(): array
	{
		return [
			'table' => 'users',
			'columns' => [
				'id' => [ 'auto_increment' => true ],
				'username' => [ 'required' => true ],
				'email' => [ 'required' => true ],
				'passwordhash' => [ 'required' => true ],
				'valid' => [ 'required' => true, 'default' => false ],
				'remainingattempts' => [ 'default' => 5 ],
				'lastattempt' => [ 'default' => 0 ],
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

	public function isLocked(): bool
	{
		if (time() > $this->lastattempt + $this->app->config('lockout_time'))
			$this->remainingattempts = $this->app->config('max_login_attempts');
		return $this->remainingattempts == 0;
	}

	public function failLogin(): void
	{
		if (time() > $this->lastattempt + $this->app->config('lockout_time'))
			$this->remainingattempts = $this->app->config('max_login_attempts');
		if ($this->remainingattempts > 0) {
			$this->remainingattempts--;
			$this->lastattempt = time();
		}
	}

	public function getSessioncount(): int
	{
		$tokens = Token::getAll([
			'userid' => $this->id,
			'type' => Token::SESSION,
		]);
		return empty($tokens) ? 0 : count($tokens);
	}

	public function removeRecoveryTokens(): void
	{
		Token::deleteByUserAndType($this, Token::RECOVERY);
	}
}
