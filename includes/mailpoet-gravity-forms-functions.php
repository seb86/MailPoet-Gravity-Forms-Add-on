<?php
/**
 * MailPoet Gravity Forms Add-on Functions
 *
 * Submission function for the front end.
 *
 * @author 		Sebs Studio
 * @category 	Includes
 * @package 	MailPoet Gravity Forms Add-on/Functions
 * @version 	2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/** 
 * This handles the form submission on the front end.
 */
function mailpoet_gform_after_submission($entry, $form){
	// Form ID
	$form_id = $form['id'];

	// find mailpoet form field
	$mailpoet_form_field = find_mailpoet_field_type('mailpoet', $form);
	if(!$mailpoet_form_field) return;

	$mailpoet_form_field_id = $mailpoet_form_field['id'];

	// get form settings
	$is_multiselect     = $mailpoet_form_field['mailpoet_multiselect'];
	$email_field_id     = $mailpoet_form_field['mailpoet_email_field_id'];
	$firstname_field_id = $mailpoet_form_field['mailpoet_firstname_field_id'];
	$lastname_field_id  = $mailpoet_form_field['mailpoet_lastname_field_id'];

	// If the user can select more than one newsletter lists.
	if( isset($is_multiselect ) && $is_multiselect == 'yes' ) {
		$mailpoet_lists = array();
		foreach($_REQUEST as $key => $value){
			if(preg_match('/mailpoet_gf_subscribe_list_.*/', $key)){
				$mailpoet_lists[] = $value;
			}
		}

		// Check that the user has selected newsletters before subscribing.
		if(sizeof($mailpoet_lists) == 0) return;
	}
	else{ // single select
		// Check if user wants to subscribe.
		if(isset($_REQUEST['input_subscribe_me_mailpoet_lists']) && $_REQUEST['input_subscribe_me_mailpoet_lists'] == '') return;

		// Fetch list
		$mailpoet_lists = array();
		$single_subscribe_to_lists = get_option('gf_mailpoet_settings'); // gets the selected lists from the settings.
		foreach($single_subscribe_to_lists as $key => $list){
			$mailpoet_lists[] = $list['list_id'];
		}
	}

	// call mailpoet
	$user_data = array( 
		'email' 	=> $entry[$email_field_id],
		'firstname' => $entry[$firstname_field_id],
		'lastname' 	=> $entry[$lastname_field_id],
	);

	$data_subscriber = array(
		'user' 		=> $user_data,
		'user_list' => array('list_ids' => $mailpoet_lists)
	);

	$userHelper =&WYSIJA::get('user','helper');
	$userHelper->addSubscriber($data_subscriber);
}

?>