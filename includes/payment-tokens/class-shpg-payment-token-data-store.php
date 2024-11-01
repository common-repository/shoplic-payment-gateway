<?php
/**
 *
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Payment_Token_Data_Store' ) ) {
	require_once dirname( WC_PLUGIN_FILE ) . '/includes/data-stores/class-wc-payment-token-data-store.php';
}

if ( ! class_exists( 'SHPG_Payment_Token_Data_Store' ) ) {
	class SHPG_Payment_Token_Data_Store extends WC_Payment_Token_Data_Store {
		/**
		 * @param int    $user_id
		 * @param string $hash
		 *
		 * @return int
		 * @see    SHPG_WooCommerce::add_data_store()
		 */
		public function get_token_id_by_card_hash( int $user_id, string $hash ): int {
			global $wpdb;

			/** @noinspection SqlResolve */
			$sql = "SELECT t.token_id FROM {$wpdb->prefix}woocommerce_payment_tokens AS t"
			       . " INNER JOIN {$wpdb->prefix}woocommerce_payment_tokenmeta AS tm"
			       . ' ON tm.payment_token_id = t.token_id'
			       . " WHERE t.user_id=%d AND tm.meta_key='card_hash' AND tm.meta_value=%s LIMIT 0, 1";

			// phpcs:ignore
			return intval( $wpdb->get_var( $wpdb->prepare( $sql, $user_id, $hash ) ) );
		}
	}
}
