<?php

namespace EbookMarket\Pages;

class HomePage extends AbstractPage
{
	public function actionIndex()
	{
		$this->actionPhpinfo();
	}

	public function actionPhpinfo()
	{
		phpinfo();
	}
}
