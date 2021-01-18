<?php

declare(strict_types=1);

namespace EbookMarket;

use EbookMarket\Exceptions\{
	InvalidRouteException,
	InvalidValueException,
	UserAuthenticationException,
};

class App extends AbstractSingleton
{
	public const DEFAULT_PAGE = 'BookPage';
	public const DEFAULT_ACTION = 'actionIndex';
	public const LOGIN_ROUTE = 'account/login';
	public const SRC_ROOT = __DIR__;

	public const LOG_EMERGENCY = 0;
	public const LOG_ALERT = 1;
	public const LOG_CRITICAL = 2;
	public const LOG_ERROR = 3;
	public const LOG_WARNING = 4;
	public const LOG_NOTICE = 5;
	public const LOG_INFO = 6;
	public const LOG_DEBUG = 7;

	protected $https;
	protected $modrewrite;
	protected $config;
	protected $db;
	protected $visitor;

	protected function __construct(array $config = [])
	{
		set_error_handler([$this, 'error_handler']);
		set_exception_handler([$this, 'exception_handler']);

		$this->https = !empty($_SERVER['HTTPS']);
		$this->modrewrite = getenv('APACHE_MOD_REWRITE') == true;
		$this->config = static::mergeConfigDefaults($config);

		error_reporting($this->config['error_reporting']);
	}

	public static function start(): void
	{
		if (Visitor::getMethod() === Visitor::METHOD_HEAD)
			exit();
		include 'config.php';
		if (!isset($config))
			$config = [];
		$app = static::getInstance($config);
		Visitor::assertMethod();
		$visitor = Visitor::getInstance();
		if (Visitor::getMethod() === Visitor::METHOD_POST
			&& !$visitor->verifyCsrfToken())
			throw new InvalidValueException(
				'Received an invalid CSRF token.');
		$app->visitor = $visitor;
		$app->route();
	}

	protected static function mergeConfigDefaults(array $config = []): array
	{
		if (isset($config['app_subdir']))
			$config['app_subdir'] = '/' . ltrim($config['app_subdir'], '/');
		if (empty($config['https_port']))
			$config['https_port'] = 443;
		if (empty($config['server_name']))
			Logger::warning('Using SERVER_NAME constant as server name. Note that this may imply a security issue if ServerName is not set in Apache 2 config or UseCanonicalName is off, allowing the user to spoof the name. To avoid issues, set the $config[\'server_name\'] configuration option in config.php (highly recommended) or, at least, check your web server configuration.');
		return array_replace_recursive([
				'server_name' => $_SERVER['SERVER_NAME'],
				'server_port' => !empty($_SERVER['HTTPS'])
					? $config['https_port'] : 80,
				'app_subdir' => '/',
				'force_https' => true,
				'db' => [
					'host' => 'localhost',
					'port' => 3306,
					'username' => 'root',
					'password' => '',
					'dbname' => 'ebookmarket',
					'use_mysqli' => false,
				],
				'mail' => [
					'enable' => true,
					'from_address' => 'noreply@ebookmarket.com',
					'from_name' => 'EbookMarket',
					'smtp_host' => 'localhost',
					'smtp_username' => '',
					'smtp_password' => '',
					'smtp_security' => 'ssl',
					'smtp_port' => 587,
				],
				'session_token_expire_time' => 30*24*60*60,
				'verify_token_expire_time' => 24*60*60,
				'recovery_token_expire_time' => 2*60*60,
				'csrf_token_expire_time' => 30*60,
				'buystep_token_expire_time' => 10*60,
				'min_password_strength' => 4,
				'max_login_attempts' => 5,
				'lockout_time' => 15*60,
				'grecaptcha_secretkey' => '',
				'grecaptcha_sitekey' => '',
				'log_level' => 6,
				'error_reporting' => E_ALL,
			], $config);
	}

	public function config(?string $key = null)
	{
		if ($key === null)
			return $this->config;
		return $this->config[$key] ?? null;
	}

	public function getServerName(): string
	{
		return $this->config['server_name'];
	}

	public function isHttps(): bool
	{
		return $this->https;
	}

	public static function visitor(): Visitor
	{
		return Visitor::getInstance();
	}

	public function db(): Db\AbstractAdapter
	{
		if (isset($this->db))
			return $this->db;

		if (!$this->config['db']['use_mysqli']
			&& extension_loaded('pdo')
			&& extension_loaded('pdo_mysql'))
			$this->db = Db\PdoAdapter::getInstance($this->config['db']);
		else if (extension_loaded('mysqli'))
			$this->db = Db\MysqliAdapter::getInstance($this->config['db']);
		else
			throw new \LogicException(
				'No database extension loaded: PDO or MySQLi is required.');

		return $this->db;
	}

