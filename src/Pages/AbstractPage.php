<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\{
	App,
	Visitor,
	Exceptions\ServerException,
	Services\MailerService,
};

abstract class AbstractPage
{
	protected const KEYWORDS = "ebooks, books, shop";
	protected const AUTHOR = "Lorenzo Cima, Nicola Ferrante, Niccolò Scatena";

	protected $app;
	protected $visitor;
	protected $title = 'EbookMarket';
	protected $cssfiles = [];
	protected $jsfiles = [];
	protected $activeMenu = '';
	protected $showSearchbar = false;

	public function __construct(?string $title = null)
	{
		$this->app = App::getInstance();
		$this->visitor = App::visitor();
		if (isset($title))
			$this->setTitle($title);
		$this->addCss('main');
		$this->addJs('main');
		self::enforceCSP();
	}

	private static function enforceCSP(): void
	{
		header('Content-Security-Policy: '
			. "default-src 'none'; "
			. "script-src 'self'; "
			. "style-src 'self'; "
			. "img-src 'self'; "
			. "connect-src 'self'; "
			. "form-action 'self'; "
			. "base-uri 'self'; "
			. "frame-ancestors 'none'; "
			. "block-all-mixed-content;");
		header('Referrer-Policy: strict-origin-when-cross-origin');
		header('X-XSS-Protection: 1; mode=block');
		header('X-Frame-Options: DENY');
	}

	protected static function htmlEscape(string $str,
		int $flags = ENT_NOQUOTES): string
	{
		return htmlspecialchars($str, $flags | ENT_HTML5, 'UTF-8');
	}

	protected static function htmlEscapeQuotes(string $str,
		int $flags = ENT_COMPAT): string
	{
		return self::htmlEscape($str, $flags);
	}

	protected function setTitle(string $title): void
	{
		$this->title = static::htmlEscape($title);
	}

	protected function setActiveMenu(string $menu): void
	{
		$this->activeMenu = $menu;
	}

