<?php

declare(strict_types=1);

namespace EbookMarket\Exception;

class Exception extends \Exception
{
	protected $route;
	protected $userMessage;

	public function __construct(string $message, ?string $route = null,
		?string $userMessage = 'Something wrong happened. Please, try again later.',
		int $code = 500, ?\Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->route = $route;
		$this->userMessage = $userMessage;
	}

	public function getRoute(): ?string
	{
		return $this->route;
	}

	public function getUserMessage(): ?string
	{
		return $this->userMessage;
	}

	public function __toString(): string
	{
		$str = '';
		if ($this->route !== null)
			$str .= "On route '{$this->route}': ";
		return $str . parent::__toString();
	}
}
