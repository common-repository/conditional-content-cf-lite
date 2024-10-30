<?php

/**
 * Time & Date/Schedule select field partial.
 *
 * @package cf-conditional content
 */

?>
<select name="Time-Date-Schedule-Selection"
	class="form-control ab-testing cf-cc-pick-start-date second-level-selection <?php
	echo ('Time-Date' === $condition_type) ? 'show-selection' : ''; ?>">

	<option value="" data-show-fields="times-dates-schedules-selections">
		<?php esc_html_e('Choose an option', 'cf-conditional-content'); ?>
	</option>

	<option value="Start-End-Date" <?php selected($rule_time_date_schedule, 'Start-End-Date'); ?>
		data-show-fields="times-dates-schedules-selections|time-date-pick-start-date|time-date-pick-end-date|locked-box"><?php // phpcs:ignore Generic.Files.LineLength.TooLong ?>
		<?php esc_html_e('Start/End Date', 'cf-conditional-content'); ?>
	</option>
</select>
