<?php

/**
 * Operator select field partial.
 *
 * @package cf-conditional content
 */

?>
<select name="operator" class="form-control referrer-custom url-custom <?php echo
	!empty($rule_operator) && 'referrer' === $condition_type && 'custom' === $trigger ? 'show-selection' : ''; ?>">
	<option value="is" <?php selected($rule_operator, 'is'); ?>>
		<?php esc_html_e('URL Is', 'cf-conditional-content'); ?>
	</option>
	<option value="is-not" <?php selected($rule_operator, 'is-not'); ?>>
		<?php esc_html_e('URL Is Not', 'cf-conditional-content'); ?>
	</option>
</select>
