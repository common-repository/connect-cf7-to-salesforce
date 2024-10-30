<?php
/**
 * License managing class
 *
 * @package CF7SF
 */

namespace Procoders\CF7SF\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

/**
 * Create the admin menu.
 */
class ScriptsManager {

	/**
	 * Main class runner.
	 */
	public static function run(): void {
		add_action( 'admin_enqueue_scripts', array( static::class, 'admin_assets' ) );
	}

	/**
	 * Enqueues assets for the admin area.
	 *
	 * @return void
	 */
	public static function admin_assets(): void {
		global $hook_suffix;
		// Check if the current page is a plugin page.
		if ( str_contains( $hook_suffix, 'cfsf_' ) ) {
			wp_enqueue_style( 'CF7SF-style', plugins_url( 'Assets/css/admin.css', __FILE__ ), array(), CF7SF_VERSION, 'all' );
			wp_enqueue_script( 'CF7SF-script', plugins_url( 'Assets/js/admin.js', __FILE__ ), array( 'jquery' ), CF7SF_VERSION, true );
		}

		wp_localize_script(
			'CF7SF-script', // This should be the handle you used when enqueuing your script
			'wp_ajax_obj',
			[
				'nonce' => wp_create_nonce('wp_ajax_nonce'),
			]
		);
	}
}
