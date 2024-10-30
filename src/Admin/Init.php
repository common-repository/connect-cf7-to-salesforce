<?php
/**
 * Initialize and display main admin panel output.
 *
 * @package CF7SF
 */

namespace Procoders\CF7SF\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use myoutdeskllc\SalesforcePhp\OAuth\OAuthConfiguration;
use myoutdeskllc\SalesforcePhp\SalesforceApi;
use Procoders\CF7SF\Functions as Functions;
use Procoders\CF7SF\Includes\SalesForceFields;
use Procoders\CF7SF\Loader as Loader;

/**
 * Returns an array containing a status message and a success flag for the submission status
 *
 */
class Init {

	/**
	 * Initializes the callback function for the given request
	 *
	 * @return void
	 */
	public function init_callback(): void {
		$template = new Loader();
		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'CF7SF_submit_form' );
		}

		if ( ! isset( $_REQUEST['id'] ) ) {
			// Lets Get all forms.
			$this->getFormList();

			return;
		}

		$id = ctype_digit( sanitize_text_field( $_REQUEST['id'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : 1;

		if ( isset( $_POST['submit'] ) ) {
			$this->updateMetaFields( $id );
			$message = $this->getSubmitStatusMessage();
		}

		$form            = $this->getFormData( $id );
		$form['message'] = $message ?? false;
		$template->set_template_data(
			array(
				'template' => $template,
				'form'     => $form,
			)
		)->get_template_part( 'admin/form' );
	}

	/**
	 * Returns an array containing form data for the given ID
	 *
	 * @param int $id The ID of the form.
	 *
	 * @return array The form data including various fields and metadata
	 */
	private function getFormData( int $id ): array {
		$_form     = get_post_meta( $id, '_form', true );
		$sf_fields = $this->getFields( array_keys( SalesForceFields::$breakFields ) );

		return array(
			'CF7SF_active'      => get_post_meta( $id, 'CF7SF_active', true ),
			'CF7SF_update_lead' => get_post_meta( $id, 'CF7SF_update_lead', true ),
			'CF7SF_fields'      => get_post_meta( $id, 'CF7SF_fields', true ),
			'title'             => get_the_title( $id ),
			'_form'             => $_form,
			'sf_fields'         => $sf_fields,
			'cf7_fields'        => $this->get_cf7_fields( $_form ),
		);
	}

	/**
	 * Returns an array containing the fields information for each group
	 *
	 * @param array $groups The array of groups
	 *
	 * @return array|null The array containing the fields information or null if no fields found
	 *
	 */
	public function getFields( array $groups ): ?array {
		$fields = [];
		foreach ( $groups as $group ) {
			$fields_ = get_option( 'CF7SF_' . $group );
			if ( $fields_ ) {
				foreach ( $fields_ as $key => $field_ ) {
					if ( is_object( $field_ ) ) {

						if ( in_array( $field_->name, SalesForceFields::$breakFields[ $group ] ) ) {
							continue;
						}
						$fields[ $group ][ $key ]['name']     = $field_->name;
						$fields[ $group ][ $key ]['label']    = $field_->label;
						$fields[ $group ][ $key ]['type']     = $field_->type;
						$fields[ $group ][ $key ]['length']   = $field_->length;
						$fields[ $group ][ $key ]['unique']   = $field_->unique;
						$fields[ $group ][ $key ]['required'] = ! $field_->nillable;
					} else {

						if ( in_array( $field_['name'], SalesForceFields::$breakFields[ $group ] ) ) {
							continue;
						}
						$fields[ $group ][ $key ]['name']     = $field_['name'];
						$fields[ $group ][ $key ]['label']    = $field_['label'];
						$fields[ $group ][ $key ]['type']     = $field_['type'];
						$fields[ $group ][ $key ]['length']   = $field_['length'];
						$fields[ $group ][ $key ]['unique']   = $field_['unique'];
						$fields[ $group ][ $key ]['required'] = ! $field_['nillable'];

					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Retrieves a list of contact forms and their details
	 *
	 * @return void
	 */
	public function getFormList(): void {
		$template = new loader();
		if ( $this->syncCF7withSalesForce() ) {
			$forms = new \WP_Query(
				array(
					'post_type'      => 'wpcf7_contact_form',
					'order'          => 'ASC',
					'posts_per_page' => - 1,
				)
			);

			$forms_array = array();
			while ( $forms->have_posts() ) {
				$forms->the_post();
				$id                           = get_the_ID();
				$forms_array[ $id ]['title']  = get_the_title();
				$forms_array[ $id ]['status'] = get_post_meta( get_the_ID(), 'CF7SF_active', true );
				$forms_array[ $id ]['link']   = menu_page_url( functions::get_plugin_slug(), 0 ) . '&id=' . $id;
			}
			wp_reset_postdata();

			$template->set_template_data(
				array(
					'template' => $template,
					'forms'    => $forms_array,
				)
			)->get_template_part( 'admin/formList' );
		} else {
			// TODO: Lets make the error template.
			$template->set_template_data(
				array(
					'template' => $template,
					'forms'    => false,
				)
			)->get_template_part( 'admin/formList' );
		}
	}

	/**
	 * Returns an instance of the SalesforceApi class with the appropriate OAuth connection
	 *
	 * @return object An instance of the SalesforceApi class
	 */
	public function getApi(): object {
		$auth = get_option( 'CF7SF_auth_data' );

		if ( ! $auth ) {
			Functions::return_error( 'Not authorized' );
		}

		try {
			$sf = new SalesforceApi( get_option( 'CF7SF_instance_url' ), CF7SF_API_VERSION );

			$conf = OAuthConfiguration::create( [
				'client_id'     => get_option( 'CF7SF_consumer_key' ),
				'client_secret' => get_option( 'CF7SF_consumer_secret' ),
				'redirect_uri'  => get_option( 'CF7SF_callback_url' ),
			] );

			$sf->restoreExistingOAuthConnection( $auth, $conf, function ( $authenticator ) {
				update_option( 'CF7SF_auth_data', $authenticator->serialize() );
			} );

			return $sf;
		} catch ( \Exception $e ) {
			Functions::return_error( $e->getMessage() );

			return $e;
		}
	}

	private function refreshSalesforceToken( $client_id, $client_secret, $refresh_token ) {
		$url = "https://login.salesforce.com/services/oauth2/token";

		$params = [
			'grant_type'    => 'refresh_token',
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'refresh_token' => $refresh_token,
		];

		$ch = curl_init( $url );

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );

		$response = curl_exec( $ch );

		if ( curl_errno( $ch ) ) {
			throw new Exception( curl_error( $ch ) );
		}

		curl_close( $ch );

		$response_data = json_decode( $response, true );

		if ( isset( $response_data['error'] ) ) {
			throw new Exception( $response_data['error_description'] );
		}

		return $response_data['access_token'];
	}

	/**
	 * Synchronizes the Salesforce object fields with CF7
	 *
	 * @return bool Returns true on successful synchronization
	 */
	private function syncCF7withSalesForce(): bool {
		$sf = $this->getApi();
		try {
			$so = $sf->getSObjectApi();
			update_option( 'CF7SF_lead', $so->getObjectFields( 'Lead', [ 'nillable' ] ) );
		} catch ( \Exception $e ) {
			Functions::return_error( $e->getMessage() );

			return false;
		}

		return true;
	}

	/**
	 * Returns an array containing a status message and a success flag for the submission status
	 *
	 * @return array The status message and a success flag
	 */
	private function getSubmitStatusMessage(): array {
		return array(
			'text'    => esc_html__( 'Integration settings saved.', 'connect-cf7-to-salesforce' ),
			'success' => true,
		);
	}

	/**
	 * Updates meta fields for a specifieds ID
	 *
	 * @param int $id The ID of the post to update meta fields for.
	 *
	 * @return void
	 */
	private function updateMetaFields( int $id ): void {
		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'CF7SF_submit_form' );
		}
		$meta_fields = array(
			'CF7SF_fields',
			'CF7SF_active',
			'CF7SF_update_lead',
		); // define the meta fields.

		// perform update_post_meta for each option field.

		foreach ( $meta_fields as $meta_field ) {
			$field_value = isset( $_POST[ $meta_field ] ) ? sanitize_post( wp_unslash( $_POST[ $meta_field ] ) ) : null;
			if ( 'CF7SF_active' === $meta_field && null === $field_value ) {
				$field_value = '0';
			}
			if ( 'CF7SF_update_person' === $meta_field && null === $field_value ) {
				$field_value = '0';
			}
			if ( 'CF7SF_update_org' === $meta_field && null === $field_value ) {
				$field_value = '0';
			}
			// update_post_meta if $field_value is not null.
			if ( null !== $field_value ) {
				update_post_meta(
					$id,
					$meta_field,
					$field_value
				);
			}
		}
	}

	/**
	 * Returns an array containing CF7 fields extracted from the given form
	 *
	 * @param string $_form The form content from which to extract CF7 fields.
	 *
	 * @return array|null The CF7 fields extracted from the form content, or null if no fields found.
	 */
	private function get_cf7_fields( string $_form ): bool|array {
		preg_match_all( '#\[([^\]]*)\]#', $_form, $matches );
		if ( null === $matches ) {
			return false;
		}

		$cf7_fields = array();
		foreach ( $matches[1] as $match ) {
			$match_explode = explode( ' ', $match );
			$field_type    = str_replace( '*', '', $match_explode[0] );
			// Continue in iteration if the field type is 'submit'.
			if ( 'submit' === $field_type ) {
				continue;
			}
			if ( isset( $match_explode[1] ) ) {
				$cf7_fields[ $match_explode[1] ] = array(
					'key'  => $match_explode[1],
					'type' => $field_type,
				);
			}
		}

		return $cf7_fields;
	}

}
