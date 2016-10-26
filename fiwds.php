<?php
/*
Plugin Name: Featured Images with Determined Sizes (FIWDS)
Plugin URI: http://jeanbaptisteaudras.com/
Description: Publishing require to have a featured image with determined size (you can configure custom width and height parameters for different custom post types).
Author: audrasjb
Version: 0.1
Author URI: http://jeanbaptisteaudras.com/
Text Domain: fiwds
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get plugin admin option page
require_once('admin/admin-options.php');

// Hook on transition post status when publishing attemps
add_action( 'transition_post_status', 'fiwds_look_for_transition_post_status', 10, 3 );
function fiwds_look_for_transition_post_status( $new_status, $old_status, $post ) {
    if ( $new_status === 'publish' && fiwds_preserve_from_publishing( $post ) ) {
        wp_die( fiwds_get_warning_message() );
    }
}

// Enqueue JS scripts to edit screen
add_action( 'admin_enqueue_scripts', 'fiwds_enqueue_edit_screen_js' );
function fiwds_enqueue_edit_screen_js( $hook ) {
	// Load admin-options JS for FIWDS
	wp_register_script( 'fiwds-admin-options-js', plugins_url( '/admin/js/fiwds-admin-options.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'fiwds-admin-options-js' );

    global $post;
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }

    if (fiwds_is_supported_post_type($post)) {
        // Load edit-post JS for FIWDS
        wp_register_script( 'fiwds-post-edit-js', plugins_url( '/admin/js/fiwds-post-edit.js', __FILE__ ), array( 'jquery' ) );
        wp_enqueue_script( 'fiwds-post-edit-js' );
		$current_post_type = get_post_type();
        $fiwds_options = get_option('fiwds_options');
		$min_width = $fiwds_options['fiwds_'.$current_post_type.'_minimal_width'];
		$min_height = $fiwds_options['fiwds_'.$current_post_type.'_minimal_height'];
		$max_width = $fiwds_options['fiwds_'.$current_post_type.'_maximal_width'];
		$max_height = $fiwds_options['fiwds_'.$current_post_type.'_maximal_height'];
		echo '<script>console.log('.$min_width.$min_height.$max_width.$max_height.');</script>';
        wp_localize_script(
            'fiwds-admin-js',
            'passedFromServer',
            array(
                'jsWarningHtml' => __( '<strong>This entry requires a featured image before publishing.</strong>', 'fiwds' ),
                'jsSmallHtml' => sprintf(
                    __( '<strong>The featured image is too small.</strong> Please use an image that is at least %s x %s pixels.', 'fiwds' ),
                    $min_width,
                    $min_height
                ),
                'jsTallHtml' => sprintf(
                    __( '<strong>The featured image is too big.</strong> Please use an image that is less than %s x %s pixels.', 'fiwds' ),
                    $max_width,
                    $max_height
                ),
                'min_width' => $min_width,
                'min_height' => $min_height,
                'max_width' => $max_width,
                'max_height' => $max_height,
            )
        );
    }
}

// Save the plugin's activation date on database
register_activation_hook( __FILE__, 'fiwds_remember_date_of_activation' );
function fiwds_remember_date_of_activation() {
    add_option( 'fiwds_activation_date', time() );
}

// i18n
add_action( 'plugins_loaded', 'fiwds_textdomain_init' );
function fiwds_textdomain_init() {
    load_plugin_textdomain(
        'fiwds',
        false,
        dirname( plugin_basename( __FILE__ ) ).'/languages'
    );
}

function fiwds_preserve_from_publishing( $post ) {
    $is_watched_post_type = fiwds_is_supported_post_type( $post );
    $fiwds_post_has_good_image_size = fiwds_post_has_good_image_size( $post );

    if ( $is_after_enforcement_time && $is_watched_post_type ) {
        return !$large_enough_image_attached;
    }
    return false;
}

// Check if current post type is supported by fiwds
function fiwds_is_supported_post_type( $post ) {
    $fiwds_options = get_option('fiwds_options', 'option_dont_exists');
    if ($fiwds_options != 'option_dont_exists') {
	    if (isset($fiwds_options['fiwds_'.get_post_type($post->ID).'_checkbox_img_required'])) {
		    $img_required = $fiwds_options['fiwds_'.get_post_type($post->ID).'_checkbox_img_required'];
//			echo '<script>console.log('.$img_required.');</script>';
			if ($img_required == 1) {
				return true;
			} else {
				return false;
			}
			return false;
	    } else {
			return false;		    
	    }
	} else {
		return false;
	}
}

// Check the dimensions of the post thumbnail
function fiwds_post_has_good_image_size( $post ) {
    $image_id = get_post_thumbnail_id( $post->ID );
    if ( $image_id === null ) {
        return false;
    }
    $image_meta = wp_get_attachment_image_src( $image_id, 'full' );
    $width = $image_meta[1];
    $height = $image_meta[2];
    $fiwds_options = get_option('fiwds_options');
    if (isset($fiwds_options['fiwds_'.get_post_type($post->ID).'_checkbox_img_required'])) {
	    $img_required = $fiwds_options('fiwds_'.get_post_type($post->ID).'_checkbox_img_required');
	}
    if (isset($fiwds_options['fiwds_'.get_post_type($post->ID).'_checkbox_size_required'])) {
	    $size_required = $fiwds_options('fiwds_'.get_post_type($post->ID).'_checkbox_size_required');
		$min_width = $fiwds_options['fiwds_'.get_post_type($post->ID).'_minimal_width'];
		$min_height = $fiwds_options['fiwds_'.get_post_type($post->ID).'_minimal_height'];
		$max_width = $fiwds_options['fiwds_'.get_post_type($post->ID).'_maximal_width'];
		$max_height = $fiwds_options['fiwds_'.get_post_type($post->ID).'_maximal_height'];
		if ( isset($min_width) && ($min_width > 0) && ($min_width != '') && $width > $min_width ) {
       		return true;
    	}
		if ( isset($min_height) && ($min_height > 0) && ($min_height != '') && $height > $min_height ) {
       		return true;
    	}
		if ( isset($max_width) && ($max_width > 0) && ($max_width != '') && $width < $max_width ) {
       		return true;
    	}
		if ( isset($max_height) && ($max_height > 0) && ($max_height != '') && $height < $max_height ) {
       		return true;
    	}
		return false;
	}
}

// Display warning messages
function fiwds_get_warning_message() {
	$current_post_type = get_post_type();
    $fiwds_options = get_option('fiwds_options');
    $img_required = $fiwds_options('fiwds_'.$current_post_type.'_checkbox_img_required');
    $img_required = $fiwds_options('fiwds_'.$current_post_type.'_checkbox_size_required');
	$min_width = $fiwds_options['fiwds_'.$current_post_type.'_minimal_width'];
	$min_height = $fiwds_options['fiwds_'.$current_post_type.'_minimal_height'];
	$max_width = $fiwds_options['fiwds_'.$current_post_type.'_maximal_width'];
	$max_height = $fiwds_options['fiwds_'.$current_post_type.'_maximal_height'];
    if ($img_required == 1) {
	    if ( $min_width == 0 && $min_height == 0 ) {
    	    return __( 'You cannot publish without a featured image.', 'fiwds' );
    	}
		return sprintf(
        	__( 'You cannot publish without a featured image that is at least %s x %s pixels.', 'fiwds' ),
			$min_width,
			$min_height
		);
	}
}