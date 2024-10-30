<?php

/**
 * Class to implement the visibility rules checker.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

use DateTime;

/**
 * Class to implement the visibility rules checker.
 */
class ConditionChecker
{
	/**
	 * Class instance.
	 *
	 * @access private
	 * @static
	 *
	 * @var ConditionChecker $instance ConditionChecker instance.
	 */
	private static $instance;

	/**
	 * Number page visits.
	 *
	 * @access private
	 *
	 * @var int $num_of_visits
	 */
	private $num_of_visits;

	/**
	 * True if request is from a new user.
	 *
	 * @var bool $is_new_user
	 */
	private $is_new_user;

	/**
	 * Referrer.
	 *
	 * @var string $referrer
	 */
	private $referrer;

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
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct()
	{
		if (headers_sent()) {
			return;
		}

		$this->referrer = $this->getCurrentReferrer();

		$this->num_of_visits = 0;

		$this->is_new_user = empty($_COOKIE[CF_CC_VISIT_COUNTS_COOKIE_NAME]);

		if (isset($_COOKIE[CF_CC_VISIT_COUNTS_COOKIE_NAME])) {
			$this->num_of_visits = (int) $_COOKIE[CF_CC_VISIT_COUNTS_COOKIE_NAME];
		}

		if (wp_doing_ajax()) {
			$this->num_of_visits ++;
		}

		if (apply_filters('cf_conditional_content_gdpr_allow_cookie', true, CF_CC_VISIT_COUNTS_COOKIE_NAME)) {
			setcookie(CF_CC_VISIT_COUNTS_COOKIE_NAME, $this->num_of_visits, strtotime('+1 days'), '/');
		}
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
	 * Async request.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function asyncRequest()
	{
		$this->is_new_user = empty($_COOKIE[CF_CC_VISIT_COUNTS_COOKIE_NAME]);

		if (!$this->is_new_user && isset($_COOKIE[CF_CC_VISIT_COUNTS_COOKIE_NAME])) {
			$this->num_of_visits = (int) $_COOKIE[CF_CC_VISIT_COUNTS_COOKIE_NAME];
		}
	}


	/**
	 * Get current request referrer.
	 *
	 * @return string Referrer.
	 */
	private function getCurrentReferrer()
	{
		$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$referrer = clean_page_url($referrer);

		return $referrer;
	}

	/**
	 * Set referrer property.
	 *
	 * @access public
	 *
	 * @param  string $url referrer URL
	 * @return void
	 */
	public function setReferrer(string $url)
	{
		$this->referrer = $url;
	}

	/**
	 * Get class instance.
	 *
	 * @access public
	 * @static
	 *
	 * @return ConditionChecker Class instance.
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check if a rule is triggered.
	 *
	 * @access public
	 *
	 * @param int $condition_id Condition id.
	 *
	 * @return bool True if triggered.
	 */
	public function isTriggered(int $condition_id)
	{
		$return = false;

		if ($condition_id <= 0) {
			$return = false;
		}

		$post_status = get_post_status($condition_id);

		if ($post_status && 'publish' !== $post_status) {
			$return = false;
		}

		$data_rules_json = get_post_meta($condition_id, CF_CC_CONDITIONS_META_KEY, true);
		$data_rules      = json_decode($data_rules_json, true);

		if (empty($data_rules)) {
			$return = false;
		}

		foreach ($data_rules as $rule) {
			if (empty($rule['condition_type'])) {
				continue;
			}

			switch ($rule['condition_type']) {
				case 'Device':
					$return = $this->isTriggeredDevice($rule);
					break;
				case 'Geolocation':
					$return = $this->isTriggeredGeolocation($rule);
					break;
				case 'PageVisit':
					$return = $this->isTriggeredPageVisit($rule);
					break;
				case 'PageUrl':
					$return = $this->isTriggeredPageURL($rule);
					break;
				case 'referrer':
					$return = $this->isTriggeredReferrer($rule);
					break;
				case 'Time-Date':
					$return = $this->isTriggeredTimeDate($rule);
					break;
				case 'url':
					$compare = $rule['compare'] ?? '';
					$return = filter_input(INPUT_GET, 'v') === $compare;
					break;
				case 'User-Behavior':
					$return = $this->isTriggeredUserBehaviour($rule);
					break;
			}
		}

		$active_conditions = filter_input(INPUT_GET, 'inversed-conditions');
		if (isset($active_conditions)) {
			$active_conditions = explode(',', $active_conditions);
			if (in_array($condition_id, $active_conditions)) {
				return !$return;
			}
		}

		return $return;
	}

	/**
	 * Check if user behaviour rule is triggered.
	 *
	 * @access protected
	 *
	 * @param array $rule Rule logic.
	 *
	 * @return bool True if rule triggered.
	 */
	protected function isTriggeredUserBehaviour(array $rule)
	{
		if (empty($rule['condition_type'])) {
			return false;
		}

		$user_behavior_options = [
			'Logged',
			'LoggedIn',
			'LoggedOut',
			'NewUser',
			'Returning',
		];

		if (
			'User-Behavior' !== $rule['condition_type'] ||
			!in_array($rule['User-Behavior'], $user_behavior_options, true)
		) {
			return false;
		}

		$is_user_logged_in = is_user_logged_in();

		$user_behavior = $rule['User-Behavior'];

		switch ($user_behavior) {
			case 'NewUser':
				return $this->is_new_user;

			case 'Returning':
				/*
				In case 'user-behavior-returning' is 'custom',
				check if the user is returning based on 'user-behavior-returning'
				OR 'user-behavior-retn-custom'
				*/
				if ('custom' === $rule['user-behavior-returning']) {
					$num_of_returns = intval($rule['user-behavior-retn-custom']);
				} else {
					$returns_options = [
						'first-visit'  => 1,
						'second-visit' => 2,
						'three-visit'  => 3,
					];

					$num_of_returns = $returns_options[$rule['user-behavior-returning']];
				}

				// In here, $num_of_returns hold the number of returns we desire.
				return $this->num_of_visits >= $num_of_returns;

			case 'LoggedIn':
				return $is_user_logged_in;

			case 'LoggedOut':
				return !$is_user_logged_in;

			case 'Logged':
				// New Version of Logged In Out.
				// Keeping the previous one for backward compatibility.
				$logged_in_out = $rule['user-behavior-logged'];

				if ('logged-in' === $logged_in_out && $is_user_logged_in) {
					// Yes! he is logged in!
					return true;
				} elseif ('logged-out' === $logged_in_out && !$is_user_logged_in) {
					// Yes! he is logged off.
					return true;
				}

				return false;
			default:
				return false;
		}
	}

	/**
	 * Check if device rule is triggered.
	 *
	 * @access public
	 *
	 * @param array $rule Rule data.
	 *
	 * @return bool True if rule triggered.
	 */
	public function isTriggeredDevice(array $rule)
	{
		if (empty($rule['condition_type'])) {
			return false;
		}

		if ('Device' !== $rule['condition_type']) {
			return false;
		}

		if (!empty($rule['user-behavior-device-mobile']) && cf_cc_is_mobile()) {
			return true; // User is on Mobile.
		} elseif (!empty($rule['user-behavior-device-tablet']) && cf_cc_is_tablet()) {
			return true; // User is on Tablet.
		} elseif (!empty($rule['user-behavior-device-desktop']) && !cf_cc_is_mobile() && !cf_cc_is_tablet()) {
			return true; // User is on Desktop.
		}

		return false;
	}

	/**
	 * Check if date/time rule is triggered.
	 *
	 * @access public
	 *
	 * @param array $rule Rule data.
	 *
	 * @return bool True if rule triggered.
	 */
	public function isTriggeredTimeDate(array $rule)
	{
		if (empty($rule['condition_type'])) {
			return false;
		}

		if (
			'Time-Date' !== $rule['condition_type']
			|| !isset($rule['time-date-start-date'])
			|| !isset($rule['time-date-end-date'])
		) {
			return false;
		}

		$format    = 'Y/m/d H:i';
		$curr_date = DateTime::createFromFormat($format, current_time($format));

		if (empty($rule['time-date-end-date']) && empty($rule['time-date-start-date'])) {
			return true;
		}
		if (empty($rule['time-date-start-date'])) {
			// No start date.
			$end_date = DateTime::createFromFormat($format, $rule['time-date-end-date']);

			if ($curr_date <= $end_date) {
				// Yes! we are in the right time frame.
				return true;
			}
		} elseif (empty($rule['time-date-end-date'])) {
			// No end date.
			$start_date = DateTime::createFromFormat($format, $rule['time-date-start-date']);

			if ($curr_date >= $start_date) {
				// Yes! we are in the right time frame.
				return true;
			}
		} else {
			// Both have dates.
			$start_date = DateTime::createFromFormat($format, $rule['time-date-start-date']);
			$end_date   = DateTime::createFromFormat($format, $rule['time-date-end-date']);
			if (
				$curr_date >= $start_date &&
				$curr_date <= $end_date
			) {
				// Yes! we are in the right time frame.
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if geolocation condition is triggered.
	 *
	 * @access public
	 *
	 * @param array $rule Rule.
	 *
	 * @return bool True if triggered.
	 */
	public function isTriggeredGeolocation(array $rule)
	{
		$provider = get_settings_geoip_provider();
		$accepted_providers = array_keys(get_accepted_geoip_providers());

		if (!$provider || !in_array($provider, $accepted_providers)) {
			return false;
		}

		if (empty($rule['condition_type'])) {
			return false;
		}

		if ('Geolocation' !== $rule['condition_type']) {
			return false;
		}

		if (empty($rule['geolocation_data'])) {
			return false;
		}

		$geolocation_data = utf8_decode($rule['geolocation_data']);
		$targets = explode('^^', $geolocation_data);
		if (empty($targets)) {
			return false;
		}

		$geoip_condition = GeoIPCondition::getInstance();
		foreach ($targets as $target) {
			$target_parts = explode('!!', $target);
			if (3 !== count($target_parts)) {
				continue;
			}

			$type = $target_parts[0];
			$target_value = strtolower(trim($target_parts[2]));

			switch (strtoupper($type)) {
				case 'STATE':
					$region = (string) strtolower(trim($geoip_condition->region()));
					if ($target_value === $region) {
						return true;
					}
					break;
				case 'COUNTRY':
					$country = (string) strtolower(trim($geoip_condition->country()));
					if ($target_value === $country) {
						return true;
					}
					break;
				case 'CITY':
					$city = (string) strtolower(trim($geoip_condition->city()));
					if ($target_value === $city) {
						return true;
					}
					break;
				case 'POSTAL_CODE':
					$postal_code = (string) strtolower(trim($geoip_condition->postal_code()));
					if ($target_value === $postal_code) {
						return true;
					}
					break;
				case 'AREA_CODE':
					/*
					WPE Geoip class doesn't have a public method for getting areacode,
					so we'll get it from the public array.
					*/
					if (empty($geoip_condition->geos['areacode'])) {
						continue 2;
					}
					$area_code = (string) strtolower(trim($geoip_condition->geos['areacode']));

					if ($target_value === $area_code) {
						return true;
					}
					break;
				default:
					break;
			}
		}
		return false;
	}

	/**
	 * Check if page visit condition is triggered.
	 *
	 * @access public
	 *
	 * @param array $rule Rule.
	 *
	 * @return bool True if triggered.
	 */
	public function isTriggeredPageVisit(array $rule)
	{
		if (empty($rule['condition_type'])) {
			return false;
		}

		if ('PageVisit' !== $rule['condition_type']) {
			return false;
		}

		if (empty($rule['page_visit_data'])) {
			return false;
		}

		$page_visit_data = utf8_decode($rule['page_visit_data']);
		$page_visit_data = explode('^^', $page_visit_data);

		foreach ($page_visit_data as $key => $value) {
			$data = explode('!!', $value);
			$page_url = $data[1];
			$operator = $data[2];
			$is_visited = PageVisitsHandler::getInstance()->checkCondition($page_url, $operator);

			if ($is_visited) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if page url condition is triggered.
	 *
	 * @access public
	 *
	 * @param array $rule Rule.
	 *
	 * @return bool True if triggered.
	 */
	public function isTriggeredPageURL(array $rule)
	{
		if (empty($rule['condition_type'])) {
			return false;
		}

		if ('PageUrl' !== $rule['condition_type']) {
			return false;
		}

		$current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$operator = $rule['page-url-operator'];
		$page_url = $rule['page-url-compare'];

		if ('is' === $operator || 'is-not' === $operator) {
			$page_url = clean_page_url($page_url);
		}

		if ('is' === $operator && $current_url === $page_url) {
			return true; // Exact match.
		} elseif ('is-not' === $operator && $current_url !== $page_url) {
			return true; // Not exact match.
		}

		return false;
	}

	/**
	 * Check if the referrer condition is triggered.
	 *
	 * @access public
	 *
	 * @param array $rule Rule.
	 *
	 * @return bool True if triggered.
	 */
	public function isTriggeredReferrer(array $rule)
	{
		if (empty($rule['condition_type'])) {
			return false;
		}

		if ('referrer' !== $rule['condition_type']) {
			return false;
		}

		$referrer = clean_page_url($this->referrer);

		// Handle referrer from the site's pages.
		if ('page-on-website' === $rule['trigger'] && $rule['page']) {
			$page_id   = (int) $rule['page'];
			$page_link = get_permalink($page_id);
			$page_link = clean_page_url($page_link);

			return $referrer === $page_link;
		}

		// Handle custom referrers.
		if ('url' === $rule['custom']) {
			$rule['compare'] = clean_page_url($rule['compare']);

			if ('is' === $rule['operator'] && $referrer === $rule['compare']) {
				return true; // Exact match.
			} elseif ('is-not' === $rule['operator'] && $referrer !== $rule['compare']) {
				return true; // Not exact match.
			}
		}

		return false;
	}

	/**
	 * Check if user timezone is in selected timezone.
	 *
	 * @access public
	 *
	 * @param string $user_timezone User timezone.
	 * @param string $timezone_name Selected timezone.
	 *
	 * @return bool True if user timezone in selected timezone.
	 */
	private function isUserTimezoneInSelectedTimeZone($user_timezone, $timezone_name)
	{
		$timezones = get_timezones();

		$zones = [];

		if (isset($timezones[$timezone_name])) {
			$zones = $timezones[$timezone_name];
		}

		if (empty($zones)) {
			return false;
		}

		foreach ($zones as $timezone) {
			if (are_they_equal_or_contains($user_timezone, $timezone)) {
				return true;
			}
		}

		return false;
	}
}
