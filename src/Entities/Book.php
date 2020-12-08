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

	public static function getPaged(int $page = 1,
		?Category $category = null, ?User $user = null,
		?string $search = null): array
	{
		$perpage = 20;
		$params = [];
		$query = 'SELECT b.* FROM `' . static::getStructure()['table'] . '` b';
		if (isset($user))
			$query .= ' INNER JOIN `' . Purchase::getStructure()['table'] . '` p'
				. ' ON b.`id` = p.`bookid`';
		if (!empty($search) || isset($category) || isset($user))
			$query .= ' WHERE';
		$and = false;
		if (!empty($search)) {
			$search = "%$search%";
			$query .= ' (b.`title` LIKE ? OR b.`author` LIKE ?)';
			$params[] = $search;
			$params[] = $search;
			$and = true;
		}
		if (isset($category)) {
			$query .= ($and ? ' AND' : '') . ' b.`categoryid` = ?';
			$params[] = $category->id;
			$and = true;
		}
		if (isset($user)) {
			$query .= ($and ? ' AND' : '') . ' p.`userid` = ?';
			$params[] = $user->id;
		}
		$query .= ' ORDER BY b.`title` ASC LIMIT ?, ?';
		$params[] = ($page - 1)*$perpage;
		$params[] = $perpage + 1;

		$db = App::getInstance()->db();
		$data = $db->fetchAll($query, ...$params);
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
