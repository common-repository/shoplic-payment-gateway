<?php
/**
 * SHPG: WooCommerce my page module.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_My_Page' ) ) {
	/**
	 * WooCommerce > My Page customize module
	 */
	class SHPG_My_Page implements SHPG_Module {
		use SHPG_Hook_Impl;
		use SHPG_Template_Impl;
		use SHPG_Context_Impl;

		/**
		 * Constoructor method
		 */
		public function __construct() {
			$this
				->add_action( 'woocommerce_order_details_after_order_table', 'order_details_payment_info', 9 )
			;
		}

		/**
		 * Add payment information at order detail page.
		 *
		 * @callback
		 * @action    woocommerce_order_details_after_order_table_items
		 *
		 * @param WC_Order $order The order instance.
		 *
		 * @see       woocommerce/templates/order/order-details.php
		 */
		public function order_details_payment_info( WC_Order $order ) {
			$this->render_payment_data( $order, 'nicepay/my-page/order-details' );
		}
	}
}
