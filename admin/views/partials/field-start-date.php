<?php

/**
 * Start date field partial.
 *
 * @package cf-conditional content
 */

$start_date_input_classes = [
	'cfdatetimepicker form-control user-behavior-returning-custom',
	'datetimepickercustom-' . $current_datetime_count,
];

if ('Time-Date' === $condition_type) {
	$start_date_input_classes[] = 'show-selection';
	$start_date = $rule['time-date-start-date'] ?? null;
}

?>
<div class="pick-date-container <?php
	echo 'Time-Date' === $condition_type && 'Start-End-Date' === $rule_time_date_schedule ? 'show-selection' : ''; ?>">
	<input type="text" name="time-date-start-date"
		data-field="time-date-pick-start-date"
		placeholder="<?php esc_attr_e('Click to pick a Date', 'cf-conditional-content'); ?>"
		class="<?php echo esc_attr(implode(' ', $start_date_input_classes)); ?>"
		<?php echo !empty($start_date) ? 'value="' . esc_attr($start_date) . '"' : ''; ?> />
</div>
