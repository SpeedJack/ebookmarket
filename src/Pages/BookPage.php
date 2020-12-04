<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\Entities\User;
use EbookMarket\Entities\Book;
use EbookMarket\Entities\Category;
use EbookMarket\Entities\Order;
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
		$id = $this->visitor->param("id", Visitor::METHOD_GET);
		if(Visitor::getMethod() === Visitor::METHOD_GET){
			if($id){
				$book = Book::get(intval($id));
				if($book){
					$user = $this->visitor->user();
					$order = Order::get(["bookid" => $book->id, "userid" => $user->id]);
					$bought = $order ? $order->completed ? true : false : false;
					$this->show("books/bookdetails", ["book" => $book, "bought" => $bought]);
				} else
					$this->error("Book Not Found");
			} else 
				$this->error("Book Not Found");
		}
	}

	public function actionDownload(): void
	{
		if(!$this->visitor->isLoggedIn())
			$this->redirect("account/login");
		$this->setActiveMenu('Shop');
		$this->setTitle('EbookMarket - Download');
		$id = $this->visitor->param("id", Visitor::METHOD_GET);
		if(Visitor::getMethod() === Visitor::METHOD_GET){
			if($id){
				$book = Book::get(intval($id));
				if($book){
					$user = $this->visitor->user();
					$order = Order::get(["bookid" => $book->id, "userid" => $user->id, "completed" => true]);
					if(!$order)
						$this->redirect("/buy", $book->id);
					//TODO DOWNLOAD!
						$this->show("books/download", ["book" => $book, "bought" => true]);
				} else
					$this->error("Book Not Found");
			} else 
				$this->error("Book Not Found");
		}
	}

	public function actionBuy(): void
	{
		if(!$this->visitor->isLoggedIn())
			$this->redirect("account/login");
		$this->setActiveMenu('Shop');
		$this->setTitle('EbookMarket - Buy');
		$id = $this->visitor->param("id", Visitor::METHOD_GET);
		if(Visitor::getMethod() === Visitor::METHOD_GET){
			if($id){
				$book = Book::get(intval($id));
				if($book){
					$user = $this->visitor->user();
					$order = Order::get(["bookid"=>$book->id,"userid"=>$user->id]);
					if(!$order){
						$order = new Order();
						$order->bookid = $book->id;
						$order->userid = $user->id;
						$order->completed = false;
						$order->save();
						$order = Order::get(["bookid"=>$book->id,"userid"=>$user->id]);
					}
					//Book already bought
					if($order->completed)
						$this->redirect("/download", ["id"=>$book->id]);
				
					$this->show("books/buy", ["book" => $book, "orderid" => $order->id]);
				} else
					$this->error("Book Not Found");
			} else 
				$this->error("Book Not Found");
		} else if(Visitor::getMethod() === Visitor::METHOD_POST) {

		}
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
