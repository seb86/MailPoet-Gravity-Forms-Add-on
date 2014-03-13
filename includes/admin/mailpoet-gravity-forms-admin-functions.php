<?php
/**
 * MailPoet Gravity Forms Add-on Admin Functions
 *
 * @author 		Sebs Studio
 * @category 	Core
 * @package 	MailPoet Gravity Forms Add-on/Admin/Functions
 * @version 	1.1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Get all MailPoet Gravity Forms Add-on screen ids
 *
 * @return array
 */
function mailpoet_gravity_forms_addon_get_screen_ids() {
	return array(
		'toplevel_page_gf_edit_forms',
		'forms_page_gf_settings',
	);
}

/**
 * Adds MailPoet Newsletter button to 
 * Gravity Forms - Standard Fields.
 */
function mailpoet_add_field_button($field_groups){
	foreach($field_groups as &$group){
		if($group['name'] == 'standard_fields'){
			$group['fields'][] = array(
									'class' => 'button',
									'value' => __('MailPoet Newsletter', 'mailpoet-gravityforms-addon'),
									'onclick' => "StartAddField('mailpoet');",
			);
			break;
		}
	}
	return $field_groups;
}

// Adds title to MailPoet Newsletter button
function mailpoet_assign_title($title, $field_type){
	if($field_type == 'mailpoet'){
		return __('MailPoet Newsletter', 'mailpoet-gravityforms-addon');
	}
}

