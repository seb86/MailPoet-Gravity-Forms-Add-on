<?php
/**
 * MailPoet Gravity Forms Add-on Settings Page
 *
 * @author 		Sebs Studio
 * @category 	Core
 * @package 	MailPoet Gravity Forms Add-on/Admin/Views
 * @version 	1.1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function settings_page(){
	// Save settings.
	if(rgpost("gf_mailpoet_submit")){
		check_admin_referer("update", "gf_mailpoet_update");

		$settings = array("username" => stripslashes($_POST["gf_mailpoet_username"]), "password" => stripslashes($_POST["gf_mailpoet_password"]), "apikey" => $_POST["gf_mailpoet_apikey"]);

		update_option("gf_mailpoet_settings", $settings);
	}
	else{
		$settings = get_option("gf_mailpoet_settings");
	}
	$mailpoet_lists = mailpoet_lists();
	?>
	<form method="post" action="">
		<?php wp_nonce_field("update", "gf_mailpoet_update") ?>
		<h3><?php _e("MailPoet Settings", "mailpoet-gravityforms-addon") ?></h3>
		<p style="text-align: left;">
			<?php _e('This setting is used only if you are using single checkbox option in the MailPoet Newsletter field settings.', 'mailpoet-gravityforms-addon') ?>
		</p>

		<table class="form-table">
			<?php foreach($mailpoet_lists as $list){ ?>
			<tr>
				<th scope="row">
					<label for="mailpoet_lists_<?php echo $list['list_id']; ?>" class="inline">
						<?php echo $list['name']; ?>
					</label>
				</th>
				<td>
				<input type="checkbox" id="input_mailpoet_lists_<?php echo $list['list_id']; ?>" name="mailpoet_subscribe[]" />
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