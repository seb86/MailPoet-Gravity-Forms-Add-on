<?php
/**
 * MailPoet Gravity Forms Add-on Hooks.
 *
 * @author 		Sebs Studio
 * @category 	Includes
 * @package 	MailPoet Gravity Forms Add-on/Hooks
 * @version 	2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('gform_after_submission', 'mailpoet_gform_after_submission', 10, 2);

?>