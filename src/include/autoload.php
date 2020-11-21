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
		throw new LogicException(
			__("The file '%s' could not be found.", $classFile),
			404);

	require $classFile;

	if (!(class_exists($class, false)
		|| interface_exists($class, false)
		|| trait_exists($class, false)))
		throw new LogicException(
			__("File '%s' does not implement class '%s'.",
				$classFile, $class),
			501);
});
