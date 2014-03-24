<?php
/**
 * MailPoet Gravity Forms Add-on Admin.
 *
 * @author 		Sebs Studio
 * @category 	Admin
 * @package 	MailPoet Gravity Forms Add-on/Admin
 * @version 	2.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MailPoet_Gravity_Forms_Add_on_Admin' ) ) {

class MailPoet_Gravity_Forms_Add_on_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Actions
		add_action( 'init', array( &$this, 'includes' ) );
		add_action( 'current_screen', array( &$this, 'conditonal_includes' ) );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		// Functions
		include( 'mailpoet-gravity-forms-admin-functions.php' );

		// Classes we only need if the ajax is not-ajax
		if ( ! is_ajax() ) {
			// Help
			if ( apply_filters( 'mailpoet_gravity_forms_enable_admin_help_tab', true ) ) {
				include( 'class-mailpoet-gravity-forms-admin-help.php' );
			}
		}
	}

	/**
	 * Include admin files conditionally
	 */
	public function conditonal_includes() {

		// Creates a new Settings page on Gravity Forms settings screen
		if( !is_plugin_active( 'wysija-newsletters/index.php' ) ) 
			return false;

		if( RGForms::get("page") == "gf_settings") {
			include('views/view-html-settings.php');
			RGForms::add_settings_page( MAILPOET_GF_PAGE, 'settings_page' );
		}

	}

}

} // end if class exists

return new MailPoet_Gravity_Forms_Add_on_Admin();

?>