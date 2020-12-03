<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\Entities\User;
use EbookMarket\Entities\Category;
use EbookMarket\Visitor;

class BookPage extends AbstractPage
{
	public function actionIndex(): void
	{
		$categories = Category::getAll();
		$this->setTitle('EbookMarket - Books');
		$this->show('test', [
			'categories' => $categories,
			'activecat' => $this->visitor->param('cat', Visitor::METHOD_GET),
		]);
	}

	public function actionTestmail(): void
	{
		$this->sendmail('user@example.com', 'Example Username',
			'verify', [
			'username' => 'RandomUser',
			'verifylink' => 'the_verify_link',
		]);
		echo 'Done!';
	}
}
