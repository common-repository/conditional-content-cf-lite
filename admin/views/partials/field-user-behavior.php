<?php

/**
 * User behavior field partial.
 *
 * @package cf-conditional content
 */

?>
<div class="cf-cc-trigger-selection">
	<p>
		<?php esc_html_e('Select the user behavior in which you would like the rule to apply, additional options are available based on the selected user behavior.', 'cf-conditional-content'); // phpcs:ignore ?>
	</p>
	<select
		name="User-Behavior"
		data-field="user-behavior-selection"
		class="form-control second-level-selection ab-testing <?php
		echo !empty($rule_user_behavior) && 'User-Behavior' === $condition_type ? 'show-selection' : ''; ?>">

		<option value="" data-show-fields="user-behavior-selection">
			<?php esc_html_e('Choose an option', 'cf-conditional-content'); ?>
		</option>

		<option value="Logged" <?php selected($rule_user_behavior, 'Logged'); ?>
			data-show-fields="user-behavior-selection|user-behavior-logged-selection">
			<?php esc_html_e('Logged In', 'cf-conditional-content'); ?>
		</option>

		<option value="NewUser" <?php selected($rule_user_behavior, 'NewUser'); ?>
			data-show-fields="user-behavior-selection|locked-box">
			<?php esc_html_e('New Visitor', 'cf-conditional-content'); ?>
		</option>

		<option value="Returning" <?php selected($rule_user_behavior, 'Returning'); ?>
			data-show-fields="user-behavior-selection|user-behavior-returning|locked-box">
			<?php esc_html_e('Returning Visitor', 'cf-conditional-content'); ?>
		</option>
	</select>
</div>
