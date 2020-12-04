<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\Entities\User;
use EbookMarket\Entities\Book;
use EbookMarket\Entities\Category;
use EbookMarket\Visitor;

class BookPage extends AbstractPage
{
	public function __construct()
	{
		parent::__construct();
		$this->enableSearchbar();
		$this->addCss('sidebar');
	}

	public function actionIndex(): void
	{
		$this->setActiveMenu('Shop');
		$this->setTitle('EbookMarket - Books');
		if(Visitor::getMethod() === Visitor::METHOD_GET){
			$cat = $this->visitor->param("cat", Visitor::METHOD_GET);
			$books = [];
			if(!$cat){
				$books = Book::getAll();
			} else {
				$category = Category::get(intval($cat));
				$books = $category->getBooks();
			}
				$this->show('books/booklist', ["books" => $books]);
			}
		
	}

	public function actionView(): void
	{
		$this->setActiveMenu('Shop');
		$this->setTitle('EbookMarket - Books');
		$cat = $this->visitor->param("cat", Visitor::METHOD_GET);
		$books = [];
		if(!$cat){
			$books = Book::getAll();
		} else {
			$books = Book::get("category", intval($cat));
		}
		$this->show('books/booklist', ["books" => $books]);
	}

	protected function buildSidebar(): ?string
	{
		$curcat = $this->visitor->param('cat', Visitor::METHOD_GET);
		$html = $this->buildMenuEntry('All Books', null, null,
			empty($curcat));
		$curcat = intval($curcat);
		$categories = Category::getAll();
		foreach ($categories as $category)
			$html .= $this->buildMenuEntry($category->name, null,
				[ 'cat' => $category->id ],
				$curcat === $category->id);
		return $html;
	}
}
