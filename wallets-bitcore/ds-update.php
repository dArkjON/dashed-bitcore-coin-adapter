<?php

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( ! function_exists( 'dashed_slug_activation_admin_init' ) ) {

	function dashed_slug_activation_admin_init() {

		add_settings_section(
			'ds_activation_section',
			'dashed-slug activation',
			'dashed_slug_activation_section',
			'ds_activation_page' );

		register_setting( 'ds_activation_page', 'ds-activation-code' );

		add_settings_field(
			'ds-activation-code',
			'dashed-slug activation code',
			'dashed_slug_activation_code',
			'ds_activation_page',
			'ds_activation_section' );
	}

	add_action( 'admin_init', 'dashed_slug_activation_admin_init' );
}

if ( is_plugin_active_for_network( 'wallets-bitcore/wallets-bitcore.php' ) ) {

	if ( ! function_exists( 'dashed_slug_activation_page_menu' ) ) {
		function dashed_slug_activation_page_menu() {

			if ( current_user_can( 'manage_network_options' ) ) {
				add_submenu_page(
					'settings.php',
					'Activate dashed-slug',
					'Activate dashed-slug.net plugins',
					'manage_network_options',
					'ds_activation_page',
					'dashed_slug_activation_page' );
			}
		}

		add_action( 'network_admin_menu', 'dashed_slug_activation_page_menu' );
	}


} else {

	if ( ! function_exists( 'dashed_slug_activation_page_menu' ) ) {
		function dashed_slug_activation_page_menu() {

			if ( current_user_can( 'manage_options' ) ) {
				add_options_page(
					'Activate dashed-slug',
					'Activate dashed-slug.net plugins',
					'manage_options',
					'ds_activation_page',
					'dashed_slug_activation_page' );
			}
		}

		add_action( 'admin_menu', 'dashed_slug_activation_page_menu' );
	}
}



if ( ! function_exists( 'dashed_slug_activation_page' ) ) {
	function dashed_slug_activation_page() {

		?><form method="post" action="<?php

			if ( is_plugin_active_for_network( 'wallets-bitcore/wallets-bitcore.php' ) ) {
				echo esc_url(
					add_query_arg(
						'action',
						'ds_activation_page',
						network_admin_url( 'edit.php' )
					)
				);
			} else {
				echo 'options.php';
			}

		?>"><?php

		settings_fields( 'ds_activation_page' );
		do_settings_sections( 'ds_activation_page' );
		submit_button();

		?></form><?php
	}
}

if ( ! function_exists( 'dashed_slug_update_network_options' ) ) {
	function dashed_slug_update_network_options() {
		check_admin_referer( 'ds_activation_page-options' );

		update_site_option( 'ds-activation-code', filter_input( INPUT_POST, 'ds-activation-code', FILTER_SANITIZE_STRING ));

		wp_redirect( add_query_arg( 'page', 'ds_activation_page', network_admin_url( 'settings.php' ) ) );
		exit;
	}
	if ( is_plugin_active_for_network( 'wallets-bitcore/wallets-bitcore.php' ) ) {
		add_action( 'network_admin_edit_ds_activation_page', 'dashed_slug_update_network_options' );
	}
}


if ( ! function_exists( 'dashed_slug_activation_section' ) ) {
	function dashed_slug_activation_section() {
		?><p style="text-size: smaller"><?php
			echo esc_html_e( 'Activate updates to the dashed-slug.net plugins', 'wallets-bitcore' );
		?></p><?php
	}
}

if ( ! function_exists( 'dashed_slug_activation_code' ) ) {
	function dashed_slug_activation_code( $args ) {
		?><input name="ds-activation-code" id="ds-activation-code" type="text" value="<?php
			echo esc_attr( call_user_func( is_plugin_active_for_network( 'wallets-bitcore/wallets-bitcore.php' ) ? 'get_site_option' : 'get_option' , 'ds-activation-code' ) );
			?>" />

		<p><?php echo esc_html_e( 'Enter here your dashed-slug.net activation code, exactly as you received it in your e-mail.', 'wallets-bitcore' ); ?></p><?php
	}
}

