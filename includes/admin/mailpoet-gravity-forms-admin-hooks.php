<?php
/**
 * MailPoet Gravity Forms Add-on Admin Hooks
 *
 * @author 		Sebs Studio
 * @category 	Admin
 * @package 	MailPoet Gravity Forms Add-on/Hooks
 * @version 	2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Support logging
add_filter('gform_logging_supported', 'set_logging_supported');

// Security check. If MailPoet is not active then none of these hooks are activated.
if( !is_plugin_active( 'wysija-newsletters/index.php' ) )
	return false;

// Filters
add_filter('gform_add_field_buttons', 'mailpoet_add_field_button'); // Creates a field button
add_filter('gform_field_type_title', 'mailpoet_assign_title', 10, 2); // Gives the new field type a Title
add_filter('gform_tooltips', 'add_mailpoet_tooltips'); // Provides help tooltips for each new field option

// Actions
add_action('gform_editor_js', 'mailpoet_gform_editor_js'); // Applies JavaScript to the form
add_action('gform_field_standard_settings', 'mailpoet_settings', 10, 2); // Assigns the new field under standard settings
add_action('gform_field_css_class', 'mailpoet_gform_field_css_class', 10, 3); // Creates a css class for detection

?>