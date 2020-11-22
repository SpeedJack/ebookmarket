<?php

declare(strict_types=1);

namespace EbookMarket;

class InvalidRouteException extends \RuntimeException implements AppException
{
	protected $pageName;
	protected $actionName;

	public function __construct(string $pageName, string $actionName,
		?\Throwable $previous = null)
	{
		$this->pageName = $pageName;
		$this->actionName = $actionName;
		parent::__construct(
			__("Action '%s'::'%s' not found.", $pageName, $actionName),
			404, $previous);
	}

	public function getPageName(): string
	{
		return $this->pageName;
	}

	public function getActionName(): string
	{
		return $this->actionName;
	}
}
