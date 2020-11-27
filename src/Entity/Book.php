<?php

namespace EbookMarket\Entity;

use EbookMarket\App;
use EbookMarket\Entity\Category;

class Book extends AbstractEntity
{
    public static function getStructure(): array
    {
        return [
            'table' => 'book',
            'columns' => [
                'id' => ['type' => self::UINT, 'auto_increment' => true],
                'title' => ['type' => self::STR, 'required' => true],
                'author' => ['type' => self::STR, 'required' => true],
                'category' => ['type' => self::UINT, 'required' => true],
                'price' => ['type' => self::FLOAT, 'required' => true],
                'year' => ['type' => self::UINT, 'default' => ((int) date('Y'))]
            ]
        ];
    }

    public static function getByCategory(string $category): array
    {
        return Category::getBooksByCategory($category);
    }


    public static function getBooksLike(string $value): array
    {
        $pattern = '%'.$value.'%';

        $query = 'SELECT * FROM '
            . self::getStructure()['table']
            . ' WHERE author LIKE ? OR title LIKE ? ;';

        $db = App::getInstance()->db();
        $data = $db->fetchAll($query, [$pattern, $pattern]);
        $entities = [];
        foreach ($data as $row)
            $entities[] = new static($row);
        return $entities;
    }

    
    public function getCategory(): Category
    {
        return Category::get($this->categoryid);
    }

    public function placeOrder(User $user): Order
    {
        $order = new Order(['book' => $this->id, 'user' => $user->id, 'payment_ok' => false]);
        $order->save();
        return $order;
    }
}
