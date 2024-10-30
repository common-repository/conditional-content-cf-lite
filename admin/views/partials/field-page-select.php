<?php

/**
 * Page select field partial.
 *
 * @package cf-conditional content
 */

?>
<select name="page"
	class="form-control referrer-custom <?php echo !empty($rule_page) ? 'show-selection' : ''; ?>">
	<option value="">
		<?php esc_html_e('Select page', 'cf-conditional-content'); ?>
	</option>
	<?php if (!empty($available_pages)) : ?>
		<?php foreach ($available_pages as $available_page) : ?>
			<option value="<?php echo (int)$available_page->ID; ?>"
				<?php selected($rule_page, $available_page->ID);?>>
				<?php echo esc_html($available_page->post_title); ?>
			</option>
		<?php endforeach; ?>
	<?php endif; ?>
</select>
