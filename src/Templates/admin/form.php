<?php if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}
$cf7_fields   = $data->form['cf7_fields'];
$fields       = $data->form['sf_fields'];
$CF7SF_fields = $data->form['CF7SF_fields'];
//$_labels_list = $data->form['_labels_list'];

$data->template->set_template_data(
	array(
		'title' => esc_html( $data->form['title'] ),
	)
)->get_template_part( 'admin/header' );
$data->template->set_template_data(
	array(
		'message' => $data->form['message'] ?? false,
	)
)->get_template_part( 'admin/message' );

?>
    <form method="post">
        <div class="form-wrap">
            <div class="form-wrapper field-list">
                <div class="form-group">
					<?php
					if ( $data->form['_form'] ) {
					if ( $cf7_fields ) {
					?>
                    <table class="widefat striped">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'CF7 Form Field', 'connect-cf7-to-salesforce' ); ?></th>
                            <th><?php esc_html_e( 'SalesForce Field', 'connect-cf7-to-salesforce' ); ?></th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th><?php esc_html_e( 'CF7 Form Field', 'connect-cf7-to-salesforce' ); ?></th>
                            <th><?php esc_html_e( 'SalesForce Field', 'connect-cf7-to-salesforce' ); ?></th>
                        </tr>
                        </tfoot>
                        <tbody>
						<?php
						foreach ( $cf7_fields as $cf7_field_key => $cf7_field_value ) {
							?>
                            <tr>
                                <td><?php echo esc_html( $cf7_field_key ); ?></td>
                                <td>
                                    <select name="CF7SF_fields[<?php echo esc_html( $cf7_field_key ); ?>][key]">
                                        <option value=""><?php esc_html_e( 'Select a field', 'connect-cf7-to-salesforce' ); ?></option>
										<?php
										$_type = '';
										if ( null !== $fields ) {
											foreach ( $fields as $field_key_ => $field_values ) {
												?>
                                                <optgroup label="<?php echo esc_html( $field_key_ ); ?>">
													<?php
													foreach ( $field_values as $field_key => $field_value ) {

														$selected = '';
														if ( isset( $CF7SF_fields[ $cf7_field_key ]['key'] ) && $CF7SF_fields[ $cf7_field_key ]['key'] === $field_key_ . '_' . $field_value['name'] ) {
															$selected = ' selected="selected"';
															$_type    = $field_value['type'];
														}
														?>
                                                        <option <?php echo $field_value['required'] ? 'data-require="true"' : ''; ?>
                                                                value="<?php echo esc_html( $field_key_ . '_' . $field_value['name'] ); ?>"<?php echo esc_html( $selected ); ?>><?php echo esc_html( $field_value['label'] ); ?>
                                                            (
															<?php
															echo 'Type: ' . esc_html( $field_value['type'] );
															echo $field_value['required'] ? esc_html__( ', Required', 'connect-cf7-to-salesforce' ) : '';
															?>
                                                            )
                                                        </option>
													<?php } ?>
                                                </optgroup>
												<?php
											}
										}
										?>
                                    </select>
                                    <input type="hidden"
                                           name="CF7SF_fields[<?php echo esc_html( $cf7_field_key ); ?>][type]"
                                           value="<?php echo esc_html( $_type ); ?>"/>
                                </td>
                            </tr>
							<?php
						}
						?>
                        </tbody>
                    </table>
                </div>
                <div class="form-group inner">
                    <div class="submit">
						<?php wp_nonce_field( 'CF7SF_submit_form' ); ?>
                        <input type='submit' class='button-primary' name="submit"
                               value="<?php esc_html_e( 'Save Changes', 'connect-cf7-to-salesforce' ); ?>"/>
                    </div>
                </div>
            </div>
            <div class="form-wrapper postbox form-conf">
                <div class="form-group inside">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row">
                                <label class="form-check-label"
                                       for="cfhs"><?php esc_html_e( 'Enable send', 'connect-cf7-to-salesforce' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" class="form-check-input" name="CF7SF_active"
                                       value="1"<?php echo '1' === $data->form['CF7SF_active'] ? ' checked' : ''; ?> />
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <hr/>
                <div class="form-group inside">

                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row">
                                <label class="form-check-label"
                                       for="cfhs"><?php esc_html_e( 'Update lead if exist', 'connect-cf7-to-salesforce' ); ?></label>
                                <p><?php esc_html_e( 'Search lead by email', 'connect-cf7-to-salesforce' ); ?></p>
                            </th>
                            <td>
                                <input type="checkbox" class="form-check-input" name="CF7SF_update_lead"
                                       value="1"<?php echo '1' === $data->form['CF7SF_update_lead'] ? ' checked' : ''; ?> />
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
		<?php
		}
		}
		?>
    </form>
<?php
$data->template->get_template_part( 'admin/footer' );