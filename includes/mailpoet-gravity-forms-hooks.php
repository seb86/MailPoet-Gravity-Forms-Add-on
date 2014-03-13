<?php
/**
 * MailPoet Gravity Forms Add-on Hooks.
 *
 * @author 		Sebs Studio
 * @category 	Includes
 * @package 	MailPoet Gravity Forms Add-on
 * @version 	1.1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('gform_after_submission', array(&$this, 'mailpoet_gform_after_submission'), 10, 2);

?>