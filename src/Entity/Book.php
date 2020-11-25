<?php
namespace EbookMarket\Entity;

use EbookMarket\Entity\Category;

class Book extends AbstractEntity
{
    private $title;
    private $author;
    private $category;
    private $price;
    private $date;

    function __construct(int $id, string $title, string $author, Category $category, float $price, string $date){
        parent::__construct($id);
        $this->title = $title;
        $this->author = $author;
        $this->category = $category;
        $this->date = $date;
        $this->price = $price;
    }
}