<?php

/**
 * Class to handle the logic related to page visits.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * Class to handle the logic related to page visits.
 */
class PageVisitsHandler
{
	/**
	 * Maximum cookie size in bytes.
	 */
	private const MAX_COOKIE_SIZE = 2000;

	/**
	 * Instance of PageVisitsHandler.
	 *
	 * @access private
	 * @static
	 *
	 * @var PageVisitsHandler
	 */
	private static $instance;

	/**
	 * An array of pages, where each page is an array with the following elements:
	 *
	 * 0 - page URL (string)
	 * 1 - saved at - UNIX timestamp (int)
	 * 2 - saved until - UNIX timestamp (int).
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $pages = [];

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
	 * PageVisitsHandler constructor.
	 *
	 * @access private
	 */
	private function __construct()
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
	 * Method to check if the specified condition is met for the provided page URL.
	 *
	 * @access public
	 *
	 * @param string $page_url Page URL.
	 * @param string $operator Operator.
	 *
	 * @return bool
	 */
	public function checkCondition($page_url, $operator = 'url is')
	{
		$this->readPagesFromCookie();

		if (!is_array($this->pages)) {
			return false;
		}

		// Operator defaults to "url is".
		if (!$operator) {
			$operator = 'url is';
		}

		foreach ($this->pages as $page) {
			if ($this->compareURLs($page_url, $page[0], $operator)) {
				if ('url is' === $operator) {
					return true;
				}
			} elseif ('url is not' === $operator) {
				return false;
			}
		}

		return ('url is' === $operator) ? false : true;
	}

	/**
	 * Method to compare two URLs and return true if the conditions are met.
	 *
	 * @access private
	 *
	 * @param string $first_page_url First URL.
	 * @param string $second_page_url Second URL.
	 * @param string $operator Operator.
	 *
	 * @return bool
	 */
	private function compareURLs($first_page_url, $second_page_url, $operator)
	{
		$first_page_url = clean_page_url($first_page_url);
		$second_page_url = clean_page_url($second_page_url);

		switch ($operator) {
			case 'url is':
				return $first_page_url === $second_page_url;
			case 'url is not':
				return $first_page_url !== $second_page_url;
			default:
				return false;
		}
	}

	/**
	 * Method to return an instance of PageVisitsHandler.
	 *
	 * @access public
	 * @static
	 *
	 * @return PageVisitsHandler
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/*
	 * Returns the time in seconds for a given interval.
	 *
	 * @access private
	 *
	 * @param string $interval
	 *
	 * @return int
	 */
	private function getIntervalSeconds($interval)
	{
		switch ($interval) {
			case 'minutes':
				return 60;
			case 'hours':
				return 60 * 60;
			case 'days':
				return 60 * 60 * 24;
			case 'weeks':
				return 60 * 60 * 24 * 7;
			case 'months':
				return 60 * 60 * 24 * 30;
			default:
				return 0;
		}
	}

	/**
	 * Method to get the time interval to be used in the pages array for the  "saved until" value.
	 *
	 * @access private
	 *
	 * @return int
	 */
	private function getSavedUntilInterval()
	{
		$visited_pages = get_settings_visited_pages();

		$seconds = $this->getIntervalSeconds($visited_pages['interval']);

		return $seconds * $visited_pages['duration'];
	}

	/**
	 * Recursive function to remove persisted pages if cookie size is too big.
	 *
	 * @access protected
	 *
	 * @return string A JSON-encoded version of the items array.
	 */
	protected function limitPagesArraySize()
	{
		// Remove items until cookie size is less than defined max cookie size.
		$json = json_encode($this->pages, JSON_UNESCAPED_UNICODE);

		// Reduce the size of the array.
		if (strlen($json) > self::MAX_COOKIE_SIZE) {
			// Remove oldest (first) item from array.
			array_shift($this->pages);
			$json = $this->limitPagesArraySize();
		}

		return $json;
	}

	/**
	 * Method to get pages from cookie.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function readPagesFromCookie()
	{
		static $read_from_cookie;

		if (isset($_COOKIE[CF_CC_VISITED_PAGES_COOKIE_NAME]) && !$read_from_cookie) {
			$pages = json_decode(stripslashes($_COOKIE[CF_CC_VISITED_PAGES_COOKIE_NAME]), true);
			$read_from_cookie = true;

			if ($pages && is_array($pages)) {
				$this->pages = $this->sanitizePagesArray($pages);
			} else {
				$this->pages = [];
			}
		}
	}

	/**
	 * Sanitize pages array.
	 *
	 * @access protected
	 *
	 * @param array $pages Pages array.
	 *
	 * @return array Sanitized pages array.
	 */
	protected function sanitizePagesArray($pages)
	{
		if (!is_array($pages)) {
			return [];
		}

		$current_time = current_time('timestamp');

		$valid_pages = [];

		foreach ($pages as $page) {
			if (!is_array($page) || count($page) > 3) {
				continue;
			}

			/*
			0 - page URL (string)
			1 - saved at - UNIX timestamp (int)
			2 - saved until - UNIX timestamp (int).
			*/
			if (!isset($page[0], $page[1], $page[2])) {
				continue;
			}

			$page[0] = $this->sanitizePageUrl($page[0]);

			try {
				// Validate dates.
				new \DateTime('@' . $page[1]);
				new \DateTime('@' . $page[2]);
			} catch (\Exception $e) {
				continue;
			}

			// The "saved until" value has been exceeded.
			if ($page[2] < $current_time) {
				continue;
			}

			$valid_pages[] = $page;
		}

		return $valid_pages;
	}

	/**
	 * Method to sanitize page URL.
	 *
	 * @access protected
	 *
	 * @param string $page_url Page URL.
	 *
	 * @return string
	 */
	protected function sanitizePageUrl($page_url)
	{
		$page_url = esc_url_raw($page_url);

		return $page_url;
	}

	/**
	 * Method to save cookie.
	 *
	 * Note that this will reduce the size of the pages array, if it's too large to fit in the cookie.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function saveCookie()
	{
		$json = $this->limitPagesArraySize();
		setcookie(CF_CC_VISITED_PAGES_COOKIE_NAME, $json, CF_CC_VISITED_PAGES_COOKIE_EXPIRY_TIME, '/');
	}

	/**
	 * Method to save visited page.
	 *
	 * @access public
	 *
	 * @param string $page_url Page URL.
	 *
	 * @return void
	 */
	public function savePage($page_url)
	{
		if (empty($page_url)) {
			return;
		}

		$this->readPagesFromCookie();

		// Remove previous entry for the same page.
		foreach ($this->pages as $index => $page) {
			if (isset($page[0]) && $page[0] === $page_url) {
				unset($this->pages[$index]);
				$this->pages = array_values($this->pages);
				break;
			}
		}

		$current_time = current_time('timestamp');

		// Add page.
		$this->pages[] = [
			$this->sanitizePageUrl($page_url),
			$current_time,
			$current_time + $this->getSavedUntilInterval(),
		];

		if (apply_filters('cf_conditional_content_gdpr_allow_cookie', true, CF_CC_VISITED_PAGES_COOKIE_NAME)) {
			$this->saveCookie();
		}
	}
}
