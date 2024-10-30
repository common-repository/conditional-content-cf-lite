<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

// phpcs:disable
if (!headers_sent()) {
	session_start();
}
// phpcs:enable

/**
 * Class implementing the public-facing functionality of the plugin.
 */
class CFCCPublic
{
	/**
	 * Elementor triggers instance.
	 *
	 * @access private
	 *
	 * @var ElementorConditions $elementor_conditions Elementor triggers object.
	 */
	private $elementor_conditions;

	/**
	 * ConditionChecker instance.
	 *
	 * @var ConditionChecker
	 */
	private $condition_checker;

	/**
	 * Instance of CFCCPublic.
	 *
	 * @access private
	 * @static
	 *
	 * @var CFCCPublic
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
	 * @access private
	 */
	private function __construct()
	{
		$this->loadDependencies();

		$this->condition_checker = ConditionChecker::getInstance();
		$this->elementor_conditions = ElementorConditions::getInstance();

		add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
		add_action('wp_ajax_cf_cc_add_page_visit', [$this, 'addPageVisitAjaxHandler']);
		add_action('wp_ajax_nopriv_cf_cc_add_page_visit', [$this, 'addPageVisitAjaxHandler']);
		// Gutenberg integration
		add_action('wp_ajax_cf_cc_gb_content', [$this, 'addGutenbergAjaxHandler']);
		add_action('wp_ajax_nopriv_cf_cc_gb_content', [$this, 'addGutenbergAjaxHandler']);
		add_filter('render_block', [$this, 'filterGutenbergBlocks'], 10, 2);

		if (get_settings_lazy_load()) {
			add_action('elementor/frontend/the_content', [$this, 'elementorTheContent']);
			add_action('wp_ajax_cf_cc_load_content', [$this, 'loadPageContent']);
			add_action('wp_ajax_nopriv_cf_cc_load_content', [$this, 'loadPageContent']);
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
	 * Get current page URL.
	 *
	 * @access private
	 *
	 * @return string Page url.
	 */
	private function getCurrentPageURL()
	{
		global $wp_query;
		if (empty($wp_query->post)) {
			return '';
		}
		$page_url = get_permalink($wp_query->post->ID);
		return $page_url;
	}

	/**
	 * Get current post ID.
	 *
	 * @access private
	 *
	 * @return integer Post ID.
	 */
	private function getCurrentPostID()
	{
		global $wp_query;
		if (empty($wp_query->post)) {
			return '';
		}
		return $wp_query->post->ID;
	}

	/**
	 * Method to get instance of CFCCPublic.
	 *
	 * @access public
	 * @static
	 *
	 * @return CFCCPublic
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the public JavaScript.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function enqueueScripts()
	{
		if (
			(class_exists('\\Elementor\\Plugin') && \Elementor\Plugin::$instance->preview->is_preview_mode()) ||
			(class_exists('FLBuilder') && \FLBuilderModel::is_builder_active())
		) {
			return;
		}

		// Gutenberg public js
		wp_register_script(
			CF_CC_PLUGIN_SLUG . '-gut',
			CF_CC_PLUGIN_URL . 'public/js/public-gut.js',
			['jquery'],
			filemtime(CF_CC_PLUGIN_DIR . 'public/js/public-gut.js'),
			false
		);

		wp_localize_script(
			CF_CC_PLUGIN_SLUG . '-gut',
			'CFCCGBSettings',
			[
				'ajax_url'  => admin_url('admin-ajax.php'),
				'nonce'     => wp_create_nonce('cf-cc-nonce'),
				'lazy_load' => (bool) get_settings_lazy_load(),
				'page_url'  => $this->getCurrentPageURL(),
			]
		);
		wp_enqueue_script(CF_CC_PLUGIN_SLUG . '-gut');

		// BB | Elementor public js
		if (class_exists('FLBuilder') && \FLBuilderModel::is_builder_enabled()) {
			wp_register_script(
				CF_CC_PLUGIN_SLUG . '-bb',
				CF_CC_PLUGIN_URL . 'public/js/public-bb.js',
				['jquery'],
				filemtime(CF_CC_PLUGIN_DIR . 'public/js/public-bb.js'),
				false
			);

			wp_localize_script(
				CF_CC_PLUGIN_SLUG . '-bb',
				'CFCCBBSettings',
				[
					'post_id'  => $this->getCurrentPostID(),
					'lazy_load' => (bool) get_settings_lazy_load(),
					'ajax_url'  => admin_url('admin-ajax.php'),
					'nonce'     => wp_create_nonce('cf-cc-nonce'),
				]
			);
			wp_enqueue_script(CF_CC_PLUGIN_SLUG . '-bb');
		} elseif (class_exists('\\Elementor\\Plugin')) {
			wp_register_script(
				CF_CC_PLUGIN_SLUG . '-elementor',
				CF_CC_PLUGIN_URL . 'public/js/public-elementor.js',
				['jquery'],
				filemtime(CF_CC_PLUGIN_DIR . 'public/js/public-elementor.js'),
				false
			);

			wp_localize_script(
				CF_CC_PLUGIN_SLUG . '-elementor',
				'CFCCElementorSettings',
				[
					'ajax_url'  => admin_url('admin-ajax.php'),
					'nonce'     => wp_create_nonce('cf-cc-nonce'),
					'page_url'  => $this->getCurrentPageURL(),
					'lazy_load' => (bool) (get_settings_lazy_load() && class_exists('\\Elementor\\Plugin')),
				]
			);
			wp_enqueue_script(CF_CC_PLUGIN_SLUG . '-elementor');
		}
	}

	/**
	 * Page visit ajax handler.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function addPageVisitAjaxHandler()
	{
		check_ajax_referer('cf-cc-nonce', 'nonce');

		$page_url = filter_input(INPUT_POST, 'page_url');
		PageVisitsHandler::getInstance()->savePage($page_url);

		wp_die();
	}

	/**
	 * Filter Gutenberg Blocks content based on lazy-load setting and conditions.
	 *
	 * @access public
	 *
	 * @param  string $block_content The block content about to be appended.
	 * @param  array $block The full block, including name and attributes.
	 *
	 * @return string $block_content The contents of a speciffic Gutenberg Block
	 */
	public function filterGutenbergBlocks($block_content, $block)
	{
		if (isset($block['attrs']['condition']) && 'publish' === get_post_status($block['attrs']['condition'])) {
			if (get_settings_lazy_load() && 'url' !== $this->getConditionType($block['attrs']['condition'])) {
				$tag = '<div class="wp-block-crowdfavorite-conditional-content-block">';
				$new_tag = <<<EOD
<div class="cc-has-condition" style="display:none"
	data-condition="{$block['attrs']['condition']}"
>
EOD;
				$block_content = substr_replace(
					$block_content,
					$new_tag,
					strpos($block_content, $tag), // Get starting position of tag.
					strlen($tag)
				);
			} elseif (!$this->isTriggeredCondition($block['attrs']['condition'])) {
				$block_content = '';
			}
		}

		return $block_content;
	}

	/**
	 * Gutenberg content ajax handler.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function addGutenbergAjaxHandler()
	{
		check_ajax_referer('cf-cc-nonce', 'nonce');

		$this->setReferrer(filter_input(INPUT_POST, 'referrer'));
		$this->asyncRequest();

		$condition_ids = explode(',', filter_input(INPUT_POST, 'cond_ids'));

		$response = [];

		foreach ($condition_ids as $condition_id) {
			if ($this->isTriggeredCondition($condition_id)) {
				$response[] .= $condition_id;
			}
		}
		wp_send_json_success($response);
		wp_die();
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
	 * Determine if conditions is triggered.
	 *
	 * @param int $condition_id The CPT ID of the condition.
	 *
	 * @return bool
	 */
	public function isTriggeredCondition($condition_id)
	{
		return (bool) $this->condition_checker->isTriggered($condition_id);
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
	 * Add a hidden field with the current Elementor post to the current content.
	 *
	 * @access public
	 *
	 * @param string $content Elementor content.
	 *
	 * @return string Updated content.
	 */
	public function elementorTheContent($content)
	{
		global $post;
		global $wp_query;

		if (!empty($post->ID)) {
			$content .= sprintf(
				'<input type="hidden" name="cf_cc_current_post" id="cf-cc-current-post" value="%d" />',
				(int) $post->ID
			);

			if (!$wp_query->is_singular()) {
				$key = 'cfccq-' . md5(wp_json_encode($wp_query->query_vars));
				set_transient($key, $wp_query->query_vars, MINUTE_IN_SECONDS);
				$content .= '<input type="hidden" name="cf_cc_q" id="cf-cc-q" value="' . esc_attr($key) . '" />';
			}
		}

		return $content;
	}

	/**
	 * Method to load dependencies for the public-facing side of the site.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function loadDependencies()
	{
		require_once __DIR__ . '/helpers/mobile-detection.php';
		require_once __DIR__ . '/class-pagevisitshandler.php';
	}

	/**
	 * AJAX load the content.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function loadPageContent()
	{
		global $post;

		check_ajax_referer('cf-cc-nonce', 'nonce');

		$post_id = (int) filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
		$key = filter_input(INPUT_POST, 'key');

		// Set the referrer sent by the ajax call, otherwise this will always target the current page.
		$this->elementor_conditions->setReferrer(filter_input(INPUT_POST, 'referrer'));

		$post = get_post($post_id); // @codingStandardsIgnoreLine.
		if (empty($post)) {
			wp_send_json_error(
				[
					'message' => __('Post not found.', 'cf-conditional-content'),
				],
				404
			);
			wp_die();
		}
		setup_postdata($post);

		// Set the REQUEST_URI for this post for any URL/link generation needs.
		$_SERVER['REQUEST_URI'] = add_query_arg(
			filter_input_array(INPUT_GET) ?? [],
			wp_make_link_relative(get_permalink())
		);

		if (! empty($key)) {
			$vars = get_transient($key);
			if (! empty($vars)) {
				delete_transient($key);
				add_filter(
					'elementor/theme/posts_archive/query_posts/query_vars',
					function ($query_vars) use ($vars) {
						return $vars;
					}
				);
			}
		}

		add_filter(
			'elementor/frontend/section/should_render',
			[$this->elementor_conditions, 'shouldRenderHook'],
			10,
			2
		);

		add_filter('elementor/frontend/widget/should_render', [$this->elementor_conditions, 'shouldRenderHook'], 10, 2);
		remove_action('elementor/frontend/after_render', [$this->elementor_conditions, 'afterRenderIfConditionalHook']);

		$this->elementor_conditions->asyncRequest();

		if ($post_id > 0 && class_exists('\\Elementor\\Plugin')) {
			try {
				// Render only elements with visibility conditions.
				$conditional_elements = $this->getConditionalElementContentForDisplay($post_id);
			} catch (\Exception $ex) {
				$conditional_elements = [];
			}

			wp_send_json_success(
				[
					'elements' => $conditional_elements,
					'id'   => $post_id,
				]
			);
		}

		wp_die();
	}

	/**
	 * Method to get conditional element content for display.
	 *
	 * @access public
	 *
	 * @param int $post_id Post ID.
	 * @param bool $with_css With CSS?
	 *
	 * @return array
	 */
	public function getConditionalElementContentForDisplay($post_id, $with_css = false)
	{
		if (post_password_required($post_id)) {
			return [];
		}

		if (!\Elementor\Plugin::$instance->db->is_built_with_elementor($post_id)) {
			return [];
		}

		$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend($post_id);

		// Change the current post, so widgets can use `documents->get_current`.
		\Elementor\Plugin::$instance->documents->switch_to_document($document);

		$data = $document->get_elements_data();

		/**
		 * Frontend builder content data.
		 *
		 * Filters the builder content in the frontend.
		 *
		 * @since 1.0.0
		 *
		 * @param array $data    The builder content.
		 * @param int   $post_id The post ID.
		 */
		$data = apply_filters('elementor/frontend/builder_content_data', $data, $post_id);

		if (empty($data)) {
			return [];
		}

		// Recursively loop through element data and render conditional content elements.
		$rendered_elements = [];
		$get_conditional_elements_recursive = function (
			$builder_content_data,
			&$rendered_elements
		) use (&$get_conditional_elements_recursive) {
			foreach ($builder_content_data as $element_data) {
				if (
					!empty($element_data['settings']['cf_condition'])
					&& 'yes' === $element_data['settings']['cf_cond_element_visiblity']
				) {
					$condition_type = strtolower(
						$this->elementor_conditions->getConditionType(
							$element_data['settings']['cf_condition']
						)
					);

					// Don't render elements with 'url' conditions.
					if (isset($condition_type) && 'url' === $condition_type) {
						continue;
					}

					// If element is a Gravity Form, ensure that it's loaded via AJAX.
					if (
						!empty($element_data['widgetType'])
						&& (false !== stristr($element_data['widgetType'], 'gform_widget'))
					) {
						$element_data['settings']['wp']['ajax'] = '1';
						$element_data['settings']['wp']['disable_scripts'] = '1';
					}

					$element = \Elementor\Plugin::$instance->elements_manager->create_element_instance($element_data);
					if (! is_a($element, \Elementor\Element_Base::class)) {
						continue;
					}

					ob_start();
					$element->print_element();
					$html = ob_get_clean();
					$rendered_elements[ $element->get_id() ] = $html;
				}

				if (! empty($element_data['elements'])) {
					$get_conditional_elements_recursive($element_data['elements'], $rendered_elements);
				}
			}
		};
		$get_conditional_elements_recursive($data, $rendered_elements);

		\Elementor\Plugin::$instance->documents->restore_document();

		return $rendered_elements;
	}
}
