<?php

/**
 * Generate admin visibility settings for Elementor sections and widgets.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

/**
 * Class for generating admin visibility settings for Elementor sections and widgets.
 */
class ElementorConditions
{
	/**
	 * Instance of ElementorConditions.
	 *
	 * @access private
	 * @static
	 *
	 * @var ElementorConditions
	 */
	private static $instance;

	/**
	 * ConditionChecker instance.
	 *
	 * @access private
	 *
	 * @var ConditionChecker
	 */
	private $condition_checker;

	/**
	 * Element visibility options.
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $element_visibility_options;

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
	 * @access protected
	 */
	protected function __construct()
	{
		$this->condition_checker = ConditionChecker::getInstance();

		$this->element_visibility_options = [
			'yes' => __('Yes', 'cf-conditional-content'),
			'no' => __('No', 'cf-conditional-content'),
		];

		add_action(
			'elementor/element/section/section_custom_css/after_section_end',
			[$this, 'sectionConditionsHook'],
			10,
			2
		);

		add_action('elementor/element/after_section_end', [$this, 'widgetConditionsHook'], 10, 3);

		if (get_settings_lazy_load()) {
			add_filter('elementor/frontend/section/should_render', [$this, 'renderIfNotConditionalHook'], 10, 2);
			add_filter('elementor/frontend/widget/should_render', [$this, 'renderIfNotConditionalHook'], 10, 2);

			add_action('elementor/frontend/after_render', [$this, 'afterRenderIfConditionalHook']);
			add_action('elementor/frontend/after_register_scripts', [$this, 'maybeEnqueueGravityFormsScripts']);
		} else {
			// Check elements visibility when content is not lazy loaded.
			add_filter('elementor/frontend/section/should_render', [$this, 'shouldRenderHook'], 10, 2);
			add_filter('elementor/frontend/widget/should_render', [$this, 'shouldRenderHook'], 10, 2);
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
	 * `elementor/frontend/after_render` action that adds a placeholder <span> for lazy-loading substution.
	 *
	 * @acccess public
	 *
	 * @param \Elementor\Element_Base $element Element instance.
	 *
	 * @return void
	 */
	public function afterRenderIfConditionalHook($element)
	{
		// If element has conditional rule, it will not be display.
		// Just add a wrapper in the page.
		$element_type = $element->get_type();
		if ('widget' !== $element_type && 'section' !== $element_type) {
			return;
		}

		$settings = $this->getSettings($element);
		if (
			!empty($settings)
			&& !empty($settings['cf_condition'])
			&& !empty($settings['cf_cond_element_visiblity'])
			&& 'no' !== $settings['cf_cond_element_visiblity']
		) {
			if (
				'url' === strtolower($this->getConditionType($settings['cf_condition']))
				&& $this->isVisibleByCondition($settings['cf_condition'])
			) {
				return;
			}
			$id = sprintf('cfcc-e-hid-element-%1$s', $element->get_id());
			echo wp_kses_post('<span id="' . $id . '"></span>');
		}

		$element->enqueue_scripts();
		$element->enqueue_styles();
	}

	/**
	 * 'should_render' filter callback to determine if a conditional content widget
	 * or section should render on initial page load.
	 *
	 * @access public
	 *
	 * @param bool $should_render Should render.
	 * @param \Elementor\Element_Base $element Element instance.
	 *
	 * @return bool
	 */
	public function renderIfNotConditionalHook($should_render, $element)
	{
		$settings = $this->getSettings($element);
		if (
			empty($settings)
			|| empty($settings['cf_cond_element_visiblity'])
			|| 'no' === $settings['cf_cond_element_visiblity']
		) {
			return $should_render;
		}

		if ('url' === $this->getConditionType($settings['cf_condition'])) {
			return $this->shouldRenderHook($should_render, $element);
		}

		// Element contains other types of conditional content rules and should be lazy-loaded.
		return false;
	}

	/**
	 * Maybe enqueue gravity forms scripts for ajax loading if there are conditional content widget with a gform.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function maybeEnqueueGravityFormsScripts()
	{
		$post = get_post();
		if (empty($post->ID)) {
			return;
		}

		if (!\Elementor\Plugin::$instance->db->is_built_with_elementor($post->ID)) {
			return;
		}

		$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend($post->ID);

		$enqueue_gform_scripts = function ($elements_data) use (&$enqueue_gform_scripts) {
			foreach ($elements_data as $element_data) {
				if (
					! empty($element_data['widgetType']) &&
					(false !== stristr($element_data['widgetType'], 'gform_widget')) &&
					! empty($element_data['settings']['wp']['form_id']) &&
					class_exists('GFForms')
				) {
					\GFForms::enqueue_form_scripts($element_data['settings']['wp']['form_id'], true);
				}

				if (! empty($element_data['elements'])) {
					$enqueue_gform_scripts($element_data['elements']);
				}
			}
		};

		$enqueue_gform_scripts($document->get_elements_data());
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
	 * Get active Conditions.
	 *
	 * @access protected
	 *
	 * @return array Array of Conditions (WP_Post elements).
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
	 * Generate Elementor section visibility settings.
	 *
	 * @access public
	 *
	 * @param \Elementor\Element_Section $section Elementor section.
	 * @param array $args    Extra arguments.
	 *
	 * @return void
	 */
	public function sectionConditionsHook($section, $args)
	{
		if ('advanced' !== $args['tab']) {
			return;
		}

		$this->addVisibilitySettings($section);
	}

	/**
	 * Generate widget visibility settings.
	 *
	 * @access protected
	 *
	 * @param \Elementor\Base_Element $element Elementor widget.
	 * @param string $section_id Section id.
	 * @param array $args Extra arguments.
	 *
	 * @return void
	 */
	public function widgetConditionsHook($element, $section_id, $args)
	{
		if ('widget' !== $element->get_type()) {
			return;
		}

		if (defined('ELEMENTOR_PRO__FILE__')) {
			$section = 'section_custom_css';
		} else {
			$section = 'section_custom_css_pro';
		}

		if ('advanced' !== $args['tab'] || $section !== $section_id) {
			return;
		}

		remove_action('elementor/element/after_section_end', [$this, 'widgetConditionsHook'], 10);

		$this->addVisibilitySettings($element);

		add_action('elementor/element/after_section_end', [$this, 'widgetConditionsHook'], 10, 3);
	}

	/**
	 * Add visibility settings for Elementor element.
	 *
	 * @access protected
	 *
	 * @param \Elementor\Base_Element $element Section or widget.
	 *
	 * @return void
	 */
	protected function addVisibilitySettings($element)
	{
		$element->start_controls_section(
			'_section_conditional_visibility',
			[
				'label' => __('Personalization', 'cf-conditional-content'),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'cf_cond_element_visiblity',
			[
				'label'        => __('Enable Personalization', 'cf-conditional-content'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'no',
				'options'      => $this->element_visibility_options,
				'prefix_class' => 'elementor-section-',
			]
		);

		$condition_options = [];
		$conditions = $this->getConditions();
		if (!empty($conditions)) {
			foreach ($conditions as $condition) {
				$condition_options[$condition->ID] = $condition->post_title;
			}
		}

		$element->add_control(
			'cf_condition',
			[
				'label'        => __('Selected Condition', 'cf-conditional-content'),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'options'      => $condition_options,
				'condition' => ['cf_cond_element_visiblity' => 'yes'],
				'prefix_class' => 'elementor-section-',
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Get instance of ElementorConditions.
	 *
	 * @access public
	 *
	 * @return ElementorConditions
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get and sanitize settings.
	 *
	 * @access private
	 *
	 * @param \Elementor\Element_Section $element Elementor section or widget.
	 *
	 * @return array Array of settings.
	 */
	private function getSettings($element)
	{
		$settings = $element->get_settings_for_display();

		if (empty($settings['cf_condition'])) {
			return [];
		}

		$element_visibility_options = array_keys($this->element_visibility_options);
		if (
			empty($settings['cf_cond_element_visiblity'])
			|| !in_array($settings['cf_cond_element_visiblity'], $element_visibility_options, true)
		) {
			$settings['cf_cond_element_visiblity'] = $element_visibility_options[0];
		}

		return $settings;
	}

	/**
	 * Determine if element is visible by condition data.
	 *
	 * @access public
	 *
	 * @param array $element_condition Element condition data.
	 *
	 * @return bool
	 */
	public function isVisibleByCondition($element_condition)
	{
		$condition_id = (int) $element_condition;
		if ($condition_id <= 0) {
			return true;
		}

		return (bool) $this->condition_checker->isTriggered($condition_id);
	}

	/**
	 * Determine if the Elementor section or widget should render or not.
	 * Filter for 'elementor/frontend/{$element_type}/should_render'.
	 *
	 * @access public
	 *
	 * @param bool $should_render True if the section should render.
	 * @param \Elementor\Element_Base $element Elementor section or widget.
	 *
	 * @return bool True if the section should render.
	 */
	public function shouldRenderHook($should_render, $element)
	{
		$settings = $this->getSettings($element);

		if (
			empty($settings)
			|| empty($settings['cf_cond_element_visiblity'])
			|| 'no' === $settings['cf_cond_element_visiblity']
		) {
			return $should_render;
		}

		return $this->isVisibleByCondition($settings['cf_condition']);
	}

	/**
	 * Returns the type for a Condition.
	 *
	 * @access public
	 *
	 * @param int $post_id The Condition post id.
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
}
