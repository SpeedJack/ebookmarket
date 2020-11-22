<?php

declare(strict_types=1);

namespace EbookMarket;

class Visitor extends AbstractSingleton
{
	protected $page = App::DEFAULT_PAGE;
	protected $action = App::DEFAULT_ACTION;
	protected $getParams = [];
	protected $postParams = [];

	protected function __construct()
	{
		$this->readParams();
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
		if (preg_match('/^[A-Za-z_][a-z0-9_]{0,20}$/', $page) !== 1)
			throw new InvalidRouteException($page);
		$this->page = "{$page}Page";
	}

	public function setAction(?string $action): void
	{
		if (empty($action)) {
			$this->action = App::DEFAULT_ACTION;
			return;
		}
		$action = ucfirst(strtolower(trim($action)));
		if (preg_match('/^[A-Za-z_][a-z0-9_]{0,20}$/', $action) !== 1)
			throw new InvalidRouteException(null, $action);
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
}
