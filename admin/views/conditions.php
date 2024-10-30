<?php

/**
* Conditions.
*
* @package cf-conditional-content
*/

namespace CrowdFavorite\ConditionalContent;

if (!defined('ABSPATH')) {
	die;
}
?>

<li data-repeater-list="group-version" class="rule-item reapeater-item reapeater-item-cloned <?php echo ( ! $is_template) ? 'reapeater-item-cloned-loaded' : ''; // phpcs:ignore?>">
	<div class="row rule-wrap">
		<div class="col-lg-3 left-tabs visible-lg-block">
			<div class="condition-label">
				<?php esc_html_e('Select a condition', 'cf-conditional-content'); ?>
			</div>
			<div class="conditional-tabs <?php echo esc_attr($condition_type); ?>">
				<div data-trigger="Device" class="tab">
					<span class="icon-osdxpi-tablet-cellphone"></span>
					<?php esc_html_e('Device', 'cf-conditional-content'); ?>
				</div>
				<div data-trigger="User-Behavior" class="tab">
					<span class="icon-osdxpi-account-outline"></span>
					<?php esc_html_e('User Behavior', 'cf-conditional-content'); ?>
				</div>
				<div data-trigger="referrer" class="tab">
					<span class="icon-osdxpi-earth"></span>
					<?php esc_html_e('Referrer Source', 'cf-conditional-content'); ?>
				</div>
				<div data-trigger="url" class="tab">
					<span class="icon-osdxpi-link"></span>
					<?php esc_html_e('Dynamic Link', 'cf-conditional-content'); ?>
				</div>
				<div data-trigger="Time-Date" class="tab">
					<span class="icon-osdxpi-calendar-clock"></span>
					<?php esc_html_e('Time & Date', 'cf-conditional-content'); ?>
				</div>
				<div data-trigger="PageVisit" class="tab">
					<span class="icon-osdxpi-application"></span>
					<?php esc_html_e('Visited Pages', 'cf-conditional-content'); ?>
				</div>
				<?php if (get_settings_geoip_provider()) : ?>
					<div data-trigger="Geolocation" class="tab">
						<span class="icon-osdxpi-earth"></span>
						<?php esc_html_e('Geolocation', 'cf-conditional-content'); ?>
					</div>
				<?php endif; ?>
			</div>

		</div>

	<div class="col-md-12 col-lg-9 right-settings">
		<div class="col-md-12">
			<div class="condition-label">
				<?php
				esc_html_e(
					'Chose a condition to create a rule:',
					'cf-conditional-content'
				);
				?>
			</div>
		</div>
		<div class="col-md-6">
			<div class="cf-cc-form-group hidden-lg">
				<select name="condition_type" class="form-control trigger-type">
					<option value=""
					data-show-fields=""
					><?php esc_html_e(
						'Select a Condition',
						'cf-conditional-content'
					); ?></option>

					<option value="Device" <?php selected($condition_type, 'Device'); ?>
						data-show-fields="user-behavior-device"><?php esc_html_e(
							'Device',
							'cf-conditional-content'
						); ?></option>

						<option value="User-Behavior" <?php selected(
							$condition_type,
							'User-Behavior'
						); ?>
						data-show-fields="user-behavior-selection">
						<?php esc_html_e('User Behavior', 'cf-conditional-content'); ?>
					</option>

					<option value="referrer" <?php selected($condition_type, 'referrer'); ?>
						data-show-fields="referrer-selection">
						<?php esc_html_e('Referrer Source', 'cf-conditional-content'); ?>
					</option>

					<option value="url" <?php selected($condition_type, 'url'); ?>
						data-show-fields="url-custom|locked-box">
						<?php esc_html_e('Dynamic Link', 'cf-conditional-content'); ?>
					</option>

					<option value="Time-Date" <?php selected($condition_type, 'Time-Date'); ?>
						data-show-fields="times-dates-schedules-selections">
						<?php esc_html_e('Time & Date', 'cf-conditional-content'); ?>
					</option>

					<option value="PageVisit" <?php selected($condition_type, 'PageVisit'); ?>
						data-show-fields="page-visit-selection">
						<?php esc_html_e('Visited Pages', 'cf-conditional-content'); ?>
					</option>

					<?php if (get_settings_geoip_provider()) : ?>
						<option value="Geolocation"
						<?php
						selected(
							$condition_type,
							'Geolocation'
						);
						?>
						data-show-fields="geolocation-selection">
						<?php esc_html_e('Geolocation', 'cf-conditional-content'); ?>
					</option>
					<?php endif; ?>
			</select>
		</div>

		<div class="cf-cc-form-group" data-field="user-behavior-device">
			<?php include 'partials/field-user-behavior-device.php'; ?>
		</div>

		<div class="cf-cc-form-group second-level-selection-container
			<?php echo ($condition_type === 'referrer') ? esc_attr($rule['hidden_stored_selection_classes']) : ''; ?> "
			data-field="referrer-selection">
			<div class="cf-cc-form-subgroup">
				<?php include 'partials/field-trigger-select.php'; ?>
			</div>

			<div class="cf-cc-form-subgroup" data-field="referrer-custom">
				<?php include 'partials/field-operator.php'; ?>
			</div>

			<?php
			$referrer_show_selection = ! empty($rule_compare) && 'referrer' === $condition_type;
			$url_show_selection = ! empty($rule_compare) && 'url' === $condition_type;
			?>

			<input type="text" name="compare_referrer" data-field="referrer-custom"
			placeholder="<?php esc_attr_e('https://your-referrer.com', 'cf-conditional-content'); ?>"
			class="form-control referrer-custom <?php echo $referrer_show_selection ? 'show-selection' : ''; ?>"
			<?php echo $referrer_show_selection ? 'value="' . esc_attr($rule_compare) . '"' : ''; ?> />

			<div class="cf-cc-form-subgroup" data-field="page-selection">
				<?php include 'partials/field-page-select.php'; ?>
			</div>
			<h5>
				Looking for advanced conditions like <i>Common Refferal Websites</i> or <i>URL contains</i> filter?
				<a href="<?php esc_html_e(CF_CC_UPSELL_URL);?>" target="_blank">Check out Conditional Content Pro!</a>
			</h5>
		</div>

		<div
			class="cf-cc-form-group second-level-selection-container
			<?php echo ($condition_type === 'Time-Date') ? esc_attr($rule['hidden_stored_selection_classes']) : ''; ?>"
			data-field="times-dates-schedules-selections">
			<div class="cf-cc-form-subgroup">
				<?php include 'partials/field-time-date-schedule-select.php'; ?>
			</div>
			<?php if (!isset($_COOKIE['set_time_instructions'])) : ?>
				<div class="cf-cc-form-subgroup" data-field="time-date-selection schedule-selection">
					<div class="set-time-info-container <?php
						echo ('Time-Date' === $condition_type) ? 'show-selection' : ''; ?>">
						<div class="settimeinstructions">
							<span class="closeX">X</span>
							<p>
								<?php
								esc_html_e(
									"This condition uses your website's time settings",
									'cf-conditional-content'
								);
								?>
								,
								<a href="/wp-admin/options-general.php" target="_blank">
									<?php esc_html_e('click here', 'cf-conditionl-content'); ?>
								</a>

								<?php esc_html_e('to make sure they set correctly.', 'cf-conditionl-content'); ?>
							</p>
						</div>
					</div>
				</div>
			<?php endif; ?>
			<div class="cf-cc-form-subgroup" data-field="time-date-pick-start-date">
				<div class="ab-testing-custom-sessions-display cf-cc-start-at-date <?php
					echo 'Time-Date' === $condition_type
						 && 'Start-End-Date' === $rule_time_date_schedule ? 'show-selection' : ''; ?>">
					<p class="start-end-date-headers">
						<?php esc_html_e('Start displaying content from:', 'cf-conditional-content'); ?>
					</p>
				</div>
			</div>
			<div class="cf-cc-form-subgroup" data-field="time-date-pick-start-date">
				<?php include 'partials/field-start-date.php'; ?>
			</div>
			<div class="cf-cc-form-subgroup" data-field="time-date-pick-end-date">
				<div class="ab-testing-custom-sessions-display cf-cc-end-at-date <?php
					echo 'Time-Date' === $condition_type
						 && 'Start-End-Date' === $rule_time_date_schedule ? 'show-selection' : ''; ?>">
					<p class="start-end-date-headers">
						<?php esc_html_e('Stop displaying content from:', 'cf-conditional-content'); ?>
					</p>
				</div>
			</div>
			<div class="cf-cc-form-subgroup" data-field="time-date-pick-end-date">
				<?php include 'partials/field-end-date.php'; ?>
			</div>
			<h5>
				Looking for advanced conditions like <i>Time Scheduling</i>?
				<a href="<?php esc_html_e(CF_CC_UPSELL_URL);?>" target="_blank">Check out Conditional Content Pro!</a>
			</h5>
		</div>

		<div class="cf-cc-form-group second-level-selection-container" data-field="page-visit-selection">
			<div class="cf-cc-form-subgroup">
				<?php include 'partials/field-page-visit.php'; ?>
			</div>

			<h5>
				Looking for advanced conditions like <i>URL contains</i> filter?
				<a href="<?php esc_html_e(CF_CC_UPSELL_URL);?>" target="_blank">Check out Conditional Content Pro!</a>
			</h5>
		</div>

		<!-- User Behavior Begin -->
		<div
			class="cf-cc-form-group second-level-selection-container
			<?php echo $condition_type === 'User-Behavior' ? esc_attr($rule['hidden_stored_selection_classes']) : '';?>"
			data-field="user-behavior-selection">
			<div class="cf-cc-form-subgroup">
				<?php include 'partials/field-user-behavior.php'; ?>
			</div>

			<div class="cf-cc-form-subgroup" data-field="user-behavior-logged-selection">
				<?php include 'partials/field-user-behavior-logged.php'; ?>
			</div>

			<div class="cf-cc-form-subgroup" data-field="user-behavior-returning">
				<?php include 'partials/field-user-behavior-returning.php'; ?>
			</div>
			<div class="cf-cc-form-subgroup">
				<?php include 'partials/field-user-behavior-returning-custom.php'; ?>
			</div>
			<h5>
				Looking for advanced conditions like <i>Browser Language</i>?
				<a href="<?php esc_html_e(CF_CC_UPSELL_URL);?>" target="_blank">Check out Conditional Content Pro!</a>
			</h5>
		</div>
		<!-- User Behavior End -->

		<!-- Dynamic link Begin -->
		<div class="cf-cc-form-group">
			<?php include 'partials/field-compare-url.php'; ?>

			<input type="hidden" name="custom" value="url" />
		</div>
		<!-- Dynamic link End -->

		<!-- Geolocation Begin -->
		<div class="cf-cc-form-group" data-field="geolocation-selection">
			<?php include 'partials/field-geolocation.php'; ?>
		</div>
		<!-- Geolocation End -->
	</div><!-- .col-md-4 -->
	<div class="need-help-link">
		<p>
			<a href="<?php echo esc_url(CF_CC_PLUGIN_DOCUMENTATION_URL); ?>" title="<?php esc_attr_e('Have a question or need help? Get in touch with us!', 'cf-conditional-content'); // phpcs:ignore?>">
				<?php esc_html_e('Have a question or need help? Get in touch with us!', 'cf-conditional-content'); ?>
			</a>
		</p>
	</div>

</div><!-- col-md-9.right-settings -->

</div>

</li> <!-- end of rule-item -->
