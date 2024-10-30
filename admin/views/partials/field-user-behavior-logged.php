<?php

/**
 * User behavior - logged field partial.
 *
 * @package cf-conditional content
 */

?>
<select name="user-behavior-logged" class="form-control referrer-custom <?php
	echo 'User-Behavior' === $condition_type && 'Logged' === $rule_user_behavior ? 'show-selection' : ''; ?>">
	<option value="logged-in" <?php selected($rule_user_behavior_logged, 'logged-in'); ?>>
		<?php esc_html_e('Yes', 'cf-conditional-content'); ?>
	</option>
	<option value="logged-out" <?php selected($rule_user_behavior_logged, 'logged-out'); ?>>
		<?php esc_html_e('No', 'cf-conditional-content'); ?>
	</option>
</select>
