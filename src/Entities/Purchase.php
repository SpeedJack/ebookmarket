<?php

declare(strict_types=1);

namespace EbookMarket\Entities;

class Order extends AbstractEntity
{
	public static function getStructure(): array
	{
		return [
			'table' => 'purchases',
			'columns' => [
				'id' => [ 'auto_increment' => true ],
				'userid' => [ 'required' => true ],
				'bookid' => [ 'required' => true ],
			]
		];
	}
}
