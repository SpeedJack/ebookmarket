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

	// TEST METHODS:
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

	public function actionDeluser(): void
	{
		$u = $this->visitor->param('u', 'GET'); //username

		$user = User::get('username', $u);
		echo 'Deleting ' . $user->username . ' (' . $user->email . ')...';
		$user->delete();
		echo ' Done!';
	}

	public function actionEdituser(): void
	{
		$i = $this->visitor->param('i', 'GET'); //userid
		$u = $this->visitor->param('u', 'GET'); //username
		$e = $this->visitor->param('e', 'GET'); //email
		$p = $this->visitor->param('p', 'GET'); //password

		$user = User::get(intval($i));
		if (isset($u))
			$user->username = $u;
		if (isset($e))
			$user->email = $e;
		if (isset($p))
			$user->password = $p;
		$user->save();
	}
}
