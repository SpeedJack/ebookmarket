<?php

declare(strict_types=1);

spl_autoload_register(function (string $class)
{
	$prefix = 'EbookMarket\\';
	$baseDir = $GLOBALS['SRC_ROOT'] . '/';

	if (strpos($class, $prefix) !== 0)
		return;

	$classFile = $baseDir . str_replace('\\', '/',
		substr($class, strlen($prefix))) . '.php';
	if (!is_file($classFile))
		throw new LogicException(
			"The file '$classFile' could not be found.");

	require $classFile;

	if (!(class_exists($class, false)
		|| interface_exists($class, false)
		|| trait_exists($class, false)))
		throw new LogicException(
			"File '$classFile' does not implement class '$class'.");
});
