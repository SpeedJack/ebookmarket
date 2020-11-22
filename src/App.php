<?php

declare(strict_types=1);

namespace EbookMarket;

require_once 'panic.php';

class App extends AbstractSingleton
{
	public const DEFAULT_PAGE = 'HomePage';
	public const DEFAULT_ACTION = 'actionIndex';
	public const SRC_ROOT = __DIR__;

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
		$this->config = self::mergeConfigDefaults($config);

		error_reporting($this->config['error_reporting']);

		$this->visitor = Visitor::getInstance();
	}

	public static function start(): void
	{
		include 'config.php';
		if (!isset($config))
			$config = [];
		self::getInstance($config)->route();
	}

	protected static function mergeConfigDefaults(array $config = []): array
	{
		if (!empty($config['app_subdir']))
			$config['app_subdir'] = '/' . trim($config['app_subdir'], '/');
		return array_replace_recursive([
				'server_name' => 'localhost',
				'server_port' => !empty($_SERVER['HTTPS']) ? 443 : 80,
				'app_subdir' => '',
				'db' => [
					'host' => 'localhost',
					'port' => 3306,
					'username' => 'root',
					'password' => '',
					'dbname' => 'ebookmarket',
					'use_mysqli' => false
				],
				'error_reporting' => E_ALL,
			], $config);
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

		$httpcode = 500;
		if ($ex instanceof InvalidRouteException
			|| $ex instanceof Db\Exception)
			$httpcode = $ex->getCode();
		else if ($ex instanceof \BadFunctionCallException)
			$httpcode = 501;
		try {
			$errorPage = new Pages\ErrorPage($httpcode,
				$ex->getMessage());
			$errorPage->showError();
		} catch (\Throwable $e) {
			echo __('Server error. Please try again later.');
		} finally {
			panic($httpcode, $ex);
		}
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
			throw new \RuntimeException(
				__('No database extension loaded: PDO or MySQLi is required.'));

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
		$class = __NAMESPACE__ . "\\Pages\\$page";

		try {
			$page = new $class();
			if (!method_exists($page, $action))
				throw new InvalidRouteException($class, $action);
		} catch (\LogicException $ex) {
			throw new InvalidRouteException($class, $action, $ex);
		}

		$page->$action();

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
		$file = "/css/$cssname.css";
		if (!file_exists($GLOBALS['APP_ROOT'] . $file))
			throw new \InvalidArgumentException(
				__('The required CSS file does not exists.'));
		return $this->config['app_subdir'] . $file;
	}

	public function getJsFile(string $jsname): string
	{
		$file = "/js/$jsname.js";
		if (!file_exists($GLOBALS['APP_ROOT'] . $file))
			throw new \InvalidArgumentException(
				__('The required JavaScript file does not exists.'));
		return $this->config['app_subdir'] . $file;
	}

	public static function buildGetParams(?array $params,
		bool $append = false): string
	{
		if (empty($params))
			return '';
		$getstr = $append ? '&' : '?';
		foreach ($params as $key => $val)
			$getstr .= urlencode($key) . '=' . urlencode($val) . '&';
		return rtrim($getstr, '&');
	}

	public function buildLink(?string $route, ?array $params = null): string
	{
		$subdir = $this->config['app_subdir'];
		$route = $route ?? '';
		$route = rtrim($route, '/');
		if (empty($route))
			return "$subdir" . self::buildGetParams($params);
		$parts = explode('/', $route);
		if (count($parts) === 2)
			list($page, $action) = $parts;
		else if (count($parts) === 1)
			$page = $parts[0];
		else
			throw new \InvalidArgumentException(
				__('Invalid route specified.'));
		$defpage = lcfirst(substr(self::DEFAULT_PAGE, 0, -4));
		$defaction = lcfirst(substr(self::DEFAULT_ACTION, 6));
		if (empty($page) && !empty($action))
			$page = $this->visitor->getPageParam();
		$page = strtolower($page ?: $defpage);
		$action = strtolower($action ?: $defaction);
		if (!$this->modrewrite) {
			if (strcmp($page, $defpage) !== 0)
				$param['page'] = $page;
			if (strcmp($action, $defaction) !== 0)
				$params['action'] = $action;
			return "$subdir" . self::buildGetParams($params);
		}
		if (strcmp($action, $defaction) === 0) {
			$action = '';
			if (strcmp($page, $defpage) === 0)
				$page = '';
		}
		return rtrim("$subdir/$page/$action", '/')
			. self::buildGetParams($params);
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
}
