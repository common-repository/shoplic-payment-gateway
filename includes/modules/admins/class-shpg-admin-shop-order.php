<?php
/**
 * SHPG: Admin > shop order module
 *
 * @package shpg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Admin_Shop_Order' ) ) {
	/**
	 * Admin > shop Order class
	 */
	class SHPG_Admin_Shop_Order implements SHPG_Admin_Module {
		use SHPG_Hook_Impl;
		use SHPG_Template_Impl;
		use SHPG_Context_Impl;

		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->add_action( 'current_screen' );
		}

		/**
		 * Current screen callback
		 *
		 * @callback
		 * @action      current_screen
		 *
		 * @param WP_Screen $screen Current $wp_screen instance.
		 *
		 * @return void
		 */
		public function current_screen( WP_Screen $screen ) {
			if ( 'shop_order' === $screen->post_type && 'post' === $screen->base ) {
				$this
					->add_action( 'admin_enqueue_scripts', 'admin_enqueue_scripts' )
					->add_action( 'woocommerce_admin_order_data_after_order_details', 'payment_data' )
				;
			}
		}

		/**
		 * Enqueue CSS.
		 *
		 * @callback
		 * @action      admin_enqueue_scripts
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			if ( ( $order = wc_get_order() ) && 0 === strpos( $order->get_payment_method(), 'shpg_' ) ) {
				$this->enqueue_style( 'shpg-shop-order-payment-data' );
			}
		}

		/**
		 * Output payment data.
		 *
		 * @callback
		 * @action    woocommerce_admin_order_data_after_order_details
		 *
		 * @param WC_Order $order The order instance.
		 *
		 * @return void
		 */
		public function payment_data( WC_Order $order ) {
			if ( 0 === strpos( $order->get_payment_method(), 'shpg_nicepay_' ) ) {
				$this->render_payment_data( $order, 'nicepay/admins/shop-order-payment-data' );
			} elseif ( 'shpg_kakao_pay' === $order->get_payment_method() ) {
				$this->render_kakao_payment_data( $order, 'kakao-pay/admins/shop-order-payment-data' );
			}
		}

		/**
		 * Display raw data
		 *
		 * @callback
		 * @action      admin_post_shpg_display_raw_data
		 *
		 * @see         SHPG_Register_Submit::get_items()
		 */
		public function display_raw_data() {
			check_admin_referer( 'shpg_display_raw_data' );

			$order_id = absint( $_GET['order_id'] ?? '0' );

			if ( $order_id && current_user_can( 'edit_post', $order_id ) ) {
				$payment_result = shpg_post_meta()->payment_result->get_value( $order_id );
				if ( is_string( $payment_result ) ) {
					$payment_result = json_decode( $payment_result, true );
				}

				$notification_result = shpg_post_meta()->notification_result->get_value( $order_id );
				if ( is_string( $notification_result ) ) {
					$notification_result = json_decode( $notification_result, true );
				}

				// single: false.
				$refund_results = array_filter( shpg_post_meta()->refund_result->get_value( $order_id ) );
				foreach ( $refund_results as &$result ) {
					$result = json_decode( $result, true );
				}

				$this->render(
					'nicepay/admins/raw-data',
					[
						'order_id'            => $order_id,
						'payment_result'      => $payment_result,
						'notification_result' => $notification_result,
						'refund_results'      => $refund_results,
					]
				);
			}
		}

		/**
		 * Display notification guide
		 *
		 * @callback
		 * @action      admin_post_shpg_display_vbank_notification_setup_guide
		 *
		 * @see         SHPG_Register_Submit::get_items()
		 */
		public function display_vbank_nodification_setup_guide() {
			$this->render( 'nicepay/admins/vbank-notification-setup-guide' );
		}

		/**
		 * Display KakaoPay order
		 *
		 * @param WC_Order $order         Order.
		 * @param string   $template_name Template.
		 *
		 * @return void
		 */
		protected function render_kakao_payment_data( WC_Order $order, string $template_name ) {
			$payment_method = _x( 'KakaoPay', 'payment method', 'shoplic-pg' );
			$raw_data_url   = add_query_arg(
				[
					'action'   => 'shpg_display_raw_data',
					'order_id' => $order->get_id(),
					'_wpnonce' => wp_create_nonce( 'shpg_display_raw_data' ),
				],
				admin_url( 'admin-post.php ' )
			);
			$test_mode      = (bool) shpg_post_meta()->test_mode->get_value( $order );

			$this->render(
				$template_name,
				[
					'payment_method' => $payment_method,
					'raw_data_url'   => $raw_data_url,
					'test_mode'      => $test_mode,
				]
			);
		}
	}
}
