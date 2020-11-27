<?php
namespace EbookMarket\Entity;

use DateTime;
use EbookMarket\Entity\Category;

class Book extends AbstractEntity
{
   public static function getStructure() : array {
    return [
        'table' => 'book',
        'columns' => [
            'id' => [ 'type' => self::UINT, 'auto_increment' => true ],
            'title' => [ 'type' => self::STR, 'required' => true ],
            'author' => [ 'type' => self::STR, 'required' => true ],
            'category' => [ 'type' => self::UINT, 'required' => true ],
            'price' => ['type' => self::FLOAT, 'required' => true],
            'date' => [ 'type' => self::UINT, 'default' => time() ]
        ]
    ];
   }

   public static function getByCategory(string $category) : array {
       return [];
   }

   public static function getByTitleLike(string $title) : array {
       return [];
   }

   public static function getByAuthorLike(string $author) : array {
       return [];
   }

   public static function getSortedByDate(DateTime $date) : array {
       return [];
   }

   public function getCategory(): Category {
        return Category::get($this->categoryid);
   }

   public function placeOrder(User $user): Order {
       $order = new Order(['book' => $this->id, 'user' => $user->id, 'payment_ok' => false]);
       $order->save();
       return $order; 
   }

   
   
}