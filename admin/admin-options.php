<?php
/* 
* Declare Option page for fiwds
*/
class FiwdsSettingsPage {
	
	// Values can be used in the fied's callbacks
	private $options;
	
	// Constructor
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'fiwds_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'fiwds_page_init' ) );
	}

	
	// This option page will appear under "Settings"
	public function fiwds_add_plugin_page() {
		add_options_page( 
			__('Featured images options', 'fiwds'), 
			__('Featured images options', 'fiwds'), 
			'manage_options', 
			'fiwds-setting-admin', 
			array( $this, 'fiwds_create_admin_page' ) 
		);
    }
    
    // Option page callback
    public function fiwds_create_admin_page() {
	    // Set class properties
	    $this->options = get_option( 'fiwds_options' );
		// check if the user have submitted the settings
		if (isset($_POST['settings-updated'])) {
			add_settings_error('fiwds_messages', 'fiwds_message', __('Settings Saved', 'fiwds'), 'updated');
		}
		?>
		<div class="wrap">
			<h2><?php echo __( 'Featured Images with Determined Sizes', 'fiwds' ) ?></h2>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'fiwds_option_group' );
				do_settings_sections( 'fiwds-setting-admin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
    }
    
    // Register and add settings
    public function fiwds_page_init() {
		// Registering global setting options
		register_setting(
			'fiwds_option_group', // Option group
			'fiwds_options', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);
		// Iterate through public posts types registered on the website, which are using native featured images. Then, display forms for each of them.
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $post_types as $type => $obj ) {
			if ( post_type_supports( $type, 'thumbnail' ) ) {
				// Adding a section
				add_settings_section(
					'fiwds_' . $obj->name . '_settings_section', // ID
					'Edit settings for ' . $obj->labels->name, // Title
					array( $this, 'print_section_info' ), // Callback
					'fiwds-setting-admin' // Page
				);
				// Adding checkbox required featured image option
				add_settings_field(
					'fiwds_' . $obj->name . '_checkbox_img_required', // ID
					'Require featured image', // Title
					array( $this, 'fiwds_checkbox_img_required_callback' ), // Callback
					'fiwds-setting-admin', // Page
					'fiwds_' . $obj->name . '_settings_section', // Section
					array('fiwds_post_type' => $obj->name)
				);      
				// Adding dimensionned size option
				add_settings_field(
					'fiwds_' . $obj->name . '_checkbox_size_required', // ID
					'Set determined sizes', // Title
					array( $this, 'fiwds_checkbox_size_required_callback' ), // Callback
					'fiwds-setting-admin', // Page
					'fiwds_' . $obj->name . '_settings_section', // Section
					array('fiwds_post_type' => $obj->name)
				);      
				// Adding minimal width option
				add_settings_field(
					'fiwds_' . $obj->name . '_minimal_width',
					'Set minimal width',
					array( $this, 'fiwds_minimal_width_callback' ),
					'fiwds-setting-admin',
					'fiwds_' . $obj->name . '_settings_section',
					array('fiwds_post_type' => $obj->name)
				);
				// Adding maximal width option
				add_settings_field(
					'fiwds_' . $obj->name . '_maximal_width',
					'Set maximal width',
					array( $this, 'fiwds_maximal_width_callback' ),
					'fiwds-setting-admin',
					'fiwds_' . $obj->name . '_settings_section',
					array('fiwds_post_type' => $obj->name)
				);
				// Adding minimal height option
				add_settings_field(
					'fiwds_' . $obj->name . '_minimal_height',
					'Set minimal height',
					array( $this, 'fiwds_minimal_height_callback' ),
					'fiwds-setting-admin',
					'fiwds_' . $obj->name . '_settings_section',
					array('fiwds_post_type' => $obj->name)
				);
				// Adding maximal height option
				add_settings_field(
					'fiwds_' . $obj->name . '_maximal_height',
					'Set maximal height',
					array( $this, 'fiwds_maximal_height_callback' ),
					'fiwds-setting-admin',
					'fiwds_' . $obj->name . '_settings_section',
					array('fiwds_post_type' => $obj->name)
				);
			}
		}
	}
	
	// Fields sanitization – @param array $input Contains all settings fields as array keys
	public function sanitize( $input ) {
		$new_input = array();
		// Iterate through public posts types registered on the website, which are using native featured images. Then, display forms for each of them.
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $post_types as $type => $obj ) {
			if ( post_type_supports( $type, 'thumbnail' ) ) {
				if( isset( $input['fiwds_' . $obj->name . '_checkbox_img_required'] ) ) {
					$new_input['fiwds_' . $obj->name . '_checkbox_img_required'] = absint( $input['fiwds_' . $obj->name . '_checkbox_img_required'] );
				}
				if( isset( $input['fiwds_' . $obj->name . '_checkbox_size_required'] ) ) {
					$new_input['fiwds_' . $obj->name . '_checkbox_size_required'] = absint( $input['fiwds_' . $obj->name . '_checkbox_size_required'] );
				}
				if( isset( $input['fiwds_' . $obj->name . '_minimal_width'] ) ) {
					$new_input['fiwds_' . $obj->name . '_minimal_width'] = absint( $input['fiwds_' . $obj->name . '_minimal_width'] );
				}
				if( isset( $input['fiwds_' . $obj->name . '_maximal_width'] ) ) {
					$new_input['fiwds_' . $obj->name . '_maximal_width'] = absint( $input['fiwds_' . $obj->name . '_maximal_width'] );
				}
				if( isset( $input['fiwds_' . $obj->name . '_minimal_height'] ) ) {
					$new_input['fiwds_' . $obj->name . '_minimal_height'] = absint( $input['fiwds_' . $obj->name . '_minimal_height'] );
				}
				if( isset( $input['fiwds_' . $obj->name . '_maximal_height'] ) ) {
					$new_input['fiwds_' . $obj->name . '_maximal_height'] = absint( $input['fiwds_' . $obj->name . '_maximal_height'] );
				}
			}
		}
		return $new_input;
	}
	
	// Prints ce section text
	public function print_section_info() {
		// Nothing to display, lol
	}
	
	// Get the settings option array and print one of its values
	public function fiwds_checkbox_img_required_callback($args) {
		echo '
			<input 
			type="checkbox" 
			id="fiwds_'.$args['fiwds_post_type'].'_checkbox_img_required" 
			name="fiwds_options[fiwds_'.$args['fiwds_post_type'].'_checkbox_img_required]" 
			value="1"'. 
			checked(isset($this->options['fiwds_'.$args['fiwds_post_type'].'_checkbox_img_required']), true, false) .
			' />';
	}

	// Get the settings option array and print one of its values
	public function fiwds_checkbox_size_required_callback($args) {
		echo '
			<input 
			type="checkbox" 
			id="fiwds_'.$args['fiwds_post_type'].'_checkbox_size_required" 
			name="fiwds_options[fiwds_'.$args['fiwds_post_type'].'_checkbox_size_required]" 
			value="1"'. checked(isset($this->options['fiwds_'.$args['fiwds_post_type'].'_checkbox_size_required']), true, false) .' 
			/>';
	}
	
	// Get the settings option array and print one of its values
	public function fiwds_minimal_width_callback($args) {
		printf(
			'<input type="text" id="fiwds_'.$args['fiwds_post_type'].'_minimal_width" name="fiwds_options[fiwds_'.$args['fiwds_post_type'].'_minimal_width]" value="%s" />',
			isset( $this->options['fiwds_'.$args['fiwds_post_type'].'_minimal_width'] ) ? esc_attr( $this->options['fiwds_'.$args['fiwds_post_type'].'_minimal_width']) : ''
		);
	}

	// Get the settings option array and print one of its values
	public function fiwds_maximal_width_callback($args) {
		printf(
			'<input type="text" id="fiwds_'.$args['fiwds_post_type'].'_maximal_width" name="fiwds_options[fiwds_'.$args['fiwds_post_type'].'_maximal_width]" value="%s" />',
			isset( $this->options['fiwds_'.$args['fiwds_post_type'].'_maximal_width'] ) ? esc_attr( $this->options['fiwds_'.$args['fiwds_post_type'].'_maximal_width']) : ''
		);
	}

	// Get the settings option array and print one of its values
	public function fiwds_minimal_height_callback($args) {
		printf(
			'<input type="text" id="fiwds_'.$args['fiwds_post_type'].'_minimal_height" name="fiwds_options[fiwds_'.$args['fiwds_post_type'].'_minimal_height]" value="%s" />',
			isset( $this->options['fiwds_'.$args['fiwds_post_type'].'_minimal_height'] ) ? esc_attr( $this->options['fiwds_'.$args['fiwds_post_type'].'_minimal_height']) : ''
		);
	}

	// Get the settings option array and print one of its values
	public function fiwds_maximal_height_callback($args) {
		printf(
			'<input type="text" id="fiwds_'.$args['fiwds_post_type'].'_maximal_height" name="fiwds_options[fiwds_'.$args['fiwds_post_type'].'_maximal_height]" value="%s" />',
			isset( $this->options['fiwds_'.$args['fiwds_post_type'].'_maximal_height'] ) ? esc_attr( $this->options['fiwds_'.$args['fiwds_post_type'].'_maximal_height']) : ''
		);
	}
	
}
/*
* End class FiwdsSettingsPage
*/

// And naw, build the page if we are connected as admin
if( is_admin() ) {
	$fiwds_settings_page = new FiwdsSettingsPage();
}