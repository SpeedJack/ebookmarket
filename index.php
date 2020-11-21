<?php

declare(strict_types=1);

$APP_ROOT = __DIR__;
$SRC_ROOT = "$APP_ROOT/src";
set_include_path(get_include_path() . PATH_SEPARATOR . "$SRC_ROOT/include");

require_once 'autoload.php';
require_once 'i18n.php';

EbookMarket\App::start();
