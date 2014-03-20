<?php
/**
 * MailPoet Gravity Forms Add-on Settings Page
 *
 * @author 		Sebs Studio
 * @category 	Core
 * @package 	MailPoet Gravity Forms Add-on/Admin/Views
 * @version 	2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function settings_page(){
	// Save settings.
	if(rgpost("gf_mailpoet_submit")){
		check_admin_referer("update", "gf_mailpoet_update");

		// Each list that has been ticked will be saved.
		if(isset($_POST['mailpoet_subscribe'])){
			$mailpoet_subscribe = $_POST['mailpoet_subscribe'];
			update_option('gf_mailpoet_settings', $mailpoet_subscribe);
		}
		else{
			delete_option('gf_mailpoet_settings');
		}
		?>
		<div id="message" class="updated fade" style="padding:10px;"><?php _e('Your settings have been saved.', MAILPOET_GF_TEXT_DOMAIN); ?></div>
		<?php
	}
	else{
		$mailpoet_subscribe = get_option("gf_mailpoet_settings");
	}
	$mailpoet_list = mailpoet_lists();
	?>
	<form method="post" action="">
		<?php wp_nonce_field("update", "gf_mailpoet_update"); ?>
		<h3><?php _e('MailPoet Gravity Forms Add-on Settings', MAILPOET_GF_TEXT_DOMAIN); ?></h3>

		<p><?php _e('This setting is used only if you are using single checkbox option in the "MailPoet" field settings of the form editor.', MAILPOET_GF_TEXT_DOMAIN); ?></p>
		<p><?php _e('Simply select the lists you want your subscribers to subscribe to and press "Save Settings".', MAILPOET_GF_TEXT_DOMAIN); ?></p>

		<table class="form-table">
			<?php
			foreach($mailpoet_list as $key => $list){
				$list_id = $list['list_id'];
				$checked = '';
				if(isset($mailpoet_subscribe) && !empty($mailpoet_subscribe)){
					if(in_array($list_id, $mailpoet_subscribe)){ $checked = ' checked="checked"'; }
				}
				?>
			<tr>
				<th scope="row">
					<label for="mailpoet_lists_<?php echo $list['list_id']; ?>" class="inline">
						<?php echo $list['name']; ?>
					</label>
				</th>
				<td>
				<input type="checkbox" id="input_mailpoet_lists_<?php echo $list['list_id']; ?>" name="mailpoet_subscribe[]" value="<?php echo esc_attr($list_id); ?>"<?php echo $checked; ?> />
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="2" ><input type="submit" name="gf_mailpoet_submit" class="button-primary" value="<?php _e("Save Settings", 'mailpoet-gravityforms-addon') ?>" /></td>
			</tr>
		</table>
	</form>
	<?php
}

?>