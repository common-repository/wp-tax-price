<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function wptp_delete_plugin() {

	delete_option( 'wtp-tax' );
	delete_option( 'wtp-tax-calc' );
	delete_option( 'wtp-tax-camma' );

}

wptp_delete_plugin();

?>