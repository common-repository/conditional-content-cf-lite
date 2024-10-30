<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @package cf-conditional-content
 */

namespace CrowdFavorite\ConditionalContent;

// phpcs:disable
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Method to remove plugin data.
 *
 * @return void
 */
function cf_cc_delete_plugin()
{
    $posts = get_posts(
        [
            'numberposts' => -1,
            'post_type'   => 'cf_cc_condition',
            'post_status' => 'any',
        ]
    );

    foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
        delete_post_meta($post->ID, 'cf_cc_trigger_default');
        delete_post_meta($post->ID, 'cf_cc_conditions');
    }
}

if (get_option('cf_cc_settings_remove_data_on_uninstall')) {
    cf_cc_delete_plugin();
}
// phpcs:enable
