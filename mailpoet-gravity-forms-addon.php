<?php
/*
 * Plugin Name: MailPoet Gravity Forms Add-on
 * Plugin URI: http://wordpress.org/plugins/mailpoet-gravity-forms-add-on
 * Description: Adds a new field to add to your forms so your visitors can subscriber to your MailPoet newsletters.
 * Version: 2.0.1
 * Author: Sebs Studio
 * Author URI: http://www.sebs-studio.com
 * Author Email: sebastien@sebs-studio.com
 * Requires at least: 3.7.1
 * Tested up to: 3.8.1
 *
 * Text Domain: mailpoet-gravityforms-addon
 * Domain Path: /languages/
 * Network: false
 *
 * Copyright: (c) 2014 Sebs Studio. (sebastien@sebs-studio.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package MailPoet_Gravity_Forms_Add_on
 * @author Sebs Studio
 * @category Core
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Main MailPoet Gravity Forms Add-on Class
 *
 * @class MailPoet_Gravity_Forms_Add_on
 * @version 2.0.1
 */

final class MailPoet_Gravity_Forms_Add_on {

	/**
	 * Constants
	 */

	// Slug
	const slug = 'mailpoet_gravity_forms_add_on';

	// Name
	const name = 'MailPoet Gravity Forms Add-on';

	// Text Domain
	const text_domain = 'mailpoet-gravityforms-addon';

	/**
	 * The Plug-in version.
	 *
	 * @var string
	 */
	public $version = "2.0.1";

	/**
	 * The WordPress version the plugin requires minimum.
	 *
	 * @var string
	 */
	public $wp_version_min = "3.7.1";

	/**
	 * The Gravity Forms version the plugin requires minimum.
	 *
	 * @var string
	 */
	public $gf_version_min = "1.7.6.11";

	/**
	 * The single instance of the class
	 *
	 * @var MailPoet Gravity Forms Add-on
	 */
	protected static $_instance = null;

	/**
	 * The Plug-in URL.
	 *
	 * @var string
	 */
	public $web_url = "http://www.mailpoet.com/";

	/**
	 * The Plug-in documentation URL.
	 *
	 * @var string
	 */
	public $doc_url = "https://github.com/seb86/MailPoet-Gravity-Forms-Add-on/wiki/";

	/**
	 * The WordPress Plug-in URL.
	 *
	 * @var string
	 */
	public $wp_plugin_url = "http://wordpress.org/plugins/mailpoet-gravity-forms-add-on";

	/**
	 * The WordPress Plug-in Support URL.
	 *
	 * @var string
	 */
	public $wp_plugin_support_url = "http://wordpress.org/support/plugin/mailpoet-gravity-forms-add-on";

	/**
	 * GitHub Username
	 *
	 * @var string
	 */
	public $github_username = "seb86";

	/**
	 * GitHub Repo URL
	 *
	 * @var string
	 */
	public $github_repo_url = "https://github.com/username/MailPoet-Gravity-Forms-Add-on/";

	/**
	 * The Plug-in manage options.
	 *
	 * @var string
	 */
	public $manage_plugin = "manage_options";

