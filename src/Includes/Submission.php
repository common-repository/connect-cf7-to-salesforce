<?php
/**
 * Submisttion class for CF7
 *
 * @package CF7SF
 */

namespace Procoders\CF7SF\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

use Procoders\CF7SF\Admin\Init;
use Procoders\CF7SF\Admin\Logs as Logs;
use Procoders\CF7SF\Functions as Functions;

/**
 * Represents a submission handler for a form.
 */
class Submission {

	/**
	 * Initialize the form submission process, intercept cf7 form submission.
	 *
	 * @param mixed $form The form object or ID.
	 * @param bool $abort Flag indicating whether the submission should be aborted.
	 * @param mixed $object The object to be used in the submission process.
	 *
	 * @return void
	 */
	public function init( $form, &$abort, $object ): void {
		$access_token = get_option( 'CF7SF_access_token' );

		// PCF7_Submission i`ts from contact form 7.
		$submission = \WPCF7_Submission::get_instance();
		$init       = new Init();

		if ( ! $submission ) {
			Functions::return_error( 'Contact Form 7 plugin is required.' );
		}
		//$post_id = $submission->get_meta( 'container_post_id' );
		$request = $submission->get_posted_data();
		$form_id = $submission->get_contact_form()->id();

		if ( $form_id && '0' == ! get_post_meta( $form_id, 'CF7SF_active', true ) ) {
			$CF7SF_fields = get_post_meta( $form_id, 'CF7SF_fields', true );

			if ( null !== $CF7SF_fields ) {
				$data = $this->prepare_data( $request, $CF7SF_fields );
				$data = $this->process_data( $data );

				$CF7SF_update_lead = get_post_meta( $form_id, 'CF7SF_update_lead', true );
				try {
					$salesforce = $init->getApi();
					$sf         = $salesforce->getStandardObjectApi();
					if ( $CF7SF_update_lead === '1' && ! empty( $data['Lead']['Email'] ) ) {
						$response = $sf->findRecord(
							'Lead',
							array( 'Email' => $data['Lead']['Email'] ),
							array( 'Id' )
						);
						if ( ! empty( $response[0]['Id'] ) ) {
							// lead exists, update it
							$sf->updateLead( $response[0]['Id'], $data['Lead'] );
						} else {
							// lead does not exist, create it
							$sf->createLead( $data['Lead'] );
						}
					}

				} catch ( \Exception $e ) {
					Logs::handleErrorResponse( $e->getMessage(), $form_id );
					$submission->set_status( 'validation_failed' );
					$abort = true;
					$submission->set_response( 'API submission errors: ' . $e->getMessage() . ' ' . $e->getLine() );
				}
			}
		}
	}

	/**
	 * Prepare data for submission.
	 *
	 * @param array $request The form submission data.
	 * @param array $CF7SF_fields Fields mapping configuration.
	 *
	 * @return array Prepared data for submission.
	 */
	private function prepare_data( array $request, array $CF7SF_fields ): array {
		$data = array();
		foreach ( $CF7SF_fields as $CF7SF_field_key => $CF7SF_field ) {
			if ( isset( $CF7SF_field['key'] ) && $CF7SF_field['key'] ) {

				$value = $request[ $CF7SF_field_key ] ?? null;
				$value = $this->format_value( $value, $CF7SF_field );

				if ( null !== $value ) {
					$data[ $CF7SF_field['key'] ] = wp_strip_all_tags( $value );
				}
			}
		}

		return $data;
	}

	/**
	 * Format the value based on its type.
	 *
	 * @param mixed $value The value to be formatted.
	 * @param array $CF7SF_field Field configuration.
	 *
	 * @return mixed The formatted value.
	 */
	private function format_value( $value, array $CF7SF_field ) {
		if ( is_array( $value ) ) {
			$value = implode( ';', $value );
		}

		if ( ( 'datetime' === $CF7SF_field['type'] || 'date' === $CF7SF_field['type'] ) && $value ) {
			$value = strtotime( $value ) . '000';
		}

		return $value;
	}

	private function process_data( array $data ): array {

		$request = [];

		foreach ( $data as $key => $value ) {
			$parts = explode( '_', $key );
			$group = $parts[0];
			array_shift( $parts );
			$field_name                       = implode( '_', $parts );
			$request[ $group ][ $field_name ] = $value;
		}

		return $request;
	}

}
