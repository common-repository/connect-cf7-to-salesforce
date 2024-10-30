<?php
/**
 * Initialize the admin menu.
 *
 * @package CF7SF
 */

namespace Procoders\CF7SF\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Procoders\CF7SF\Admin\Init as Init;
use Procoders\CF7SF\Admin\Logs as Logs;
use Procoders\CF7SF\Admin\Settings as Settings;
use Procoders\CF7SF\Functions as Functions;

/**
 * Create the admin menu.
 */
class RegisterMenu {

	/**
	 * Main class runner.
	 */
	public static function run(): void {
		add_action( 'admin_menu', array( static::class, 'init_menu' ) );
	}

	/**
	 * Register the plugin menu.
	 */
	public static function init_menu(): void {

		$init     = new Init();
		$settings = new Settings();
		$logs     = new Logs();

		$slug = functions::get_plugin_slug();

		add_menu_page(
			esc_html__( 'Contact Form 7 - SalesForce Integration', 'connect-cf7-to-salesforce' ),
			esc_html__( 'CF7 - SalesForce', 'connect-cf7-to-salesforce' ),
			'manage_options',
			$slug,
			array( $init, 'init_callback' ),
			'dashicons-forms'
		);

		add_submenu_page(
			$slug,
			esc_html__( 'CF7 - SalesForce: Settings', 'connect-cf7-to-salesforce' ),
			esc_html__( 'Settings', 'connect-cf7-to-salesforce' ),
			'manage_options',
			'cfsf_settings',
			array( $settings, 'settings_callback' ),
		);

		add_submenu_page(
			$slug,
			esc_html__( 'CF7 - SalesForce: Error Logs', 'connect-cf7-to-salesforce' ),
			esc_html__( 'Error Logs', 'connect-cf7-to-salesforce' ),
			'manage_options',
			'cfsf_api_error_logs',
			array( $logs, 'error_logs_callback' ),
		);

	}
}
