<?php

declare(strict_types=1);

function get_previous_exception_desc(\Throwable $ex, int $level = 1): string
{
	if ($ex === null || $ex->getPrevious() === null)
		return '';
	$pex = $ex->getPrevious();
	$txt = str_repeat("\t", $level - 1) . 'Previous Exception:' . PHP_EOL
		. str_repeat("\t", $level) . 'In %s:%d' . PHP_EOL
		. str_repeat("\t", $level) . 'Exception Type: %s' . PHP_EOL
		. str_repeat("\t", $level) . 'Exception Code: %d' . PHP_EOL
		. str_repeat("\t", $level) . 'Exception Message: %s' . PHP_EOL
		. get_previous_exception_desc($ex, $level + 1);
}

function panic(int $httpcode = 500, ?\Throwable $ex = null): void
{
	$errormsg = __("[%s]\t!!! Panic !!!", date('Y-m-d H:i:s')) . PHP_EOL
		. __('HTTP Code: %d', $httpcode) . PHP_EOL;
	if ($ex !== null)
		$errormsg .= __('In %s:%d', $ex->getFile(), $ex->getLine()) . PHP_EOL
			. __('Exception Type: %s', get_class($ex)) . PHP_EOL
			. __('Exception Code: %d', $ex->getCode()) . PHP_EOL
			. __('Exception Message: %s', $ex->getMessage()) . PHP_EOL
			. __('Stack Trace: %s', $ex->getTraceAsString()) . PHP_EOL
			. get_previous_exception_desc($ex) . PHP_EOL
			. str_repeat('-', 80) . PHP_EOL;
	error_log($errormsg);

	http_response_code($httpcode);
	exit(1);
}
