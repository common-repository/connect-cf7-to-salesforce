<?php
/**
 * Initialize and display admin panel output.
 *
 * @package CF7SF
 */

namespace Procoders\CF7SF\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use myoutdeskllc\SalesforcePhp\OAuth\OAuthConfiguration;
use myoutdeskllc\SalesforcePhp\SalesforceApi;
use Procoders\CF7SF\Loader as Loader;

/**
 * Class Settings
 */
class Settings {

	/**
	 * Handles and updates settings submitted from the admin panel.
	 * Renders the settings template with updated values.
	 */
	public function settings_callback(): void {
		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'CF7SF_submit_form' );
		}

		$template = new Loader();

		$settings                     = array();
		$notification_subject_default = esc_html__( 'API Error Notification', 'connect-cf7-to-salesforce' );

		// Check for 'submit' submission.
		if ( isset( $_POST['submit'] ) ) {
			$this->updateOptionFields();
		}
		if ( get_option( 'CF7SF_token' ) && get_option( 'CF7SF_refresh_token' ) ) {
			$settings['token']      = get_option( 'CF7SF_token' );
			$settings['token_time'] = get_option( 'CF7SF_token_time' );
		} else {
			$settings['token'] = get_option( 'CF7SF_token' );
		}

		// Get saved options.
		$settings['consumer_key']    = get_option( 'CF7SF_consumer_key' );
		$settings['consumer_secret'] = get_option( 'CF7SF_consumer_secret' );
		$settings['callback_url']    = get_option( 'CF7SF_callback_url' );
		$settings['instance_url']    = get_option( 'CF7SF_instance_url' );
		$settings['sandbox']         = get_option( 'CF7SF_sandbox' );

		// Get notification_subject, set default if not exists.
		$settings['notification_subject'] = get_option( 'CF7SF_notification_subject', $notification_subject_default );
		$settings['notification_send_to'] = get_option( 'CF7SF_notification_send_to' );
		$settings['uninstall']            = get_option( 'CF7SF_uninstall' );

		$template->set_template_data(
			array(
				'template' => $template,
				'settings' => $settings,
			)
		)->get_template_part( 'admin/settings' );
	}


	/**
	 * Iterates over defined option fields and updates each with the submitted value
	 * Casts to int if the option is 'CF7SF_uninstall'
	 */
	private function updateOptionFields(): void {
		$option_fields = array(
			'CF7SF_consumer_key',
			'CF7SF_consumer_secret',
			'CF7SF_callback_url',
			'CF7SF_instance_url',
			'CF7SF_sandbox',
			'CF7SF_notification_subject',
			'CF7SF_notification_send_to',
			'CF7SF_uninstall',
		); // define the option fields.

		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'CF7SF_submit_form' );
		}

		// perform update_option for each option field.
		foreach ( $option_fields as $option_field ) {
			$field_value = isset( $_POST[ $option_field ] )
				? sanitize_text_field( wp_unslash( $_POST[ $option_field ] ) )
				: null;

			// update_option only if $field_value is not null; casting to int if it's 'CF7SF_uninstall'.
			update_option(
				$option_field,
				'CF7SF_uninstall' === $option_field
					? (int) $field_value
					: $field_value
			);
		}
	}


	private function reset_forms(): void {
		$all_forms_ids = get_posts( array(
			'fields'         => 'ids',
			'posts_per_page' => - 1,
			'post_type'      => 'CF7SF_contact_form'
		) );
		foreach ( $all_forms_ids as $id ) {
			delete_post_meta( $id, 'CF7SF_active' );
		}
	}


	public function revoke_token(): void {

		if (!check_ajax_referer('wp_ajax_nonce', '_ajax_nonce', false)) {
			$error_message = __('Nonce verification failed', 'connect-cf7-to-salesforce');
			wp_send_json_error($error_message);
		}

		$token = get_option( 'CF7SF_token' );
		//$token = unserialize( get_option( 'CF7SF_auth_data' ))->token;
		if ( $token ) {
			$response = wp_remote_post( get_option( 'CF7SF_instance_url' ), array(
				'method'  => 'POST',
				'headers' => array(
					'content-type' => 'application/x-www-form-urlencoded'
				),
				'body'    => array(
					'token' => $token,
				)
			) );
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				wp_send_json_error( __( "Something went wrong: ", 'connect-cf7-to-salesforce' ) . $error_message );
			} else {
				delete_option( 'CF7SF_token' );
				delete_option( 'CF7SF_refresh_token' );
				delete_option( 'CF7SF_token_time' );
				delete_option( 'CF7SF_auth_data' );
				wp_send_json_success( $response );
			}
		}
	}

	/**
	 * Logs in to Salesforce using OAuth.
	 * Retrieves the necessary configuration options from the database and
	 * starts the OAuth login process.
	 *
	 * @return void
	 */
	public function login_to_salesforce(): void {

		if (!check_ajax_referer('wp_ajax_nonce', '_ajax_nonce', false)) {
			$error_message = __('Nonce verification failed', 'connect-cf7-to-salesforce');
			wp_send_json_error($error_message);
		}

		$oauthConfig = OAuthConfiguration::create( [
			'client_id'     => get_option( 'CF7SF_consumer_key' ),
			'client_secret' => get_option( 'CF7SF_consumer_secret' ),
			'redirect_uri'  => get_option( 'CF7SF_callback_url' ),
		] );

		if ( get_option( 'CF7SF_sandbox' ) !== '1' ) {
			$salesforceApi = new SalesforceApi( get_option( 'CF7SF_instance_url' ), CF7SF_API_VERSION );
		} else {
			$salesforceApi = new SalesforceApi();
		}
		[ $url, $state ] = array_values( $salesforceApi->startOAuthLogin( $oauthConfig ) );
		if ( $url ) {
			update_option( 'CF7SF_state', $state );
			wp_send_json_success( $url );
		} else {
			wp_send_json_error( __( 'Error with oAuth', 'connect-cf7-to-salesforce' ) );
		}
	}

	/**
	 * Handles the OAuth response after the user authorizes the application.
	 * Retrieves the authorization code and state from the query string and exchanges it for access and refresh tokens.
	 * If successful, the tokens are saved in the database and the user is redirected to the settings page.
	 * If unsuccessful, the user is redirected to the settings page.
	 */
	public function handle_oauth_response(): void {

		// No nonce needed here

		if ( isset( $_GET['code'] ) && isset( $_GET['state'] ) ) {
			$code  = sanitize_text_field( $_GET['code'] );
			$state = sanitize_text_field( $_GET['state'] );

			$oauthConfig = OAuthConfiguration::create( [
				'client_id'     => get_option( 'CF7SF_consumer_key' ),
				'client_secret' => get_option( 'CF7SF_consumer_secret' ),
				'redirect_uri'  => get_option( 'CF7SF_callback_url' ),
			] );

			if ( get_option( 'CF7SF_sandbox' ) !== '1' ) {
				$salesforceApi = new SalesforceApi( get_option( 'CF7SF_instance_url' ) );
			} else {
				$salesforceApi = new SalesforceApi();
			}

			$authenticator = $salesforceApi->completeOAuthLogin( $oauthConfig, $code, $state );
			$token         = $authenticator->getAccessToken();
			$refresh       = $authenticator->getRefreshToken();
			if ( $token && $refresh ) {
				update_option( 'CF7SF_token', $token );
				update_option( 'CF7SF_refresh_token', $refresh );
				update_option( 'CF7SF_auth_data', $authenticator->serialize() );
				update_option( 'CF7SF_token_time', current_time( 'timestamp' ) );
			}
			wp_safe_redirect( '/wp-admin/admin.php?page=cfsf_settings' );
		}
	}

}
