<?php

/**
 * User behavior - returning custom field partial.
 *
 * @package cf-conditional content
 */

$returning_custom_classes = 'form-control user-behavior-returning-custom';

if (!empty($rule['user-behavior-retn-custom']) && ('User-Behavior' === $condition_type)) {
	$returning_custom_classes .= ' show-selection';
	$returning_custom_value = $rule['user-behavior-retn-custom'];
}

?>
<input type="text" name="user-behavior-retn-custom"
	data-field="user-behavior-retn-custom"
	placeholder="<?php esc_attr_e('Choose no. of visits', 'cf-conditional-content'); ?>"
	class="<?php echo esc_attr($returning_custom_classes); ?>"
	<?php echo !empty($returning_custom_value) ? 'value="' . esc_attr($returning_custom_value) . '"' : ''; ?> />
