<?php
/**
 * Plugin Name:       Connect CF7 to Salesforce
 * Plugin URI:        #
 * Description:       CF7 To Salesforce plugin allows you to send Contact Form 7 data to Salesforce.
 * Version:           1.0.0
 * Requires at least: 5.3
 * Requires PHP:      8.0
 * Author:            ProCoders
 * Author URI:        https://procoders.tech/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       connect-cf7-to-salesforce
 * Domain Path:       /languages
 *
 * @package CF7SF
 */

namespace Procoders\CF7SF;

use \Procoders\CF7SF\Admin\Settings as Setting;
use \Procoders\CF7SF\Admin\RegisterMenu as Menu;
use \Procoders\CF7SF\Admin\SettingsLinks as Links;
use \Procoders\CF7SF\Admin\ScriptsManager as Scripts;

define( 'CF7SF_VERSION', '1.0.0' );
define( 'CF7SF_FILE', __FILE__ );
define( 'CF7SF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CF7SF_API_VERSION', 'v51.0' );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * CF7SF class.
 */
class CF7SF {

	/**
	 * Holds the class instance.
	 *
	 * @var CF7SF $instance
	 */
	private static ?CF7SF $instance = null;

	/**
	 * Return an instance of the class
	 *
	 * @return CF7SF class instance.
	 * @since 1.0.0
	 */
	public static function get_instance(): CF7SF {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Perform actions after all plugins have been loaded.
	 *
	 * This method is executed after all plugins have been loaded during the WordPress core loading process.
	 * It performs various actions such as loading the plugin text domain, registering menus and scripts,
	 * adding actions for AJAX requests, and initializing plugin features.
	 *
	 * @return void
	 */
	public function plugins_loaded(): void {
		load_plugin_textdomain(
			'connect-cf7-to-salesforce',
			false,
			basename( __FILE__ ) . '/languages'
		);

		// Register the admin menu.
		Menu::run();
		Links::run();
		// Register Script.
		Scripts::run();

		$submission = new Includes\Submission();
		$setting = new Setting();

		add_action('wp_ajax_login_to_salesforce', array($setting, 'login_to_salesforce'));
		add_action('wp_ajax_revoke_token', array($setting, 'revoke_token'));

		add_action('admin_init', array($setting, 'handle_oauth_response'));

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wpcf7_before_send_mail', array( $submission, 'init' ), 10, 3 );
	}

	/**
	 * Init plugin.
	 */
	public function init(): void {
		// Silent.
	}
}

add_action(
	'plugins_loaded',
	function () {
		$CF7SF = CF7SF::get_instance();
		$CF7SF->plugins_loaded();
	}
);
