<?php

/**
 * Geolocation field partial.
 *
 * @package cf-conditional content
 */

$are_there_any_selections = !empty($rule['geolocation_data']);

?>
<div>
	<div class="cf-cc-trigger-selection cf-cc-autocomplete-selection-display <?php
		echo (isset($rule['condition_type']) && 'Geolocation' === $rule['condition_type']) ? 'show-selection' : ''; ?>"
		data-field="geolocation-selection">

		<div class="cf-cc-autocomplete-fields-container <?php echo $are_there_any_selections ? 'shown' : null; ?>">

			<div class="locations-description <?php echo !$are_there_any_selections ? 'hide-field' : ''; ?>">
				<?php esc_html_e('Targeted locations:', 'cf-conditional-content'); ?>
			</div>

			<?php
			if ($are_there_any_selections) {
				$geolocation_data = utf8_decode($rule['geolocation_data']);
				$geolocation_data = str_replace('\\', '', $geolocation_data);
				?>
				<input class="cf-cc-autocomplete-data-field" type="hidden"
					name="repeater[<?php echo esc_attr($current_version_index); ?>][geolocation_data]"
					value="<?php echo esc_attr($geolocation_data); ?>"/>

				<?php
				$geolocation_data_array = explode('^^', $geolocation_data);
				$i = 0;
				foreach ($geolocation_data_array as $key => $value) {
					if ('1' != $value) {
						$exploded_data = explode('!!', $value);
						$address = $exploded_data[1];
						?>
						<div class="locationField">
							<span class="specific-location"><?php echo esc_html($address); ?></span>
							<button class="remove-autocomplete" data-pos="<?php echo esc_attr($i); ?>">
								<i class="dashicons dashicons-dismiss" aria-hidden="true"></i>
							</button>
						</div>
						<?php
						$i++;
					}
				}
			} else {
				?>
				<input class="cf-cc-autocomplete-data-field" type="hidden"
					name="repeater[<?php echo esc_attr($current_version_index); ?>][geolocation_data]" />
				<?php
			}
			?>
		</div>

		<div class="select-locations-container">
			<div class="selection-title">
				<div class="none-selected <?php echo $are_there_any_selections ? 'hide-field' : null; ?>">
					<i class="dashicons dashicons-post-status map-marker-near-input" aria-hidden="true"></i>
					<?php esc_html_e('Select a location', 'cf-conditional-content'); ?>
				</div>

				<div class="multiple-selected <?php echo !$are_there_any_selections ? 'hide-field' : ''; ?>">
					<i class="dashicons dashicons-post-status map-marker-near-input" aria-hidden="true"></i>
					<?php esc_html_e('Add another location', 'cf-conditional-content'); ?>
				</div>
			</div>

			<div class="selection-inputs-container">

				<div class="cf-cc-autocomplete-wrapper">
					<label>
						<input name="cf-cc-autocomplete-option" checked type="radio"
							class="cf-cc-autocomplete-opener"
							data-open="cf-cc-autocomplete-country" />
						<?php esc_html_e('Country', 'cf-conditional-content'); ?>
					</label>
					<div class="cf-cc-autocomplete-container cf-cc-autocomplete-country cf-cc-geo-selected">
						<input placeholder="<?php esc_html_e('Country (start typing)', 'cf-conditional-content'); ?>"
							class="countries-autocomplete cf-cc-input-autocomplete"
							type="search" data-symbol="COUNTRY"
							autocomplete="off" />
					</div>
				</div>

				<div class="cf-cc-autocomplete-wrapper">
					<label>
						<input name="cf-cc-autocomplete-option" type="radio"
							class="cf-cc-autocomplete-opener"
							data-open="select-city-container" />
						<?php esc_html_e('City', 'cf-conditional-content'); ?>
					</label>

					<div class="select-city-container cf-cc-autocomplete-container">
						<input class="cf-geolocation-autocomplete"
							placeholder="<?php esc_html_e('City (start typing)', 'cf-conditional-content'); ?>"
							type="search" />
					</div>
				</div>

				<div class="cf-cc-autocomplete-wrapper">
					<label>
						<input name="cf-cc-autocomplete-option" type="radio"
							class="cf-cc-autocomplete-opener"
							data-open="cf-cc-autocomplete-state" />
						<?php esc_html_e('State', 'cf-conditional-content'); ?>
					</label>

					<div class="cf-cc-autocomplete-container cf-cc-autocomplete-state">
						<input placeholder="<?php esc_html_e('State (start typing)', 'cf-conditional-content'); ?>"
							class="states-autocomplete cf-cc-input-autocomplete"
							type="search" data-symbol="STATE"
							autocomplete="off" />
					</div>
				</div>

				<div class="cf-cc-autocomplete-wrapper">
					<label>
						<input name="cf-cc-autocomplete-option" type="radio"
							class="cf-cc-autocomplete-opener"
							data-open="cf-cc-autocomplete-postal-code" />
						<?php esc_html_e('Zip Code / Postal Code', 'cf-conditional-content'); ?>
					</label>

					<div class="cf-cc-autocomplete-container cf-cc-autocomplete-postal-code">
						<input class="cf-cc-postal-code"
							placeholder="<?php esc_attr_e('Postal Code', 'cf-conditional-content'); ?>"
							data-symbol="POSTAL_CODE"
							data-label="<?php esc_attr_e('Postal Code', 'cf-conditional-content'); ?>"
							type="search"
							autocomplete="off" />

						<div class="geolocation-add-container">
							<button class="button-primary"
								data-input-field-class="cf-cc-postal-code">
								<?php esc_html_e('Add Postal Code', 'cf-conditional-content'); ?>
							</button>
						</div>
					</div>
				</div>
				<div class="cf-cc-autocomplete-wrapper>">
					<span
						<?php if ('wp-engine' !== get_option('cf_cc_settings_geoip_provider')) : ?>
							data-trigger="hover"
							data-container="body"
							data-toggle="popover"
							data-placement="right"
							data-content="<?php
								esc_html_e('Available only for WP Engine provider', 'cf-conditional-content'); ?>
								"
						<?php endif; ?>
					>
					<label>
						<input name="cf-cc-autocomplete-option" type="radio"
							class="cf-cc-autocomplete-opener"
							<?php echo ('wp-engine' === get_option('cf_cc_settings_geoip_provider')) ?: 'disabled'; ?>
							data-open="cf-cc-autocomplete-area-code" />
						<?php esc_html_e('Area Code', 'cf-conditional-content'); ?>
					</label>
					</span>
					<div class="cf-cc-autocomplete-container cf-cc-autocomplete-area-code">
						<input class="cf-cc-area-code"
							placeholder="<?php esc_attr_e('Area code', 'cf-conditional-content'); ?>"
							value=""
							data-symbol="AREA_CODE"
							data-label="<?php esc_attr_e('Area Code', 'cf-conditional-content'); ?>"
							autocomplete="off" />

						<div class="geolocation-add-container">
							<button class="button-primary" data-input-field-class="cf-cc-area-code">
								<?php esc_html_e('Add Area Code', 'cf-conditional-content'); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