	public function route(?array $getParams = null,
		?array $postParams = null, bool $resetParams = false): void
	{
		if ($resetParams)
			$this->visitor->clearParams();
		$this->visitor->addParams($getParams, $postParams);

		$page = $this->visitor->getPage();
		$action = $this->visitor->getAction();
		if (preg_match('/^[A-Za-z_][a-z0-9_]{0,20}Page$/', $page) !== 1
			|| preg_match('/^action[A-Za-z_][a-z0-9_]{0,20}$/', $action) !== 1)
			throw new InvalidRouteException(
				$this->visitor->getRoute());

		$class = __NAMESPACE__ . "\\Pages\\$page";
		try {
			$pageinstance = new $class();
			if (!method_exists($pageinstance, $action))
				throw new InvalidRouteException(
					$this->visitor->getRoute());
		} catch (\LogicException $ex) {
			throw new InvalidRouteException(
				$this->visitor->getRoute(),
				null, null, 404, $ex);
		}

		try {
			$pageinstance->$action();
		} catch (UserAuthenticationException $ex) {
			$loginlink = $this->buildAbsoluteLink(static::LOGIN_ROUTE);
			header("Location: $loginlink", true, 302);
		}
		exit();
	}

	public function reroute(?string $route, ?array $getParams = null,
		?array $postParams = null, bool $resetParams = false): void
	{
		$this->visitor->setRoute($route);
		$this->route($getParams, $postParams, $resetParams);
	}

	public function rerouteHome(): void
	{
		$this->reroute(null);
	}

	public function getCssFile(string $cssname): string
	{
		$file = "css/$cssname.css";
		if (!file_exists($GLOBALS['APP_ROOT'] . "/$file"))
			throw new \InvalidArgumentException(
				"The required CSS file '/$file' does not exists.");
		return $this->config['app_subdir'] . $file;
	}

	public function getJsFile(string $jsname): string
	{
		$file = "js/$jsname.js";
		if (!file_exists($GLOBALS['APP_ROOT'] . "/$file"))
			throw new \InvalidArgumentException(
				"The required JavaScript file '/$file' does not exists.");
		return $this->config['app_subdir'] . $file;
	}

	public static function buildGetParams(?array $params,
		bool $append = false): string
	{
		if (empty($params))
			return '';
		return ($append ? '&' : '?') . http_build_query($params);
	}

	public function buildLink(?string $route, ?array $params = null): string
	{
		$subdir = $this->config['app_subdir'];
		$route = $route ?? '';
		$route = rtrim($route, '/');
		if (empty($route))
			return "$subdir" . static::buildGetParams($params);
		$parts = explode('/', $route);
		if (count($parts) === 2) {
			list($page, $action) = $parts;
		} else if (count($parts) === 1) {
			$page = $parts[0];
			$action = null;
		} else {
			throw new \InvalidArgumentException(
				"Invalid route specified: $route.");
		}
		$defpage = lcfirst(substr(static::DEFAULT_PAGE, 0, -4));
		$defaction = lcfirst(substr(static::DEFAULT_ACTION, 6));
		if (empty($page) && !empty($action))
			$page = $this->visitor->getPageParam();
		$page = strtolower($page ?: $defpage);
		$action = strtolower($action ?: $defaction);
		if (!$this->modrewrite) {
			if (strcmp($page, $defpage) !== 0)
				$param['page'] = $page;
			if (strcmp($action, $defaction) !== 0)
				$params['action'] = $action;
			return "$subdir" . static::buildGetParams($params);
		}
		if (strcmp($action, $defaction) === 0) {
			$action = '';
			if (strcmp($page, $defpage) === 0)
				$page = '';
		}
		$subdir = rtrim($subdir, '/');
		return rtrim("$subdir/$page/$action", '/')
			. static::buildGetParams($params);
	}

	public function buildAbsoluteLink(?string $route,
		?array $params = null): string
	{
		$port = $this->config['server_port'];
		$portstr = (($this->isHttps() && $port == 443)
			|| (!$this->isHttps() && $port == 80)) ? '' : ':' . $port;
		return 'http' . ($this->isHttps() ? 's' : '') . '://'
			. $this->config['server_name'] . $portstr
			. $this->buildLink($route, $params);
	}

	public function error_handler(int $errno, string $errstr,
		?string $errfile = null, ?int $errline = null): bool
	{
		if (!(error_reporting() & $errno))
			return false;
		throw new \ErrorException($errstr, 500, $errno,
			$errfile, $errline);
	}

	public function exception_handler(\Throwable $ex): void
	{
		while (ob_get_level())
			@ob_end_clean();

		try {
			$errorPage = new Pages\ErrorPage($ex);
			$errorPage->showError();
		} catch (\Throwable $e) {
			echo 'Server error. Please try again later.';
			@Logger::alert('Can not show error page.');
		} finally {
			try {
				@Logger::exception($ex);
			} catch (\Throwable $ex) {
				@Logger::emergency('Can not log exception.');
			}
			exit(1);
		}
	}
}
