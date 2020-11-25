<?php
namespace EbookMarket\Entity;

use EbookMarket\Entity\Category;

class Book extends AbstractEntity
{
    private $title;
    private $author;
    private $category;
    private $date;

    function __construct(int $id, string $title, string $author, Category $category, string $date){
        parent::__construct($id);
        $this->$title = $title;
        $this->$author = $author;
        $this->$category = $category;
        $this->$date = $date;
    }
}