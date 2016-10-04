<?php
/* 
* Declare Option page for fiwds
*/
function fiwds_admin_add_page() {
	add_options_page( 
		'Featured images options', 
		'Featured images options', 
		'manage_options', 
		'fiwds-options', 
		'fiwds_options_page' 
	);
}
add_action( 'admin_menu', 'fiwds_admin_add_page' );

/*
* Displaying our options on a one-paged way at first
*/
function fiwds_options_page() {
?>
	<div class="wrap">

		<h2><?php _e( 'Featured Images with Determined Sizes', 'fiwds' ) ?></h2>
            
		<?php
		// Iterate to public posts types registered on the website, which are using native featured images
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $post_types as $type => $obj ) {
			if ( post_type_supports( $type, 'thumbnail' ) ) {
				echo '<h3 class="' . $obj->name . '-fiwds-options-h3">' . $obj->labels->name . '</h3>';
				// register settings for this post type
				fiwds_post_type_init_settings($obj);
			}
		}
		?>

	</div>
<?php
}

function fiwds_post_type_init_settings($obj){
	echo $obj->name;
}

function FOO_fiwds_post_type_init_settings(){
	// Create Settings
	$option_group = 'fiwds';
	$option_name = 'fiwds_post_types';
	register_setting( $option_group, $option_name );

	$minimum_size_option = 'fiwds_minimum_size';
	register_setting( $option_group, $minimum_size_option );

	// Create section of Page
	$settings_section = 'fiwds_main';
	$page = 'fiwds';
	add_settings_section( $settings_section, __( 'Post Types', 'fiwds' ), 'fiwds_main_section_text_output', $page );

	// Add fields to that section
	add_settings_field( $option_name, __('Post Types that require featured images ', 'fiwds' ), 'fiwds_post_types_input_renderer', $page, $settings_section );

	// Minimum Image requirements
	$size_section = 'fiwds_size';
	add_settings_section($size_section, __('Image Size', 'fiwds'), 'fiwds_size_text_output', $page);

	add_settings_field($minimum_size_option, __('Minimum size of the featured images', 'fiwds'), 'fiwds_size_option_renderer', $page, $size_section);
}

function fiwds_main_section_text_output() {
	_e( '<p>You can specify the post type for Require Featured Image to work on. By default it works on Posts only.</p><p>If you\'re not seeing a post type here that you think should be, it probably does not have support for featured images. Only post types that support featured images will appear on this list.</p>', 'fiwds' );
}

function fiwds_size_text_output(){
	_e('<p>The minimum acceptable size can be set for featured images. This size means that posts with images smaller than the specified dimensions cannot be published. By default the sizes are zero, so any image size will be accepted.</p>','fiwds');
}

function fiwds_return_post_types_which_support_featured_images() {
	$post_types = get_post_types( array( 'public' => true ), 'objects' );
	foreach ( $post_types as $type => $obj ) {
		if ( post_type_supports( $type, 'thumbnail' ) ) {
			$return[$type] = $obj;
		}
	}
	return $return;
}

function fiwds_return_min_dimensions() {
	$minimum_size = get_option('fiwds_minimum_size');
	if (isset($minimum_size['width']) && $minimum_size['width'] == 0) {
		$minimum_size['width'] = 0;
	}
	if (isset($minimum_size['height']) && $minimum_size['height'] == 0) {
		$minimum_size['height'] = 0;
	}
	return $minimum_size;
}

function fiwds_post_types_input_renderer() {
	$option = fiwds_return_post_types();
	$post_types = fiwds_return_post_types_which_support_featured_images();

	foreach ( $post_types as $type => $obj ) {
		if ( in_array( $type, $option ) ) {
			echo '<input type="checkbox" name="fiwds_post_types[]" value="'.$type.'" checked="checked">'.$obj->label.'<br>';
		} else {
			echo '<input type="checkbox" name="fiwds_post_types[]" value="'.$type.'">'.$obj->label.'<br>';
		}
	}
}

function fiwds_size_option_renderer(){
	$dimensions = fiwds_return_min_dimensions();
	echo '<input type="number" name="fiwds_minimum_size[width]", value="'.$dimensions["width"].'"> width (px) <br>';
	echo '<input type="number" name="fiwds_minimum_size[height]", value="'.$dimensions["height"].'"> height (px)<br>';
}
