<?php
/**
 * @file
 * @author Niccolò Scatena <speedjack95@gmail.com>
 * @copyright GNU General Public License, version 3
 */

/**
 * @brief Checks if a string begins with the characters of a specified string.
 *
 * @param[in] string $haystack	The string to check.
 * @param[in] string $needle	The string to search at the beginning of
 * 				$haystack.
 * @retval bool			TRUE if $haystack starts with $needle; FALSE
 * 				otherwise.
 */
function starts_with($haystack, $needle)
{
	$len = strlen($needle);
	return (substr($haystack, 0, $len) === $needle);
}

/**
 * @brief Checks if a string ends with the characters of a specified string.
 *
 * @param[in] string $haystack	The string to check.
 * @param[in] string $needle	The string to search at the end of $haystack.
 * @retval bool			TRUE if $haystack ends with $needle; FALSE
 * 				otherwise.
 */
function ends_with($haystack, $needle)
{
	$len = strlen($needle);
	return $len === 0 || (substr($haystack, -$len) === $needle);
}


/**
 * @brief Remove the specified prefix from the beginning of a string.
 *
 * @param[in] string $str	The string to trim.
 * @param[in] string $prefix	The prefix to remove.
 * @retval string		The string without the prefix.
 */
function trim_prefix($str, $prefix)
{
	$len = strlen($prefix);
	if (starts_with($str, $prefix))
		return substr($str, $len);
	return $str;
}

/**
 * @brief Remove the specified suffix from the end of a string.
 *
 * @param[in] string $str	The string to trim.
 * @param[in] string $suffix	The suffix to remove.
 * @retval string		The string without the suffix.
 */
function trim_suffix($str, $suffix)
{
	$len = strlen($suffix);
	if (ends_with($str, $suffix))
		return substr($str, 0, -$len);
	return $str;
}
