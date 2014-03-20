<?php
/**
 * MailPoet Gravity Forms Add-on Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author 		Sebs Studio
 * @category 	Core
 * @package 	MailPoet Gravity Forms Add-on/Core Functions
 * @version 	2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Include core functions
include( 'mailpoet-gravity-forms-conditional-functions.php' );

/** 
 * Shared Function - MailPoet field
 * 
 * Adds the input area to the front and admin form editor.
 */
add_action('gform_field_input', 'mailpoet_gform_field_input', 10, 5); // This hook is placed here to load for both front and admin.
function mailpoet_gform_field_input($input, $field, $value, $lead_id, $form_id){
	if($field["type"] == "mailpoet"){
		$field_id       = $field['id'];
		$input_id       = 'mailpoet-'.$field['id'];
		$input_name     = $form_id.'_'.$field['id'];
		$tabindex       = GFCommon::get_tabindex();
		$css            = isset($field['cssClass']) ? $field['cssClass'] : '';
		$is_multiselect = isset($field['mailpoet_multiselect']) ? $field['mailpoet_multiselect'] : '';
		$checkbox_label = isset($field['mailpoet_checkbox_label']) ? $field['mailpoet_checkbox_label'] : __('Yes, please subscribe me to your newsletter.', 'mailpoet-gravityforms-addon');

		$mailpoet_lists = mailpoet_lists(); // Fetch all enabled lists created.

		$html = "<div class='ginput_container'>";

		$html .= "<ul class='gfield_checkbox' id='input_".$field_id."'>";

		// Display single checkbox if list selection not enabled.
		if($is_multiselect == 'no'){
			$li_class 		= 'gchoice_'.$field_id;
			$input_id 		= 'input_subscribe_me_mailpoet_lists';
			$input_name 	= $input_id;
			$input_value 	= '1';
			if(empty($checkbox_label)){
				$list_name 	= __('Yes, please subscribe me to your newsletter.', 'mailpoet-gravityforms-addon');
			}
			else{
				$list_name 	= $checkbox_label;
			}

			$html .= "<li class='".$li_class."'><input id='".$input_id."' class='gform_mailpoet ".esc_attr($css)."' type='checkbox' name='".$input_name."' value='".$input_value."' ".$tabindex." /><label for='".$input_id."'>".$list_name."</label></li>";
		}
		else{
			// If multi selection of list is enabled, check that each list has been selected and only display those.
			foreach($mailpoet_lists as $list){
				$list_id   = $list['list_id'];
				$list_name = $list['name'];

				$input_name  = "mailpoet_gf_subscribe_list_".$list_id;
				$input_value = $list_id;
				$li_class    = 'gchoice_'.$field_id.'_'.$list_id;

				// If the list was selected then display that list.
				if( isset( $field[$input_name] ) && $field[$input_name] == true && substr($input_name, -1, 1) == $list_id ) {
					$html .= "<li class='".$li_class."'><input id='".$input_name."' class='gform_mailpoet ".esc_attr($css)."' type='checkbox' name='".$input_name."' value='".$input_value."' ".$tabindex." /><label for='".$input_name."'>".$list_name."</label></li>";
				}
			}
		}

		$html .= "</ul>";
		$html .= "</div>";
		$html = str_replace("\n", '', $html);

		return $html;
	}

	return $input;
}

/**
 * Finds the form field by given field id.
 */
function find_mailpoet_field_ID($id, $form){
	foreach($form['fields'] as $field){
		if($field['id'] == $id) return $field;
	}
	return false;
}

/**
 * Finds the form field by given field type.
 */
function find_mailpoet_field_type($type, $form){
	foreach($form['fields'] as $field){
		if($field['type'] == $type) return $field;
	}
	return false;
}

/**
 * Gets all enabled lists in MailPoet
 */
function mailpoet_lists(){
	// This will return an array of results with the name and list_id of each mailing list
	$model_list = WYSIJA::get('list','model');
	$mailpoet_lists = $model_list->get(array('name','list_id'), array('is_enabled' => 1));

	return $mailpoet_lists;
}

/**
 * Logs all activity.
 */
function set_logging_supported($plugins) {
	$plugins[MAILPOET_GF_SLUG] = "MailPoet Gravity Forms Add-on";
	return $plugins;
}

/**
 * Logs all errors.
 */
function log_error($message) {
	if( class_exists('GFLogging') ) {
		GFLogging::include_logger();
		GFLogging::log_message(MAILPOET_GF_SLUG, $message, KLogger::ERROR);
	}
}

/**
 * Displays all logged debug messages.
 */
function log_debug($message){
	if( class_exists('GFLogging') ) {
		GFLogging::include_logger();
		GFLogging::log_message(MAILPOET_GF_SLUG, $message, KLogger::DEBUG);
	}
}

?>