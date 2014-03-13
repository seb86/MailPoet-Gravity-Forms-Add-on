<?php
/**
 * MailPoet Gravity Forms Add-on Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author 		Sebs Studio
 * @category 	Core
 * @package 	MailPoet Gravity Forms Add-on/Functions
 * @version 	2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Include core functions
include( 'mailpoet-gravity-forms-conditional-functions.php' );

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