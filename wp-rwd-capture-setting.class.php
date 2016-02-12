<?php

/* wp-rwd-capture setting page */
class CaptureSettingsPage
{
	/** configuration options */
	private $options;

	/**
	 * init
	 */
	public function __construct()
	{
		// add admin page
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		// start page
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * add page to the menu
	 */
	public function add_plugin_page()
	{
		add_menu_page( 'Capture Settings', 'Capture Settings', 'manage_options', 'capture_setting', array( $this, 'create_admin_page' ) );
	}

	/**
	 * Initialize admin page
	 */
	public function page_init()
	{
		register_setting( 'capture_setting', 'capture_setting', array( $this, 'sanitize' ) );

		add_settings_section( 'capture_setting_section_id', '', '', 'capture_setting' );

		add_settings_field( 'apikey', 'API key', array( $this, 'apikey_callback' ), 'capture_setting', 'capture_setting_section_id' );
		add_settings_field( 'endpoint', 'API Endpoint', array( $this, 'endpoint_callback' ), 'capture_setting', 'capture_setting_section_id' );
		add_settings_field( 'template', 'UA Templates Endpoint', array( $this, 'template_callback' ), 'capture_setting', 'capture_setting_section_id' );

	}

	/**
	 * Output html
	 */
	public function create_admin_page()
	{
		$this->options = get_option( 'capture_setting' );
		?>
		<div class="wrap">
			<h2>WP-RWD-Capture Configurations</h2>
	 <p>
		You need to create an account for <a href="https://screenshot-web.com/">screenshot-web.com</a> first.<br/>
		After you create an accout, go to settings page and copy your APIKEY.
	 </p>
			<?php
			global $parent_file;
			if ( $parent_file != 'options-general.php' ) {
				require(ABSPATH . 'wp-admin/options-head.php');
			}
			?>
			<form method="post" action="options.php">
			<?php
				settings_fields( 'capture_setting' );
				do_settings_sections( 'capture_setting' );
				submit_button();
			?>
			</form>
	 <h3>Template List</h3>
	 <p>shortcode example</p>
	 <blockquote style="background-color:#f8f8f8; padding:20px">
		#Default(Google Chrome)<br> [ssweb]http://example.com[/ssweb]<br><br>
	 	#Full Page<br> [ssweb height=0]http://example.com[/ssweb]<br><br>
	 	#Set iPhone6 as UA<br> [ssweb <font color="red">template_id=3</font>]http://example.com[/ssweb]<br><br>
	 	#Specify selector<br> [ssweb selector="#wsod_worldMarkets"]http://money.cnn.com/data/world_markets/americas/[/ssweb]<br><br>
	 	#Recapture page with the same configurations (count up {ver} attribute)<br> [ssweb ver=2]http://example.com[/ssweb]<br><br>
	 </blockquote>
	 <p><?php include_once("templates.getua.inc.html");?></p>
	 <p class="form-control" id="templates" name="templates"></p>
		</div>
		<?php
	}

	/**
	 * output apikey html
	 */
	public function apikey_callback()
	{
		$apikey = isset( $this->options['apikey'] ) ? $this->options['apikey'] : '';
		?><input type="text" id="apikey" size="50" name="capture_setting[apikey]" value="<?php esc_attr_e( $apikey ) ?>" /><?php
	}

	/**
	 * output endpoint html
	 */
	public function endpoint_callback()
	{
		$ep = isset( $this->options['endpoint'] ) ? $this->options['endpoint'] : 'https://screenshot-web.com/api/capture/';
		?><input type="text" id="endpoint" size="50" name="capture_setting[endpoint]" value="<?php esc_attr_e( $ep ) ?>" /><?php
	}

	/**
	 * output UA template list html
	 */
	public function template_callback()
	{
		$tpl = isset( $this->options['template'] ) ? $this->options['template'] : 'https://screenshot-web.com/templates/';
		?><input type="text" id="template" size="50" name="capture_setting[template]" value="<?php esc_attr_e( $tpl ) ?>" /><?php
	}
 
	/**
	 * sanitize input values
	 *
	 * @param array $input values
	 */
	public function sanitize( $input )
	{
		$this->options = get_option( 'capture_setting' );

		$new_input = array();

		if( isset( $input['apikey'] ) && trim( $input['apikey'] ) !== '' ) {
			$new_input['apikey'] = sanitize_text_field( $input['apikey'] );
		}
		else {
			add_settings_error( 'capture_setting', 'apikey', 'Please input API KEY' );
			$new_input['apikey'] = isset( $this->options['apikey'] ) ? $this->options['apikey'] : '';
		}

		if( isset( $input['endpoint'] ) && trim( $input['endpoint'] ) !== '' ) {
			$new_input['endpoint'] = sanitize_text_field( $input['endpoint'] );
		}
		else {
			$new_input['endpoint'] = isset( $this->options['endpoint'] ) ? $this->options['endpoint'] : 'https://screenshot-web.com/api/capture/';
		}
		if( isset( $input['template'] ) && trim( $input['template'] ) !== '' ) {
			$new_input['template'] = sanitize_text_field( $input['template'] );
		}
		else {

			$new_input['template'] = isset( $this->options['template'] ) ? $this->options['template'] : 'https://screenshot-web.com/templates/';
		}

		return $new_input;
	}

}
