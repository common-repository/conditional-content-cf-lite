<?php

/**
 * Settings functions.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * Method to get default values for the visited pages setting.
 *
 * @return array
 */
function get_default_settings_visited_pages()
{
	return [
		'duration' => 2,
		'interval' => 'weeks',
	];
}

/**
 * Method to get accepted geolocation providers.
 *
 * @return array
 */
function get_accepted_geoip_providers()
{
	return CF_CC_GEOIP_PROVIDERS;
}

/**
 * Method to get the Google Maps API key.
 *
 * @return array
 */
function get_settings_google_maps_api()
{
	return get_option('cf_cc_settings_google_maps_api_key');
}

/**
 * Method to get the value for the "lazy_load" setting.
 *
 * @return bool
 */
function get_settings_lazy_load()
{
	return (bool)get_option('cf_cc_settings_lazy_load');
}

/**
 * Method to get the value for the "remove data on uninstall" setting.
 *
 * @return bool
 */
function get_settings_remove_data_on_uninstall()
{
	return (bool)get_option('cf_cc_settings_remove_data_on_uninstall');
}

/**
 * Method to get the value for the "Geolocation Provider" setting.
 *
 * @return bool
 */
function get_settings_geoip_provider()
{
	return get_option('cf_cc_settings_geoip_provider');
}

/**
 * Method to get the value for the "Geolocation Provider" setting.
 *
 * @return bool
 */
function get_settings_geoip_provider_key()
{
	return get_option('cf_cc_settings_geoip_provider_key');
}

/**
 * Method to get the values for the "visited pages" setting.
 *
 * @return array
 */
function get_settings_visited_pages()
{
	return get_option('cf_cc_settings_visited_pages', get_default_settings_visited_pages());
}
