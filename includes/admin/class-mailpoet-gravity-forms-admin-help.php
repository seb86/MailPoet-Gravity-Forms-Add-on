<?php
/**
 * This provides a help tab with links to documentation and the repository.
 *
 * @author 		Sebs Studio
 * @category 	Admin
 * @package 	MailPoet Gravity Forms Add-on/Admin
 * @version 	1.1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MailPoet_Gravity_Forms_Admin_Help' ) ) {

/**
 * MailPoet_Gravity_Forms_Admin_Help Class
 */
class MailPoet_Gravity_Forms_Admin_Help {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'current_screen', array( &$this, 'add_tabs' ), 50 );
	}

	/**
	 * Add help tabs
	 */
	public function add_tabs() {
		$screen = get_current_screen();

		if ( ! in_array( $screen->id, mailpoet_gravity_forms_addon_get_screen_ids() ) )
			return;

		$screen->add_help_tab( array(
			'id'	=> 'mailpoet_gravity_forms_docs_tab',
			'title'	=> __( 'Documentation', MAILPOET_GF_TEXT_DOMAIN ),
			'content'	=>

				'<p>' . sprintf( __( 'Thank you for using %s :) Should you need help using %s please read the documentation.', MAILPOET_GF_TEXT_DOMAIN ), MAILPOET_GF, MAILPOET_GF ) . '</p>' .

				'<p><a href="' . MailPoet_Gravity_Forms_Add_on()->doc_url . '" class="button button-primary">' . sprintf( __( '%s Documentation', MAILPOET_GF_TEXT_DOMAIN ), MAILPOET_GF ) . '</a></p>'

		) );

		$screen->add_help_tab( array(
			'id'	=> 'mailpoet_gravity_forms_support_tab',
			'title'	=> __( 'Support', MAILPOET_GF_TEXT_DOMAIN ),
			'content'	=>

				'<p>' . sprintf( __( 'After <a href="%s">reading the documentation</a>, for further assistance you can use the <a href="%s">community forum</a>.', MAILPOET_GF_TEXT_DOMAIN ), MailPoet_Gravity_Forms_Add_on()->doc_url, MailPoet_Gravity_Forms_Add_on()->wp_plugin_support_url ) . '</p>' .

				'<p><a href="' . MailPoet_Gravity_Forms_Add_on()->wp_plugin_support_url . '" class="button button-primary">' . __( 'Community Support', MAILPOET_GF_TEXT_DOMAIN ) . '</a></p>'

		) );

		$screen->add_help_tab( array(
			'id'	=> 'mailpoet_gravity_forms_bugs_tab',
			'title'	=> __( 'Found a bug?', MAILPOET_GF_TEXT_DOMAIN ),
			'content'	=>

				'<p>' . sprintf( __( 'If you find a bug within <strong>%s</strong> you can create a ticket via <a href="%s">Github issues</a>. Ensure you read the <a href="%s">contribution guide</a> prior to submitting your report. Be as descriptive as possible.', MAILPOET_GF_TEXT_DOMAIN ), MAILPOET_GF, MAILPOET_GF_GITHUB_REPO_URL . 'issues?state=open', MAILPOET_GF_GITHUB_REPO_URL . 'blob/master/CONTRIBUTING.md' ) . '</p>' .

				'<p><a href="' . MAILPOET_GF_GITHUB_REPO_URL . 'issues?state=open" class="button button-primary">' . __( 'Report a bug', MAILPOET_GF_TEXT_DOMAIN ) . '</a></p>'

		) );

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', MAILPOET_GF_TEXT_DOMAIN ) . '</strong></p>' .
			'<p><a href=" ' . MailPoet_Gravity_Forms_Add_on()->web_url . ' " target="_blank">MailPoet</a></p>' .
			'<p><a href=" ' . MailPoet_Gravity_Forms_Add_on()->wp_plugin_url . ' " target="_blank">' . __( 'Project on WordPress.org', MAILPOET_GF_TEXT_DOMAIN ) . '</a></p>' .
			'<p><a href="' . MAILPOET_GF_GITHUB_REPO_URL . '" target="_blank">' . __( 'Project on Github', MAILPOET_GF_TEXT_DOMAIN ) . '</a></p>'
		);
	}

} // end class.

} // end if class exists.

return new MailPoet_Gravity_Forms_Admin_Help();

?>