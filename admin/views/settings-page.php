<?php

/**
 * Settings page view.
 *
 * @var string $google_maps_api_key Google Maps API key.
 * @var array $intervals An array of intervals where the keys are the slugs and the values are the names.
 * @var bool $lazy_load Whether or not to load the content asynchronously.
 * @var bool $remove_data_uninstall Whether or not to remove the plugin data on uninstall.
 * @var array $visited_pages An array containing the duration and the interval for the visited pages.
 *
 * @see AdminSettings::renderSettingsPage()
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="cc-upsell-banner">
	<div class="cta">
		<img
			alt="Conditional Content Logo"
			src="<?php echo esc_url(CF_CC_PLUGIN_URL . 'admin/assets/images/logo.png'); ?>"
		>
	</div>
	<div class="content">
		<h2><?php esc_html_e('Upgrade to Conditional Content Pro', 'cf-conditional-content'); ?></h2>
		<p>
			<?php esc_html_e(
				'Looking for additional functionality including rulesets, multiple geolocation providers,
				advanced conditions like time scheduling and more? Check out our premium licenses.',
				'cf-conditional-content'
			); ?>
		</p>
	</div>
	<div class="cta">
		<a href="<?php echo esc_url(CF_CC_UPSELL_URL); ?>" target="_blank" class="button-primary">
			<?php esc_html_e('Upgrade to Pro', 'cf-conditional-content'); ?>
		</a>
	</div>
</div>
<div class="wrap">
	<h1 class="wp-heading">
		<?php esc_html_e('Conditional Content Settings', 'cf-conditional-content'); ?>
	</h1>
	<div class="cf-cc-settings-page-wrapper cf-cc-body">
		<?php do_action('cf_cc_legacy_settings'); ?>
		<form method="post" action="options.php" class="cf-cc-settings-form">
			<?php settings_fields(CF_CC_PLUGIN_SETTINGS_GROUP); ?>
			<?php do_settings_sections(CF_CC_PLUGIN_SETTINGS_GROUP); ?>

			<table class="form-table cf-cc-settings-tbl">
				<tbody>
					<tr valign="top">
						<th class="cf-cc-settings-td" scope="row" valign="top">
							<?php esc_html_e('GENERAL SETTINGS', 'cf-conditional-content'); ?>
						</th>
					</tr>
					<tr valign="top">
						<td class="cf-cc-settings-td" scope="row" valign="baseline">
							<b><?php esc_html_e('Visited pages tracking duration', 'cf-conditional-content'); ?></b>
						</td>
						<td valign="baseline">
							<input
								name="cf_cc_settings_visited_pages[duration]"
								type="text"
								class="cf_cc_settings_page_option cf_cc_setting_page_option_number_select"
								value="<?php echo esc_attr($visited_pages['duration']); ?>" />
							<select name="cf_cc_settings_visited_pages[interval]">
								<?php if (!empty($intervals) && is_array($intervals)) : ?>
									<?php foreach ($intervals as $key => $text) : ?>
										<option value="<?php echo esc_attr($key); ?>"
											<?php selected($visited_pages['interval'], $key); ?>>
											<?php echo esc_html($text); ?>
										</option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
							</br>
							<i>
								<?php
								esc_html_e(
									"The duration used by the 'visited pages' condition to track users visits.",
									'cf-conditional-content'
								);
								?>
							</i>
						</td>
					</tr>
					<tr valign="top">
						<td class="cf-cc-settings-td" scope="row" valign="baseline">
							<b><?php esc_html_e('Remove data on uninstall?', 'cf-conditional-content'); ?></b>
						</td>
						<td valign="baseline">
							<input
								type="checkbox"
								<?php echo $remove_data_uninstall ? 'checked' : ''; ?>
								name="cf_cc_settings_remove_data_on_uninstall"
								type="text"
								class="cf_cc_settings_page_option"
								value="remove_data_on_uninstall" />
							<i>
								<?php
								esc_html_e(
                                	// phpcs:ignore Generic.Files.LineLength.TooLong
									'Check this box if you would like Conditional Content to completely remove all of its data when the plugin is deleted.',
									'cf-conditional-content'
								); ?>
							</i>
						</td>
					</tr>
					<tr valign="top">
						<td class="cf-cc-settings-td" scope="row" valign="baseline">
							<b><?php esc_html_e('Lazy load content?', 'cf-conditional-content'); ?></b>
						</td>
						<td valign="baseline">
							<input
								type="checkbox"
								<?php echo $lazy_load ? 'checked' : ''; ?>
								name="cf_cc_settings_lazy_load"
								type="text"
								class="cf_cc_settings_lazy_load"
								value="lazy_load" />
							<i>
								<?php
								esc_html_e(
                                	// phpcs:ignore Generic.Files.LineLength.TooLong
									'Check this box if you would like Conditional Content to load the content asynchronously.',
									'cf-conditional-content'
								); ?>
							</i>
						</td>
					</tr>
					<tr valign="top">
						<td class="cf-cc-settings-td" scope="row" valign="baseline">
							<b><?php esc_html_e('GeoIP Provider', 'cf-conditional-content'); ?></b>
						</td>
						<td valign="baseline">
							<select name="cf_cc_settings_geoip_provider">
								<option default value="">
									<?php esc_html_e('Do not use geolocation', 'cf-conditional-content'); ?>
								</option>
								<?php if (!empty($geoip_providers) && is_array($geoip_providers)) : ?>
									<?php foreach ($geoip_providers as $key => $provider) : ?>
										<option value="<?php echo esc_attr($key); ?>"
											<?php selected($geoip_selected_provider, $key); ?>
											<?php class_exists('\WPEngine\GeoIp') ?: disabled('wp-engine', $key); ?>>
											<?php echo esc_html($provider['name']); ?>
										</option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
							</br>
							<i>
								<?php
								echo sprintf(
									esc_html__(
										/* Translators: %1$s - Documentation URL. */
										'To use WP Engine as a GeoIP provider please consult the documentation available at %1$s.', // phpcs:ignore Generic.Files.LineLength.TooLong
										'cf-conditional-content'
									),
									'<a href="https://wpengine.com/support/enabling-geotarget">
                                        https://wpengine.com/support/enabling-geotarget
                                    </a>'
								);
								?>
							</i>
							<h5>
								Looking for other <i>geolocation providers</i>?
								<a href="<?php esc_html_e(CF_CC_UPSELL_URL);?>" target="_blank">
									Check out Conditional Content Pro!
								</a>
							</h5>
						</td>
					</tr>
					<?php if ($geoip_selected_provider) : ?>
						<tr valign="top">
							<td class="cf-cc-settings-td" scope="row" valign="baseline">
								<b><?php esc_html_e('Google Maps API Key', 'cf-conditional-content'); ?></b>
							</td>
							<td valign="baseline">
								<input
									type="text"
									name="cf_cc_settings_google_maps_api_key"
									type="text"
									class="cf_cc_settings_page_option"
									value="<?php echo esc_attr($google_maps_api_key); ?>"
									size="50"
								/>
							</br>
							<i>
								<?php
									esc_html_e(
                                        // phpcs:ignore Generic.Files.LineLength.TooLong
										'An API key is required to use autocomplete suggestions for geolocation conditions.',
										'cf-conditional-content'
									);
								?>
							</i>
							</td>
						</tr>
					<?php endif; ?>
					<tr valign="top">
						<td>
							<?php submit_button(); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
