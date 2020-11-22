<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

class HomePage extends AbstractPage
{
	public function actionIndex(): void
	{
		$this->setTitle(__('EbookMarket - HomePage'));
		$this->show('test');
	}

	public function actionPhpinfo(): void
	{
		phpinfo();
	}
}
