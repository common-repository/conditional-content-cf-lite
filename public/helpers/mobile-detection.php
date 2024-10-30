<?php

/**
 * Mobile detection functions.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * Check if request from mobile.
 *
 * @return boolean True if request from mobile.
 */
function cf_cc_is_mobile()
{
	include_once __DIR__ . '/Mobile_Detect.php';

	$detect = new Mobile_Detect();
	return $detect->isMobile() && !$detect->isTablet();
}

/**
 * Check if request from tablet.
 *
 * @return boolean True if request from tablet.
 */
function cf_cc_is_tablet()
{
	include_once __DIR__ . '/Mobile_Detect.php';

	$detect = new Mobile_Detect();
	return $detect->isTablet();
}
