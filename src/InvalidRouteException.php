<?php

declare(strict_types=1);

namespace EbookMarket;

class InvalidRouteException extends \RuntimeException
{
	protected $pageName;
	protected $actionName;

	public function __construct(?string $pageName,
		?string $actionName = null, ?\Throwable $previous = null)
	{
		$this->pageName = $pageName;
		$this->actionName = $actionName;
		parent::__construct(
			__("The requested page could not be found."),
			404, $previous);
	}

	public function getPageName(): ?string
	{
		return $this->pageName;
	}

	public function getActionName(): ?string
	{
		return $this->actionName;
	}
}
