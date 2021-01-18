<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\Exceptions\InvalidRouteException;

class PhpinfoPage extends AbstractPage
{
	public function __construct(?string $title = null)
	{
	}

	public function actionIndex(): void
	{
		/* Disable page used for testing */
		throw new InvalidRouteException($this->visitor->getRoute());
		phpinfo();
	}

	protected function buildSidebar(): ?string
	{
		return null;
	}
}
