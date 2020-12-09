<?php

declare(strict_types=1);

namespace EbookMarket;

use EbookMarket\Entities\{
	User,
	Token,
};
use EbookMarket\Exceptions\{
	InvalidMethodException,
	UserAuthenticationException,
};

class Visitor extends AbstractSingleton
{
	public const METHOD_UNKNOWN = 0;
	public const METHOD_GET = 1;
	public const METHOD_HEAD = 1 << 1;
	public const METHOD_POST = 1 << 2;
	public const METHOD_PUT = 1 << 3;
	public const METHOD_DELETE = 1 << 4;
	public const METHOD_CONNECT = 1 << 5;
	public const METHOD_OPTIONS = 1 << 6;
	public const METHOD_TRACE = 1 << 7;
	public const METHOD_PATCH = 1 << 8;
	public const METHOD_ANY = ~0;

	protected $app;
	protected $page = App::DEFAULT_PAGE;
	protected $action = App::DEFAULT_ACTION;
	protected $getParams = [];
	protected $postParams = [];
	protected $user;
	protected $csrfToken;
	protected $verifiedCsrf;
	protected $ajax;

	protected function __construct()
	{
		$this->app = App::getInstance();
		$this->readParams();
		$this->authenticate();
	}

	public function clearParams(): void
	{
		$this->getParams = [];
		$this->postParams = [];
	}

	public function addGetParams(?array $params = null): void
	{
		if (!empty($params))
			foreach ($params as $key => $value)
				$this->getParams[$key] = trim($value);
	}

	public function addPostParams(?array $params = null): void
	{
		if (!empty($params))
			foreach($params as $key => $value)
				$this->postParams[$key] = trim($value);
	}

	public function addParams(?array $getParams = null,
		?array $postParams = null): void
	{
		$this->addGetParams($getParams);
		$this->addPostParams($postParams);
	}

	public function setPage(?string $page): void
	{
		if (empty($page)) {
			$this->page = App::DEFAULT_PAGE;
			return;
		}
		$page = ucfirst(strtolower(trim($page)));
		$this->page = "{$page}Page";
	}

	public function setAction(?string $action): void
	{
		if (empty($action)) {
			$this->action = App::DEFAULT_ACTION;
			return;
		}
		$action = ucfirst(strtolower(trim($action)));
		$this->action = "action$action";
	}


	public function setRoute(?string $route): void
	{
		$route = $route ?? '';
		$route = rtrim($route, '/');
		$parts = explode('/', $route);
		if (count($parts) === 2)
			list($page, $action) = $parts;
		else if (count($parts) === 1)
			$page = $parts[0];
		else
			throw new \InvalidArgumentException(
				"Invalid route specified: $route.");
		$this->setPage($page ?? null);
		$this->setAction($action ?? null);
	}

	public function getRoute(): string
	{
		return $this->getPageParam() . '/' . $this->getActionParam();
	}

	public function isRoute(string $route): bool
	{
		return strcasecmp($this->getRoute(), $route) === 0;
	}

	public function isPage(string $page): bool
	{
		return strcasecmp($this->getPageParam(), $page) === 0;
	}

	public function isAction(string $action): bool
	{
		return strcasecmp($this->getActionParam(), $action) === 0;
	}

	public function getPage(): string
	{
		return $this->page;
	}

	public function getAction(): string
	{
		return $this->action;
	}

	public function getPageParam(): string
	{
		return lcfirst(substr($this->page, 0, -4));
	}

	public function getActionParam(): string
	{
		return lcfirst(substr($this->action, 6));
	}

	public function param(string $key,
		int $method = self::METHOD_ANY): ?string
	{
		//$method = strtoupper($method);
		switch ($method) {
		case self::METHOD_POST:
			return $this->postParams[$key] ?? null;
		case self::METHOD_GET:
			return $this->getParams[$key] ?? null;
		case self::METHOD_ANY:
			return $this->postParams[$key] ??
				$this->getParams[$key] ?? null;
		default:
			return null;
		}
	}

	public function hasParam(string $key,
		int $method = self::METHOD_ANY): bool
	{
		return !empty($this->param($key, $method));
	}

	public static function assertMethod(
		int $allowed = self::METHOD_GET | self::METHOD_POST): void
	{
		if (!(static::getMethod() & $allowed))
			throw new InvalidMethodException('Invalid method: '
			. $_SERVER['REQUEST_METHOD'] . '.');
	}

	public function assertAjax()
	{
		if (!$this->isAjax())
			throw new InvalidMethodException('Expected an ajax request. Normal request received.');
	}

	public static function getMethod(): int
	{
		$method = $_SERVER['REQUEST_METHOD'];
		switch($method) {
		case 'GET':
			return self::METHOD_GET;
		case 'HEAD':
			return self::METHOD_HEAD;
		case 'POST':
			return self::METHOD_POST;
		case 'PUT':
			return self::METHOD_PUT;
		case 'DELETE':
			return self::METHOD_DELETE;
		case 'CONNECT':
			return self::METHOD_CONNECT;
		case 'OPTIONS':
			return self::METHOD_OPTIONS;
		case 'TRACE':
			return self::METHOD_TRACE;
		case 'PATCH':
			return self::METHOD_PATCH;
		default:
			return self::METHOD_UNKNOWN;
		}
	}

