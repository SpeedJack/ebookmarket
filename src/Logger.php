<?php

declare(strict_types=1);

namespace EbookMarket;

class Logger
{
	public const LVL_EMERG = 0;
	public const LVL_ALERT = 1;
	public const LVL_CRIT = 2;
	public const LVL_ERR = 3;
	public const LVL_WARN = 4;
	public const LVL_NOTICE = 5;
	public const LVL_INFO = 6;
	public const LVL_DEBUG = 7;

	protected static $loglevel = self::LVL_INFO;

	public static function setLogLevel(int $level): void
	{
		self::$loglevel = $level;
	}

	public static function emergency(string $message): void
	{
		self::log(self::LVL_EMERG, $message);
	}

	public static function alert(string $message): void
	{
		self::log(self::LVL_ALERT, $message);
	}

	public static function critical(string $message): void
	{
		self::log(self::LVL_CRIT, $message);
	}

	public static function error(string $message): void
	{
		self::log(self::LVL_ERR, $message);
	}

	public function exception(?\Throwable $ex = null): void
	{
		$method = '';
		if (Visitor::getMethod() !== Visitor::METHOD_UNKNOWN)
			$method = $_SERVER['REQUEST_METHOD'];
		$uri = $_SERVER['REQUEST_URI'];
		$errormsg .= "Request: $method $uri" . PHP_EOL;
		$errormsg .= 'HTTP Code: ' . http_response_code() . PHP_EOL;
		if (isset($ex))
			$errormsg .= strval($ex);
		self::error($errormsg);
	}

	public static function warning(string $message): void
	{
		self::log(self::LVL_WARN, $message);
	}

	public static function notice(string $message): void
	{
		self::log(self::LVL_NOTICE, $message);
	}

	public static function info(string $message): void
	{
		self::log(self::LVL_INFO, $message);
	}

	public static function debug(string $message): void
	{
		self::log(self::LVL_DEBUG, $message);
	}

	public static function log(int $level, string $message): void
	{
		if ($level > self::$loglevel)
			return;
		// TODO: log to db
		if ($level < self::LVL_INFO)
			error_log('[' . self::getLogLevelName($level)
				. "] $message" . PHP_EOL);
	}

	public static function getLogLevelName(int $level): string
	{
		switch ($level) {
		case self::LVL_EMERG:
			return 'EMERGENCY';
		case self::LVL_ALERT:
			return 'ALERT';
		case self::LVL_CRIT:
			return 'CRITICAL';
		case self::LVL_ERR:
			return 'ERROR';
		case self::LVL_WARN:
			return 'WARNING';
		case self::LVL_NOTICE:
			return 'NOTICE';
		case self::LVL_INFO:
			return 'INFO';
		case self::LVL_DEBUG:
			return 'DEBUG';
		default:
			return 'LOG';
		}
	}
}
