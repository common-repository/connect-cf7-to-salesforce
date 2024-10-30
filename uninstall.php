<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$uninstall = get_option( 'CF7SF_uninstall' );
if ( $uninstall ) {
	delete_option( 'CF7SF_access_token' );
	delete_option( 'CF7SF_persons' );
	delete_option( 'CF7SF_organizations' );
	delete_option( 'CF7SF_notes' );
	delete_option( 'CF7SF_lead' );
	delete_option( 'CF7SF_file' );
	delete_option( 'CF7SF_marketing_status' );
}