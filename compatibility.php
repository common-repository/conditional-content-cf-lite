<?php

/**
 * Class holding Logic for compatibility with other CC versions
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent\Lite;

/**
 * Compatibility Class
 */
class Compatibility
{
	/**
	 * Compatibility instance.
	 *
	 * @access protected
	 * @static
	 *
	 * @var Compatibility
	 */
	protected static $instance;

	/**
	 * Available versions.
	 *
	 * @access protected
	 * @static
	 *
	 * @var array
	 */
	protected static $versions;

	/**
	 * Current version.
	 *
	 * @access protected
	 * @static
	 *
	 * @var array
	 */
	protected static $current;

	/**
	 * Compatible flag.
	 *
	 * @access public
	 *
	 */
	public $is_compatible = true;

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
	 * Construct the compatibility class.
	 *
	 * @access protected
	 */
	protected function __construct(string $current, array $versions)
	{
		$this::$current = $current;
		$this::$versions = $versions;
		// Load required WP functions if unavailable
		if (!function_exists('is_plugin_active')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		$this->checkCompat();
	}

	/**
	 * Method to get instance of Compatibility.
	 *
	 * @access public
	 * @static
	 *
	 * @param string $current The current version of the plugin
	 * @param array $versions An array of incompatible versions
	 *
	 * @return Compatibility
	 */
	public static function getInstance(string $current, array $versions)
	{
		if (!self::$instance) {
			self::$instance = new self($current, $versions);
		}

		return self::$instance;
	}

	/**
	 * Method to check for other active CC versions
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function checkCompat()
	{
		foreach ($this::$versions as $version) {
			if (is_plugin_active($version)) {
				$this->is_compatible = false;
				return;
			}
		}
	}

	/**
	 * Method to fire the notice and disable plugin
	 *
	 * @access public
	 *
	 * @return  void
	 */
	public function disable()
	{
		if (!$this->is_compatible) {
			add_action('admin_notices', [$this, 'disableWithNotice']);
			add_action('network_admin_notices', [$this, 'disableWithNotice']);
		}
	}

	/**
	 * Method to display an incompatibility notice and disable the plugin
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function disableWithNotice()
	{
		if (isset($_GET['activate'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			unset($_GET['activate']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$message = esc_html__(
			'Oops! It looks like you have another version of this plugin already active. Please disable the other versions before retrying activation.', // phpcs:ignore
			'cf-conditional-content'
		);

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
		deactivate_plugins($this::$current);
	}
}
