<?php

namespace EbookMarket\Entity;

use EbookMarket\Entity\Book;
use EbookMarket\Entity\User;

class Order extends AbstractEntity
{
    public static function getStructure(): array
    {
        return [
            'table' => 'order',
            'columns' => [
                'id' => ['type' => self::UINT, 'auto_increment' => true],
                'user' => ['type' => self::STR, 'required' => true],
                'book' => ['type' => self::STR, 'required' => true],
                'payment_ok' => ['type' => self::BOOL, 'required' => true, 'default' => 'false'],
                'date' => ['type' => self::UINT, 'default' => time()]
            ]
        ];
    }


    public function pay(string $cc_number, string $cc_cv2, float $amount): bool
    {
        $bookToBuy = Book::get($this->book);
        if($bookToBuy == null) return false; 
        if($amount != $bookToBuy->price) return false;
        $this->setValue("payment_ok", true);
        $this->save();
        //if(!validate($cc_number) || !validate($cc_cv2)) return false;
        //if(!payment($cc_number, $cc_cv2, $amount)->success) return false;
        return true;
    }
}
