<?php
/**
 * MailPoet Gravity Forms Add-on Admin Functions
 *
 * @author 		Sebs Studio
 * @category 	Admin
 * @package 	MailPoet Gravity Forms Add-on/Functions
 * @version 	2.0.1
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
 * Adds MailPoet button to Gravity Forms
 * @Standard Fields.
 */
function mailpoet_add_field_button($field_groups){
	foreach($field_groups as &$group){
		if($group['name'] == 'standard_fields'){
			$group['fields'][] = array(
									'class' => 'button',
									'value' => 'MailPoet',
									'onclick' => "StartAddField('mailpoet');",
			);
			break;
		}
	}
	return $field_groups;
}

// Adds title to MailPoet field
function mailpoet_assign_title($title, $field_type){
	if($field_type == 'mailpoet'){
		return 'MailPoet';
	}
}

/**
 * Now we execute javascript for the 
 * field to load correctly.
 */
function mailpoet_gform_editor_js(){
?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		fieldSettings["mailpoet"] = ".label_setting, .description_setting, .admin_label_setting, .css_class_setting, .visibility_setting, .mailpoet_setting";

		// binding to the load field settings event to initialize the checkbox
		jQuery(document).bind("gform_load_field_settings", function(event, field, form){
			// handle multiselect option
			if(field["mailpoet_multiselect"] == 'yes'){
				jQuery("#mailpoet_multiselect option[value=yes]").attr('selected', 'selected');
				jQuery(".mailpoet_setting.checkbox_label").hide();
				jQuery(".mailpoet_setting.mailpoet_lists").show();
			}
			else{
				jQuery("#mailpoet_multiselect option[value=no]").attr('selected', 'selected');
				jQuery(".mailpoet_setting.checkbox_label").show();
				jQuery(".mailpoet_setting.mailpoet_lists").hide();
			}

			// handle checkbox label
			jQuery("#mailpoet_checkbox_label").val(field["mailpoet_checkbox_label"]);

			// handle field id settings
			jQuery("#mailpoet_email_field_id").val(field["mailpoet_email_field_id"]);
			jQuery("#mailpoet_firstname_field_id").val(field["mailpoet_firstname_field_id"]);
			jQuery("#mailpoet_lastname_field_id").val(field["mailpoet_lastname_field_id"]);

			// handle lists selection
			jQuery.each(field, function(index, val){
				if(index.substr(0,26) == 'mailpoet_gf_subscribe_list'){
					jQuery("#"+index).attr("checked", field[index] == true);
				}
			});

		});

		// Updates the single checkbox label when typing.
		jQuery("#mailpoet_checkbox_label").keyup(function(){
			jQuery("label[for='input_subscribe_me_mailpoet_lists']").text(jQuery(this).val());
		});

		// Changes between single or multiselect options.
		jQuery("#mailpoet_multiselect").change(function(){
			if( jQuery(this).val() == 'yes' ){
				jQuery(".mailpoet_setting.checkbox_label").hide();
				jQuery(".mailpoet_setting.mailpoet_lists").show();
			}
			else{
				jQuery(".mailpoet_setting.checkbox_label").show();
				jQuery(".mailpoet_setting.mailpoet_lists").hide();
			}
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
						<input class="list_id_<?php echo $list['list_id']; ?>" type="checkbox" id="mailpoet_gf_subscribe_list_<?php echo $list['list_id']; ?>" onclick="SetFieldProperty('mailpoet_gf_subscribe_list_<?php echo $list['list_id']; ?>', this.checked);" />
						<label for="mailpoet_lists_<?php echo $list['list_id']; ?>" class="inline">
							<?php echo $list['name']; ?>
						</label>
					</li>
				<?php } ?>
				</ul>
			</div>
		</li>

		<li class="mailpoet_setting field_setting checkbox_label">
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

?>