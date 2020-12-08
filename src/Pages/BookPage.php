<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\Entities\User;
use EbookMarket\Entities\Book;
use EbookMarket\Entities\Category;
use EbookMarket\Entities\Purchase;
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

	protected function showBooks(bool $userlibrary = false): void
	{
		$cat = $this->visitor->param('cat', Visitor::METHOD_GET);
		$search = $this->visitor->param('s', Visitor::METHOD_GET);
		$pageparam = $this->visitor->param('p', Visitor::METHOD_GET);
		$category = null;
		if (!empty($cat)) {
			$catid = intval($cat);
			if ($catid !== 0)
				$category = Category::get($catid);
		}
		if (!empty($search))
			$title = 'Search Results';
		else if ($userlibrary)
			$title = 'Your Library';
		else if (isset($category))
			$title = 'Books in ' . static::htmlEscape($category->name);
		else
			$title = 'All Books';
		$search = empty($search) ? null : trim($search);
		$page = 1;
		if (!empty($pageparam)) {
			$page = intval($pageparam);
			$page = $page <= 0 ? 1 : $page;
		}
		$books = Book::getPaged($page, $category,
			$userlibrary ? $this->visitor->user() : null, $search);
		$last = true;
		if (count($books) > 20)
			$last = false;
		$books = array_slice($books, 0, 20, true);
		$this->addCss('booklist');
		$this->show('books/booklist', [
			'title' => $title,
			'books' => $books,
			'page' => $page,
			'lastpage' => $last,
			'islibrary' => $userlibrary,
			'category' => $category,
			'search' => $search ? static::htmlEscapeQuotes($search) : null,
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
		$this->showBooks();
	}

	public function actionLibrary(): void
	{
		if(!$this->visitor->isLoggedIn())
			$this->redirect('account/login');
		$this->setActiveMenu('My Library');
		$this->setTitle('EbookMarket - My Library');
		$this->showBooks(true);
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

	protected function buildPageLink(?int $page = null,
		?Category $category = null, ?string $search = null): string
	{
		$params = [];
		if (!isset($page)) {
			$pageparam = $this->visitor->param('p', Visitor::METHOD_GET);
			$page = 1;
			if (!empty($pageparam)) {
				$page = intval($pageparam);
				$page = $page <= 0 ? 1 : $page;
			}
		}
		$catid = 0;
		if (!isset($category)) {
			$cat = $this->visitor->param('cat', Visitor::METHOD_GET);
			if (!empty($cat))
				$catid = intval($cat);
		} else {
			$catid = $category->id;
		}
		$search = empty($search) ? null : $search;
		if ($catid !== 0)
			$params['cat'] = $catid;
		if ($search !== null)
			$params['s'] = $search;
		if ($page > 1)
			$params['p'] = $page;
		$route = null;
		if ($this->visitor->isAction('library'))
			$route = 'book/library';
		return $this->app->buildLink($route, $params);
	}

	protected function buildSidebar(): ?string
	{
		$curcat = $this->visitor->param('cat', Visitor::METHOD_GET);
		$curcat = intval($curcat);
		$categories = Category::getAll();
		$allbooks = true;
		$html = '';
		$route = null;
		if ($this->visitor->isAction('library'))
			$route = 'book/library';
		foreach ($categories as $category) {
			$html .= $this->buildMenuEntry($category->name, $route, [
				'cat' => $category->id,
				], $curcat === $category->id);
			if ($curcat === $category->id)
				$allbooks = false;
		}
		return $this->buildMenuEntry('All Books', $route, null, $allbooks) . $html;
	}
}
