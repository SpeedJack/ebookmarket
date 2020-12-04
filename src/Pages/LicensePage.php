<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

class LicensePage extends AbstractPage
{
	public function actionIndex(): void
	{
		$this->setTitle('EbookMarket - License');
		$this->show('license');
	}

	protected function buildSidebar(): ?string
	{
		return null;
	}
}
