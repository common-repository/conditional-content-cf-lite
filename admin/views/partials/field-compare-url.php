<?php

/**
 * Compare URL field partial.
 *
 * @package cf-conditional content
 */

$referrer_show_selection = !empty($rule_compare) && 'referrer' === $condition_type;
$url_show_selection = !empty($rule_compare) && 'url' === $condition_type;

?>

<input type="text" name="compare_url" data-field="url-custom"
	placeholder="<?php esc_attr_e('Name your query string', 'cf-conditional-content'); ?>"
	class="form-control url-custom <?php echo $url_show_selection ? 'show-selection' : ''; ?>"
	<?php echo $url_show_selection ? 'value="' . esc_attr($rule_compare) . '"' : ''; ?> />

<div class="instructions" data-field="url-custom">
	<p>
		<?php
		esc_html_e(
			'Add the following string to the end of your page URL to display the content:',
			'cf-conditional-content'
		);
		?>
	</p>

    <?php // phpcs:ignore Generic.Files.LineLength.TooLong ?>
	<pre class="cf-cc-dynamic-link-code"><code>?v=<b><?php echo $url_show_selection ? esc_html($rule_compare) : 'your-query-string'; ?></b></code></pre>

	<p class="query-example">
		<?php
		echo esc_html(
			sprintf(
				// Translators: %s - query string value.
				__('I.e., www.url.com?v=%s', 'cf-conditional-content'),
				$url_show_selection ? $rule_compare : 'your-query-string'
			)
		);
		?>
	</p>
</div>

<div class="custom-url-display instructions"
	<?php echo empty($rule_compare) && 'url' === $condition_type ? 'style="display:block;"' : ''; ?>
	data-field="custom-url-display">
	<p>
		<?php
		esc_html_e(
			'Add the following parameter at the end of the page URL',
			'cf-conditional-content'
		);
		?>:
	</p>

	<pre class="query-string-code">
		<code>?v=<b><?php echo empty($rule_compare) ? esc_html($rule_compare) : ''; ?></b></code>
	</pre>

	<p class="query-example">
		<?php esc_html_e('I.e., www.url.com?v=your-query-string', 'cf-conditional-content'); ?>
	</p>
</div>