	protected function enableSearchbar(bool $enable = true): void
	{
		$this->showSearchbar = $enable;
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
			"The required template '$template' does not exists.");
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
		exit();
	}

	protected function replyJson(array $data): void
	{
		header('Content-Type: application/json');
		echo json_encode($data);
		exit();
	}

	protected function redirectAjax(?string $route,
		?array $params = []): void
	{
		$this->replyJson([
			'redirect' => $this->app->buildLink($route, $params),
		]);
	}

	protected function showModal(string $template, array $params = []): void
	{
		ob_start();
		$this->loadTemplate($template, $params);
		$html = ob_get_clean();
		$data = [
			'html' => $html,
		];
		$this->replyJson($data);
	}

	protected function modalMessage(string $title, string $message,
		?string $redirect = null): void
	{
		$params = [
			'title' => $title,
			'message' => $message,
			'redirect' => $redirect,
		];
		$this->showModal('messagebox', $params);
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

	protected function getCsrfToken(): string
	{
		return static::htmlEscapeQuotes(
			$this->visitor->generateCsrfToken()->usertoken);
	}

	protected function getTxtMail(string $template, string &$subject,
		array $params = []): string
	{
		$file = App::SRC_ROOT . "/templates/mail/$template.txt";
		if (!file_exists($file))
			throw new \InvalidArgumentException(
				"The required template '$file' does not exists.");
		$content = file_get_contents($file);
		if ($content === false)
			throw new ServerException(
				"Can not access file '$file'.");
		$content = strtr($content, $params);
		$content = preg_replace('/\r?\n/', "\r\n", $content);
		$content = explode("\r\n\r\n", $content, 2);
		$subject = $content[0];
		if (count($content) !== 2)
			return '';
		return $content[1];
	}

	protected function getHtmlMail(string $template,
		array $params = []): ?string
	{
		$file = App::SRC_ROOT . "/templates/mail/$template.html";
		if (!file_exists($file))
			return null;
		$content = file_get_contents($file);
		if ($content === false)
			return null;
		$content = strtr($content, $params);
		$content = preg_replace('/\r?\n/', "\r\n", $content);
		return $content;
	}

	protected function sendmail(string $to, string $toname,
		string $template, ?array $params = null): void
	{
		if ($this->app->config('mail')['enable'] !== true)
			return;

		$replace = [];
		if (!empty($params))
			foreach ($params as $key => $value)
				$replace['{{' . $key . '}}'] = $value;

		$subject='';
		$txtmsg = $this->getTxtMail($template, $subject, $replace);
		$htmlmsg = $this->getHtmlMail($template, $replace);

		MailerService::sendmail($to, $toname, $subject, $txtmsg,
			$htmlmsg, $params);
	}

	protected function buildMenuEntry(string $name, ?string $route = null,
		?array $params = null, bool $active = false): string
	{
		ob_start();
		$this->loadTemplate('menuentry', [
			'name' => $name,
			'link' => $this->app->buildLink($route, $params),
			'active' => $active,
		]);
		return ob_get_clean();
	}

	protected function buildTopMenuEntry(string $name, ?string $route = null,
		?array $params = null): string
	{
		return $this->buildMenuEntry($name, $route, $params,
			strcasecmp($name, $this->activeMenu) === 0);
	}

	protected function buildMenu(): string
	{
		$html = $this->buildTopMenuEntry('Shop');
		if ($this->visitor->isLoggedIn())
			$html .= $this->buildTopMenuEntry('My Library', 'book/library');
		$html .= '<div class="right">';
		if ($this->visitor->isLoggedIn()) {
			$html .= $this->buildMenuEntry(static::htmlEscape(
				$this->visitor->user()->username), 'account',
				null, $this->visitor->isPage('account'));
			$html .= $this->buildTopMenuEntry('Logout', 'account/logout');
		} else {
			$html .= $this->buildTopMenuEntry('Login', 'account/login');
			$html .= $this->buildTopMenuEntry('Sign Up', 'account/register');
		}
		return $html . '</div>';
	}

	abstract public function actionIndex(): void;
	abstract protected function buildSidebar(): ?string;

/* Legacy mail() code {{{
	protected function getTxtMail(string $template, string &$subject,
		array $params = []): string
	{
		$file = App::SRC_ROOT . "/templates/mail/$template.txt";
		if (!file_exists($file))
			throw new \InvalidArgumentException(
				"The required template '$file' does not exists.");
		$content = file_get_contents($file);
		if ($content === false)
			throw new ServerException(
				"Can not access file '$file'.");
		$content = preg_replace('/\r?\n/', "\r\n", $content);
		$content = strtr($content, $params);
		$content = explode("\r\n\r\n", $content, 2);
		$subject = $content[0];
		if (count($content) !== 2)
			return '';
		$message = wordwrap($content[1], 70, "\r\n");
		return mb_convert_encoding($message, '7bit');
	}

	protected function getHtmlMail(string $template,
		array $params = []): ?string
	{
		$file = App::SRC_ROOT . "/templates/mail/$template.html";
		if (!file_exists($file))
			return null;
		$content = file_get_contents($file);
		if ($content === false)
			return null;
		$content = preg_replace('/\r?\n/', "\r\n", $content);
		$content = strtr($content, $params);
		$content = wordwrap($content, 70, "\r\n", true);
		return quoted_printable_encode($content);
	}

	protected function buildMailMessage(string $txt, ?string $html,
		array &$headers): string
	{
		if (!empty($txt) && isset($html)) {
			$boundary = uniqid('------=_Part_', true);
			$contenttype = "multipart/alternative; boundary=\"$boundary\"";
			$message = $boundary . "\r\n"
				. 'Content-Type: text/plain; charset=UTF-8' . "\r\n"
				. 'Content-Transfer-Encoding: 7bit' . "\r\n"
				. "\r\n" . $txt . "\r\n"
				. $boundary . "\r\n"
				. 'Content-Type: text/html; charset=UTF-8' . "\r\n"
				. 'Content-Transfer-Encoding: quoted-printable' . "\r\n"
				. "\r\n" . $html . "\r\n"
				. $boundary . "\r\n";
		} else if (isset($html)) {
			$contenttype = 'text/html; charset=UTF-8';
			$headers['Content-Transfer-Encoding'] = 'quoted-printable';
			$message = $html;
		} else if (!empty($txt)) {
			$contenttype = 'text/plain; charset=UTF-8';
			$headers['Content-Transfer-Encoding'] = '7bit';
			$message = $txt;
		} else {
			throw new \InvalidArgumentException(
				'Either the text/plain or the text/html mail message must be provided.');
		}
		$headers['MIME-Version'] = '1.0';
		$headers['Content-Type'] = $contenttype;
		return $message;
	}

	protected function sendmail(string $to, string $template,
		?array $params = null): bool
	{
		if ($this->app->config('enable_mail') !== true)
			return true;
		$replace = [];
		if (!empty($params))
			foreach ($params as $key => $value)
				$replace['{{' . $key . '}}'] = $value;
		$subject='';
		$txtmsg = $this->getTxtMail($template, $subject, $replace);
		$htmlmsg = $this->getHtmlMail($template, $replace);
		$headers = $this->app->config('mail_headers');
		$message = $this->buildMailMessage($txtmsg, $htmlmsg, $headers);
		return mail($to, $subject, $message, $headers);
	}
/* }}} */
}
