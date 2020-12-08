<?php

declare(strict_types=1);

namespace EbookMarket\Entities;

class Category extends AbstractEntity
{
	public static function getStructure(): array
	{
		return [
		'table' => 'categories',
		'columns' => [
			'id' => [ 'auto_increment' => true ],
			'name' => [ 'required' => true ],
			],
		];
	}

	public static function getAll($name = null, $value = null,
		?string $orderby = null, bool $or = false): array
	{
		if (!isset($orderby))
			$orderby = 'name';
		return parent::getAll($name, $value, $orderby, $or);
	}
}
