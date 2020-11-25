<?php
namespace EbookMarket\Entity;

use EbookMarket\Entity\Book;
use EbookMarket\Entity\User;

class Order extends AbstractEntity
{
    private $book;
    private $user;
    private $date;
    private $payment;

    public function __construct(int $id, Book $book, User $user, string $date, bool $payment)
    {
        parent::__construct($id);
        $this->$book = $book;
        $this->$user = $user;
        $this->$date = $date;
        $this->$payment = $payment;
    }
}