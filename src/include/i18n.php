<?php

declare(strict_types=1);

if (function_exists('gettext')) {
	bindtextdomain('EbookMarket', $GLOBALS['APP_ROOT'] . '/locale');
	bind_textdomain_codeset('EbookMarket', 'UTF-8');
	textdomain('EbookMarket');
} else {
	function gettext(string $message)
	{
		return $message;
	}
}

function __(string $message, ...$params): string
{
	return sprintf(gettext($message), ...$params);
}
