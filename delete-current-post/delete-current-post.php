<?php
/**
 * Plugin Name: Delete Current Post (Confirm Only)
 */

if (!defined('ABSPATH')) exit;

add_shortcode('delete_current_post', function () {
    if (!is_singular() || !is_user_logged_in()) return '';

    global $post;
    if (!$post) return '';

    $user_id = get_current_user_id();

    // Author OR admin
    if (
        (int) $post->post_author !== $user_id &&
        !current_user_can('delete_others_posts')
    ) {
        return '';
    }

    // Auto redirect → same post type archive
    $post_type = get_post_type($post);
    $archive = get_post_type_archive_link($post_type) ?: home_url();

    wp_enqueue_script(
        'dcp-js',
        plugin_dir_url(__FILE__) . 'delete.js',
        ['jquery'],
        null,
        true
    );

    wp_localize_script('dcp-js', 'DCP', [
        'ajax'     => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('dcp_delete'),
        'post_id'  => $post->ID,
        'redirect' => esc_url($archive)
    ]);

    return '
    <button id="dcp-confirm-delete"
        class="px-4 py-2 text-sm font-medium
               bg-red-600 text-white rounded-md
               hover:bg-red-700 transition
               disabled:opacity-50">
        Confirm Delete
    </button>';
});




// AJAX handler for deleting the post
add_action('wp_ajax_dcp_delete_post', function () {
    check_ajax_referer('dcp_delete', 'nonce');

    $post_id = (int) $_POST['post_id'];
    $post = get_post($post_id);

    if (!$post) {
        wp_send_json_error('Invalid post');
    }

    $user_id = get_current_user_id();

    if (
        (int) $post->post_author !== $user_id &&
        !current_user_can('delete_others_posts')
    ) {
        wp_send_json_error('Unauthorized');
    }

    // Soft delete → move to trash
    wp_trash_post($post_id);

    wp_send_json_success();
});
