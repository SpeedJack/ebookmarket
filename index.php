<?php

declare(strict_types=1);

namespace EbookMarket;

$APP_ROOT = __DIR__;
$SRC_ROOT = "$APP_ROOT/src";
set_include_path(get_include_path() . PATH_SEPARATOR . "$SRC_ROOT/include");

include_once 'config.php';
if (!isset($config))
	$config = [];

require_once 'autoload.php';
require_once 'i18n.php';
require_once 'panic.php';

$app = App::getInstance($config);
$app->route();
