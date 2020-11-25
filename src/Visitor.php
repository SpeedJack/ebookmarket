<?php

declare(strict_types=1);

namespace EbookMarket;
use \EbookMarket\Entity\User;
use \EbookMarket\Entity\AuthToken;

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

	protected $page = App::DEFAULT_PAGE;
	protected $action = App::DEFAULT_ACTION;
	protected $getParams = [];
	protected $postParams = [];

	public $user;
	public $authToken;


	protected function __construct()
	{
		$this->readParams();
		$this->initSession();
		$this->login();
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
				__('Invalid route specified.'));
		$this->setPage($page ?? null);
		$this->setAction($action ?? null);
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

	public function param(string $key, string $method = 'ANY'): string
	{
		$method = strtoupper($method);
		switch ($method) {
		case 'POST':
			return $this->getParams[$key] ?? null;
		case 'GET':
			return $this->postParams[$key] ?? null;
		case 'ANY':
		default:
			return $this->postParams[$key] ??
				$this->getParams[$key] ?? null;
		}
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
		foreach ($_POST as $key => $value)
			if (preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,20}$/', $key) === 1)
				$this->addPostParams([$key => $value]);
	}

	public function setAuthTokenCookie($authToken, $validator)
	{
		$this->setCookie('authToken', [
				'id' => $authToken->getId(),
				'validator' => $validator
			], $authToken->getExpireTime(), true);
		$this->authToken = $authToken;
		$this->user = $authToken->getUser();
	}

	public function setSessionUser($user)
	{
		$this->user = $user;
		$this->initSession();
		$_SESSION['userid'] = $user->getId();
	}

	public function session($key)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	public function cookie($key)
	{
		return isset($_COOKIE[$key])
			? $_COOKIE[$key] : null;
	}

	public function setCookie($key, $value, $expire = 0, $httponly = false, $path = '/')
	{
		if (is_array($value))
			foreach ($value as $arraykey => $data)
				setcookie($key . '[' . $arraykey . ']', $data,
					$expire, $path, $this->_app->serverName,
					$this->_app->isHttps, $httponly);
		else
			setcookie($key, $value, $expire, $path,
				$this->_app->serverName, $this->_app->isHttps,
				$httponly);
		$_COOKIE[$key] = $value;
	}

	public function unsetCookie($key)
	{
		if (!isset($_COOKIE[$key]))
			return;
		if (is_array($_COOKIE[$key])) {
			foreach ($_COOKIE[$key] as &$cookie)
				$cookie = '';
			$this->setCookie($key, $_COOKIE[$key], time() - 60*60*24);
			return;
		}
		$this->setCookie($key, '', time() - 60*60*24);
		unset($_COOKIE[$key]);
	}

	private function initSession()
	{
		if (session_status() === PHP_SESSION_NONE)
			session_start();
		if (!isset($_SESSION['canary']) || $_SESSION['canary'] <
			time() - $this->app->config['session_canary_lifetime']) {
			session_regenerate_id(true);
			$_SESSION['canary'] = time();
			$_COOKIE[session_name()] = session_id();
		}
	}
	
	private function destroySession()
	{
		if (session_status() !== PHP_SESSION_ACTIVE)
			return;
		$this->unsetCookie(session_name());
		session_destroy();
	}

	private function login()
	{
		$user = false;
		$authToken = false;
		$userid = $this->session('userid');
		$clientToken = $this->cookie('authToken');
		if (!empty($clientToken) && !empty($clientToken['id'])) {
			$authToken = AuthToken::getById($clientToken['id']);
			if ($authToken !== false && !empty($clientToken['validator']))
				$user = $authToken->authenticate($clientToken['validator']);
			if ($user === false
				|| (!empty($userid) && $user->getId() !== $userid)) {
				$authToken = false;
				$user = false;
				$this->destroySession();
				$this->unsetCookie('authToken');
			} else {
				$_SESSION['userid'] = $user->getId();
			}
		} else if (!empty($userid)) {
			$user = $this->_em->getFromDb('User', $userid);
		}
		$this->user = $user ?: null;
		$this->authToken = $authToken ?: null;
		if (!$this->isLoggedIn())
			$this->logout();
	}

	public function isLoggedIn()
	{
		return isset($this->user);
	}

	public function logout()
	{
		if (isset($this->authToken))
			$this->authToken->delete();
		$this->authToken = null;
		$this->user = null;
		$this->destroySession();
		$this->unsetCookie('authToken');
	}
}
