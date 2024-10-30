<?php

/**
 * Plugin to display page sections and widgets based on configurable visibility rules.
 *
 * Plugin Name:       Conditional Content by Crowd Favorite
 * Plugin URI:		  https://crowdfavorite.com
 * Description:       Display page sections and widgets based on configurable visibility rules.
 * Version:           2.1.2
 * Author:            Crowd Favorite
 * Author URI:		  https://crowdfavorite.com
 * Text Domain:       cf-conditional-content
 * Domain Path:       /languages/
 * License:           GPL v2 or later
 *
 * LICENSE
 * This file is part of Conditional Content by Crowd Favorite.
 *
 * Conditional Content by Crowd Favorite is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package cf-conditional-content
 * @author  Crowd Favorite <support@crowdfavorite.com>
 * @license http://www.gnu.org/licenses/gpl.txt GPL 2.0
 */

namespace CrowdFavorite\ConditionalContent;

 // phpcs:disable
if (!defined('WPINC')) {
    die;
}

/**
 * Check compatibility with other CC versions before loading the plugin.
 */
// Define current and available versions.
$current = plugin_basename(__FILE__);
$versions = [
	'conditional-content-pro/conditional-content-pro.php',
	'conditional-content-pro-osdxp/conditional-content-pro-osdxp.php'
];
// Make sure the Compatibility class isn't already available.
if (!class_exists(__NAMESPACE__ . '\\Lite\\Compatibility')) {
	require_once plugin_dir_path(__FILE__) . 'compatibility.php';
}


// Stop plugin functionality from being loaded if other versions are already present.
if (!Lite\Compatibility::getInstance($current, $versions)->is_compatible) {
	return;
}
// phpcs:enable

define('CF_CC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CF_CC_PLUGIN_URL', plugins_url('/', __FILE__));
define('CF_CC_PLUGIN_BASENAME', plugin_basename(__FILE__));

define('CF_CC_PLUGIN_NAME', 'Conditional Content by Crowd Favorite');
define('CF_CC_PLUGIN_SLUG', 'cf-conditional-content');
define('CF_CC_PLUGIN_VERSION', '2.1.2');

define('CF_CC_CPT_CONDITION', 'cf_cc_condition');

define('CF_CC_PAGE_SLUG', 'cf-conditional-content');

define('CF_CC_CONDITIONS_META_KEY', 'cf_cc_conditions');

define('CF_CC_PLUGIN_SETTINGS_PAGE', CF_CC_PAGE_SLUG . '-settings');
define('CF_CC_PLUGIN_SETTINGS_GROUP', 'cf_cc_settings');

define('CF_CC_VISIT_COUNTS_COOKIE_NAME', 'cf_cc_visit_counts');
define('CF_CC_VISITED_PAGES_COOKIE_NAME', 'cf_cc_visited_pages');
define('CF_CC_VISITED_PAGES_COOKIE_EXPIRY_TIME', 2123467898);

define('CF_CC_PHP_MIN_VER', '7');

define('CF_CC_ADMIN_LIBS_JS_DIR', plugin_dir_path(__FILE__) . 'admin/libs/js/');
define('CF_CC_ADMIN_LIBS_JS_URL', plugins_url('/admin/libs/js/', __FILE__));
define('CF_CC_ADMIN_LIBS_CSS_DIR', plugin_dir_path(__FILE__) . 'admin/libs/css/');
define('CF_CC_ADMIN_LIBS_CSS_URL', plugins_url('/admin/libs/css/', __FILE__));
define('CF_CC_ADMIN_LIBS_FONTS_DIR', plugin_dir_path(__FILE__) . 'admin/libs/fonts/');
define('CF_CC_ADMIN_LIBS_FONTS_URL', plugins_url('/admin/libs/fonts/', __FILE__));
define('CF_CC_ADMIN_BUILD_DIR', plugin_dir_path(__FILE__) . 'admin/build/');
define('CF_CC_ADMIN_BUILD_URL', plugins_url('/admin/build/', __FILE__));
define('CF_CC_PLUGIN_DOCUMENTATION_URL', 'https://crowdfavorite.com');
define('CF_CC_UPSELL_URL', 'https://crowdfavorite.com/products/conditional-content-pro-for-wordpress/');

define('CF_CC_GEOIP_PROVIDERS', [
	'wp-engine' => [
		'name' => 'WP Engine Geolocation'
	]
]);

// phpcs:disable
require_once plugin_dir_path(__FILE__) . 'includes/class-cfconditionalcontent.php';

CFConditionalContent::getInstance();
// phpcs:enable
