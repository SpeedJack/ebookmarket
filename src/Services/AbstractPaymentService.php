<?php

declare(strict_types=1);

namespace EbookMarket\Services;

abstract class AbstractPaymentService
{
	abstract public static function submit(string $cardno,
		string $validThru, string $cvc, float $amount): bool;
}
