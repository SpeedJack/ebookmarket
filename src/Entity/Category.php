<?php

namespace EbookMarket\Entity;
use EbookMarket\App;

class Category extends AbstractEntity
{

    public static function getStructure(): array
    {
        return [
            'table' => 'category',
            'columns' => [
                'id' => ['type' => self::UINT, 'auto_increment' => true],
                'name' => ['type' => self::STR, 'required' => true],
            ],
        ];
    }

    public static function getBooksByCategory(string $name) : array {
        $query = 'SELECT b.* FROM '
        . Book::getStructure()['table'] 
        . ' b INNER JOIN '
        . static::getStructure()['table'] 
        . ' c ON b.category = c.id
        WHERE c.`name` = ? ;';

        $db = App::getInstance()->db();
		$data = $db->fetchAll($query, $name);
		$entities = [];
		foreach ($data as $row)
			$entities[] = new static($row);
		return $entities;
    }

    public function getBooks() : array {
       return static::getBooksByCategory($this->name);
    } 
}
