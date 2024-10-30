<?php

/**
 * Helper functions.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * Check if strings equal or one contains the other.
 *
 * @param string $a First string.
 * @param string $b Second string.
 *
 * @return bool True if strings equal or one contains the other.
 */
function are_they_equal_or_contains($a, $b)
{
	return (false !== strpos($a, $b) || false !== strpos($b, $a) || $a === $b);
}

/**
 * Method to clean URL.
 *
 * @param string $url URL.
 *
 * @return string
 */
function clean_page_url($url)
{
	$url = trim($url, '/');
	$url = str_replace('http://', '', $url);
	$url = str_replace('https://', '', $url);
	$url = str_replace('www.', '', $url);

	return $url;
}

/**
 * Function to get an array of timezones.
 *
 * @return array
 */
function get_timezones()
{
	static $timezones;

	if (!$timezones) {
		$timezones = include CF_CC_PLUGIN_DIR . 'data/timezones.php';
	}

	return $timezones;
}

/**
 * Check if needle is in an array of strings.
 *
 * @param array  $haystack Array of strings.
 * @param string $needle String to check.
 *
 * @return boolean True if needle found.
 */
function haystack_contains_needle($haystack, $needle)
{
	if (!$haystack || !$needle || !is_array($haystack)) {
		return false;
	}

	foreach ($haystack as $val) {
		if ((false !== strpos($val, $needle)) || (false !== strpos($needle, $val))) {
			return true;
		}
	}

	return false;
}

/**
 * Convert a multi-dimensional array into a two-dimensional array, flattening same values for keys.
 * @param  array $array The multi-dimensional array.
 * @return array
 */
function array_flatten($array)
{
	if (!is_array($array)) {
		return false;
	}
	$result = array();
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$result = array_merge_recursive($result, array_flatten($value));
		} else {
			$result = array_merge_recursive($result, array($key => $value));
		}
	}
	return $result;
}
