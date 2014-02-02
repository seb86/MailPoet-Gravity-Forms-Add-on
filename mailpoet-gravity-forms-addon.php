<?php
/*
Plugin Name: MailPoet Gravity Forms Add-on
Plugin URI: http://www.mailpoet.com
Description: Adds a new field to add to your forms so your visitors can subscriber to your MailPoet newsletters.
Version: 1.0.0
Author: Sebs Studio
Author URI: http://www.sebs-studio.com
Author Email: sebastien@sebs-studio.com
License:

  Copyright 2014 Sebs Studio (sebastien@sebs-studio.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

class MailPoet_Gravity_Forms_Add_on {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'MailPoet Gravity Forms Add-on';
	const slug = 'mailpoet_gravity_forms_add_on';

	private static $version = "1.0.0";
	private static $min_gravityforms_version = "1.7.6.11";

	/**
	 * Constructor
	 */
	function __construct(){
		// Hook up to the init action
		add_action('init', array(&$this, 'init_mailpoet_gravity_forms_add_on'));
	}

	/**
	 * Runs when the plugin is initialized
	 */
	function init_mailpoet_gravity_forms_add_on(){
		if( is_admin() ) {
			// Setup localization
			load_plugin_textdomain(self::slug, false, dirname(plugin_basename(__FILE__)).'/languages');

			// Support logging
			add_filter('gform_logging_supported', array(&$this, 'set_logging_supported'));

			if( !$this->is_gravityforms_supported() ) {
				return;
			}

			add_filter('gform_add_field_buttons', array(&$this, 'mailpoet_add_field_button'));
			add_filter('gform_field_type_title', array(&$this, 'mailpoet_assign_title'), 10, 2);
			add_action('gform_editor_js', array(&$this, 'mailpoet_gform_editor_js'));
			add_action('gform_field_standard_settings', array(&$this, 'mailpoet_settings'), 10, 2);
			add_filter('gform_tooltips', array(&$this, 'add_mailpoet_tooltips'));
			add_action('gform_field_css_class', array(&$this, 'mailpoet_gform_field_css_class'), 10, 3);
		}
		else{
			add_action('gform_after_submission', array(&$this, 'mailpoet_gform_after_submission'), 10, 2);
		}
		add_action('gform_field_input', array(&$this, 'mailpoet_gform_field_input'), 10, 5);
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

			$mailpoet_lists = $this->mailpoet_lists();

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
					if(isset($field[$input_name]) && $field[$input_name] == $list_id){ $html .= ' checked="checked"'; }
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
			$mailpoet_lists = $this->mailpoet_lists();
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

	// find form field by given field id
	public function find_mailpoet_field_ID($id, $form){
		foreach($form['fields'] as $field){
			if($field['id'] == $id) return $field;
		}
		return false;
	}

	// find form field by given field id
	public function find_mailpoet_field_type($type, $form){
		foreach($form['fields'] as $field){
			if($field['type'] == $type) return $field;
		}
		return false;
	}

	/**
	 * Gets all MailPoet lists
	 *
	 * @access public
	 */
	public function mailpoet_lists(){
		// This will return an array of results with the name and list_id of each mailing list
		$model_list = WYSIJA::get('list','model');
		$mailpoet_lists = $model_list->get(array('name','list_id'), array('is_enabled' => 1));

		return $mailpoet_lists;
	}

	/**
	 * Checks if Gravity Forms is installed.
	 *
	 * @access private static
	 */
	private static function is_gravityforms_installed(){
		return class_exists('RGForms');
	}

	/**
	 * Checks if the currently active version of 
	 * Gravity Forms supports this add-on.
	 *
	 * @access private static
	 */
	private static function is_gravityforms_supported(){
		if(class_exists('GFCommon')){
			$is_correct_version = version_compare(GFCommon::$version, self::$min_gravityforms_version, ">=");
			return $is_correct_version;
		}
		else{
			return false;
		}
	}

	// Returns the url of the plugin's root folder
	protected function get_base_url(){
		return plugins_url(null, __FILE__);
	}

	// Returns the physical path of the plugin's root folder
	protected function get_base_path(){
		$folder = basename(dirname(__FILE__));
		return WP_PLUGIN_DIR . "/" . $folder;
	}

	// Logs all activity.
	function set_logging_supported($plugins) {
		$plugins[self::slug] = "MailPoet";
		return $plugins;
	}

	private static function log_error($message) {
		if( class_exists('GFLogging') ) {
			GFLogging::include_logger();
			GFLogging::log_message(self::slug, $message, KLogger::ERROR);
		}
	}

	private static function log_debug($message){
		if( class_exists('GFLogging') ) {
			GFLogging::include_logger();
			GFLogging::log_message(self::slug, $message, KLogger::DEBUG);
		}
	}

} // end class

$mailpoet_gravity_forms = new MailPoet_Gravity_Forms_Add_on();

?>