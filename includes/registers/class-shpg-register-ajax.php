<?php
/**
 * SHPG: AJAX (admin-ajax.php, or wc-ajax) register.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Ajax' ) ) {
	/**
	 * AJAX register class
	 */
	class SHPG_Register_Ajax extends SHPG_Register_Base_Ajax {
		/**
		 * Get AJAX callback items
		 *
		 * @return Generator
		 */
		public function get_items(): Generator {
			/**
			 * Import MID from credit card payment method.
			 *
			 * @see  SHPG_Gateway_NicePay::add_import_feature()
			 * @uses SHPG_WooCommerce::response_import_mid()
			 */
			yield new SHPG_Reg_Ajax( 'shpg_request_import_mid', 'wc@response_import_mid' );

			/**
			 * Import merchant key from credit card payment method.
			 *
			 * @see  SHPG_Gateway_NicePay::add_import_feature()
			 * @uses SHPG_WooCommerce::response_import_merchant_key()
			 */
			yield new SHPG_Reg_Ajax( 'shpg_request_import_merchant_key', 'wc@response_import_merchant_key' );

			/**
			 * List user's registered billing key information.
			 *
			 * Currently, alpha-feature.
			 *
			 * @uses SHPG_WooCommerce::get_user_cards()
			 */
			// yield new SHPG_Reg_Ajax( 'shpg_get_user_cards', 'wc@get_user_cards', false, true );

			/**
			 * Delete user's registered billing key information.
			 *
			 * Currently, alpha-feature.
			 *
			 * @uses SHPG_WooCommerce::remove_billing_key()
			 */
			yield new SHPG_Reg_Ajax( 'shpg_remove_billing_key', 'wc@remove_billing_key', false, true );
		}
	}
}