if ( ! function_exists( 'dashed_slug_notify_missing_code') ) {
	function dashed_slug_notify_missing_code() {
		if ( ! call_user_func( is_plugin_active_for_network( 'wallets-bitcore/wallets-bitcore.php' ) ? 'get_site_option' : 'get_option' , 'ds-activation-code' )) {

			?><div class="notice notice-info is-dismissible">
				<h2>updates to dashed-slug.net plugins</h2>
				<p>Thank you for installing a <strong>dashed-slug</strong> plugin extension.</p>
				<p>If you have paid for premium membership, you are entitled to auto-updates for all the <em>dashed-slug</em> plugins.</p>
				<p>To enable updates please go to the
				<a href="<?php
				if ( is_plugin_active_for_network( 'wallets-bitcore/wallets-bitcore.php' ) ) {
					echo network_admin_url( 'settings.php?page=ds_activation_page' );
				} else {
					echo admin_url( 'options-general.php?page=ds_activation_page' );
				}
				?>">dashed-slug activation panel</a>
				and enter your personal activation code.</p>
				<p>Your activation code is available when you log in to <a href="http://dashed-slug.net">dashed-slug.net</a> and you should have received it in your mail when you subscribed.</p>
				<p>Contact <a href="mailto:info@dashed-slug.net">info@dashed-slug.net</a> for any questions/problems with activation.</p>
			</div><?php
		}
	}

	add_action( is_plugin_active_for_network( 'wallets-bitcore/wallets-bitcore.php' ) ? 'network_admin_notices' : 'admin_notices', 'dashed_slug_notify_missing_code' );
}

function wallets_bitcore_get_update_json( $plugin_slug ) {

	$user_nonce = call_user_func( is_plugin_active_for_network( 'wallets-bitcore/wallets-bitcore.php' ) ? 'get_site_option' : 'get_option' , 'ds-activation-code' );

	if ( $user_nonce ) {

		$json_url = "https://www.dashed-slug.net/plugin-update/$plugin_slug/$user_nonce";
		$url_hash = 'ds_update_' . md5( $json_url );
		$cached_result = get_transient( $url_hash );

		if ( false === $cached_result ) {
			$update_json = wp_remote_get( $json_url );
			set_transient( $url_hash, $update_json, 4 * HOUR_IN_SECONDS );
		} else {
			$update_json = $cached_result;
		}

		if ( is_array( $update_json ) && isset( $update_json['response'] )  && isset( $update_json['response']['code'] ) ) {

			if ( 200 == $update_json['response']['code'] ) {

				$update_info = json_decode( $update_json['body'] );
				if ( isset( $update_info->sections ) ) {
					$update_info->sections = (array) $update_info->sections;
				}

				if ( ! is_null( $update_info ) ) {
					return $update_info;
				}

			}
		}
	}

	return false;
}

if ( ! function_exists( 'wallets_bitcore_pre_set_site_transient_update_plugins' ) ) {
	function wallets_bitcore_pre_set_site_transient_update_plugins( $transient ) {


		$plugin_slug = 'wallets-bitcore';
		$plugin = "$plugin_slug/$plugin_slug.php";

		$update_info = wallets_bitcore_get_update_json( $plugin_slug );

		if ( is_object( $update_info )
			&& isset( $update_info->new_version )
			&& version_compare(
				'1.1.0',
				$update_info->new_version,
				'<' ) ) {

			if ( ! isset( $transient->response[ $plugin ] ) ) {
				$transient->response[ $plugin ] = $update_info;
			}
		}

		return $transient;

	}
}

add_filter( 'pre_set_site_transient_update_plugins', 'wallets_bitcore_pre_set_site_transient_update_plugins' );

function wallets_bitcore_plugins_api($def, $action, $args) {

	$plugin_slug = 'wallets-bitcore';

	if ( isset( $args->slug) && $args->slug == $plugin_slug && 'plugin_information' == $action ) {
		return wallets_bitcore_get_update_json( $plugin_slug );
	}

	return $def;

}

add_filter('plugins_api', 'wallets_bitcore_plugins_api', 10, 3);

