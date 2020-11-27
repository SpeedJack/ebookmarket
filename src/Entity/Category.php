<?php

namespace EbookMarket\Entity;

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
}
