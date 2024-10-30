<?php

/**
 * Implement admin conditions.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * Class implementing admin conditions.
 */
class AdminConditions
{
	/**
	 * Instance of AdminConditions.
	 *
	 * @access private
	 * @static
	 *
	 * @var AdminConditions
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
		add_action('add_meta_boxes_' . CF_CC_CPT_CONDITION, [$this, 'addConditionsMetaBox'], 1);
		add_action(
			'manage_' . CF_CC_CPT_CONDITION . '_posts_custom_column',
			[$this, 'renderConditionColumnData'],
			10,
			2
		);
		add_action('save_post_' . CF_CC_CPT_CONDITION, [$this, 'saveConditionData']);

		add_filter('manage_' . CF_CC_CPT_CONDITION . '_posts_columns', [$this, 'addCustomColumns'], 100, 1);
		add_action('in_admin_header', [$this, 'addNavTabs']);
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
	 * Add Conditions Nav Tabs.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function addNavTabs()
	{
		do_action('cf_cc_navtabs');
	}

	/**
	 * Add Conditions metabox.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function addConditionsMetaBox()
	{
		add_meta_box(
			CF_CC_CPT_CONDITION . '_metabox',
			__('Conditions', 'cf-conditional-content'),
			[$this, 'renderConditionsMetabox'],
			CF_CC_CPT_CONDITION,
			'normal',
			'high'
		);
	}

	/**
	 * Implement Conditions metabox.
	 *
	 * @access public
	 *
	 * @param \WP_Post $post Current post.
	 *
	 * @return void
	 */
	public function renderConditionsMetabox($post)
	{
		// Get trigger rules data.
		$data = [];
		global $available_pages, $post_status, $data_rules;
		// Default Content + corresponding Default Metadata.
		$data_default               = get_post_meta($post->ID, 'cf_cc_trigger_default', true);
		$data_default_metadata_json = get_post_meta($post->ID, 'cf_cc_trigger_default_metadata', true);
		if (! empty($data_default_metadata_json)) {
			$data_default_metadata = json_decode($data_default_metadata_json, true);
		} else {
			$data_default_metadata = [];
		}
		// Rules + Versions.
		$data_rules_json = get_post_meta($post->ID, CF_CC_CONDITIONS_META_KEY, true);
		$data_rules      = json_decode($data_rules_json, true);
		// Get all available pages.
		$args            = [
			'sort_order'       => 'asc',
			'sort_column'      => 'post_title',
			'hierarchical'     => 1,
			'child_of'         => 0,
			'post_type'        => 'page',
			'post_status'      => 'publish',
			'suppress_filters' => true,
		];
		$available_pages = get_pages($args);
		$post_status     = get_post_status();

		include 'views/conditions-metabox.php';
	}

	 /**
	  * Generate output for managing a rule.
	  *
	  * @param array $rule Rule.
	  * @param boolean $is_template True if this is a template and not a rule.
	  *
	  * @return void
	  */
	public function generateRuleItem($rule = [], $is_template = false)
	{
		global $available_pages, $post_status, $data_rules;


		$visited_pages = get_settings_visited_pages();
		$visited_pages_info = $visited_pages['duration'] . ' ' . $visited_pages['interval'];
		if ($is_template) {
			$current_version_index  = 'index_placeholder';
			$current_datetime_count = '{datetime_number}';
			$current_instructions   = '{version_instructions}';
		} else {
			$current_version_index  = 0;
			$current_datetime_count = '';
			$current_instructions   = __(
				'Chose a condition to create a rule:',
				'cf-conditional-content'
			);
		}
		$trigger                      = $rule['trigger'] ?? '';
		$condition_type               = $rule['condition_type'] ?? '';
		$rule_time_date_schedule      = $rule['Time-Date-Schedule-Selection'] ?? '';
		$rule_user_behavior           = $rule['User-Behavior'] ?? '';
		$rule_user_behavior_logged    = $rule['user-behavior-logged'] ?? '';
		$rule_user_behavior_returning = $rule['user-behavior-returning'] ?? '';
		$rule_page                    = $rule['page'] ?? '';
		$rule_page_url_operator       = $rule['page-url-operator'] ?? '';
		$rule_page_url_compare        = $rule['page-url-compare'] ?? '';
		$rule_operator                = $rule['operator'] ?? '';
		$rule_compare                 = $rule['compare'] ?? '';
		$rule_chosen_common_referrers = $rule['chosen-common-referrers'] ?? '';

		include 'views/conditions.php';
	}