	protected function setAjax(bool $ajax = true): void
	{
		$this->ajax = $ajax;
	}

	public function isAjax(): bool
	{
		return Visitor::getMethod() === self::METHOD_POST && $this->ajax;
	}

	protected function readParams(): void
	{
		foreach ($_GET as $key => $value) {
			if (preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,20}$/', $key) !== 1)
				continue;
			if (strcasecmp($key, 'page') === 0)
				$this->setPage($value);
			else if (strcasecmp($key, 'action') === 0)
				$this->setAction($value);
			else
				$this->addGetParams([$key => $value]);
		}
		foreach ($_POST as $key => $value) {
			if (preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,20}$/', $key) !== 1)
				continue;
			if (strcasecmp($key, 'ajax') === 0)
				$this->setAjax();
			else
				$this->addPostParams([$key => $value]);
		}
	}

	public function isLoggedIn(bool $valid = true): bool
	{
		return isset($this->user) && (!$valid || $this->user->valid);
	}

	public function login(User $user, bool $rememberme = false): void
	{
		if (!$user->hasAuthtoken())
			$user->login();
		$token = $user->authtoken;
		if ($token === null)
			return;
		$this->setSessionToken($token, $rememberme);
		$this->setUser($user);
	}

	public function logout(): void
	{
		if (!$this->user)
			return;
		$authtoken = $this->cookie('authtoken');
		$this->unsetSessionToken();
		$this->user = null;
		if ($authtoken === null)
			return;
		$token = Token::get($authtoken);
		if ($token === null)
			return;
		$token->delete();
	}

	protected function setSessionToken(Token $token,
		bool $rememberme = false): void
	{
		$this->setCookie('authtoken', $token->usertoken,
			$rememberme ? $token->expiretime : 0);
	}

	protected function unsetSessionToken(): void
	{
		$this->unsetCookie('authtoken');
	}

	public function setCookie(string $key, string $value,
		int $expire = 0, bool $samesiteStrict = false): void
	{
		setcookie($key, $value, [
				'expires' => $expire,
				'path' => '/',
				'domain' => $this->app->getServerName(),
				'secure' => $this->app->isHttps(),
				'httponly' => true,
				'samesite' => $samesiteStrict ? 'Strict' : 'Lax',
			]);
		$_COOKIE[$key] = $value;
	}

	public function unsetCookie(string $key): void
	{
		if (!isset($_COOKIE[$key]))
			return;
		$this->setCookie($key, $_COOKIE[$key], time() - 60*60*24);
		unset($_COOKIE[$key]);
	}

	public function cookie(string $name): ?string
	{
		return $_COOKIE[$name] ?? null;
	}

	protected function setUser(User $user): void
	{
		$this->user = $user;
	}

	public function user(bool $valid = true): ?User
	{
		if (!$this->isLoggedIn($valid))
			return null;
		return $this->user;
	}

	public function assertUser(bool $valid = true): User
	{
		$user = $this->user($valid);
		if ($user === null)
			throw new UserAuthenticationException('Not authenticated.');
		return $user;
	}

	protected function authenticate(): void
	{
		$authtoken = $this->cookie('authtoken');
		if ($authtoken === null)
			return;
		$token = Token::get($authtoken);
		if ($token === null) {
			$this->unsetSessionToken();
			return;
		}
		$user = $token->authenticate($authtoken);
		if ($user === null || !$user->valid) {
			$this->unsetSessionToken();
			$token->delete();
			return;
		}
		$user->setAuthtoken($token);
		$this->setUser($user);
	}

	public function generateCsrfToken(): Token
	{
		if (!isset($this->csrfToken)) {
			$this->csrfToken = Token::createNewCsrf($this->user);
			$this->csrfToken->save();
		}
		return $this->csrfToken;
	}

	public function verifyCsrfToken(int $method = self::METHOD_POST): bool
	{
		if ($this->verifiedCsrf)
			return true;
		$token = $this->param('csrftoken', $method);
		if ($token === null)
			return false;
		$realtoken = Token::get($token);
		if ($realtoken === null)
			return false;
		if ($realtoken->isExpired()) {
			$realtoken->delete();
			return false;
		}
		$token = ltrim(strstr($token, ':'), ':');
		if ($token === false)
			return false;
		if ($realtoken->type !== Token::CSRF
			|| !$realtoken->verifyToken($token))
			return false;
		if ((isset($this->user) && $realtoken->userid !== $this->user->id)
			|| (!isset($this->user) && $realtoken->userid !== null))
			return false;
		$realtoken->delete();
		$this->verifiedCsrf = true;
		return true;
	}
}
