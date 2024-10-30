<?php

/**
 * Page visit field partial.
 *
 * @package cf-conditional content
 */

use function CrowdFavorite\ConditionalContent\clean_page_url;

$are_there_any_selections = !empty($rule['page_visit_data']);

?>

<div class="cf-cc-trigger-selection cf-cc-autocomplete-selection-display <?php
	echo ('PageVisit' === $condition_type) ? 'show-selection' : ''; ?>">

	<div class="cf-cc-autocomplete-fields-container <?php echo ($are_there_any_selections) ? 'shown' : ''; ?>">
		<div class="locations-description <?php echo ( ! $are_there_any_selections) ? 'hide-field' : ''; ?>">
			<div class="cf-cc-pages-visited-settings-explain">
				<?php
				esc_html_e(
					'This version will be displayed if the visitor has visited one of the following pages in the last',
					'cf-conditional-content'
				);
				?>
				<a href="<?php echo esc_url(admin_url('edit.php?post_type=' . CF_CC_CPT_CONDITION . '&page=' . CF_CC_PLUGIN_SETTINGS_PAGE)); ?>"
					target="_blank">
					<i class="dashicons dashicons-edit"><!--icon--></i>
					<?php echo esc_html($visited_pages_info); ?>
				</a>.
			</div>
		</div>

		<?php
		if ($are_there_any_selections) {
			$page_visit_data = utf8_decode($rule['page_visit_data']);
			$page_visit_data = str_replace('\\', '', $page_visit_data);
			?>
			<input class="cf-cc-autocomplete-data-field" type="hidden"
				name="page_visit_data"
				value="<?php echo esc_attr($page_visit_data); ?>"/>

			<?php
			$page_visit_datat_array = explode('^^', $page_visit_data);
			$i = 0;
			foreach ($page_visit_datat_array as $key => $value) {
				if ('1' !== $value) {
					$exploded_data = explode('!!', $value);
					if (is_numeric($exploded_data[2])) {
						$page_url = get_permalink($exploded_data[2]);
						$page_url = clean_page_url($page_url);
						$operator = 'url is';
					} else {
						$page_url = $exploded_data[1];
						$operator = $exploded_data[2];
					}
					?>
					<div class="locationField">
						<span class="cf-cc-page-visit-operator-field"><?php echo esc_html($operator); ?>: </span>
						<br>
						<span class="specific-location"><?php echo esc_url($page_url); ?></span>
						<button class="remove-autocomplete" data-pos="<?php echo (int)$i; ?>">
							<i class="dashicons dashicons-dismiss" aria-hidden="true"></i>
						</button>
					</div>
					<?php
					$i++;
				}
			}
		} else {
			?>
			<input class="cf-cc-autocomplete-data-field" type="hidden" name="page_visit_data" />
			<?php
		}
		?>
	</div>

	<div class="select-locations-container">
		<div class="selection-title">
			<div class="none-selected <?php echo $are_there_any_selections ? 'hide-field' : ''; ?>">
				<?php esc_html_e('Enter the URL of the visited pages you would like the content applied.', 'cf-conditional-content'); // phpcs:ignore?>
			</div>

			<div class="multiple-selected <?php echo !$are_there_any_selections ? 'hide-field' : ''; ?>">
				<?php esc_html_e('Add another page', 'cf-conditional-content'); ?>
			</div>
		</div>
	</div>
	<div class="selection-inputs-container">
		<div class="cf-cc-form-subgroup">
			<div class="cf-cc-trigger-selection">
				<select name="page-visit-operator"
					class="second-level-selection form-control referrer-custom url-custom cf-cc-page-visit-operator
					<?php echo ('PageVisit' === $condition_type) ? 'show-selection' : ''; ?>"
					data-field="page-visit-selection">
					<option value="" data-show-fields="page-visit-selection">
						<?php esc_html_e('Choose an option', 'cf-conditional-content'); ?>
					</option>

					<option value="url is" data-show-fields="page-visit-selection|url-input">
						<?php esc_html_e('URL Is', 'cf-conditional-content'); ?>
					</option>
					<option value="url is not" data-show-fields="page-visit-selection|url-input">
						<?php esc_html_e('URL Is Not', 'cf-conditional-content'); ?>
					</option>
				</select>
			</div>
		</div>
		<div class="cf-cc-form-subgroup page-visit-url-input" data-field="url-input">
			<input type="text" placeholder="Type" data-symbol="PAGEURL"
				class="cf-page-visit-autocomplete cf-cc-input-autocomplete"
				autocomplete="off" />
		</div>
		<p>
			<?php
			esc_html_e(
				'Press the ENTER key to add the URL to the URLs list.',
				'cf-conditional-content'
			);
			?>
		</p>
	</div>
</div>
