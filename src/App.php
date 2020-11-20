<?php
namespace EbookMarket;

require_once 'string-functions.php';
require_once 'error-functions.php';

class App extends AbstractSingleton
{

	const APP_ROOT = __DIR__;
	public $serverName;
	public $serverPort;
	public $isHttps;
	public $pageClass;
	public $actionName;
	public $config = [];
	protected $_visitor;
	protected $_db;
	protected $_em;
	protected function __construct(array $config = [])
	{
		$this->_setConfig($config);
		$this->_setServerInfos();
		$this->_setLanguage();

		$this->_em = Entity\EntityManager::getInstance();

		@set_exception_handler(array($this, 'exception_handler'));
		@set_error_handler(array($this, 'error_handler'));
	}

	public function init()
	{
		$this->_visitor = $this->_em->create('Visitor');
	}

	public function getDb()
	{
		if (isset($this->_db))
			return $this->_db;

		if (!$this->config['db']['prefer_mysqli_over_pdo'] &&
			extension_loaded('pdo') && extension_loaded('pdo_mysql'))
			$this->_db = Db\PdoAdapter::getInstance($this->config['db']);
		else if (extension_loaded('mysqli'))
			$this->_db = Db\MysqliAdapter::getInstance($this->config['db']);
		else
			throw new \Exception(
				__("No database extension loaded: PDO or MySQLi is required."),
				500
			);

		return $this->_db;
	}

	public function route(array $getParams = [], array $postParams = [],
		$resetParams = false)
	{
		if ($resetParams)
			$this->_visitor->clearParams();
		$this->_visitor->setGetParams($getParams);
		$this->_visitor->setPostParams($postParams);

		$page = str_replace('_', '\\', $this->_visitor->page);
		$action = $this->_visitor->action;
		$page = "Pweb\\Pages\\$page";

		$this->actionName = $action;

		$pageClass = new $page();
		$this->pageClass = $pageClass;

		if (!method_exists($pageClass, $action))
			throw new InvalidRouteException($page, $action);
		$pageClass->$action();

		die();
	}

	public function reroute($page, $action = null,
		array $getParams = [], $postParams = [], $resetParams = true)
	{
		$this->_visitor->setRoute($page, $action);
		$this->route($getParams, $postParams, $resetParams);
	}

	public function externalRedirect($link, $permanent = false)
	{
		header("Location: $link", true, $permanent ? 301 : 302);
		die();
	}

	public function redirect($page, $action = null, array $params = [],
		$permanent = false)
	{
		$link = $this->buildAbsoluteLink($page, $action, $params);
		$this->externalRedirect($link, $permanent);
	}

	public function redirectHome(array $params = [], $permanent = false)
	{
		$this->redirect(null, null, $params, $permanent);
	}

	public function rerouteHome()
	{
		$this->reroute(null);
	}
	public function buildLink($page = null, $action = null, array $params = [])
	{
		if (empty($page))
			return 'index.php';
		$rawAction = '';
		$page = $page === '__current' ?
			trim_suffix($this->_visitor->page, 'Page') : $page;
		$rawPage = "?page=$page";
		if ($this->config['use_url_rewrite'])
			$rawPage = "/$page";
		if (!empty($action)) {
			$action = $action === '__current' ?
				trim_prefix($this->_visitor->action, 'action') : $action;
			if ($this->config['use_url_rewrite'])
				$rawAction = "/$action";
			else
				$rawAction = "&action=$action";
		}
		$rawParams = $this->_getRawParams($params, true);
		return "index.php$rawPage$rawAction$rawParams";
	}

	public function buildExternalLink($link, $https = true, array $params = [],
		$port = 80)
	{
		$link = trim($link);
		$link = trim_prefix($link, 'https://');
		$link = trim_prefix($link, 'http://');

		$portStr = ":$port";
		if ($port === 80)
			$portStr = '';
		$portPos = strpos($link, '/');
		if ($portPos === false) {
			$portPos = strlen($link);
			$portStr .= '/';
		}
		$link = substr_replace($link, $portStr, $portPos, 0);

		return 'http' . ($https ? 's' : '') . "://$link"
			. $this->_getRawParams($params);
	}
	public function buildAbsoluteLink($page = null, $action = null, array $params = [])
	{
		$link = $this->serverName .
			(isset($this->config['website_subfolder']) ?
			$this->config['website_subfolder'] : '');
		if ($this->config['use_url_rewrite']) {
			if (isset($page))
				$link .= "/$page" . (isset($action) ? "/$action" : '');
			else
				$link .= '/index.php';
		} else {
			$link .= '/index.php';
			if (!empty($page))
				$params['page'] = $page;
			if (!empty($action))
				$params['action'] = $action;
		}
		return $this->buildExternalLink($link, $this->isHttps, $params,
			$this->serverPort);
	}
	public function error_handler($severity, $message, $file, $line)
	{
		if (!(error_reporting() & $severity))
			return;
		throw new \ErrorException($message, 0, $severity, $file, $line);
	}

