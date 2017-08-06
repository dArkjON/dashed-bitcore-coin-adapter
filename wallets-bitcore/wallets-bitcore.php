<?php
/*
 * Plugin Name: Bitcoin and Altcoin Wallets: Bitcore RPC Adapter
 * Description: Allows your Wallets plugin to interface with the BitCore daemon (bitcored).
 * Version: 1.0.1
 * Plugin URI: https://www.dashed-slug.net/bitcoin-altcoin-wallets-wordpress-plugin/bitcore-adapter-extension
 * Author: dashed-slug <info@dashed-slug.net>, dArkjON
 * Author URI: http://dashed-slug.net
 * Text Domain: wallets-bitcore
 * Domain Path: /languages/
 * License: GPLv2 or later
 *
 * @license GNU General Public License, version 2
 * @package wallets-bitcore
 * @since 1.0.0
 */

/*
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 Copyright dashed-slug <info@dashed-slug.net>
*/


// don't load directly
defined( 'ABSPATH' ) || die( '-1' );

define( 'DSWALLETS_bITCORE_PATH', dirname(__FILE__) );

include_once __DIR__ . '/ds-update.php';
require_once __DIR__ . '/includes/third-party/class-tgm-plugin-activation.php';

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( ! class_exists( 'Dashed_Slug_Wallets_Bitcore_RPC' ) ) {

	final class Dashed_Slug_Wallets_Bitcore_RPC {

		public function __construct() {
			add_action( 'wallets_declare_adapters', array( &$this, 'action_wallets_declare_adapters' ) );
		}

		public function action_wallets_declare_adapters() {
			include_once __DIR__ . '/includes/wallets-bitcore-adapter.php';
		}

		/** @internal */
		public static function action_activate( $network_active ) {
			$base_network_active = is_plugin_active_for_network( 'wallets/wallets.php' );
			if ( $base_network_active && ! $network_active) {
				deactivate_plugins( 'wallets-bitcore/wallets-bitcore.php', false );
				wp_die(
					__( 'You cannot activate this plugin extension on a single blog because the base plugin is network-activated.', 'wallets-bitcore' ),
					__( 'Cannot activate plugin', 'wallets-bitcore' )
				);
			} elseif ( $network_active && ! $base_network_active ) {
				deactivate_plugins( 'wallets-bitcore/wallets-bitcore.php', false, true );
				wp_die(
					__( 'You cannot network-activate this plugin extension because the base plugin is not network-activated.', 'wallets-bitcore' ),
					__( 'Cannot activate plugin', 'wallets-bitcore' )
				);
			}

			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-general-enabled', 'on' );
			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-rpc-ip', '127.0.0.1' );
			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-rpc-port', '8555' );
			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-rpc-user', '' );
			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-rpc-password', '' );
			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-rpc-path', '' );

			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-fees-move', '0.05000000' );
			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-fees-move-proportional', '0' );
			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-fees-withdraw', '0.00500000' );
			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-fees-withdraw-proportional', '0' );

			call_user_func( $network_active ? 'add_site_option' : 'add_option', 'wallets-bitcore-core-node-settings-other-minconf', '6' );
		}

		/** @internal */
		public static function filter_plugin_action_links( $links ) {
			$settings_url = admin_url( 'admin.php?page=wallets-menu-bitcore-core-node' );

			$links[] = '<a href="' . esc_attr( $settings_url ) . '">'	. __( 'Settings', 'wallets-bitcore' ) . '</a>';
			$links[] = '<a href="https://www.dashed-slug.net/forums/forum/bitcore-rpc-adapter-extension-support/" style="color: #dd9933;">' . __( 'Support', 'wallets-bitcore' ) . '</a>';

			return $links;
		}

		public static function filter_network_admin_plugin_action_links( $links, $plugin_file ) {
			if ( 'wallets-bitcore/wallets-bitcore.php' == $plugin_file ) {
				$settings_url = network_admin_url( 'admin.php?page=wallets-menu-bitcore-core-node' );

				$links[] = '<a href="' . esc_attr( $settings_url ) . '">'	. __( 'Settings', 'wallets-bitcore' ) . '</a>';
				$links[] = '<a href="https://www.dashed-slug.net/forums/forum/bitcore-rpc-adapter-extension-support/" style="color: #dd9933;">' . __( 'Support', 'wallets-bitcore' ) . '</a>';
			}
			return $links;
		}


		public static function register_required_plugins() {
			$plugins = array(
				array(
					'name' => 'Bitcoin and Altcoin Wallets',
					'slug' => 'wallets',
					'required' => true,
					'version' => '2.4.0',
				),
			);

			$config = array(
				'id' => 'wallets-bitcore',
				'default_path' => '',
				'menu' => 'tgmpa-install-plugins',
				'parent_slug' => 'plugins.php',
				'capability' => 'manage_options',
				'has_notices' => true,
				'dismissable' => true,
				'dismiss_msg' => '',
				'is_automatic' => false,
				'message' => '',
			);

			tgmpa( $plugins, $config );
		} // end function register_required_plugins()
	}

	add_action( 'tgmpa_register', array( 'Dashed_Slug_Wallets_Bitcore_RPC', 'register_required_plugins' ) );

	if ( is_plugin_active_for_network( 'wallets-bitcore/wallets-bitcore.php' ) ) {
		add_filter( 'network_admin_plugin_action_links', array( 'Dashed_Slug_Wallets_Bitcore_RPC', 'filter_network_admin_plugin_action_links' ), 10, 2);
	} else {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( 'Dashed_Slug_Wallets_Bitcore_RPC', 'filter_plugin_action_links' ) );
	}

	register_activation_hook( __FILE__, array( 'Dashed_Slug_Wallets_Bitcore_RPC', 'action_activate' ) );

	new Dashed_Slug_Wallets_Bitcore_RPC();

}