	/**
	 * Filter Conditions columns.
	 *
	 * @access public
	 *
	 * @param array $initial_columns An array of initial columns.
	 *
	 * @return array Updated list columns.
	 */
	public function addCustomColumns($initial_columns)
	{
		$columns = [
			'cb' => '<input type="checkbox" />',
			'title' => __('Title', 'cf-conditional-content'),
			'condition' => __('Conditions', 'cf-conditional-content'),
		];

		// Add initial columns after our custom columns.
		foreach ($initial_columns as $column_slug => $column_title) {
			if (!array_key_exists($column_slug, $columns)) {
				$columns[$column_slug] = $column_title;
			}
		}

		// Set date column at the end of the table.
		$columns['date'] = __('Date', 'cf-conditional-content');

		return $columns;
	}

	/**
	 * Extract autocomplete selection.
	 *
	 * @access private
	 *
	 * @param string $data Page visit data.
	 *
	 * @return string Extracted data.
	 */
	private function extractAutocompleteSelectionData($data)
	{
		if (!empty($data)) {
			$data = explode('^^', $data);
			$split_data = [];

			foreach ($data as $key => $value) {
				if (! empty($value) && '1' !== $value) {
					array_push($split_data, $value);
				}
			}

			$data = utf8_encode(implode('^^', $split_data));
			$data = str_replace('\\', '\\\\', $data);
		}

		return $data;
	}

	/**
	 * Get instance of AdminConditions.
	 *
	 * @access public
	 * @static
	 *
	 * @return AdminConditions
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Helper method.
	 * Loads given's $post_id default version metadata from DB.
	 *
	 * @param int $post_id Post id.
	 *
	 * @return array
	 */
	private function loadDefaultVersionMetadata($post_id)
	{
		$data_default_metadata_json = get_post_meta(
			$post_id,
			'cf_cc_trigger_default_metadata',
			true
		);

		if (! empty($data_default_metadata_json)) {
			$default_version_metadata = json_decode($data_default_metadata_json, true);
		} else {
			$default_version_metadata = [];
		}

		return $default_version_metadata;
	}

	/**
	 * Add column data.
	 *
	 * @access public
	 *
	 * @param string $column  Column id.
	 * @param int $post_id Post id.
	 *
	 * @return void
	 */
	public function renderConditionColumnData($column, $post_id)
	{
		if ('condition' !== $column) {
			return;
		}

		$data = [];
		$conditions = '';

		$data_json = get_post_meta($post_id, CF_CC_CONDITIONS_META_KEY, true);

		if (!empty($data_json)) {
			$data = json_decode($data_json, true);
		}

		if (empty($data)) {
			return;
		}

		$conditions_array     = [];
		$query_strings_used = [];

		foreach ($data as $rule) {
			if ('url' === $rule['condition_type'] && ! empty($rule['compare'])) {
				$query_strings_used[] = "{$rule['compare']}";
			} else {
				if (empty($rule['condition_type'])) {
					// In case no trigger got chosen.
					$condition_type = __('Blank', 'cf-conditional-content');
				} else {
					$condition_type = $rule['condition_type'];
				}

				if (!in_array($condition_type, $conditions_array, true)) {
					$conditions_array[] = $condition_type;
				}
			}
		}

		// Add all query strings selected to the triggers array.
		if (!empty($query_strings_used)) {
			$conditions_array[] = __('Custom URL', 'cf-conditional-content')
				. '(?v=' . implode(', ', $query_strings_used) . ')';
		}

		if (! empty($conditions_array)) {
			$conditions = implode('<br/>', $conditions_array);
		}

		echo wp_kses_post($conditions);
	}

