<?php

declare(strict_types=1);

namespace EbookMarket;

abstract class AbstractSingleton
{
	private static $instances = [];

	public static function getInstance(...$params): self
	{
		$class = get_called_class();
		if (!isset(self::$instances[$class]))
		       self::$instances[$class] = new $class(...$params);
		return self::$instances[$class];
	}
}
