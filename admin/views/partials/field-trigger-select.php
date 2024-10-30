<?php

/**
* Trigger select field partial.
*
* @package cf-conditional content
*/

?>
<p>
	<?php esc_html_e('Select the referral source in which you would like the rule to apply, additional options are available based on the selected user behavior.', 'cf-conditional-content'); // phpcs:ignore?>
</p>

<select name="trigger"
class="form-control second-level-selection <?php
echo ( ! empty($trigger) && 'referrer' === $condition_type) ? 'show-selection' : ''; ?>">

<option value="" data-show-fields="referrer-selection">
	<?php esc_html_e('Choose an option', 'cf-conditional-content'); ?>
</option>

<option value="custom" <?php selected($trigger, 'custom'); ?>
	data-show-fields="referrer-selection|referrer-custom|locked-box">
	<?php esc_html_e('URL', 'cf-conditional-content'); ?>
</option>
<option value="page-on-website" <?php selected($trigger, 'page-on-website'); ?>
	data-show-fields="referrer-selection|page-selection|locked-box">
	<?php esc_html_e('Page on your website', 'cf-conditional-content'); ?>
</option>
</select>
