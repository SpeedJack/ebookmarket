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

	public function actionTestmail(): void
	{
		var_dump($this->sendmail('speedjack95@gmail.com', 'verify', [
			'username' => 'RandomUser',
			'verifylink' => 'the_verify_link',
		]));
	}
}
