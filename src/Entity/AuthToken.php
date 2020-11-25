<?php
namespace EbookMarket\Entity;

use EbookMarket\Entity\Category;

class Book extends AbstractEntity
{
    private $userId;
    private $expiration;

    public function __construct(string $id, $userId, $expiration){
        parent::__construct($id);
        $this->userId = $userId;
        $this->expiration = $expiration;
    }
}