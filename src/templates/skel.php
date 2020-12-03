<?php declare(strict_types=1) ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="keywords" content="<?= static::KEYWORDS ?>">
		<meta name="description" content="EbookMarket is a platform for selling ebooks online.">
		<meta name="author" content="<?= static::AUTHOR ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= $this->title ?></title>
		<link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico">
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
		<?php 
			$this->loadTemplate("header", $params);
			if (empty($params['noaside']))
				$this->loadTemplate("sidebar", $params); 
			$this->loadTemplate($template, $params);
			$this->loadTemplate("footer", $params);
		?>
	</body>
</html>
