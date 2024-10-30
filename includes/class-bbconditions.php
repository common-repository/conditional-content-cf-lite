<?php

/**
 * Generate admin visibility settings for Beaver Builder modules.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * Class for Beaver Builder module integration.
 */
class BBConditions
{
	/**
	 * Instance of BBConditions.
	 *
	 * @access private
	 * @static
	 *
	 * @var BBConditions
	 */
	private static $instance;

	/**
	 * ConditionChecker instance.
	 *
	 * @var ConditionChecker
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
	 * Get instance of BBConditions.
	 *
	 * @access public
	 *
	 * @return BBConditions
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
	 * @access private
	 */
	private function __construct()
	{
		$this->condition_checker = ConditionChecker::getInstance();
		add_action('fl_builder_hidden_node', [$this, 'placeholderNode']);
		add_filter('fl_builder_register_settings_form', [ $this, 'conditionSettingsHook' ], 1010, 2);
		add_filter('fl_builder_is_node_visible', [ $this, 'shouldRenderHook' ], 11, 2);
		if (get_settings_lazy_load()) {
			add_action('wp_ajax_cf_cc_bb_content', [$this, 'renderNodes']);
			add_action('wp_ajax_nopriv_cf_cc_bb_content', [$this, 'renderNodes']);
		}
	}

	/**
	 * Generate Beaver Builder module personalization settings.
	 *
	 * @access public
	 *
	 * @param array $form Beaver Builder module settings array.
	 * @param string $slug Beaver Builder module slug.
	 *
	 * @return array
	 */
	public function conditionSettingsHook($form, $slug)
	{
		$modules = \FLBuilderModel::get_enabled_modules(); //* getting all active modules slug
		$condition_options = [];
		$conditions = $this->getConditions();
		if (!empty($conditions)) {
			foreach ($conditions as $condition) {
				$condition_options[$condition->ID] = $condition->post_title;
			}
		}
		if (in_array($slug, $modules)) {
			$form['personalization'] = [
				'title' => __('Personalization', 'cf-conditional-content'),
				'sections' => [
					'cf_personalization_visibility' => [
						'title' => __('Visibility Rules', 'cf-conditional-content'),
						'fields' => [
							'cf_personalization_enabled' => [
								'type'    => 'button-group',
								'label'   => __('Enable Personalization', 'cf-conditional-content'),
								'default' => 'false',
								'options' => [
									'false'   => 'No',
									'true'    => 'Yes'
								],
								'toggle'  => [
									'true'    => ['sections' => ['cf_personalization_conditions']]
								]
							]
						]
					],
					'cf_personalization_conditions' => [
						'title' => __('Available Conditions', 'cf-conditional-content'),
						'fields' => [
							'cf_personalization_condition' => [
								'type'    => 'select',
								'label'   => __('Selected Condition', 'cf-conditional-content'),
								'options' => $condition_options
							]
						]
					]
				]
			];
		}

		return $form;
	}

	/**
	 * Callback for the custom AJAX endpoint.
	 *
	 * @access public
	 *
	 *
	 * @return void
	 */
	public function renderNodes()
	{
		$response = [];
		if (! class_exists('FLBuilderModel')) {
			wp_send_json_error(
				[
					'message' => __('Beaver Builder Missing', 'cf-conditional-content'),
				],
				400
			);
			wp_die();
		}
		check_ajax_referer('cf-cc-nonce', 'nonce');

		$referrer = filter_input(INPUT_POST, 'referrer');
		// Set the referrer sent by the ajax call, otherwise this will always target the current page.
		$this->setReferrer($referrer);

		$this->asyncRequest();

		$post_id = (int) filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
		$node_ids = explode(',', filter_input(INPUT_POST, 'node_ids'));

		\FLBuilderModel::set_post_id($post_id);

		foreach ($node_ids as $node_id) {
			$node = \FLBuilderModel::get_node($node_id);
			if (
				$node && 'module' === $node->type
				&& $this->isVisibleByCondition($node->settings->cf_personalization_condition)
			) {
				$response[ $node_id ] = [];
				ob_start();
				\FLBuilder::render_module($node->node);
				$response[ $node_id ]['html'] = ob_get_clean();
				$response[ $node_id ]['js'] = \FLBuilder::render_module_js($node->node);
			}
		}


		wp_send_json_success($response);
		wp_die();
	}

