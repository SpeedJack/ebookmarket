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
				'id' => [ 'auto_increment' => true ],
				'title' => [ 'required' => true ],
				'author' => [ 'required' => true ],
				'pubdate' => [ 'required' => true ],
				'price' => [ 'required' => true ],
				'filehandle' => [ 'required' => true ],
				'categoryid' => [ 'required' => true ],
			]
		];
	}

	public static function getByCategory(string $category, ?User $user = null): array
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

		if($user)
			$data = $db->fetchAll($query, $category, $user->id);
		else
			$data = $db->fetchAll($query, $category);
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


	public function getCover(): string
	{
		$coverfile = 'assets/covers/' . $this->filehandle;
		if (file_exists($GLOBALS['APP_ROOT'] . "/$coverfile.jpg"))
			return "/$coverfile.jpg";
		if (file_exists($GLOBALS['APP_ROOT'] . "/$coverfile.png"))
			return "/$coverfile.png";
		return '';
	}

	public function getAvailableFormats(): string
	{
		$ebookfile = 'assets/ebooks/' . $this->filehandle;
		$fmts = '';
		if (file_exists($GLOBALS['APP_ROOT'] . "/$ebookfile.pdf"))
			$fmts[] = 'pdf,';
		if (file_exists($GLOBALS['APP_ROOT'] . "/$ebookfile.epub"))
			$fmts[] = 'epub,';
		if (file_exists($GLOBALS['APP_ROOT'] . "/$ebookfile.mobi"))
			$fmts[] = 'mobi';
		return rtrim($fmts,  ',');
	}
}
