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


    public static function getLike(string $field, string $value): array
    {
        if(!in_array($field, Book::getStructure()['columns'], TRUE))
            throw new \InvalidArgumentException($field. "is not a valid column name");
        $pattern = '%'.$value.'%';

        $query = 'SELECT * FROM '
            . self::getStructure()['table']
            . ' WHERE `. $field .` LIKE ? ;';

        $db = App::getInstance()->db();
        $data = $db->fetchAll($query, $pattern);
        $entities = [];
        foreach ($data as $row)
            $entities[] = new static($row);
        return $entities;
    }

    public static function getByAuthorLike(string $author): array
    {
        return static::getLike("author", $author);
    }

    public static function getByTitleLike(string $author): array
    {
        return static::getLike("title", $author);
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
