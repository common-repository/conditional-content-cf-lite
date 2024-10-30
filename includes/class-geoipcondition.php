<?php

/**
 * The file that defines the geolocation condition class
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * The core plugin class.
 */
class GeoIPCondition
{
	/**
	 * GeoIPCondition instance.
	 *
	 * @access protected
	 * @static
	 *
	 * @var GeoIPCondition
	 */
	protected static $instance;

	/**
	 * GeoIP provider name.
	 *
	 * @access protected
	 *
	 * @var $provider
	 */
	protected $provider;

	/**
	 * GeoIP provider API key.
	 *
	 * @access protected
	 *
	 * @var $key
	 */
	protected $key;

	/**
	 * GeoIP provider API endpoint.
	 *
	 * @access protected
	 *
	 * @var $endpoint
	 */
	protected $endpoint;

	/**
	 * Retrieved GeoIP data.
	 *
	 * @access protected
	 *
	 * @var $data
	 */
	public $data;

	/**
	 * Locally saved states information.
	 *
	 * @access protected
	 *
	 * @var $states
	 */
	protected $states = [];

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
	 * Construct the class
	 *
	 * @access protected
	 *
	 * @since    1.0.0
	 */
	protected function __construct()
	{
		$this->provider = get_settings_geoip_provider();

		$this->key = get_settings_geoip_provider_key();

		$this->ip = $this->getUserIp();

		$this->endpoint = $this->buildEndpoint();

		$this->data = $this->getData();

		$this->states = json_decode(file_get_contents(CF_CC_PLUGIN_DIR . '/admin/libs/resources/states.json', true));
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
	 * Method to get instance of GeoIPCondition.
	 *
	 * @access public
	 * @static
	 *
	 * @return GeoIPCondition
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = (get_settings_geoip_provider() === 'wp-engine' && class_exists('\WPEngine\GeoIp'))
							? \WPEngine\GeoIp::instance()
							: new self();
		}

		return self::$instance;
	}

	/**
	 * Method to retrieve the GeoIP information.
	 *
	 * @access private
	 *
	 * @return string
	 */
	private function getData()
	{
		$safe_url = 'https://' . $this->endpoint;
		$unsafe_url = 'http://' . $this->endpoint;

		$safe_response = $this->retrieveBody($safe_url);
		$response = empty($safe_response->ip) ? $this->retrieveBody($unsafe_url) : $safe_response;

		return empty($response->ip) ? null : $response;
	}

	/**
	 * Method to make a remote call and return json decoded response body.
	 *
	 * @access private
	 *
	 * @return string
	 */
	private function retrieveBody($url)
	{
		return json_decode(wp_remote_retrieve_body(wp_remote_get($url)));
	}

	/**
	 * Construct the API endpoint to query.
	 *
	 * @access private
	 *
	 * @return string
	 */
	private function buildEndpoint()
	{
		$providerData = CF_CC_GEOIP_PROVIDERS[$this->provider];

		$endpoint = empty($providerData['url']) ? null : $providerData['url'];

		if ($endpoint) {
			$endpoint = $endpoint . $this->ip;
		} else {
			return null;
		}

		switch ($this->provider) {
			case 'ipstack':
				return $endpoint . '?access_key=' . $this->key . '&fields=ip,country_code,region_code,city,zip';
			case 'ipinfo':
				return $endpoint . '/geo?token=' . $this->key;
			case 'ipdata':
				return $endpoint . '?api-key=' . $this->key . '&fields=ip,country_code,region_code,city,postal';
			default:
				return null;
		}
	}

	/**
	 * Method to retrieve the current users IP address.
	 *
	 * @access private
	 *
	 * @return string
	 */
	private function getUserIp()
	{
		if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	/**
	 * Method to retrieve geolocated region.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function region()
	{
		$prefix = ('US' === $this->country()) ? '' : $this->country() . '-';

		if ('ipinfo' === $this->provider) {
			if (empty($this->data->region)) {
				return null;
			}
			foreach ($this->states as $state) {
				if ($this->data->region === $state->name) {
					return $prefix . $this->data->region;
				}
			}
		}

		return empty($this->data->region_code) ? null : $prefix . $this->data->region_code;
	}

	/**
	 * Method to retrieve geolocated country.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function country()
	{
		if (!empty($this->data->country)) {
			return $this->data->country;
		}

		return empty($this->data->country_code) ? null : $this->data->country_code;
	}

	/**
	 * Method to retrieve geolocated city.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function city()
	{
		return empty($this->data->city) ? null : $this->data->city;
	}

	/**
	 * Method to retrieve geolocated postal_code.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function postal_code() // phpcs:ignore
	{
		if (!empty($this->data->zip)) {
			return $this->data->zip;
		}

		return empty($this->data->postal) ? null : $this->data->postal;
	}
}
