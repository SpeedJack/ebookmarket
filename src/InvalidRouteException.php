<?php

declare(strict_types=1);

namespace EbookMarket;

class InvalidRouteException extends \BadMethodCallException
{
	public $pageName;
	public $actionName;

	public function __construct(string $pageName, string $actionName, \Throwable $previous = null)
	{
		$this->pageName = $pageName;
		$this->actionName = $actionName;
		parent::__construct(__("The requested page could not be found."), 404, $previous);
	}
}
