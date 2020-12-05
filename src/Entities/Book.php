<?php

declare(strict_types=1);

namespace EbookMarket\Entities;
use EbookMarket\App;

class Book extends AbstractEntity
{
	public static function getStructure(): array
	{
		return [
			'table' => 'books',
			'columns' => [
				'id' => [ 'type' => self::UINT, 'auto_increment' => true ],
				'title' => [ 'type' => self::STR, 'required' => true ],
				'author' => [ 'type' => self::STR, 'required' => true ],
				'pubdate' => [ 'type' => self::STR ],
				'price' => [ 'type' => self::FLOAT, 'required' => true ],
				'filehandle' => [ 'type' => self::STR, 'required' => true ],
				'categoryid' => [ 'type' => self::UINT, 'required' => true ],
			]
		];
	}

	public static function getByCategory(string $category, User $user): array
	{
		$query = 'SELECT b.* FROM '
			. static::getStructure()['table']
			. ' b INNER JOIN '
			. Category::getStructure()['table']
			. ' c ON b.categoryid = c.id ';
		$where = 'WHERE c.name = ? ';
		if($user)
		{
			$query .= 'INNER JOIN '
				.Order::getStructure()['table']
				.' o ON b.category = o.category';
			$where .= 'o.userid = ? AND o.complete = 1';
		}

		$db = App::getInstance()->db();
		$data = $db->fetchAll($query, $category, $user->id);
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


}
