<?php

/**
 * Conditions metabox view.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

if (!defined('ABSPATH')) {
	die;
}
$hidden_stored_selection_classes = '';

if (! empty($data_rules[0]['hidden_stored_selection_classes'])) {
	$hidden_stored_selection_classes = $data_rules[0]['hidden_stored_selection_classes'];
}
?>

<div class="admin-conditions-wrap <?php echo esc_attr($hidden_stored_selection_classes); ?>"><!--wrap-->
	<div class="repeater">
		<div id="cf-cc-versions-container">
			<ul class="cf-cc-versions-sortable">
				<?php
				if (! empty($data_rules)) {
					$this->generateRuleItem($data_rules[0]);
				} else {
					$this->generateRuleItem(null);
				}
				?>
			</ul>
		</div>

		<input type="hidden" id="post_id" value="<?php echo (int)$post->ID; ?>"/>
		<input type="hidden" id="hidden_stored_selection_classes" name="hidden_stored_selection_classes"
			value="<?php echo esc_attr($hidden_stored_selection_classes); ?>"/>
	</div>

</div><!-- /.wrap -->
