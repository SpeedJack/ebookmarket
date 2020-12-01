<?php

declare(strict_types=1);

namespace EbookMarket\Services;

abstract class AbstractPaymentService
{
	abstract public function submit(string $cardholder, string $cardno,
		string $validThru, int $cvc): bool;
}