	/**
	 * Async request.
	 */
	public function asyncRequest()
	{
		$this->condition_checker->asyncRequest();
	}

	/**
	 * Set refferer.
	 *
	 * @access public
	 * @param  string $url
	 *
	 * @return void
	 */
	public function setReferrer(string $url)
	{
		$this->condition_checker->setReferrer($url);
	}

	/**
	 * Get active conditions.
	 *
	 * @return array Array of conditions (WP_Post elements).
	 */
	protected function getConditions()
	{
		return get_posts(
			[
				'post_type'      => CF_CC_CPT_CONDITION,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			]
		);
	}

	/**
	 * Determine if element is visible by condition.
	 *
	 * @param object $element_condition Element condition data.
	 *
	 * @return bool
	 */
	public function isVisibleByCondition($element_condition)
	{
		if ($element_condition <= 0) {
			return true;
		}
		return (bool) $this->condition_checker->isTriggered($element_condition);
	}

	/**
	 * Determine if the BB Module should render or not.
	 *
	 * @access public
	 *
	 * @param bool $visible True if the section should render.
	 * @param object $node Beaver Builder Node.
	 *
	 * @return boolean True if the section should render.
	 */
	public function shouldRenderHook($visible, $node)
	{
		$settings = $node->settings;
		// Return true during REST requests so nodes can be rendered asynchronously.
		if (wp_doing_ajax()) {
			return true;
		}

		// Do nothing if personalization settings are not set
		if (
			empty($settings) ||
			\FLBuilderModel::is_builder_active() ||
			!isset($settings->cf_personalization_enabled) ||
			'false' === $settings->cf_personalization_enabled ||
			!$settings->cf_personalization_enabled
		) {
			return $visible;
		}

		// If lazy-load is active && node has non-url conditions deffer visibility to an async request.
		if (get_settings_lazy_load() && !$this->onlyUrlConditions($settings)) {
			return false;
		}
		$result = $this->isVisibleByCondition($settings->cf_personalization_condition);
		if ('' === $result) {
			return $visible;
		} else {
			return $result;
		}
	}

	/**
	 * Checks if a given node only has url conditions.
	 *
	 * @param object $settings Beaver Builder Node settings.
	 *
	 * @return bool true if only url conditions | false otherwise.
	 */
	private function onlyUrlConditions($settings)
	{
		if ('url' !== $this->getConditionType($settings->cf_personalization_condition)) {
			return false;
		}
		return true;
	}

	/**
	 * Returns the type for a condition.
	 *
	 * @param int $post_id The condition post id.
	 *
	 * @return string|null
	 */
	public function getConditionType($post_id)
	{
		$visibility_rule = json_decode(
			get_post_meta($post_id, CF_CC_CONDITIONS_META_KEY, true)
		);
		if (!empty($visibility_rule[0]->condition_type)) {
			return (string) $visibility_rule[0]->condition_type;
		}

		return null;
	}

	/**
	 * Render a placeholder div in place of a hidden node
	 *
	 * @param object $node Beaver Builder module node.
	 *
	 * @return null
	 */
	public function placeholderNode($node)
	{
			echo '<div class="cf-cc-bb-node-placeholder" data-node-id="' . esc_attr($node->node) . '"></div>';
		if (
			property_exists($node->settings, 'widget')
				&& 'GFWidget' === $node->settings->widget
				&& class_exists('GFForms')
		) {
			$form_id = $node->settings->{'widget-gform_widget'}->form_id;
			\GFForms::enqueue_form_scripts($form_id, true);
		}
	}
}
