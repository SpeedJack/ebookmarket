<?php
namespace EbookMarket\Entity;

use DateTime;
use \EbookMarket\Entity\User;

class AuthToken extends AbstractEntity
{
    private $userId;
    private $expiration;
    private $user;

    public function __construct(string $authToken, int $userId, DateTime $expiration, User $user){
        parent::__construct($authToken);
        $this->userId = $userId;
        $this->expiration = $expiration;
        $this->user = $user;
    }

    public function isExpired()
	{
		return $this->expiration <= time();
	}

	public function resetExpireTime()
	{
		$this->_set('expireTime', time() + $this->app->config['auth_token_duration']);
    }

    public function verifyToken($token)
	{
		return password_verify($token, $this->authToken);
	}

    public function authenticate($token)
	{
		if ($this->isExpired() || !$this->verifyToken($token))
			return false;
		$this->resetExpireTime();
		return $this->user;
	}
}
