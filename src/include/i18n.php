<?php

declare(strict_types=1);

function __(string $message, ...$params): string
{
	return stripslashes(sprintf($message, ...$params));
}
