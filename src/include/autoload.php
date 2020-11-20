<?php

declare(strict_types=1);

require_once 'panic.php';

spl_autoload_register(function (string $class)
{
	$prefix = 'EbookMarket\\';
	$baseDir = $GLOBALS['SRC_ROOT'] . '/';

	if (strpos($class, $prefix) !== 0)
		return;

	$classFile = $baseDir . str_replace('\\', '/',
		substr($class, strlen($prefix))) . '.php';
	if (!is_file($classFile))
		panic(404);

	require $classFile;

	if (!(class_exists($class, false)
		|| interface_exists($class, false)
		|| trait_exists($class, false)))
		panic(501);
});
