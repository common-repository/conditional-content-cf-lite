<?php

/**
 * The file that defines the core plugin class
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * The core plugin class.
 */
class CFConditionalContent
{
	/**
	 * CFConditionalContent instance.
	 *
	 * @access protected
	 * @static
	 *
	 * @var CFConditionalContent
	 */
	protected static $instance;

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
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @access protected
	 *
	 * @since    1.0.0
	 */
	protected function __construct()
	{
		add_action('plugins_loaded', [$this, 'init']);
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
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function adminNoticeMinimumPHPVersion()
	{
		if (isset($_GET['activate'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			unset($_GET['activate']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$message = sprintf(
			/* Translators: %1$s - Plugin name, %2$s - "PHP", %3$s - Required PHP version. */
			esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'cf-conditional-content'),
			'<strong>' . esc_html(CF_CC_PLUGIN_NAME) . '</strong>',
			'<strong>' . esc_html__('PHP', 'cf-conditional-content') . '</strong>',
			CF_CC_PHP_MIN_VER
		);

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);

		deactivate_plugins(CF_CC_PLUGIN_BASENAME);
	}

	/**
	 * Method to get instance of Core.
	 *
	 * @access public
	 * @static
	 *
	 * @return CFConditionalContent
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Method to initialize plugin.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function init()
	{
		if (!function_exists('is_plugin_active')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		// Check for required PHP version.
		if (version_compare(PHP_VERSION, CF_CC_PHP_MIN_VER, '<')) {
			add_action('admin_notices', [$this, 'adminNoticeMinimumPHPVersion']);
			add_action('network_admin_notices', [$this, 'adminNoticeMinimumPHPVersion']);
			return;
		}

		add_action('init', [$this, 'registerConditionPostType']);

		require_once plugin_dir_path(__FILE__) . 'class-geoipcondition.php';
		require_once plugin_dir_path(__FILE__) . 'class-conditionchecker.php';
		require_once plugin_dir_path(__FILE__) . 'class-elementorconditions.php';
		require_once plugin_dir_path(__FILE__) . 'class-bbconditions.php';
		require_once plugin_dir_path(__FILE__) . 'class-conditionspreview.php';

		$this->loadDependencies();

		// Instantiate ElementorConditions.
		ElementorConditions::getInstance();
		// Instantiate BBConditions.
		BBConditions::getInstance();

		Admin::getInstance();
		CFCCPublic::getInstance();
		ConditionsPreview::getInstance();
	}

	/**
	 * Load dependencies.
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function loadDependencies()
	{
		/**
		 * Helper functions.
		 */
		require_once CF_CC_PLUGIN_DIR . 'includes/helpers.php';

		/**
		 * Settings functions
		 */
		require_once CF_CC_PLUGIN_DIR . 'includes/settings.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once CF_CC_PLUGIN_DIR . 'admin/class-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once CF_CC_PLUGIN_DIR . 'public/class-cf-cc-public.php';
	}

	/**
	 * Register custom post.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function registerConditionPostType()
	{
		$labels = [
			'add_new'            => _x('Add New', 'condition', 'cf-conditional-content'),
			'add_new_item'       => __('Add Condition', 'cf-conditional-content'),
			'all_items'          => __('All Conditions', 'cf-conditional-content'),
			'edit_item'          => __('Edit Condition', 'cf-conditional-content'),
			'menu_name'          => _x('Conditions', 'admin menu', 'cf-conditional-content'),
			'name'               => _x('Conditions', 'post type general name', 'cf-conditional-content'),
			'new_item'           => __('New Condition', 'cf-conditional-content'),
			'not_found'          => __('No Conditions found', 'cf-conditional-content'),
			'not_found_in_trash' => __('No Conditions found in the Trash', 'cf-conditional-content'),
			'parent_item_colon'  => '',
			'search_items'       => __('Search Conditions', 'cf-conditional-content'),
			'singular_name'      => _x('Condition', 'post type singular name', 'cf-conditional-content'),
			'view_item'          => __('View Condition', 'cf-conditional-content'),
		];

		$args = [
			'capability_type'     => 'post',
			'description'         => __('CF Conditional Content - Conditions', 'cf-conditional-content'),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'labels'              => $labels,
			'menu_position'       => 26,
			'public'              => true,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'rewrite'             => false,
			'show_in_menu'        => true,
			'show_ui'             => true,
			'supports'            => ['title'],
		];

		register_post_type(CF_CC_CPT_CONDITION, $args);
	}
}
