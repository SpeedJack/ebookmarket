<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\Entities\User;

class BookPage extends AbstractPage
{
	public function actionIndex(): void
	{
		$this->setTitle('EbookMarket - Books');
		$this->show('test'); //TODO
	}
}
