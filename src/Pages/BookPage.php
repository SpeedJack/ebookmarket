<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\Entities\User;
use EbookMarket\Entities\Book;
use EbookMarket\Entities\Category;
use EbookMarket\Entities\Order;
use EbookMarket\Visitor;
use EbookMarket\Services\FakePaymentService;

class BookPage extends AbstractPage
{
	public function __construct()
	{
		parent::__construct();
		$this->enableSearchbar();
		$this->addCss('sidebar');
	}

	private function showBookList(?User $user = null): void
	{
		$cat = $this->visitor->param('cat', Visitor::METHOD_GET);
		$category = null;
		if (!empty($cat)) {
			$catid = intval($cat);
			if ($catid !== 0)
				$category = Category::get($catid);
		}
		if (isset($category)) {
			$books = $category->getBooks();
			$title = 'Books in ' . static::htmlEscape($category->name);
		} else {
			$books = Book::getAll();
			$title = 'All Books';
		}
		$this->show('books/booklist', [
			'title' => $title,
			'books' => $books,
		]);
	}

	public function actionIndex(): void
	{
		$this->app->reroute('book/list');
	}

	public function actionList(): void
	{
		$this->setActiveMenu('Shop');
		$this->setTitle('EbookMarket - Shop');
		$this->addCss('booklist');
		$this->showBookList();
	}

	public function actionLibrary(): void
	{
		if(!$this->visitor->isLoggedIn())
			$this->redirect('account/login');
		$this->setActiveMenu('My Library');
		$this->setTitle('EbookMarket - My Library');
		$this->getBookList($this->visitor->user());
	}

	public function actionView(): void
	{
		$this->setActiveMenu('Shop');
		$this->setTitle('EbookMarket - Books');
		$id = $this->visitor->param('id', Visitor::METHOD_GET);
		if(Visitor::getMethod() === Visitor::METHOD_GET){
			if($id){
				$book = Book::get(intval($id));
				if($book){
					$user = $this->visitor->user();
					$order = null;
					if($user)
						$order = Order::get(['bookid' => $book->id, 'userid' => $user->id]);
					$bought = $order ? $order->completed ? true : false : false;
					$this->show('books/bookdetails', ['book' => $book, 'bought' => $bought]);
				} else
					$this->error('Book Not Found');
			} else
				$this->error('Book Not Found');
		}
	}

	public function actionDownload(): void
	{
		if(!$this->visitor->isLoggedIn())
			$this->redirect('account/login');
		$this->setActiveMenu('Shop');
		$this->setTitle('EbookMarket - Download');
		$id = $this->visitor->param('id', Visitor::METHOD_GET);
		if(Visitor::getMethod() === Visitor::METHOD_GET){
			if($id){
				$book = Book::get(intval($id));
				if($book){
					$user = $this->visitor->user();
					$order = Order::get(['bookid' => $book->id, 'userid' => $user->id, 'completed' => true]);
					if(!$order)
						$this->redirect('/buy', $book->id);
					header('Content-Type: application/pdf');
					header('Content-Disposition: attachment; filename="'.$book->filehandle.'.pdf"');
					header('Content-Transfer-Encoding: binary');
					header('Accept-Ranges: bytes');
					header('Connection: Keep-Alive');
					header('Content-Length: ' . filesize("assets/ebooks/$book->filehandle.pdf"));
					readfile("assets/ebooks/$book->filehandle.pdf");

				} else
					$this->error('Book Not Found');
			} else
				$this->error('Book Not Found');
		}
	}

	public function actionBuy(): void
	{
		if(!$this->visitor->isLoggedIn())
			$this->redirect('account/login');
		$this->setActiveMenu('Shop');
		$this->setTitle('EbookMarket - Buy');
		$id = $this->visitor->param('id', Visitor::METHOD_GET);
		if(Visitor::getMethod() === Visitor::METHOD_GET){
			if($id){
				$book = Book::get(intval($id));
				if($book){
					$user = $this->visitor->user();
					$order = Order::get(['bookid'=>$book->id,'userid'=>$user->id]);
					if(!$order){
						$order = new Order();
						$order->bookid = $book->id;
						$order->userid = $user->id;
						$order->completed = false;
						$order->save();
						$order = Order::get(['bookid'=>$book->id,'userid'=>$user->id]);
					}
					//Book already bought
					if($order->completed)
						$this->redirect('/download', ['id'=>$book->id]);
					$this->show('books/buy', ['book' => $book, 'orderid' => $order->id]);
				} else
					$this->error('Book Not Found');
			} else
				$this->error('Book Not Found');
		} else if(Visitor::getMethod() === Visitor::METHOD_POST) {
			$orderid = $this->visitor->param('orderid', Visitor::METHOD_POST);
			$cc_number = $this->visitor->param('cc_number', Visitor::METHOD_POST);
			$cc_cv2 = $this->visitor->param('cc_cv2', Visitor::METHOD_POST);
			$expiration = $this->visitor->param('expiration', Visitor::METHOD_POST);

			$order = Order::get($orderid);
			$user = $this->visitor->user();
			if(!$order)
				$this->error('Payment Rejected ', 'The payment has not been accepted');
			if($order->userid !== $user->id)
				$this->error('Payment Rejected ', 'The payment has not been accepted');
			if($order->completed)
				$this->redirect('/view', ['id' => $order->bookid]);
			if(empty($cc_number) || empty($cc_cv2) || empty($expiration))
				$this->error('Payment Rejected ', 'The payment has not been accepted');
			$book = Book::get($order->bookid);
			if(!$book)
				$this->error('Payment Rejected ', 'The payment has not been accepted');
			if(FakePaymentService::submit($cc_number, $expiration, $cc_cv2, $book->price)){
				$order->completed = true;
				$order->save();
				$this->redirect('/view', ['id' => $order->bookid]);
			} else {
				$this->error('Payment Rejected ', 'The payment has not been accepted');
			}

		}
	}

	protected function buildSidebar(): ?string
	{
		$curcat = $this->visitor->param('cat', Visitor::METHOD_GET);
		$curcat = intval($curcat);
		$categories = Category::getAll();
		$allbooks = true;
		$html = '';
		foreach ($categories as $category) {
			$html .= $this->buildMenuEntry($category->name, null,
				[ 'cat' => $category->id ],
				$curcat === $category->id);
			if ($curcat === $category->id)
				$allbooks = false;
		}
		return $this->buildMenuEntry('All Books', null, null, $allbooks) . $html;
	}
}
