<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\Entities\User;
use EbookMarket\Entities\Category;
use EbookMarket\Visitor;

class BookPage extends AbstractPage
{
	public function actionIndex(): void
	{
		$this->setActiveMenu('Shop');
		$this->enableSearchbar();
		$this->setTitle('EbookMarket - Books');
		$this->show('test');
	}

	public function actionTestmail(): void
	{
		$this->sendmail('user@example.com', 'Example Username',
			'verify', [
			'username' => 'RandomUser',
			'verifylink' => 'the_verify_link',
		]);
		echo 'Done!';
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
