<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\Exceptions\InvalidRouteException;

class LoripsumPage extends AbstractPage
{
	public function actionIndex(): void
	{
		/* Disable page used for testing */
		//throw new InvalidRouteException($this->visitor->getRoute(), null, null, 404);
		$this->setTitle('EbookMarket - Test Page');
		$this->show('loripsum');
	}

	protected function buildSidebar(): ?string
	{
		return null;
	}
}
