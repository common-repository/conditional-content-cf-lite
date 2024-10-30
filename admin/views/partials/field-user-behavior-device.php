<?php

/**
 * User behavior - device field partial.
 *
 * @package cf-conditional content
 */

$is_mobile_checked = !empty($rule['user-behavior-device-mobile']) && 'Device' === $condition_type;
$is_tablet_checked = !empty($rule['user-behavior-device-tablet']) && 'Device' === $condition_type;
$is_desktop_checked = !empty($rule['user-behavior-device-desktop']) && 'Device' === $condition_type;

?>
<div class="devices-container user-behavior-device <?php echo 'Device' === $condition_type ? 'show-selection' : ''; ?>">

	<p class="deviceinstructions">
        <?php esc_html_e('Select the device(s) in which you would like the content to appear.', 'cf-conditional-content'); // phpcs:ignore?>
	</p>

	<div class="device-container">
		<label>
			<input type="checkbox" id="user-behavior-device-mobile" name="user-behavior-device-mobile"
				class="form-control deviceformcontrol" <?php echo $is_mobile_checked ? 'checked' : ''; ?> />

			<?php esc_html_e('Mobile', 'cf-conditional-content'); ?>
		</label>
	</div>
	<div class="device-container">
		<label>
			<input type="checkbox" name="user-behavior-device-tablet"
				class="form-control deviceformcontrol" <?php echo $is_tablet_checked ? 'checked' : ''; ?> />

			<?php esc_html_e('Tablet', 'cf-conditional-content'); ?>
		</label>
	</div>
	<div class="device-container">
		<label>
			<input type="checkbox" name="user-behavior-device-desktop"
				class="form-control deviceformcontrol" <?php echo $is_desktop_checked ? 'checked' : ''; ?> />

			<?php esc_html_e('Desktop', 'cf-conditional-content'); ?>
		</label>
	</div>
</div>