// Adds the input area to the external side
function mailpoet_gform_field_input($input, $field, $value, $lead_id, $form_id){
	if($field["type"] == "mailpoet"){
		$field_id       = $field['id'];
		$input_id       = 'mailpoet-'.$field['id'];
		$input_name     = $form_id.'_'.$field['id'];
		$tabindex       = GFCommon::get_tabindex();
		$css            = isset($field['cssClass']) ? $field['cssClass'] : '';
		$is_multiselect = isset($field['mailpoet_multiselect']) ? $field['mailpoet_multiselect'] : '';
		$checkbox_label = isset($field['mailpoet_checkbox_label']) ? $field['mailpoet_checkbox_label'] : __('Yes, please subscribe me to your newsletter.', 'mailpoet-gravityforms-addon');

		$mailpoet_lists = mailpoet_lists();

		$html = "<div class='ginput_container'>";

		$html .= "<ul class='gfield_checkbox' id='input_".$field_id."'>";

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
			// If multi selection of Newsletters is enabled.
			foreach($mailpoet_lists as $list){
				$list_id   = $list['list_id'];
				$list_name = $list['name'];

				$input_id    = "input_mailpoet_lists_".$list_id;
				$input_name  = $input_id;
				$input_value = $list_id;
				$li_class    = 'gchoice_'.$field_id.'_'.$list_id;

				$html .= "<li class='".$li_class."'><input id='".$input_id."' class='gform_mailpoet ".esc_attr($css)."' type='checkbox' name='".$input_name."' value='".$input_value."'";
				//if(isset($field[$input_name]) && $field[$input_name] == $list_id){ $html .= ' checked="checked"'; }
				$html .= $tabindex." /><label for='".$input_id."'>".$list_name."</label></li>";
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
 * Now we execute javascript for the 
 * field to load correctly.
 */
function mailpoet_gform_editor_js(){
?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		// Add all textarea settings to the "MailPoet Newsletter" field plus custom "mailpoet_setting"
		// this will show all fields that Paragraph Text field shows plus my custom setting
		// fieldSettings["mailpoet"] = fieldSettings["textarea"] + ", .mailpoet_setting"; 

		// from forms.js; can add custom "mailpoet_setting" as well
		// this will show all the fields of the Paragraph Text field minus a couple that I didn't want to appear.
		fieldSettings["mailpoet"] = ".label_setting, .description_setting, .admin_label_setting, .css_class_setting, .visibility_setting, .mailpoet_setting";

		// binding to the load field settings event to initialize the checkbox
		jQuery(document).bind("gform_load_field_settings", function(event, field, form){
			// handle multiselect option
			if(field["mailpoet_multiselect"] == 'yes'){
				jQuery("#mailpoet_multiselect option[value=yes]").attr('selected', 'selected');
			}
			else{
				jQuery("#mailpoet_multiselect option[value=no]").attr('selected', 'selected');
			}

			// handle checkbox label
			jQuery("#mailpoet_checkbox_label").val(field["mailpoet_checkbox_label"]);
			// handle field id settings
				jQuery("#mailpoet_email_field_id").val(field["mailpoet_email_field_id"]);
				jQuery("#mailpoet_firstname_field_id").val(field["mailpoet_firstname_field_id"]);
				jQuery("#mailpoet_lastname_field_id").val(field["mailpoet_lastname_field_id"]);

				// handle lists selection
				/*for(var key in field){
					if(key.substr(0,20) == 'input_mailpoet_lists'){
						jQuery("#"+key).attr("checked", field[key] == true);
						console.log('value: ', field[key]);
					}
				}*/

				jQuery.each(field, function(index, val){
					if(index.substr(0,20) == 'input_mailpoet_lists'){
						jQuery("#"+index).attr("checked", field[index] == true);
						//console.log('value: ', field[index]);
					}
				});

			});

			jQuery("#mailpoet_checkbox_label").keyup(function(){
				jQuery("label[for='input_subscribe_me_mailpoet_lists']").text(jQuery(this).val());
			});

		});
	</script>
	<?php
}

// Add a custom setting to the mailpoet properties
function mailpoet_settings($position, $form_id){
	// Create settings on position 50 (right after Field Label)
	if($position == 50){
		$mailpoet_lists = mailpoet_lists();
		?>
		<li class="mailpoet_setting field_setting">
			<?php _e('Subscriber fields', 'mailpoet-gravityforms-addon'); ?>
			<?php gform_tooltip("form_mailpoet_custom_fields"); ?>
			<br>
			<label for="mailpoet_firstname_field_id" class="inline">
				<?php _e('Use First Name from Field ID', 'mailpoet-gravityforms-addon'); ?>
			</label>
			<input type="text" id="mailpoet_firstname_field_id" style="width:10%" onkeyup="SetFieldProperty('mailpoet_firstname_field_id', this.value);" />

			<br>
			<label for="mailpoet_lastname_field_id" class="inline">
				<?php _e('Use Last Name from Field ID', 'mailpoet-gravityforms-addon'); ?>
			</label>
			<input type="text" id="mailpoet_lastname_field_id" style="width:10%" onkeyup="SetFieldProperty('mailpoet_lastname_field_id', this.value);" />

			<br>
			<label for="mailpoet_email_field_id" class="inline">
				<?php _e('Use Email Address from Field ID', 'mailpoet-gravityforms-addon'); ?>
			</label>
			<input type="text" id="mailpoet_email_field_id" style="width:10%" onkeyup="SetFieldProperty('mailpoet_email_field_id', this.value);" />
		</li>

		<li class="mailpoet_setting field_setting">
			<label for="mailpoet_multiselect" class="inline">
				<?php _e('Enable multiselect', 'mailpoet-gravityforms-addon'); ?>
				<?php gform_tooltip("form_mailpoet_multiselect"); ?>
			</label>
			<select id="mailpoet_multiselect" onchange="SetFieldProperty('mailpoet_multiselect', this.value);">
				<option value="no"><?php _e('No', 'mailpoet-gravityforms-addon'); ?></option>
				<option value="yes"><?php _e('Yes', 'mailpoet-gravityforms-addon'); ?></option>
			</select>
		</li>

		<li class="mailpoet_setting field_setting mailpoet_lists">
			<div id="gfield_settings_mailpoet_lists_container">
				<?php _e('MailPoet lists', 'mailpoet-gravityforms-addon'); ?>
				<?php gform_tooltip("form_mailpoet_lists"); ?>

				<ul id="field_mailpoet_lists">
				<?php foreach($mailpoet_lists as $list){ ?>
					<li>
						<input type="checkbox" id="input_mailpoet_lists_<?php echo $list['list_id']; ?>" onclick="SetFieldProperty('input_mailpoet_lists_<?php echo $list['list_id']; ?>', this.checked);" />
						<label for="mailpoet_lists_<?php echo $list['list_id']; ?>" class="inline">
							<?php echo $list['name']; ?>
						</label>
					</li>
				<?php } ?>
				</ul>
			</div>
		</li>

		<li class="mailpoet_setting field_setting">
			<label for="mailpoet_checkbox_label">
				<?php _e('Single checkbox label', 'mailpoet-gravityforms-addon'); ?>
				<?php gform_tooltip("form_mailpoet_checkbox_label"); ?>
			</label>
			<input type="text" id="mailpoet_checkbox_label" onkeyup="SetFieldProperty('mailpoet_checkbox_label', this.value);" class="fieldwidth-3" />
		</li>
		<?php
	}
}

// Filter to add a new tooltip
function add_mailpoet_tooltips($tooltips){
	$tooltips['form_mailpoet_checkbox_label'] = "<h6>".__('Single checkbox label', 'mailpoet-gravityforms-addon')."</h6>";
	$tooltips['form_mailpoet_checkbox_label'] .= "<b>".__('This is ignored when multiselect is enabled.', 'mailpoet-gravityforms-addon')."</b>";
	$tooltips['form_mailpoet_checkbox_label'] .= "<br>".__('Default: Yes, please subscribe me to your newsletter.', 'mailpoet-gravityforms-addon');
	$tooltips['form_mailpoet_custom_fields'] = "<h6>".__('Custom Fields', 'mailpoet-gravityforms-addon')."</h6>";
	$tooltips['form_mailpoet_custom_fields'] .= __('You need to tell MailPoet from which Gravity Forms Fields it should take the users name and email address from.', 'mailpoet-gravityforms-addon');
	$tooltips['form_mailpoet_multiselect'] = "<h6>".__('Enable Multiselect', 'mailpoet-gravityforms-addon')."</h6>".__('Let the user select multiple lists.', 'mailpoet-gravityforms-addon');
	$tooltips['form_mailpoet_lists'] = "<h6>".__('MailPoet Lists', 'mailpoet-gravityforms-addon')."</h6>".__('Select which lists the user should be able to subscribe to.', 'mailpoet-gravityforms-addon');

	return $tooltips;
}

// Add a custom class to the field li
function mailpoet_gform_field_css_class($classes, $field, $form){
	if($field['type'] == 'mailpoet'){
		$classes .= ' gform_mailpoet';
	}

	return $classes;
}

// handle form submission
function mailpoet_gform_after_submission($entry, $form){
	// Form ID
	$form_id = $form['id'];

	// find mailpoet form field
	$mailpoet_form_field = $this->find_mailpoet_field_type('mailpoet', $form);
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
			if(preg_match('/input_mailpoet_lists_.*/', $key)){
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
		foreach($mailpoet_form_field as $key => $value){
			if(preg_match('/input_mailpoet_lists_.*/', $key)){
				$mailpoet_lists[] = $value;
			}
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