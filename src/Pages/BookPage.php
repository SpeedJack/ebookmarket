<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use \EbookMarket\Entity\User;

class BookPage extends AbstractPage
{
	public function actionIndex(): void
	{
		$this->setTitle(__('EbookMarket - Books'));
		$this->show('test'); //TODO
	}
}
