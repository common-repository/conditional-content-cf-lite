<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * Class implementing the admin functionality.
 */
class Admin
{
	/**
	 * Instance of Admin.
	 *
	 * @access private
	 * @static
	 *
	 * @var Admin
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
	 * Initialize the class and set its properties.
	 *
	 * @access protected
	 */
	private function __construct()
	{
		$this->loadDependencies();

		add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
		add_action('enqueue_block_editor_assets', [$this, 'enqueueGutenbergScript'], 0);
		add_action('admin_enqueue_scripts', [$this, 'enqueueStyles']);
		add_action('plugin_action_links_' . CF_CC_PLUGIN_BASENAME, [$this, 'addSettingsLink']);
	}

	/**
	 * Adds plugin settings page link to plugin links in WordPress Dashboard Plugins Page
	 *
	 * @param array $settings Uses $prefix . "plugin_action_links_$plugin_file" action.
	 * @return array Array of settings
	 */
	public function addSettingsLink($settings)
	{
		$admin_anchor = sprintf(
			'<a href="%s">%s</a>',
			esc_url(admin_url('edit.php?post_type=' . CF_CC_CPT_CONDITION . '&page=' . CF_CC_PLUGIN_SETTINGS_PAGE)),
			esc_html__('Settings', 'cf-conditional-content')
		);

		if (! is_array($settings)) {
			return [$admin_anchor];
		}

		return array_merge([$admin_anchor], $settings);
	}
	/**
	 * Get instance of Admin.
	 *
	 * @access public
	 * @static
	 *
	 * @return Admin
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
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
	 * Register the Gutenberg JavaScript for the admin area.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function enqueueGutenbergScript()
	{
		wp_register_script(
			CF_CC_PLUGIN_SLUG . '-gutenberg',
			CF_CC_ADMIN_BUILD_URL . 'gutenberg.js',
			['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'],
			filemtime(CF_CC_ADMIN_BUILD_DIR . 'gutenberg.js'),
			true // Enqueue the script in the footer.
		);

		$cpts = get_posts(
			[
				'post_type'      => CF_CC_CPT_CONDITION,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			]
		);

		$conditions = array();
		foreach ($cpts as $cpt) {
			$conditions[] = [
				'label' => $cpt->post_title,
				'value' => $cpt->ID
			];
		}

		wp_localize_script(
			CF_CC_PLUGIN_SLUG . '-gutenberg',
			'CFCCGBAdminSettings',
			[
				'conditions' => $conditions
			]
		);

		wp_enqueue_script(CF_CC_PLUGIN_SLUG . '-gutenberg');

		register_block_type('crowdfavorite/conditional-content-block', array(
			'editor_script' => CF_CC_PLUGIN_SLUG . '-gutenberg',
		));
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function enqueueScripts()
	{
		if (!($screen = get_current_screen()) || 'cf_cc_condition' !== $screen->post_type) {
			return;
		}

		wp_enqueue_script(
			CF_CC_PLUGIN_SLUG . '-helpers',
			CF_CC_ADMIN_LIBS_JS_URL . 'helpers.js',
			[],
			filemtime(CF_CC_ADMIN_LIBS_JS_DIR . 'helpers.js'),
			false
		);

		wp_enqueue_script(
			CF_CC_PLUGIN_SLUG . '-date-time-picker-full',
			CF_CC_ADMIN_LIBS_JS_URL . 'datetimepicker.full.min.js',
			['jquery'],
			filemtime(CF_CC_ADMIN_LIBS_JS_DIR . 'datetimepicker.full.min.js'),
			false
		);

		wp_enqueue_script(
			CF_CC_PLUGIN_SLUG . '-weekly-schedule',
			CF_CC_ADMIN_LIBS_JS_URL . 'jquery.weekly-schedule-plugin.min.js',
			['jquery'],
			filemtime(CF_CC_ADMIN_LIBS_JS_DIR . 'jquery.weekly-schedule-plugin.min.js'),
			false
		);

		wp_enqueue_script(
			CF_CC_PLUGIN_SLUG . '-google-places',
			CF_CC_ADMIN_LIBS_JS_URL . 'google-places.js',
			['jquery'],
			filemtime(CF_CC_ADMIN_LIBS_JS_DIR . 'google-places.js'),
			true
		);

		wp_enqueue_script(
			CF_CC_PLUGIN_SLUG . '-easy-autocomplete',
			CF_CC_ADMIN_LIBS_JS_URL . 'jquery.easy-autocomplete.min.js',
			['jquery'],
			'1.3.5',
			false
		);

		$google_maps_api_key = get_settings_google_maps_api();

		if ($google_maps_api_key) {
			wp_enqueue_script(
				CF_CC_PLUGIN_SLUG . '-google-maps',
				sprintf(
                    // phpcs:ignore Generic.Files.LineLength.TooLong
					'https://maps.googleapis.com/maps/api/js?key=%s&language=en&libraries=places&callback=initCFGeolocationAutocomplete',
					$google_maps_api_key
				),
				[],
				'20190923.1033',
				true
			);
		}

		wp_enqueue_script(
			CF_CC_PLUGIN_SLUG . '-admin',
			CF_CC_ADMIN_BUILD_URL . (WP_DEBUG ? 'app.js' : 'app.min.js'),
			['jquery'],
			filemtime(CF_CC_ADMIN_BUILD_DIR . (WP_DEBUG ? 'app.js' : 'app.min.js')),
			false
		);

		wp_localize_script(
			CF_CC_PLUGIN_SLUG . '-admin',
			'CFCCAdmin',
			[
				'text' => [
					'duplicatedQueryString' => esc_html__(
						'This query string is already in use with the current trigger.',
						'cf-conditional-content'
					),
					'duplicatedQueryStringOnPublish' => esc_html__(
                        // phpcs:ignore Generic.Files.LineLength.TooLong
						'It is not possible to create two query strings with the same name. If you publish now, the second version will be deleted.',
						'cf-conditional-content'
					),
				],
			]
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function enqueueStyles()
	{
		if (!($screen = get_current_screen()) || 'cf_cc_condition' !== $screen->post_type) {
			return;
		}

		wp_enqueue_style(
			CF_CC_PLUGIN_SLUG . '-grid',
			CF_CC_ADMIN_LIBS_CSS_URL . 'grid.css',
			[],
			filemtime(CF_CC_ADMIN_LIBS_CSS_DIR . 'grid.css'),
			'all'
		);

		wp_enqueue_style(
			CF_CC_PLUGIN_SLUG . '-admin',
			CF_CC_ADMIN_BUILD_URL . (WP_DEBUG ? 'style.css' : 'style.min.css'),
			[],
			filemtime(CF_CC_ADMIN_BUILD_DIR . (WP_DEBUG ? 'style.css' : 'style.min.css')),
			'all'
		);

		wp_enqueue_style(
			CF_CC_PLUGIN_SLUG . '-date-time-picker',
			CF_CC_ADMIN_LIBS_CSS_URL . 'datetimepicker.css',
			[],
			filemtime(CF_CC_ADMIN_LIBS_CSS_DIR . 'datetimepicker.css'),
			'all'
		);

		wp_enqueue_style(
			CF_CC_PLUGIN_SLUG . '-easy-autocomplete',
			CF_CC_ADMIN_LIBS_CSS_URL . 'easy-autocomplete.min.css',
			[],
			'1.3.5',
			'all'
		);

		if (is_rtl()) {
			wp_enqueue_style(
				CF_CC_PLUGIN_SLUG . '-admin-rtl',
				CF_CC_ADMIN_LIBS_CSS_URL . 'admin-rtl.css',
				[],
				filemtime(CF_CC_ADMIN_LIBS_CSS_DIR . 'admin-rtl.css'),
				'all'
			);
		}
	}

	/**
	 * Load the class responsible for orchestrating the actions and filters of the core plugin.
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function loadDependencies()
	{
		require_once CF_CC_PLUGIN_DIR . 'admin/class-adminconditions.php';
		require_once CF_CC_PLUGIN_DIR . 'admin/class-adminsettings.php';

		AdminConditions::getInstance();
		AdminSettings::getInstance();
	}
}
