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
		$this->config = $this->mergeConfigDefaults($config);

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

	protected function mergeConfigDefaults(array $config = []): array
	{
		return array_replace_recursive([
				'server_name' => 'localhost',
				'server_port' => $this->https ? 443 : 80,
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

	public function getDb(): Db\AbstractAdapter
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

	public function reroute(?string $page, ?string $action = null,
		?array $getParams = null, ?array $postParams = null,
		bool $resetParams = false): void
	{
		$this->visitor->setRoute($page, $action);
		$this->route($getParams, $postParams, $resetParams);
	}

	public function rerouteHome(): void
	{
		$this->reroute(null);
	}

	public static function externalRedirect(string $link,
		bool $permanent = false): void
	{
		header("Location: $link", true, $permanent ? 301 : 302);
		exit();
	}

	public function redirect(?string $page, ?string $action,
		?array $params = null, bool $permanent = false): void
	{
		$link = $this->buildAbsoluteLink($page, $action, $params);
		self::externalRedirect($link, $permanent);
	}

	public function redirectPermanently(?string $page, ?string $action,
		?array $params = null): void
	{
		$this->redirect($page, $action, $params, true);
	}

	public function redirectHome(?array $params = null,
		bool $permanent = false): void
	{
		$this->redirect(null, null, $params, $permanent);
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

	public function buildLink(?string $page = null, ?string $action = null,
		?array $params = null): string
	{
		if (empty($page) && empty($action))
			return '/' . self::buildGetParams($params);
		$page = $page ?? lcfirst(substr(self::DEFAULT_PAGE, 0, -4));
		$page = $page === '__current' ?
			$this->visitor->getPageParam() : $page;
		$action = $action ?? '';
		$action = $action === '__current' ?
			$this->visitor->getActionParam() : $action;

		if (!$this->modrewrite) {
			$params['page'] = $page;
			$params['action'] = $action;
			return '/' . self::buildGetParams($params);
		}
		return rtrim("/$page/$action", '/')
			. self::buildGetParams($params);
	}

	public function buildAbsoluteLink(?string $page = null,
		?string $action = null, ?array $params = null): string
	{
		$subdir = $this->config['app_subdir'];
		if ($subdir !== null && $subdir !== '')
			$subdir = '/' . trim($subdir, '/');
		$port = $this->config['server_port'];
		$portstr = (($this->isHttps() && $port == 443)
			|| (!$this->isHttps() && $port == 80)) ? '' : ':' . $port;
		return 'http' . ($this->isHttps() ? 's' : '') . '://'
			. $this->config['server_name'] . $portstr . $subdir
			. $this->buildLink($page, $action, $params);
	}

	public function isHttps(): bool
	{
		return $this->https;
	}

	public static function visitor(): Visitor
	{
		return Visitor::getInstance();
	}
}
