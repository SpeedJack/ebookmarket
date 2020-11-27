<?php

declare(strict_types=1);

namespace EbookMarket\Entity;

use EbookMarket\App;

class Book extends AbstractEntity
{
	public static function getStructure(): array
	{
		return [
			'table' => 'book',
			'columns' => [
				'id' => [ 'type' => self::UINT, 'auto_increment' => true ],
				'title' => [ 'type' => self::STR, 'required' => true ],
				'author' => [ 'type' => self::STR, 'required' => true ],
				'category' => [ 'type' => self::UINT, 'required' => true ],
				'price' => [ 'type' => self::FLOAT, 'required' => true ],
				'year' => [ 'type' => self::UINT ],
			]
		];
	}

	public static function getByCategory(string $category): array
	{
		$query = 'SELECT b.* FROM `'
			. static::getStructure()['table']
			. '` b INNER JOIN `'
			. Category::getStructure()['table']
			. '` c ON b.category = c.id WHERE c.name = ? ;';

		$db = App::getInstance()->db();
		$data = $db->fetchAll($query, $name);
		$entities = [];
		foreach ($data as $row)
			$entities[] = new static($row);
		return $entities;
	}

	public static function getBooksLike(string $value): array
	{
		$pattern = '%'.$value.'%';

		$query = 'SELECT * FROM `' . self::getStructure()['table']
		. '` WHERE `author` LIKE ? OR `title` LIKE ?;';

		$db = App::getInstance()->db();
		$data = $db->fetchAll($query, $pattern, $pattern);
		$entities = [];
		foreach ($data as $row)
			$entities[] = new static($row);
		return $entities;
	}

	public function getCategory(): ?Category
	{
		return Category::get($this->categoryid);
	}

	public function placeOrder(User $user): Order
	{
		$order = new Order();
		$order->book = $this;
		$order->user = $user;
		$order->save();
		return $order;
	}
}
