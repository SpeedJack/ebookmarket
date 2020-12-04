<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

class PhpinfoPage extends AbstractPage
{
	public function actionIndex(): void
	{
		phpinfo();
	}

	protected function buildSidebar(): ?string
	{
		return null;
	}
}