	/**
	 * Save Condition data.
	 *
	 * @access public
	 *
	 * @param int $post_id Post id.
	 *
	 * @return void
	 */
	public function saveConditionData($post_id)
	{
		if (!current_user_can('edit_post', $post_id)) {
			die(esc_html_e('You do not have sufficient privilege to edit the post', 'cf-conditional-content'));
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Prevent quick edit from clearing custom fields.
		if (defined('DOING_AJAX') && DOING_AJAX) {
			return;
		}

		$condition_data = [];
		$condition_data['default'] = filter_input(INPUT_POST, 'cf_cc_default');

		// Get custom rule classes
		$selection_classes = filter_input(INPUT_POST, 'hidden_stored_selection_classes');
		// Load default's version metadata.
		$default_version_metadata = $this->loadDefaultVersionMetadata($post_id);

		$compare = filter_input(INPUT_POST, 'compare_referrer');

		$compare_url = filter_input(INPUT_POST, 'compare_url');
		if (! empty($compare_url)) {
			$compare = $compare_url;
		}

		$page_url_compare = filter_input(INPUT_POST, 'page-url-compare');
		$page_url_operator = filter_input(INPUT_POST, 'page-url-operator');
		$condition_type = filter_input(INPUT_POST, 'condition_type');

		// Begin User Behavior.
		$user_behavior = filter_input(INPUT_POST, 'User-Behavior');
		$user_behavior_loggedinout = filter_input(INPUT_POST, 'user-behavior-loggedinout');
		$user_behavior_returning = filter_input(INPUT_POST, 'user-behavior-returning');
		$user_behavior_retn_custom = filter_input(INPUT_POST, 'user-behavior-retn-custom');

		// End User Behavior.

		$number_of_views = (int) filter_input(INPUT_POST, 'saved_number_of_views', FILTER_VALIDATE_INT);

		$user_behavior_device_mobile  = false;
		$user_behavior_device_tablet  = false;
		$user_behavior_device_desktop = false;

		if ('on' === filter_input(INPUT_POST, 'user-behavior-device-mobile')) {
			$user_behavior_device_mobile = true;
		}

		if ('on' === filter_input(INPUT_POST, 'user-behavior-device-tablet')) {
			$user_behavior_device_tablet = true;
		}

		if ('on' === filter_input(INPUT_POST, 'user-behavior-device-desktop')) {
			$user_behavior_device_desktop = true;
		}

		$page_visit_data = $this->extractAutocompleteSelectionData(filter_input(INPUT_POST, 'page_visit_data'));

		switch ($condition_type) {
			case 'Device':
				$rule_data = [
					'user-behavior-device-mobile'  => $user_behavior_device_mobile,
					'user-behavior-device-tablet'  => $user_behavior_device_tablet,
					'user-behavior-device-desktop' => $user_behavior_device_desktop,
				];
				break;
			case 'Geolocation':
				$rule_data = [
					'geolocation_data' => $this->extractAutocompleteSelectionData($group_item['geolocation_data'] ?? null) // phpcs:ignore
				];
				break;
			case 'PageVisit':
				$rule_data = [
					'page_visit_data' => $page_visit_data
				];
				break;
			case 'PageUrl':
				$rule_data = [
					'page-url-compare' => $page_url_compare,
					'page-url-operator' => $page_url_operator
				];
				break;
			case 'referrer':
				$rule_data = [
					'trigger' => filter_input(INPUT_POST, 'trigger'),
					'custom' => filter_input(INPUT_POST, 'custom'),
					'page' => filter_input(INPUT_POST, 'page'),
					'operator' => filter_input(INPUT_POST, 'operator'),
					'compare' => $compare,
				];
				break;
			case 'Time-Date':
				$rule_data = [
					'time-date-start-date' => filter_input(INPUT_POST, 'time-date-start-date'),
					'time-date-end-date' => filter_input(INPUT_POST, 'time-date-end-date'),
					'Time-Date-Start' => filter_input(INPUT_POST, 'Time-Date-Start'),
					'Time-Date-End' => filter_input(INPUT_POST, 'Time-Date-End'),
				];
				break;
			case 'url':
				$rule_data = [
					'compare' => $compare,
				];
				break;
			case 'User-Behavior':
				$rule_data = [
					'User-Behavior' => $user_behavior,
					'user-behavior-loggedinout' => $user_behavior_loggedinout,
					'user-behavior-returning' => $user_behavior_returning,
					'user-behavior-retn-custom' => $user_behavior_retn_custom,
					'user-behavior-logged' => filter_input(INPUT_POST, 'user-behavior-logged'),
				];
				break;
			default:
				$rule_data = [];
				break;
		}

		$rule_data = array_merge([
			'condition_type' => $condition_type,
			'hidden_stored_selection_classes' => $selection_classes,
			'number_of_views' => $number_of_views
		], $rule_data);

		$condition_data['rules'][] = $rule_data;

		update_post_meta($post_id, 'cf_cc_trigger_default', $condition_data['default']);

		$this->saveDefaultVersionMetadata($post_id, $default_version_metadata);

		// Update rules.
		update_post_meta(
			$post_id,
			CF_CC_CONDITIONS_META_KEY,
			wp_json_encode($condition_data['rules'], JSON_UNESCAPED_UNICODE)
		);
	}

	/**
	 * Helper method.
	 * Save to the DB in $post_id's default version metadata.
	 *
	 * @access private
	 *
	 * @param int $post_id Post id.
	 * @param array $default_version_metadata Default metadata.
	 *
	 * @return void
	 */
	private function saveDefaultVersionMetadata($post_id, $default_version_metadata)
	{
		$default_version_metadata_json = wp_json_encode($default_version_metadata, JSON_UNESCAPED_UNICODE);

		update_post_meta(
			$post_id,
			'cf_cc_trigger_default_metadata',
			$default_version_metadata_json
		);
	}
}
