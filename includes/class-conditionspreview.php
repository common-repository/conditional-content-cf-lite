<?php

/**
 * Generate admin bar menu items for toggling conditions on page.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * Class for generating admin bar preview conditions.
 */
class ConditionsPreview
{
	/**
	 * Instance of ConditionsPreview.
	 *
	 * @access private
	 * @static
	 *
	 * @var ConditionsPreview
	 */
	private static $instance;

	/**
	 * Admin bar top menu item id.
	 *
	 * @access private
	 * @static
	 *
	 * @var ConditionsPreview
	 */
	private $admin_bar_id = 'cf-admin-bar-conditional-content';

	/**
	 * Total conditions applied.
	 *
	 * @access private
	 * @static
	 *
	 * @var ConditionsPreview
	 */
	private $conditions_applied = 0;

	/**
	 * ConditionChecker instance.
	 *
	 * @access private
	 *
	 * @var ConditionsPreview
	 */
	private $condition_checker;

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
	 * Get instance of ConditionsPreview.
	 *
	 * @access public
	 *
	 * @return ConditionsPreview
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @access protected
	 */
	protected function __construct()
	{
		$this->condition_checker = ConditionChecker::getInstance();

		add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);

		// If user has permissions hook into admin bar and display live preview support options.
		if (!is_admin() && current_user_can('manage_options')) {
			add_action('admin_bar_menu', [$this, 'init'], 500);
		}
	}

	/**
	 * Initialize conditional preview.
	 *
	 * @param object $admin_bar WP_Admin_Bar instance
	 * @return void
	 */
	public function init($admin_bar)
	{
		global $post;
		// Register first item.
		$this->registerTopMenuItem($admin_bar);

		// Add conditions based on page editor.
		if (class_exists('FLBuilderModel') && \FLBuilderModel::is_builder_enabled()) {
			$this->conditionPreviewBb($admin_bar);
		} elseif (
			did_action('elementor/loaded')
			&& \Elementor\Plugin::$instance->db->is_built_with_elementor($post->ID)
		) {
			$this->conditionPreviewElementor($admin_bar);
		} else {
			$this->conditionPreviewGutenberg($admin_bar);
		}

		// Display no results.
		$this->noResults($admin_bar);

		// Reset rules.
		$this->resetRules($admin_bar);
	}

	/**
	 * Register the public Styles.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function enqueueStyles()
	{
		wp_enqueue_style(
			CF_CC_PLUGIN_SLUG . '-public-style',
			CF_CC_ADMIN_BUILD_URL . (WP_DEBUG ? 'public-style.css' : 'public-style.min.css'),
			[],
			filemtime(CF_CC_ADMIN_BUILD_DIR . (WP_DEBUG ? 'public-style.css' : 'public-style.min.css')),
			'all'
		);
	}

	/**
	 * Add first conditional preview admin bar menu item.
	 *
	 * @param object $admin_bar WP_Admin_Bar instance
	 * @return void
	 */
	private function registerTopMenuItem($admin_bar)
	{
		$admin_bar->add_menu([
			'id' => $this->admin_bar_id,
			'title' => esc_html__('Conditions Preview', 'cf-conditional-content'), // Admin bar title.
			'meta' => [
				'title' => esc_html__(
					'View existing conditions on page',
					'cf-conditional-content'
				), // Title to show on hover.
			]
		]);
	}

	/**
	 * Add no results text as menu item to the admin bar.
	 *
	 * @param object $admin_bar WP_Admin_Bar instance
	 * @return void
	 */
	private function noResults($admin_bar)
	{
		// If counter of conditions_applied is 0 add no conditions found text.
		if (!$this->conditions_applied) {
			$admin_bar->add_menu([
				'id' => $this->admin_bar_id . '-none',
				'title' => esc_html__('No conditions found on this page', 'cf-conditional-content'), // Admin bar title.
				'parent' => $this->admin_bar_id,
				'meta' => [
					'title' => esc_html__(
						'No conditions for this page',
						'cf-conditional-content'
					), // Title to show on hover.
				]
			]);
		}
	}

	/**
	 * When conditions are applied on page add reset rules menu item last.
	 *
	 * @param object $admin_bar WP_Admin_Bar instance
	 * @return void
	 */
	private function resetRules($admin_bar)
	{
		global $wp;
		// If counter of conditions_applied is greater than 0 add reset conditions link.
		if ($this->conditions_applied) {
			$admin_bar->add_menu([
				'id' => $this->admin_bar_id . '-reset',
				'title' => esc_html__('Reset Rules', 'cf-conditional-content'), // Admin bar title.
				'parent' => $this->admin_bar_id,
				'href' => home_url($wp->request),
				'meta' => [
					'title' => esc_html__('Reset Rules', 'cf-conditional-content'), // Title to show on hover.
				]
			]);
		}
	}

	/**
	 * Get elementor conditions settings.
	 *
	 * @param int $post_id
	 * @return array Conditions found on page.
	 */
	private function getElementsConditions($post_id)
	{
		$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend($post_id);

		// Change the current post, so widgets can use `documents->get_current`.
		\Elementor\Plugin::$instance->documents->switch_to_document($document);
		$elementor_elements_data = $document->get_elements_data();
		\Elementor\Plugin::$instance->documents->restore_document();

		// Build conditions applied on page array.
		if (!empty($elementor_elements_data)) {
			$elementor_elements_data_flatten = array_flatten($elementor_elements_data);
			if (isset($elementor_elements_data_flatten['cf_cond_visiblity_rule'])) {
				return (array) $elementor_elements_data_flatten['cf_cond_visiblity_rule'];
			}
		}

		return false;
	}

	/**
	 * Add conditions preview applied to pages created with elementor.
	 *
	 * @param object $admin_bar WP_Admin_Bar instance
	 * @return void
	 */
	private function conditionPreviewElementor($admin_bar)
	{
		global $post;
		$post_id = $post->ID;

		if (!\Elementor\Plugin::$instance->db->is_built_with_elementor($post_id)) {
			return;
		}

		$conditions = $this->getElementsConditions($post_id);

		if (!empty($conditions)) {
			$applied_conditions_query_arg = filter_input(INPUT_GET, 'inversed-conditions');
			$applied_conditions_array = [];

			// If conditions are already applied we convert them to array.
			if (!empty($applied_conditions_query_arg)) {
				$applied_conditions_array = explode(',', $applied_conditions_query_arg);
				$applied_conditions_array = array_flip($applied_conditions_array);
			}

			foreach ($conditions as $conditionId) {
				if ($this->hasPublishedConditions((array) $conditionId)) {
					$this->addPreviewMenu($admin_bar, $conditionId, $applied_conditions_array);
					$this->conditions_applied++;
				}
			}
		}
	}

	/**
	 * Add conditions preview applied to pages created with beaver builder.
	 *
	 * @param object $admin_bar WP_Admin_Bar instance
	 * @return void
	 */
	private function conditionPreviewBb($admin_bar)
	{
		$applied_conditions_query_arg = filter_input(INPUT_GET, 'inversed-conditions');
		$applied_conditions_array = [];

		// If conditions are already applied we convert them to array.
		if (!empty($applied_conditions_query_arg)) {
			$applied_conditions_array = explode(',', $applied_conditions_query_arg);
			$applied_conditions_array = array_flip($applied_conditions_array);
		}
		$rows = \FLBuilderModel::get_nodes();

		foreach ($rows as $row) {
			if (
				isset($row->settings->cf_personalization_enabled) &&
				'false' !== $row->settings->cf_personalization_enabled
			) {
				// Foreach condition associated to a module,
				// add a admin bar menu sub-item to check/uncked the condition on preview.
				foreach ($row->settings->cf_personalization_condition as $condition) {
					$conditionId = $condition->cf_personalization_condition_rule;

					if ($this->hasPublishedConditions((array) $conditionId)) {
						$this->addPreviewMenu($admin_bar, $conditionId, $applied_conditions_array);
						$this->conditions_applied++;
					}
				}
			}
		}
	}

	/**
	 * Add conditions preview applied to pages created with gutenberg.
	 *
	 * @param object $admin_bar WP_Admin_Bar instance
	 * @return void
	 */
	private function conditionPreviewGutenberg($admin_bar)
	{
		global $post;

		$blocks = parse_blocks($post->post_content);

		if (!empty($blocks)) {
			$applied_conditions_query_arg = filter_input(INPUT_GET, 'inversed-conditions');
			$applied_conditions_array = [];

			// If conditions are already applied we convert them to array.
			if (!empty($applied_conditions_query_arg)) {
				$applied_conditions_array = explode(',', $applied_conditions_query_arg);
				$applied_conditions_array = array_flip($applied_conditions_array);
			}

			// Parse every block and get all conditions associated.
			foreach ($blocks as $block) {
				if (
					isset($block['attrs']['condition'])
					&& $this->hasPublishedConditions($block['attrs']['condition'])
				) {
					// add a admin bar menu sub-item to check/uncked the condition on preview.
					$this->addPreviewMenu($admin_bar, $block['attrs']['condition'], $applied_conditions_array);
					$this->conditions_applied++;
				}
			}
		}
	}

	/**
	 * Check an array of ids for non-published status.
	 *
	 * @access private
	 *
	 * @param  int $condition Condition id
	 *
	 * @return bool If all ids are published conditions
	 */
	private function hasPublishedConditions($condition)
	{
		if ('publish' !== get_post_status($condition)) {
			return false;
		}
		return true;
	}

	/**
	 * Register condition admin bar menu item.
	 *
	 * @param object $admin_bar
	 * @param int $conditionId
	 * @param array $applied_conditions_array
	 * @return void
	 */
	private function addPreviewMenu($admin_bar, $conditionId, $applied_conditions_array)
	{
		$conditionId = (int) $conditionId;
		$condition_status = (bool) $this->condition_checker->isTriggered($conditionId, true);

		$condition_query = $applied_conditions_array;

		if ($condition_status !== isset($condition_query[$conditionId])) {
			$status = sprintf(
				'<span class="active" title="%s">(A)</span>',
				esc_attr__('Active by default', 'cf-conditional-content')
			);
		} else {
			$status = sprintf(
				'<span class="inactive" title="%s">(I)</span>',
				esc_attr__('Inactive by default', 'cf-conditional-content')
			);
		}

		// Condition exists and we remove it.
		if (isset($condition_query[$conditionId])) {
			unset($condition_query[$conditionId]);
			$title = 'Remove condition from preview';
			$class = 'condition-remove';

			// condition is activated by the preview else it is deactivated from the preview.
			if (!$condition_status) {
				$title = 'Reactivate condition to preview.';
				$class = 'condition-add default-active';
			}
		} else {
			$condition_query[$conditionId] = $conditionId;
			$title = 'Apply condition to preview';
			$class = 'condition-add';

			// condition is activated by default else it is deactivated by default.
			if ($condition_status) {
				$title = 'Remove condition from preview';
				$class = 'condition-remove default-active';
			}
		}

		$conditional_query_string = implode(',', array_flip($condition_query));

		$admin_bar->add_menu([
			'id' => 'cf-admin-bar-conditional-content-' . $conditionId,
			'parent' => 'cf-admin-bar-conditional-content',
			'title' => '<span>' . get_the_title($conditionId) . $status . '</span>', // Admin bar title.
			'href' => add_query_arg(['inversed-conditions' => $conditional_query_string]),
			'meta' => [
				'title' => esc_html__($title, 'cf-conditional-content'), // Title to show on hover.
				'class' => esc_attr($class),
			]
		]);
	}
}
