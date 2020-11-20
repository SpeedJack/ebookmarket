<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

abstract class AbstractPage
{
	abstract public function actionIndex(): void;
}
