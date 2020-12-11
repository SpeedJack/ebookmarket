<?php

declare(strict_types=1);

namespace EbookMarket\Entities;

class Purchase extends AbstractEntity
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

	public function setUser(User $user): void
	{
		$this->setValue('userid', $user->id);
	}

	public function getUser(): ?User
	{
		return User::get($this->userid);
	}

	public function setBook(Book $book): void
	{
		$this->setValue('bookid', $book->id);
	}

	public function getBook(): ?Book
	{
		return Book::get($this->bookid);
	}
}
