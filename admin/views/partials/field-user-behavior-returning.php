<?php

/**
 * User behavior - returning field partial.
 *
 * @package cf-conditional content
 */

?>
<div class="ab-testing-custom-sessions-display <?php
	echo ('Returning' === $rule_user_behavior) ? 'show-selection' : ''; ?>">
	<p class="instructionabovefield">
		<?php esc_html_e('Show this content after:', 'cf-conditional-content'); ?>
	</p>
</div>

<select name="user-behavior-returning"
	class="form-control referrer-custom second-level-selection <?php
	echo 'Returning' === $rule_user_behavior ? 'show-selection' : ''; ?>"
	data-field="user-behavior-returning">

	<option
		value="first-visit"
		<?php selected($rule_user_behavior_returning, 'first-visit'); ?>
		data-show-fields="user-behavior-selection|user-behavior-returning|locked-box">
		<?php esc_html_e('First Visit', 'cf-conditional-content'); ?>
	</option>

	<option
		value="second-visit"
		<?php selected($rule_user_behavior_returning, 'second-visit'); ?>
		data-show-fields="user-behavior-selection|user-behavior-returning|locked-box">
		<?php esc_html_e('2 Visits', 'cf-conditional-content'); ?>
	</option>

	<option
		value="three-visit"
		<?php selected($rule_user_behavior_returning, 'three-visit'); ?>
		data-show-fields="user-behavior-selection|user-behavior-returning|locked-box">
		<?php esc_html_e('3 Visits', 'cf-conditional-content'); ?>
	</option>

	<option value="custom" <?php selected($rule_user_behavior_returning, 'custom'); ?>
		data-show-fields="user-behavior-selection|user-behavior-returning|user-behavior-retn-custom">
		<?php esc_html_e('Custom', 'cf-conditional-content'); ?>
	</option>
</select>
