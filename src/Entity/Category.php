<?php

declare(strict_types=1);

namespace EbookMarket\Entity;

class Category extends AbstractEntity
{
	public static function getStructure(): array
	{
		return [
		'table' => 'categories',
		'columns' => [
			'id' => [ 'type' => self::UINT, 'auto_increment' => true ],
			'name' => [ 'type' => self::STR, 'required' => true ],
			],
		];
	}

	public function getBooks(): array
	{
		return Book::getByCategory($this->name);
	}
}
