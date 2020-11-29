<?php

declare(strict_types=1);

namespace EbookMarket\Entity;

class Order extends AbstractEntity
{
	public static function getStructure(): array
	{
		return [
			'table' => 'orders',
			'columns' => [
				'id' => [ 'type' => self::UINT, 'auto_increment' => true ],
				'userid' => [ 'type' => self::STR, 'required' => true ],
				'bookid' => [ 'type' => self::STR, 'required' => true ],
				'completed' => [ 'type' => self::BOOL, 'default' => false ],
			]
		];
	}


	public function pay(string $cc_number, string $cc_cv2, float $amount): bool
	{
		$bookToBuy = Book::get($this->book);
		if($bookToBuy == null)
			return false;
		if($amount != $bookToBuy->price)
			return false;
		$this->payment_ok = true;
		$this->save();
		//if(!validate($cc_number) || !validate($cc_cv2)) return false;
		//if(!payment($cc_number, $cc_cv2, $amount)->success) return false;
		return true;
	}
}
