<?php

declare(strict_types=1);

namespace EbookMarket\Services;

class FakePaymentService
{
	private const PROCESSING_TIME = 10;
	private const FAILURE_RATE = 10;

	public static function submit(string $cardno, string $validThru,
		string $cvc, float $amount): bool
	{
		sleep(self::PROCESSING_TIME);
		if (preg_match('/^[0-9]{12,19}$/', $cardno) !== 1)
			return false;
		if (preg_match('/^(20[2-9][0-9])-(0[1-9]|1[0-2])$/', $validThru) !== 1)
			return false;
		if (preg_match('/^[0-9]{3,4}$/', $cvc) !== 1)
			return false;
		return $amount >= 0.01; //&& mt_rand(1, 100) > self::FAILURE_RATE;
	}
}