	public function exception_handler($e)
	{
		panic(500, __("Unhandled Exception: %s\n",
			addslashes($e->getMessage())), $e);
	}
	private function _setConfig(array $config = [])
	{
		$this->config = array_replace_recursive([
			'app_name' => '> CTF',
			'header_motd' => 'A platform for Jeopardy style CTFs.',
			'website_subfolder' => '',
			'super_admin_ids' => [ 1 ],
			'db' => [
				'host' => 'localhost',
				'port' => 3306,
				'username' => 'root',
				'password' => '',
				'dbname' => 'pweb_ctf',
				'charset' => 'utf8',
				'prefer_mysqli_over_pdo' => false
			],
			'use_url_rewrite' => false,
			'fallback_server_name' => 'localhost',
			'fallback_server_port' => 80,
			'default_per_page' => 15,
			'session_canary_lifetime' => 60*5,
			'auth_token_length' => 20,
			'auth_token_duration' => 60*60*24*365,
			'min_password_length' => 8,
			'username_regex' => '/^[a-zA-Z0-9._-]{5,32}$/',
			'flag_regex' => '/^(?:f|F)(?:l|L)(?:a|A)(?:g|G)\{[ -z|~]{1,249}\}$/',
			'form_validation' => [
				'username_regex' => '^[a-zA-Z0-9._-]{5,32}$',
				'username_maxlength' => 32,
				'flag_regex' => '^(?:f|F)(?:l|L)(?:a|A)(?:g|G)\{[ -z|~]+\}$',
				'flag_maxlength' => 255
			],
			'locales' => [
				'/^en/i' => 'en_US.UTF-8',
				'/^it/i' => 'it_IT.UTF-8'
			],
			'default_locale' => 'en',
			'selector_languages' => [ 'en', 'it' ],
			'social_names' => [
				'facebook' => '',
				'instagram' => '',
				'twitter' => '',
				'youtube' => ''
			],
			'debug' => false,
			'show_all_exceptions' => false,
			'use_fallback_server_infos' => false,
			'error_log' => null
		], $config);
	}

	private function _getCanonicalLocale($lang)
	{
		$lang = trim($lang);
		foreach ($this->config['locales'] as $pattern => $locale)
			if (preg_match($pattern, $lang))
				return $locale;
		return false;
	}

	private function _isValidLanguage($lang)
	{
		return $this->_getCanonicalLocale($lang) !== false;
	}

	private function _setLanguage()
	{
		if (!extension_loaded('gettext') || php_sapi_name() === 'cli')
			return;

		if (!empty($_GET['lang'])
			&& $this->_isValidLanguage($_GET['lang'])) {
			$lang = $_GET['lang'];
		} else if (!empty($_COOKIE['lang'])
			&& $this->_isValidLanguage($_COOKIE['lang'])) {
			$lang = $_COOKIE['lang'];
		} else {
			$acceptLang = !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])
				? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';

			if (extension_loaded('intl')) {
				\Locale::setDefault($this->config['default_locale']);
				$lang = \Locale::acceptFromHttp($acceptLang);
			} else if (!empty($acceptLang)) {
				$langs = explode(',', $acceptLang);
				array_walk($langs, function (&$lang) {
					$lang = strtr(strtok($lang, ';'), '-', '_');
				});
				foreach ($langs as $elem)
					if ($this->_isValidLanguage($elem)) {
						$lang = $elem;
						break;
					}
			}
		}

		/* always use default locale for easier debugging */
		$lang = (!empty($lang) && !$this->config['debug'])
			? trim($lang) : $this->config['default_locale'];

		$lang = $this->_getCanonicalLocale($lang) ?: $lang;

		/* NOTE: on Windows may not work. Seems a PHP bug. Works when
		 * run from cmd.exe with:
		 * > set LANG=en
		 * > xampp-control.exe
		 */
		@putenv("LANG=$lang");
		@putenv("LANGUAGE=$lang");
		@putenv("LC_MESSAGES=$lang");
		@putenv("LC_ALL=$lang");
		if (@setlocale(LC_ALL, $lang) === false) {
			$lang = $this->_getCanonicalLocale($this->config['default_locale']);
			@putenv("LANG=$lang");
			@putenv("LANGUAGE=$lang");
			@putenv("LC_MESSAGES=$lang");
			@putenv("LC_ALL=$lang");
			@setlocale(LC_ALL, $lang);
		}

		if (!isset($_COOKIE['lang']) || $lang !== $_COOKIE['lang'])
			setcookie('lang', $lang, time()+60*60*24*365*10,
				'/', $this->serverName);
	}

	private function _setServerInfos()
	{
		$this->isHttps = !empty($_SERVER['HTTPS']);

		if ($this->config['use_fallback_server_infos']
			|| php_sapi_name() === 'cli') {
			$this->serverName = $this->config['fallback_server_name'];
			$this->serverPort = $this->config['fallback_server_port'];
			return;
		}

		if (!empty($_SERVER['SERVER_NAME']))
			$this->serverName = trim($_SERVER['SERVER_NAME']);
		else
			$this->serverName = $this->config['fallback_server_name'];
		if (!empty($_SERVER['SERVER_PORT']))
			$this->serverPort = $_SERVER['SERVER_PORT'];
		else
			$this->serverPort = $this->config['fallback_server_port'];
	}

	private function _getRawParams(array $params, $append = false)
	{
		$rawParams = '';
		$i = 0;
		if (empty($params))
			return '';
		foreach ($params as $name => $value) {
			if ($i == 0 && !$append)
				$rawParams .= '?';
			else
				$rawParams .= '&';
			$rawParams .= urlencode($name) . '=' . urlencode($value);
			$i++;
		}
		return $rawParams;
	}

}