	/**
	 * Main MailPoet Gravity Forms Add-on Instance
	 *
	 * Ensures only one instance of this plugin is loaded or can be loaded.
	 *
	 * @access public static
	 * @see MailPoet_Gravity_Forms_Add_on()
	 * @return MailPoet Gravity Forms Add-on - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @access public
	 * return MailPoet_Gravity_Forms_Add_on
	 */
	public function __construct() {
		// Auto-load classes on demand
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( array( &$this, 'autoload' ) );

		// Define constants
		$this->define_constants();

		// Check plugin requirements
		$this->check_requirements();

		// Include required files
		$this->includes();

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'action_links' ) );
		add_action( 'init', array( &$this, 'init_mailpoet_gravity_forms_add_on' ) );
	}

	/**
	 * Auto-load MailPoet Gravity Forms Add-on classes on demand to reduce memory consumption.
	 *
	 * @param mixed $class
	 * @return void
	 */
	public function autoload( $class ) {

		$class = strtolower( $class );

		if ( strpos( $class, 'mailpoet_gravity_forms_' ) === 0 ) {

			$path = $this->plugin_path() . '/includes/';
			$file = 'class-' . str_replace( '_', '-', $class ) . '.php';

			if ( is_readable( $path . $file ) ) {
				include_once( $path . $file );
				return;
			}
		}
	}

	/**
	 * Define Constants
	 *
	 * @access private
	 */
	private function define_constants() {
		define( 'MAILPOET_GF', self::name ); // Plugin Name
		define( 'MAILPOET_GF_SLUG', self::slug ); // Plugin slug
		define( 'MAILPOET_GF_FILE', __FILE__ ); // Plugin file name
		define( 'MAILPOET_GF_VERSION', $this->version ); // Plugin version
		define( 'MAILPOET_GF_WP_VERSION_REQUIRE', $this->wp_version_min ); // WordPress requires to be...
		define( 'MAILPOET_GF_VERSION_REQUIRE', $this->gf_version_min ); // The plugin requires Gravit Forms to be at least...
		define( 'MAILPOET_GF_PAGE', 'MailPoet' ); // Settings page slug
		define( 'MAILPOET_GF_TEXT_DOMAIN', self::text_domain );

		define( 'MAILPOET_GF_README_FILE', 'http://plugins.svn.wordpress.org/mailpoet-gravity-forms-add-on/trunk/readme.txt' );

		define( 'MAILPOET_GF_GITHUB_USERNAME', $this->github_username );
		define( 'MAILPOET_GF_GITHUB_REPO_URL' , str_replace( 'username', MAILPOET_GF_GITHUB_USERNAME, $this->github_repo_url ) );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		define( 'MAILPOET_GF_SCRIPT_MODE', $suffix );
	}

	/**
	 * Plugin action links.
	 *
	 * @access public
	 * @param mixed $links
	 * @param string $file plugin file path and name being processed
	 * @return void
	 */
	public function action_links( $links ) {
		// List your action links
		if( current_user_can( $this->manage_plugin ) ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=gf_settings&subview=' . MAILPOET_GF_PAGE ) . '">' . __( 'Settings', MAILPOET_GF_TEXT_DOMAIN ) . '</a>',
			);
		}

		return array_merge( $links, $plugin_links );
	}

	/**
	 * Checks that the WordPress setup meets the plugin requirements.
	 *
	 * @access private
	 * @global string $wp_version
	 * @return boolean
	 */
	private function check_requirements() {
		global $wp_version;

		require_once(ABSPATH.'/wp-admin/includes/plugin.php');

		if (!version_compare($wp_version, MAILPOET_GF_WP_VERSION_REQUIRE, '>=')) {
			add_action('admin_notices', array( &$this, 'display_req_notice' ) );
			return false;
		}

		if( function_exists( 'is_plugin_active' ) ) {
			if ( !is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
				add_action('admin_notices', array( &$this, 'display_req_gf_not_active_notice' ) );
				return false;
			}
			else{
				if( $this->is_gravityforms_installed() ) {
					if( ! $this->is_gravityforms_supported() ) {
						add_action('admin_notices', array( &$this, 'display_req_gf_notice' ) );
						return false;
					}
				}
			}
		}

		if( !is_plugin_active( 'gravityforms/gravityforms.php' ) && !is_plugin_active( 'wysija-newsletters/index.php' ) ) {
			add_action('admin_notices', array( &$this, 'display_req_notice_mailpoet' ) );
			add_action('admin_notices', array( &$this, 'display_req_gf_notice' ) );
			return false;
		}

		if( !is_plugin_active( 'wysija-newsletters/index.php' ) ) {
			add_action('admin_notices', array( &$this, 'display_req_notice_mailpoet' ) );
			return false;
		}

		return true;
	}

	/**
	 * Display the WordPress requirement notice.
	 *
	 * @access static
	 */
	static function display_req_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __('Sorry, <strong>%s</strong> requires WordPress ' . MAILPOET_GF_WP_VERSION_REQUIRE . ' or higher. Please upgrade your WordPress setup', self::text_domain), MAILPOET_GF );
		echo '</p></div>';
	}

	/**
	 * Display the Gravity Forms requirement notice.
	 *
	 * @access static
	 */
	static function display_req_gf_not_active_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __('Sorry, <strong>%s</strong> requires Gravity Forms. Activate it now or <a href="%s" target="_blank">purchase it today!</a>', self::text_domain), MAILPOET_GF, 'http://www.gravityforms.com' );
		echo '</p></div>';
	}

	/**
	 * Display the Gravity Forms requirement notice.
	 *
	 * @access static
	 */
	static function display_req_gf_notice() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __('Sorry, <strong>%s</strong> requires Gravity Forms ' . MAILPOET_GF_VERSION_REQUIRE . ' or higher. Please update Gravity Forms for %s to work.', self::text_domain), MAILPOET_GF, MAILPOET_GF );
		echo '</p></div>';
	}

	/**
	 * Display the requirement notice for MailPoet.
	 *
	 * @access static
	 */
	static function display_req_notice_mailpoet() {
		echo '<div id="message" class="error"><p>';
		echo sprintf( __('Sorry, <strong>%s</strong> requires MailPoet Newsletters for this plugin to work. Please install and activate <strong><a href="%s">MailPoet Newsletters</a></strong> first.', 'mailpoet_bbpress_addon'), MAILPOET_GF, admin_url('plugin-install.php?tab=search&type=term&s=MailPoet+Newsletters+%28formerly+Wysija%29') );
		echo '</p></div>';
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @access public
	 * @return void
	 */
	public function includes() {
		include_once( 'includes/mailpoet-gravity-forms-core-functions.php' ); // Contains core functions for the front/back end.

		if ( is_admin() ) {
			$this->admin_includes();
		}

		if ( ! is_admin() || defined('DOING_AJAX') ) {
			$this->frontend_includes();
		}
	}

	/**
	 * Include required admin files.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_includes() {
		include_once( 'includes/admin/class-mailpoet-gravity-forms-admin.php' ); // Admin section
		include_once( 'includes/admin/mailpoet-gravity-forms-admin-hooks.php' ); // Hooks used in the admin
	}

	/**
	 * Include required frontend files.
	 *
	 * @access public
	 * @return void
	 */
	public function frontend_includes() {
		include_once( 'includes/mailpoet-gravity-forms-functions.php' ); // Contains functions for various front-end events
		include_once( 'includes/mailpoet-gravity-forms-hooks.php' ); // Include hooks
	}

	/**
	 * Runs when the plugin is initialized.
	 *
	 * @access public
	 */
	public function init_mailpoet_gravity_forms_add_on(){
		// Set up localisation
		$this->load_plugin_textdomain();

		// Load JavaScript and stylesheets
		$this->register_scripts_and_styles();
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any 
	 * following ones if the same translation is present.
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), self::text_domain );

		load_textdomain( self::text_domain, WP_LANG_DIR . "/".self::slug."/" . $locale . ".mo" );

		// Set Plugin Languages Directory
		// Plugin translations can be filed in the mailpoet-gravity-forms-add-on/languages/ directory
		// Wordpress translations can be filed in the wp-content/languages/ directory
		load_plugin_textdomain( self::text_domain, false, dirname( plugin_basename( __FILE__ ) ) . "/languages" );
	}

	/** Helper functions ******************************************************/

	/**
	 * Get the plugin url.
	 *
	 * @access public
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @access public
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Checks if Gravity Forms is installed.
	 */
	function is_gravityforms_installed(){
		return class_exists("RGForms");
	}

	/**
	 * Checks if the currently active version of 
	 * Gravity Forms supports this add-on.
	 */
	function is_gravityforms_supported(){
		if(class_exists('GFCommon')){
			$is_correct_version = version_compare(GFForms::$version, MAILPOET_GF_VERSION_REQUIRE, '>=' );
			return $is_correct_version;
		}
		else{
			return false;
		}
	}

	/**
	 * Registers and enqueues stylesheets and javascripts 
	 * for the administration panel and the front of the site.
	 *
	 * @access private
	 */
	private function register_scripts_and_styles() {
		if ( is_admin() ) {
			// Stylesheet
			$this->load_file( self::slug . '-style', '/assets/css/admin/mailpoet-gravity-forms-addon.css' );
		} // end if/else
	} // end register_scripts_and_styles

	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 *
	 * @access private
	 */
	private function load_file( $name, $file_path, $is_script = false, $support = array(), $version = '' ) {
		$url = $this->plugin_url() . $file_path;
		$file = $this->plugin_path() . $file_path;

		if( file_exists( $file ) ) {
			if( $is_script ) {
				wp_register_script( $name, $url, $support, $version );
				wp_enqueue_script( $name );
			}
			else {
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			} // end if
		} // end if

	} // end load_file

} // end class

/**
 * Returns the main instance of MailPoet_Gravity_Forms_Add_on to prevent the need to use globals.
 *
 * @return MailPoet Gravity Forms Add-on
 */
function MailPoet_Gravity_Forms_Add_on() {
	return MailPoet_Gravity_Forms_Add_on::instance();
}

// Global for backwards compatibility.
$GLOBALS['mailpoet_gravity_forms_add_on'] = MailPoet_Gravity_Forms_Add_on();

?>