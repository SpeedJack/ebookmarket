<?php declare(strict_types=1) ?>
<!DOCTYPE html>
<html lang="<?= __('en') ?>">
	<head>
		<meta charset="UTF-8">
		<meta name="keywords" content="<?= self::KEYWORDS ?>">
		<meta name="description" content="<?= __('EbookMarket is a platform for selling ebooks online.') ?>">
		<meta name="author" content="<?= self::AUTHOR ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= $this->title ?></title>
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
		<?php
		foreach ($this->cssfiles as $css)
			echo '<link rel="stylesheet" type="text/css" href="' . $css . '">';
		foreach ($this->jsfiles as $js) {
			$defer = $js['defer'] ? ' defer' : '';
			echo '<script src="' . $js['file'] . '"' . $defer . '></script>';
		}
		?>
	</head>
	<body>
		<?php $this->loadTemplate($template, $params); ?>
	</body>
</html>
