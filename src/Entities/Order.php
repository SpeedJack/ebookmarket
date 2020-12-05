<?php

declare(strict_types=1);

namespace EbookMarket\Entities;

class Order extends AbstractEntity
{
	public static function getStructure(): array
	{
		return [
			'table' => 'orders',
			'columns' => [
				'id' => [ 'type' => self::UINT, 'auto_increment' => true ],
				'userid' => [ 'type' => self::STR, 'required' => true ],
				'bookid' => [ 'type' => self::STR, 'required' => true ],
				'completed' => [ 'type' => self::BOOL, 'default' => false ],
			]
		];
	}
}
