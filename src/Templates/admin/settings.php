<?php if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}
$data->template->set_template_data(
	array(
		'title' => esc_html__( 'Settings', 'connect-cf7-to-salesforce' ),
	)
)->get_template_part( 'admin/header' );

$data->template->set_template_data(
	array(
		'message' => $data->settings['message'] ?? false,
	)
)->get_template_part( 'admin/message' );
?>
    <div id="poststuff">
        <div id="post-body">
            <div class="form-wrapper postbox">
                <h2 class="hndle">
                    <label for="title">
						<?php esc_html_e( 'Access Token', 'connect-cf7-to-salesforce' ); ?>
                    </label>
                </h2>
                <div class="form-group inside">
                    <form method="post">
						<?php wp_nonce_field( 'CF7SF_submit_form' ); ?>


                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="CF7SF_consumer_key"><?php esc_html_e( 'Consumer Key: ', 'connect-cf7-to-salesforce' ); ?></label>
                                </th>
                                <td>
                                    <input class="mw-400" type="text" name="CF7SF_consumer_key" id="CF7SF_consumer_key"
                                           value="<?php echo esc_html( $data->settings['consumer_key'] ) ?? ''; ?>"
                                           required/>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="CF7SF_consumer_secret"><?php esc_html_e( 'Consumer Secret: ', 'connect-cf7-to-salesforce' ); ?></label>
                                </th>
                                <td>
                                    <input class="mw-400" type="text" name="CF7SF_consumer_secret"
                                           id="CF7SF_consumer_secret"
                                           value="<?php echo esc_html( $data->settings['consumer_secret'] ) ?? ''; ?>"
                                           required/>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="CF7SF_callback_url"><?php esc_html_e( 'Callback URL: ', 'connect-cf7-to-salesforce' ); ?></label>
                                </th>
                                <td>
                                    <input class="mw-400" type="url" name="CF7SF_callback_url" id="CF7SF_callback_url"
                                           value="<?php echo esc_html( $data->settings['callback_url'] ) ?? get_site_url() . '/wp-admin/?action=get_code'; ?>"
                                           required/>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="CF7SF_instance_url"><?php esc_html_e( 'Instance URL: ', 'connect-cf7-to-salesforce' ); ?></label>
                                </th>
                                <td>
                                    <input class="mw-400" type="url" name="CF7SF_instance_url" id="CF7SF_instance_url"
                                           value="<?php echo esc_html( $data->settings['instance_url'] ) ?? ''; ?>"
                                           required/>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label class="form-check-label"
                                           for="CF7SF_sandbox"><?php esc_html_e( 'Sandbox', 'connect-cf7-to-salesforce' ); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" class="form-check-input" name="CF7SF_sandbox"
                                           id="CF7SF_sandbox"
                                           value="1" <?php echo esc_html( $data->settings['sandbox'] ) === "1" ? ' checked' : ''; ?> />
                                </td>
                            </tr>

							<?php if ( $data->settings['instance_url'] && $data->settings['callback_url'] && $data->settings['consumer_secret'] && $data->settings['consumer_key'] ) { ?>
								<?php if ( $data->settings['token'] ) { ?>
                                    <tr>
                                        <th scope="row">
											<?php esc_html_e( 'Connection status', 'connect-cf7-to-salesforce' ); ?>
                                        </th>
                                        <td>
											<?php
											echo esc_html__( 'Connected to ', 'connect-cf7-to-salesforce' ) .
											     '<code>' . esc_html( $data->settings['instance_url'] ) . '</code>' .
											     esc_html__( ' on ', 'connect-cf7-to-salesforce' ) .
											     esc_html( gmdate( 'F d, Y h:i:s A', $data->settings['token_time'] ?? '' ) );
											?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <a href="#" id="revoke_salesforce"
                                               class='button button-secondary'><span
                                                        class="dashicons dashicons-no"></span> <?php esc_html_e( 'Revoke Access', 'connect-cf7-to-salesforce' ); ?>
                                            </a>
                                        </th>
                                    </tr>
								<?php } else { ?>
                                    <tr>

                                        <th scope="row">
                                            <a href="#" id="login_to_salesforce"
                                               class='button button-secondary'><span
                                                        class="dashicons dashicons-admin-plugins"></span><?php esc_html_e( 'Login to SalesForce', 'connect-cf7-to-salesforce' ); ?>
                                            </a>
                                        </th>
                                    </tr>
								<?php } ?>
							<?php } ?>

                            <tr>
                                <th scope="row">
                                    <input type='submit' class='button-primary' name="submit"
                                           value="<?php esc_html_e( 'Save Credentials', 'connect-cf7-to-salesforce' ); ?>"/>
                                </th>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="form-group inside">
                    <strong><?php esc_html_e( 'Salesforce Setup', 'connect-cf7-to-salesforce' ); ?></strong>

                    <ol>
                        <li><?php esc_html_e( 'In Salesforce, go to Setup -&gt; App Manager -&gt; create new "Connected APP"', 'connect-cf7-to-salesforce' ); ?></li>
                        <li><?php esc_html_e( 'Enter Application Name(eg. My App) then check "Enable OAuth Settings" checkbox', 'connect-cf7-to-salesforce' ); ?></li>
                        <li><?php esc_html_e( 'Enter', 'connect-cf7-to-salesforce' ); ?>
                            <code><?php echo esc_html( get_site_url() ) ?>/wp-admin/?action=get_code</code>
							<?php esc_html_e( 'in Callback URL', 'connect-cf7-to-salesforce' ); ?>
                        </li>
                        <li><?php esc_html_e( 'Select OAuth Scopes', 'connect-cf7-to-salesforce' ); ?> <code>Access and
                                manage
                                your data (api)</code> <?php esc_html_e( 'and', 'connect-cf7-to-salesforce' ); ?> <code>Perform
                                requests on your behalf at any time (refresh_token,
                                offline_access)</code> <?php esc_html_e( 'then Save
                            Application', 'connect-cf7-to-salesforce' ); ?>
                        </li>
                        <li><?php esc_html_e( 'Copy Consumer Key and Secret', 'connect-cf7-to-salesforce' ); ?></li>
                    </ol>


                </div>
            </div>
        </div>
        <div class="form-wrapper">
            <form method="post">
                <h3 scope="row">
                    <label><?php esc_html_e( 'API Error Notification', 'connect-cf7-to-salesforce' ); ?></label>
                </h3>

                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'Subject', 'connect-cf7-to-salesforce' ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" name="CF7SF_notification_subject"
                                   value="<?php echo esc_html( $data->settings['notification_subject'] ) ?? ''; ?>"/>
                            <p class="description"><?php esc_html_e( 'Enter the subject.', 'connect-cf7-to-salesforce' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'Send To', 'connect-cf7-to-salesforce' ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" name="CF7SF_notification_send_to"
                                   value="<?php echo esc_html( $data->settings['notification_send_to'] ) ?? ''; ?>"/>
                            <p class="description"><?php esc_html_e( 'Enter the email address. For multiple email addresses, you can add email address by comma separated.', 'connect-cf7-to-salesforce' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label class="form-check-label"
                                   for="gridcfhs_uninstallCheck"><?php esc_html_e( 'Delete data on uninstall?', 'connect-cf7-to-salesforce' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" class="form-check-input" name="CF7SF_uninstall"
                                   id="CF7SF_uninstall"
                                   value="1" <?php echo esc_html( $data->settings['uninstall'] ) === "1" ? ' checked' : ''; ?> />
                        </td>
                    </tr>

                    </tbody>
                </table>
                <div class="submit">
					<?php wp_nonce_field( 'CF7SF_submit_form' ); ?>
                    <input type='submit' class='button-primary' name="submit"
                           value="<?php esc_html_e( 'Save Changes', 'connect-cf7-to-salesforce' ); ?>"/>
                </div>
            </form>
        </div>
    </div>
<?php
$data->template->get_template_part( 'admin/footer' );


