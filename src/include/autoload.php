<?php

spl_autoload_register(function ($class)
{
	$prefix = 'EbookMarket\\';
	$baseDir = $GLOBALS['SRC_ROOT'] . '/';

	if (strpos($class, $prefix) !== 0)
		return;

	$classFile = $baseDir . str_replace('\\', '/',
		substr($class, strlen($prefix))) . '.php';
	if (!is_file($classFile)) {
		http_response_code(404);
		exit(1);
	}

	require $classFile;

	if (!(class_exists($class, false)
		|| interface_exists($class, false)
		|| trait_exists($class, false))) {
		http_response_code(501);
		exit(1);
	}
});
