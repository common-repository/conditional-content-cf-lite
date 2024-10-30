<?php

/**
 * End date field partial.
 *
 * @package cf-conditional content
 */

$end_date_input_classes = [
	'form-control user-behavior-returning-custom cfdatetimepicker',
	'datetimepickercustom-' . $current_datetime_count,
];

if ('Time-Date' === $condition_type) {
	if (!empty($rule_time_date_schedule)) {
		$end_date_input_classes[] = 'show-selection';
	}

	$end_date = $rule['time-date-end-date'] ?? null;
}

?>
<div class="pick-date-container <?php
	echo 'Time-Date' === $condition_type && 'Start-End-Date' === $rule_time_date_schedule ? 'show-selection' : ''; ?>">
	<input type="text" name="time-date-end-date"
		data-field="time-date-pick-end-date"
		placeholder="<?php esc_attr_e('Click to pick a Date', 'cf-conditional-content'); ?>"
		class="<?php echo esc_attr(implode(' ', $end_date_input_classes)); ?>"
		<?php echo !empty($end_date) ? 'value="' . esc_attr($end_date) . '"' : ''; ?> />
</div>
