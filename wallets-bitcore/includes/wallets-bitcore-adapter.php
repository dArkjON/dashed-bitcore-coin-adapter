<?php
// don't load directly
defined( 'ABSPATH' ) || die( '-1' );

if ( class_exists( 'Dashed_Slug_Wallets_Coin_Adapter_RPC' ) && ! class_exists( 'Dashed_Slug_Wallets_Bitcore_RPC_Adapter' ) ) {

	final class Dashed_Slug_Wallets_Bitcore_RPC_Adapter extends Dashed_Slug_Wallets_Coin_Adapter_RPC {

		// helpers

		// settings api

		// section callbacks

		/** @internal */
		public function section_fees_cb() {
			if ( ! current_user_can( 'manage_wallets' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'wallets-bitcore' ) );
			}

			?><p><?php esc_html_e( 'You can set two types of fees:', 'wallets-bitcore'); ?></p>
				<ul>
					<li>
						<strong><?php esc_html_e( 'Transaction fees', 'wallets-bitcore' )?></strong> &mdash;
						<?php esc_html_e( 'These are the fees a user pays when they send funds to other users.', 'wallets-bitcore' )?>
					</li><li>
						<p><strong><?php esc_html_e( 'Withdrawal fees', 'wallets-bitcore' )?></strong> &mdash;
						<?php esc_html_e( 'This the amount that is subtracted from a user\'s account in addition to the amount that they send to another address on the blockchain.', 'wallets-bitcore' )?></p>
						<p><?php echo __( 'Fees are calculated as: <i>total_fees = fixed_fees + amount * proportional_fees</i>.', 'wallets-bitcore' ); ?></p>
						<p class="card"><?php esc_html_e( 'This withdrawal fee is NOT the network fee, and you are advised to set the withdrawal fee to an amount that will cover the network fee of a typical transaction, possibly with some slack that will generate profit. To control network fees use the paytxfee setting in bitcore.conf', 'wallets-bitcore' ) ?>
						<a href="https://manpages.debian.org/testing/bitcore/bitcore.conf.5.en.html" target="_blank"><?php esc_html_e( 'Refer to the documentation for details.', 'wallets-bitcore' )?></a></p>
					</li>
				</ul><?php
		}

		// input field callbacks

		// API

		public function get_adapter_name() {
			return 'Bitcore Node';
		}

		public function get_name() {
			return 'Bitcore';
		}

		public function get_sprintf() {
			return 'BTX' . '%01.8f';
		}

		public function get_symbol() {
			return 'BTX';
		}

		public function get_icon_url() {
			return plugins_url( '../assets/sprites/bitcore-logo.png', __FILE__ );
		}
	}
}
