<?php
/*
Plugin Name: Featured Images with Determined Sizes (FIWDS)
Plugin URI: http://jeanbaptisteaudras.com/
Description: Publishing require to have a featured image with determined size (you can configure custom width and height parameters for different custom post types).
Author: audrasjb
Version: 1.2.2
Author URI: http://jeanbaptisteaudras.com/
Text Domain: fiwds
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get plugin admin option page
require_once('admin/admin-options.php');

add_action( 'transition_post_status', 'fiwds_guard', 10, 3 );
function fiwds_guard( $new_status, $old_status, $post ) {
    if ( $new_status === 'publish' && fiwds_should_stop_post_publishing( $post ) ) {
        wp_die( fiwds_get_warning_message() );
    }
}

add_action( 'admin_enqueue_scripts', 'fiwds_enqueue_edit_screen_js' );
function fiwds_enqueue_edit_screen_js( $hook ) {
    global $post;
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }

    if ( fiwds_is_supported_post_type( $post ) && fiwds_is_in_enforcement_window( $post ) ) {
        wp_register_script( 'fiwds-admin-js', plugins_url( '/admin/js/fiwds-admin.js', __FILE__ ), array( 'jquery' ) );
        wp_enqueue_script( 'fiwds-admin-js' );

        $minimum_size = get_option( 'fiwds_minimum_size' );
        wp_localize_script(
            'fiwds-admin-js',
            'passedFromServer',
            array(
                'jsWarningHtml' => __( '<strong>This entry has no featured image.</strong> Please set one. You need to set a featured image before publishing.', 'fiwds' ),
                'jsSmallHtml' => sprintf(
                    __( '<strong>This entry has a featured image that is too small.</strong> Please use an image that is at least %s x %s pixels.', 'fiwds' ),
                    $minimum_size['width'],
                    $minimum_size['height']
                ),
                'width' => $minimum_size['width'],
                'height' => $minimum_size['height'],
            )
        );
    }
}

register_activation_hook( __FILE__, 'fiwds_set_default_on_activation' );
function fiwds_set_default_on_activation() {
    add_option( 'fiwds_post_types', array('post') );
    add_option( 'fiwds_enforcement_start', time() );
}

add_action( 'plugins_loaded', 'fiwds_textdomain_init' );
function fiwds_textdomain_init() {
    load_plugin_textdomain(
        'fiwds',
        false,
        dirname( plugin_basename( __FILE__ ) ).'/lang'
    );
}

/**
 * These are helpers that aren't ever registered with events
 */

function fiwds_should_stop_post_publishing( $post ) {
    $is_watched_post_type = fiwds_is_supported_post_type( $post );
    $is_after_enforcement_time = fiwds_is_in_enforcement_window( $post );
    $large_enough_image_attached = fiwds_post_has_large_enough_image_attached( $post );

    if ( $is_after_enforcement_time && $is_watched_post_type ) {
        return !$large_enough_image_attached;
    }
    return false;
}

function fiwds_is_supported_post_type( $post ) {
    return in_array( $post->post_type, fiwds_return_post_types() );
}

function fiwds_return_post_types() {
    $option = get_option( 'fiwds_post_types', 'default' );
    if ( $option === 'default' ) {
        $option = array( 'post' );
        add_option( 'fiwds_post_types', $option );
    } elseif ( $option === '' ) {
        // For people who want the plugin on, but doing nothing
        $option = array();
    }
    return apply_filters( 'fiwds_post_types', $option );
}

function fiwds_is_in_enforcement_window( $post ) {
    return strtotime($post->post_date) > fiwds_enforcement_start_time();
}

function fiwds_enforcement_start_time() {
    $option = get_option( 'fiwds_enforcement_start', 'default' );
    if ( $option === 'default' ) {
        // added in 1.1.0, activation times for installations before
        //  that release are set to two weeks prior to the first call
        $existing_install_guessed_time = time() - ( 86400*14 );
        add_option( 'fiwds_enforcement_start', $existing_install_guessed_time );
        $option = $existing_install_guessed_time;
    }
    return apply_filters( 'fiwds_enforcement_start', (int)$option );
}

function fiwds_post_has_large_enough_image_attached( $post ) {
    $image_id = get_post_thumbnail_id( $post->ID );
    if ( $image_id === null ) {
        return false;
    }
    $image_meta = wp_get_attachment_image_src( $image_id, 'full' );
    $width = $image_meta[1];
    $height = $image_meta[2];
    $minimum_size = get_option( 'fiwds_minimum_size' );

    if ( $width >= $minimum_size['width'] && $height >=  $minimum_size['height'] ){
        return true;
    }
    return false;
}

function fiwds_get_warning_message() {
    $minimum_size = get_option('fiwds_minimum_size');
    // Legacy case
    if ( $minimum_size['width'] == 0 && $minimum_size['height'] == 0 ) {
        return __( 'You cannot publish without a featured image.', 'fiwds' );
    }
    return sprintf(
        __( 'You cannot publish without a featured image that is at least %s x %s pixels.', 'fiwds' ),
        $minimum_size['width'],
        $minimum_size['height']
    );
}