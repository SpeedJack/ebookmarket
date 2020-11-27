<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use \EbookMarket\Entity\User;

class BookPage extends AbstractPage
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

	public function actionCreateuser(): void
	{
		$u = $this->visitor->param('u', 'GET'); //username
		$e = $this->visitor->param('e', 'GET'); //email
		$p = $this->visitor->param('p', 'GET'); //password

		$user = new User();
		$user->username = $u;
		$user->password = $p;
		$user->email = $e;
		$user->save();

		echo 'User created.';
	}
}
