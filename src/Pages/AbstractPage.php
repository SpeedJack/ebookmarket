<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

abstract class AbstractPage
{
	public function __construct()
	{
	}

	abstract public function actionIndex(): void;
}
