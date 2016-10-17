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
		register_setting(
			'fiwds_option_group', // Option group
			'fiwds_options', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);
		add_settings_section(
			'fiwds_settings_section', // ID
			'My fiwds Settings', // Title
			array( $this, 'print_section_info' ), // Callback
			'fiwds-setting-admin' // Page
		);
		add_settings_field(
			'fiwds_checkbox_size_required', // ID
			'Check it to set minimum size for posts', // Title
			array( $this, 'fiwds_checkbox_size_required_callback' ), // Callback
			'fiwds-setting-admin', // Page
			'fiwds_settings_section' // Section
		);      
		add_settings_field(
			'fiwds_minimal_width',
			'Set minimal width',
			array( $this, 'fiwds_minimal_width_callback' ),
			'fiwds-setting-admin',
			'fiwds_settings_section'
		);
	}
	
	// Fields sanitization – @param array $input Contains all settings fields as array keys
	public function sanitize( $input ) {
		$new_input = array();
		if( isset( $input['fiwds_checkbox_size_required'] ) ) {
			$new_input['fiwds_checkbox_size_required'] = absint( $input['fiwds_checkbox_size_required'] );
		}
		if( isset( $input['fiwds_minimal_width'] ) ) {
			$new_input['fiwds_minimal_width'] = absint( $input['fiwds_minimal_width'] );
		}
		return $new_input;
	}
	
	// Prints ce section text
	public function print_section_info() {
		echo 'Enter your fiwds settings for POSTS below:';
	}
	
	// Get the settings option array and print one of its values
	public function fiwds_checkbox_size_required_callback() {
		echo '<input type="checkbox" id="fiwds_checkbox_size_required" name="fiwds_options[fiwds_checkbox_size_required]" value="1"'. checked(isset($this->options['fiwds_checkbox_size_required']), true, false) .' />';
	}
	
	// Get the settings option array and print one of its values
	public function fiwds_minimal_width_callback() {
		printf(
			'<input type="text" id="title" name="fiwds_options[fiwds_minimal_width]" value="%s" />',
			isset( $this->options['fiwds_minimal_width'] ) ? esc_attr( $this->options['fiwds_minimal_width']) : ''
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