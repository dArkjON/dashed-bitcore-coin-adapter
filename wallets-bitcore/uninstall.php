<?php
if ( defined( 'WP_UNINSTALL_PLUGIN' ) ) {

	function wallets_delete_option( $option ) {
		delete_option( $option );
		delete_site_option( $option );
	}

	$option_slug = 'wallets-bitcore-core-node-settings';

	wallets_delete_option( "{$option_slug}-general-enabled" );
	wallets_delete_option( "{$option_slug}-rpc-ip" );
	wallets_delete_option( "{$option_slug}-rpc-port" );
	wallets_delete_option( "{$option_slug}-rpc-user" );
	wallets_delete_option( "{$option_slug}-rpc-password" );
	wallets_delete_option( "{$option_slug}-rpc-path" );

	wallets_delete_option( "{$option_slug}-fees-move" );
	wallets_delete_option( "{$option_slug}-fees-move-proportional" );
	wallets_delete_option( "{$option_slug}-fees-withdraw" );
	wallets_delete_option( "{$option_slug}-fees-withdraw-proportional" );

	wallets_delete_option( "{$option_slug}-other-minconf" );
}
