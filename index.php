<?php

namespace EbookMarket;

$APP_ROOT = __DIR__;
$SRC_ROOT = "$APP_ROOT/src";
set_include_path(get_include_path() . PATH_SEPARATOR . "$SRC_ROOT/include");

require_once 'autoload.php';


/* Temporary Router (damnly vulnerable) */
$page = 'HomePage';
$action = 'actionIndex';
if (!empty($_GET['page']))
	$page = ucfirst($_GET['page']) . 'Page';
if (!empty($_GET['action']))
	$action = 'action' . ucfirst($_GET['action']);
$className = __NAMESPACE__ . "\\Pages\\$page";

$class = new $className();
if (!method_exists($class, $action)) {
	http_response_code(404);
	exit(1);
}
$class->$action();
