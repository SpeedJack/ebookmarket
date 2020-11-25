<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use \EbookMarket\App;
use \EbookMarket\Visitor;

abstract class AbstractPage
{
	protected const KEYWORDS = "ebooks, books, shop";
	protected const AUTHOR = "Lorenzo Cima, Nicola Ferrante, Niccolò Scatena";

	protected $app;
	protected $visitor;
	protected $title = 'EbookMarket';
	protected $cssfiles = [];
	protected $jsfiles = [];

	public function __construct(?string $title = null)
	{
		$this->app = App::getInstance();
		$this->visitor = App::visitor();
		if (isset($title))
			$this->setTitle($title);
		$this->addCss('main');
		$this->addJs('main');
	}

	protected static function htmlEscape(string $str,
		int $flags = ENT_NOQUOTES): string
	{
		return htmlspecialchars($str, $flags | ENT_HTML5, 'UTF-8');
	}

	protected static function htmlEscapeQuotes(string $str,
		int $flags = ENT_COMPAT): string
	{
		return htmlEscape($str, $flags);
	}

	protected function setTitle(string $title): void
	{
		$this->title = static::htmlEscape($title);
	}

	protected function addCss(string $css): void
	{
		$this->cssfiles[$css] = $this->app->getCssFile($css);
	}

	protected function addJs(string $js, bool $defer = true): void
	{
		$this->jsfiles[$js] = [
			'file' => $this->app->getJsFile($js),
			'defer' => $defer
		];
	}

	protected static function getTemplateFile(string $template): string
	{
		$tmplfile = App::SRC_ROOT . "/templates/$template";
		if (file_exists("$tmplfile.php"))
			return "$tmplfile.php";
		if (file_exists("$tmplfile.html"))
			return "$tmplfile.html";
		throw new \InvalidArgumentException(
			__('The required template does not exists.'));
	}

	protected function loadTemplate(string $template, array $params = []): void
	{
		$templatefile = static::getTemplateFile($template);
		$app = $this->app;
		$visitor = $this->visitor;
		extract($params, EXTR_SKIP);
		include $templatefile;
	}

	protected function show(string $template, array $params = []): void
	{
		$skelfile = static::getTemplateFile('skel');
		include $skelfile;
	}

	protected function replyJson(array $data): void
	{
		header('Content-Type: application/json');
		echo json_encode($data);
	}

	protected function redirectAjax(?string $route,
		?array $params = []): void
	{
		$this->replyJson([
			'redirect' => $this->app->buildLink($route, $params),
		]);
	}

	protected function showModal(string $template, array $params = [],
		?string $redirect = null): void
	{
		ob_start();
		$this->loadTemplate($template, $params);
		$html = ob_get_clean();
		$data = [
			'modal' => true,
			'html' => $html,
		];
		if (isset($redirect))
			$data['redirect'] = $redirect;
		$this->replyJson($data);
	}

	protected function message(string $title, string $message,
		?string $redirect = null, bool $error = false): void
	{
		$params = [
			'title' => $title,
			'message' => $message,
			'error' => $error,
		];
		$this->showModal('messagebox', $params, $redirect);
	}

	protected function error(string $title, string $message,
		?string $redirect = null): void
	{
		$this->message($title, $message, $redirect, true);
	}

	protected static function externalRedirect(string $link,
		bool $permanent = false): void
	{
		header("Location: $link", true, $permanent ? 301 : 302);
		exit();
	}

	protected function redirect(?string $route, ?array $params = null,
		bool $permanent = false): void
	{
		$link = $this->app->buildAbsoluteLink($route, $params);
		static::externalRedirect($link, $permanent);
	}

	protected function redirectPermanently(?string $route,
		?array $params = null): void
	{
		$this->redirect($route, $params, true);
	}

	protected function redirectHome(?array $params = null,
		bool $permanent = false): void
	{
		$this->redirect(null, $params, $permanent);
	}

	protected function redirectHomePermanently(?array $params = null): void
	{
		$this->redirectHome($params, true);
	}

	public static function assertMethod(
		int $allowed = Visitor::METHOD_GET | Visitor::METHOD_POST): void
	{
		if (!(Visitor::getMethod() & $allowed))
			throw new AppException(__('Invalid method.'), 405);
	}


	abstract public function actionIndex(): void;
}
