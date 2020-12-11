<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\{
	Entities\User,
	Entities\Token,
	Entities\Book,
	Entities\Category,
	Entities\Purchase,
	Visitor,
	Services\FakePaymentService,
};

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

	protected function getBuyStepToken(bool $steptwo = false): string
	{
		$type = $steptwo ? Token::BUYSTEP2 : Token::BUYSTEP1;
		$token = Token::createNew($this->visitor->user(), $type);
		$token->save();
		return static::htmlEscapeQuotes($token->usertoken);
	}

	public function actionIndex(): void
	{
		$this->app->reroute('book/list');
	}

	public function actionList(): void
	{
		Visitor::assertMethod(Visitor::METHOD_GET);
		$this->setActiveMenu('Shop');
		$this->setTitle('EbookMarket - Shop');
		$this->showBooks();
	}

	public function actionLibrary(): void
	{
		Visitor::assertMethod(Visitor::METHOD_GET);
		$this->visitor->assertUser();
		$this->setActiveMenu('My Library');
		$this->setTitle('EbookMarket - My Library');
		$this->showBooks(true);
	}

	public function actionView(): void
	{
		Visitor::assertMethod(Visitor::METHOD_GET);
		$this->setActiveMenu('Shop');
		$id = $this->visitor->param('id', Visitor::METHOD_GET);
		if (empty($id))
			throw new InvalidValueException(
				'Submitted an empty book id.',
				$this->visitor->getRoute(),
				'Can not find this book.');
		$id = intval($id);
		if ($id === 0)
			throw new InvalidValueException(
				'Submitted an invalid book id.',
				$this->visitor->getRoute(),
				'Can not find this book.');
		$book = Book::get($id);
		if($book === null)
			throw new InvalidValueException(
				'Submitted a non-existent book id.',
				$this->visitor->getRoute(),
				'Can not find this book.');

		$bought = false;
		if ($this->visitor->isLoggedIn()) {
			$purchase = Purchase::get([
				'bookid' => $book->id,
				'userid' => $this->visitor->user()->id,
			]);
			$bought = $purchase !== null;
		}
		$this->setTitle('EbookMarket - ' . $book->title);
		$this->addCss('book');
		if (!$bought){
			$this->addJs('buyform');
			$this->addCss('form');
		}
			
		$this->show('books/bookdetails', [
			'book' => $book,
			'bought' => $bought,
		]);
	}

	public function actionDownload(): void
	{
		Visitor::assertMethod(Visitor::METHOD_GET);
		if(!$this->visitor->isLoggedIn())
			$this->redirect('account/login');
		$id = $this->visitor->param('id', Visitor::METHOD_GET);
		$fmt = $this->visitor->param('fmt', Visitor::METHOD_GET);
		if (empty($id))
			throw new InvalidValueException(
				'Submitted an empty book id.',
				$this->visitor->getRoute(),
				'Can not find this book.');
		$id = intval($id);
		if ($id === 0)
			throw new InvalidValueException(
				'Submitted an invalid book id.',
				$this->visitor->getRoute(),
				'Can not find this book.');
		$book = Book::get($id);
		if($book === null)
			throw new InvalidValueException(
				'Submitted a non-existent book id.',
				$this->visitor->getRoute(),
				'Can not find this book.');

		$purchase = Purchase::get([
			'bookid' => $book->id,
			'userid' => $this->visitor->user()->id,
		]);
		if ($purchase === null)
			throw new UserAuthenticationException('Not authorized.');

		if (empty($fmt))
			$fmt = 'pdf';
		$fmt = trim($fmt);
		$contenttype = '';
		$filename = '';
		$file = $book->getEbookFile($fmt, $contenttype, $filename);
		if ($file === null)
			throw new InvalidValueException(
				'User requested a book in a format not available.',
				$this->visitor->getRoute(),
				'Can not find this book.');

		header("Content-Type: $contenttype");
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary');
		header('Accept-Ranges: bytes');
		header('Content-Length: ' . filesize($file));
		readfile($file);
	}

	//TODO: make it work; split in two actions (Buy, Finish); check buystep tokens
	public function actionBuy(): void
	{
		Visitor::assertMethod(Visitor::METHOD_POST);
		
		if(!$this->visitor->isLoggedIn())
			$this->redirect('account/login');
		$this->setActiveMenu('Shop');
		$this->setTitle('EbookMarket - Buy');
		if(Visitor::getMethod() === Visitor::METHOD_POST){
			$this->visitor->assertAjax();
			$id = $this->visitor->param('id', Visitor::METHOD_POST);
			$steptoken = $this->visitor->param('steptoken', Visitor::METHOD_POST);
			$token = Token::get($steptoken);
			if( !$token ||
				!$token->validateType(Token::BUYSTEP1) || 
				!$token->authenticate($steptoken, Token::BUYSTEP1) 
			) {
				throw new InvalidValueException(
					'Invalid Request',
					$this->visitor->getRoute(),
					'Invalid Request');
			}
			if(!$id) {
				throw new InvalidValueException(
					'Invalid Request',
					$this->visitor->getRoute(),
					'Invalid Request');
			};

			$book = Book::get(intval($id));
			if(!$book){
				throw new InvalidValueException(
					'Invalid Request',
					$this->visitor->getRoute(),
					'Invalid Request');
			}
			$user = $this->visitor->user();
			$purchase = Purchase::get(['bookid' => $book->id, 'userid' => $user->id]);
			
			if($purchase){
				$this->show('books/bookdetails', [
					'book' => $book,
					'bought' => true,
				]);
			} else {
				$buystep2 = $this->getBuyStepToken(true);
				$this->showModal('books/buy', [
					'steptoken' => $buystep2,
					'book' => $book,
					'reload' => true
				]);
			}

			
			
			// if($book){
			// 	$user = $this->visitor->user();
			// 	$purchase = Purchase::get(['bookid'=>$book->id,'userid'=>$user->id]);
			// 	if(!$purchase){
			// 		$purchase = new Purchase();
			// 		$purchase->bookid = $book->id;
			// 		$purchase->userid = $user->id;
			// 		$purchase->completed = false;
			// 		$purchase->save();
			// 		$purchase = Purchase::get(['bookid'=>$book->id,'userid'=>$user->id]);
			// 	}
			// 		//Book already bought
			// 		if($purchase->completed)
			// 			$this->redirect('/download', ['id'=>$book->id]);
			// 		$this->show('books/buy', ['book' => $book, 'purchaseid' => $purchase->id]);
			// 	} else
			// 		$this->error('Book Not Found');
			// } else
			// 	$this->error('Book Not Found');
		// } else if(Visitor::getMethod() === Visitor::METHOD_POST) {
		// 	$purchaseid = $this->visitor->param('purchaseid', Visitor::METHOD_POST);
		// 	$cc_number = $this->visitor->param('cc_number', Visitor::METHOD_POST);
		// 	$cc_cv2 = $this->visitor->param('cc_cv2', Visitor::METHOD_POST);
		// 	$expiration = $this->visitor->param('expiration', Visitor::METHOD_POST);

		// 	$purchase = Purchase::get($purchaseid);
		// 	$user = $this->visitor->user();
		// 	if(!$purchase)
		// 		$this->error('Payment Rejected ', 'The payment has not been accepted');
		// 	if($purchase->userid !== $user->id)
		// 		$this->error('Payment Rejected ', 'The payment has not been accepted');
		// 	if($purchase->completed)
		// 		$this->redirect('/view', ['id' => $purchase->bookid]);
		// 	if(empty($cc_number) || empty($cc_cv2) || empty($expiration))
		// 		$this->error('Payment Rejected ', 'The payment has not been accepted');
		// 	$book = Book::get($purchase->bookid);
		// 	if(!$book)
		// 		$this->error('Payment Rejected ', 'The payment has not been accepted');
		// 	if(FakePaymentService::submit($cc_number, $expiration, $cc_cv2, $book->price)){
		// 		$purchase->completed = true;
		// 		$purchase->save();
		// 		$this->redirect('/view', ['id' => $purchase->bookid]);
		// 	} else {
		// 		$this->error('Payment Rejected ', 'The payment has not been accepted');
		// 	}

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
