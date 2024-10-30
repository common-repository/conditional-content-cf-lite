<?php

/**
 * Implement admin settings.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * Class implementing admin settings.
 */
class AdminSettings
{
	/**
	 * Instance of AdminSettings.
	 *
	 * @access private
	 * @static
	 *
	 * @var AdminSettings
	 */
	private static $instance;

	/**
	 * Cannot be cloned.
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function __clone()
	{
	}

	/**
	 * Initialize class and add related hooks.
	 *
	 * @access private
	 */
	private function __construct()
	{
		add_action('admin_init', [$this, 'registerSettings']);
		add_action('admin_menu', [$this, 'registerSettingsPage']);
	}

	/**
	 * Cannot be unserialized.
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function __wakeup()
	{
	}

	/**
	 * Method to get intervals.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function getIntervals()
	{
		static $intervals;

		if (!$intervals) {
			$intervals = [
				'minutes' => __('Minutes', 'cf-conditional-content'),
				'hours' => __('Hours', 'cf-conditional-content'),
				'days' => __('Days', 'cf-conditional-content'),
				'weeks' => __('Weeks', 'cf-conditional-content'),
				'months' => __('Months', 'cf-conditional-content'),
			];
		}

		return $intervals;
	}

	/**
	 * Get instance of AdminSettings.
	 *
	 * @access public
	 * @static
	 *
	 * @return AdminSettings
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Implements the plugin settings page.
	 * Main display will be overriden by a custom post type.
	 *
	 * @access public
	 *
	 * @return bool Return false.
	 */
	public function pluginSettingsPage()
	{
		return false;
	}

	/**
	 * Register settings page in the admin menu.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function registerSettingsPage()
	{
		add_submenu_page(
			'edit.php?post_type=' . CF_CC_CPT_CONDITION,
			__('Settings', 'cf-conditional-content'),
			__('Settings', 'cf-conditional-content'),
			'manage_options',
			CF_CC_PLUGIN_SETTINGS_PAGE,
			[$this, 'renderSettingsPage'],
			15
		);
	}

	/**
	 * Implement settings page.
	 *
	 * @access public
	 *
	 * @param \WP_Post $post Current post.
	 *
	 * @return void
	 */
	public function renderSettingsPage($post)
	{
		$google_maps_api_key = get_settings_google_maps_api();
		$intervals = $this->getIntervals();
		$lazy_load = get_option('cf_cc_settings_lazy_load');
		$remove_data_uninstall = get_settings_remove_data_on_uninstall();
		$visited_pages = get_settings_visited_pages();
		$geoip_providers = get_accepted_geoip_providers();
		$geoip_selected_provider = get_settings_geoip_provider();
		$geoip_provider_key = get_settings_geoip_provider_key();
		require_once 'views/settings-page.php';
	}

	/**
	 * Method to register settings.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function registerSettings()
	{
		register_setting(
			CF_CC_PLUGIN_SETTINGS_GROUP,
			'cf_cc_settings_visited_pages',
			[
				'default' => get_default_settings_visited_pages(),
				'sanitize_callback' => [$this, 'sanitizePagesVisited'],
			]
		);

		register_setting(
			CF_CC_PLUGIN_SETTINGS_GROUP,
			'cf_cc_settings_remove_data_on_uninstall',
			'boolval'
		);

		register_setting(
			CF_CC_PLUGIN_SETTINGS_GROUP,
			'cf_cc_settings_lazy_load',
			'boolval'
		);

		register_setting(
			CF_CC_PLUGIN_SETTINGS_GROUP,
			'cf_cc_settings_geoip_provider',
			[
				'type'              => 'string',
				'default'           => class_exists('\WPEngine\GeoIp') ? 'wp-engine' : '',
				'sanitize_callback' => [$this, 'sanitizeGeolocation'],
			]
		);

		register_setting(
			CF_CC_PLUGIN_SETTINGS_GROUP,
			'cf_cc_settings_google_maps_api_key'
		);

		add_action('admin_notices', [$this, 'outputAdminHeader'], -10);
	}

	/**
	 * Method  to sanitize the visited pages setting values.
	 *
	 * @access public
	 *
	 * @param array $value Value.
	 *
	 * @return array
	 */
	public function sanitizePagesVisited($value)
	{
		$default_values = get_default_settings_visited_pages();
		$intervals = $this->getIntervals();

		if (!is_array($value)) {
			$value = [];
		}

		$value['duration'] = abs($value['duration'] ?? $default_values['duration']);

		if (!isset($intervals[$value['interval']])) {
			$value['interval'] = $default_values['interval'];
		}

		return $value;
	}

	/**
	 * Method  to sanitize the geolocation provider setting values.
	 *
	 * @access public
	 *
	 * @param string $value Value.
	 *
	 * @return string
	 */
	public function sanitizeGeolocation($value)
	{
		$accepted_values = array_keys(get_accepted_geoip_providers());

		$value = filter_var($value, FILTER_SANITIZE_STRING);

		if (!in_array($value, $accepted_values) || '' === $value) {
			return '';
		}

		return $value;
	}

	/**
	 * Add markup for admin toolbar with cf-conditional-content branding logo.
	 *
	 * @return void
	 */
	public function outputAdminHeader()
	{
		if (
			CF_CC_CPT_CONDITION !== filter_input(INPUT_GET, 'post_type')
			&& filter_input(INPUT_GET, 'page') !== 'cf-conditional-content-settings'
		) {
			return;
		}
		?>
			<div class="cf-cc-admin-toolbar">
				<h2 style="background-image:url(<?php echo esc_url(CF_CC_PLUGIN_URL . '/admin/assets/images/conditional-content-icon.svg'); ?>);">
					<?php esc_html_e('Conditional Content', 'cf-conditional-content'); ?>
					<span class="cf-cc-admin-toolbar-copy">
						<?php esc_html_e('by Crowd Favorite', 'cf-conditional-content'); ?>
					</span>
				</h2>
			</div>
		<?php
	}
}
