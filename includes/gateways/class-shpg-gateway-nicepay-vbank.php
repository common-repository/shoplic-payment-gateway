<?php
/**
 * SHPG: NicePay payment gateway: virtual bank account
 *
 * @package shpg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Gateway_NicePay_VBank' ) ) {
	/**
	 * NicePay Bank Payment Gateway (가상계좌)
	 */
	class SHPG_Gateway_NicePay_VBank extends SHPG_Gateway_NicePay {
		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->id                 = 'shpg_nicepay_vbank';
			$this->method_title       = _x( 'Virtual Bank Account', 'Method title', 'shoplic-pg' );
			$this->method_description = _x( 'Proceed with payment by virtual bank account.', 'Method description', 'shoplic-pg' );
			$this->pay_method         = 'VBANK';

			parent::__construct();
		}

		/**
		 * Initialize form fields
		 *
		 * @return void
		 */
		public function init_form_fields() {
			parent::init_form_fields();

			$this->form_fields['title']['default']       = _x( 'Virtual Bank Account', 'Method title', 'shoplic-pg' );
			$this->form_fields['description']['default'] = _x( 'Shoplic NicePay payment gateway, virtual bank account.', 'Method description', 'shoplic-pg' );

			// VBANK need more description.
			$this->form_fields['update_status']['description'] .= ' ' . _x( 'The order status will be updated after account transfer is confirmed.', 'Settings field', 'shoplic-pg' );

			/**
			 * 'vbank_info' will display its own information section instead of any form related widgets.
			 *
			 * @uses SHPG_Gateway_NicePay_VBank::generate_vbank_info_html()
			 */
			$this->form_fields['vbank_info'] = [ 'type' => 'vbank_info' ];
		}

		/**
		 * Generate 'vbank_info' field HTML.
		 *
		 * @param string $key  Key for this field option.
		 * @param array  $data Data for this field optin.
		 *
		 * @return string
		 *
		 * @used-by WC_Settings_API::generate_settings_html()
		 */
		public function generate_vbank_info_html( string $key, array $data ): string {
			$field_key = $this->get_field_key( $key );

			$defaults = [
				'title'       => __( 'Notification', 'shoplic-pg' ),
				'url'         => static::get_notification_url(),
				'desc_tip'    => false,
				'description' => sprintf(
					'%s <a id="vbank-info" href="%s" target="_blank">%s</a>',
					__( 'Copy this URL and paste it in the NicePay admin page.', 'shoplic-pg' ),
					esc_url( admin_url( 'admin-post.php?action=shpg_display_vbank_notification_setup_guide' ) ),
					__( 'Need a help?', 'shoplic-pg' )
				),
			];

			$data = wp_parse_args( $data, $defaults );

			return $this->render(
				'nicepay/admins/vbank-info',
				[
					'field_key' => $field_key,
					'data'      => $data,
				],
				'',
				false
			);
		}

		/**
		 * Return notification URL.
		 *
		 * @return string
		 */
		public static function get_notification_url(): string {
			return home_url( '/shpg-noti/vbank/' );
		}

		/**
		 * Handle notification response.
		 *
		 * @used-by SHPG_WooCommerce::noti_vbank()
		 *
		 * @throws Exception Validation error.
		 */
		public static function handle_notification_response() {
			// Message charset is EUC-KR.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$post = mb_convert_encoding( $_POST, 'utf-8', 'euc-kr' );

			$pay_method = $post['PayMethod'] ?? '';
			if ( 'VBANK' !== $pay_method ) {
				throw new Exception( sprintf( 'Wrong PayMethod: \'%s\'', $pay_method ) );
			}

			$order_id = $post['MallReserved'] ?? '0';
			$order    = wc_get_order( $order_id );
			if ( ! $order ) {
				throw new Exception( sprintf( 'Failed to read order object. MallReserved: \'%s\'', $order_id ) );
			}

			$result_code    = $post['ResultCode'] ?? '';
			$result_message = $post['ResultMsg'] ?? '';

			if ( '4110' !== $result_code ) {
				$order->update_status(
					'wc-failed',
					/* translators: 1 is notified result code, 2 is notified result message. */
					sprintf( __( 'Payment failed. %1$s: %2$s', 'shoplic-pg' ), $result_code, $result_message )
				);
				throw new Exception( sprintf( 'Payment failed. %s: %s', $result_code, $result_message ) );
			}

			$gateway  = wc_get_payment_gateway_by_order( $order );
			$post_mid = $post['MID'] ?? '';
			$post_mk  = $post['MerchantKey'] ?? '';

			if ( ! $gateway instanceof SHPG_Gateway_NicePay_VBank ) {
				throw new Exception( '$gateway is not an instance of ' . self::class );
			} elseif ( $post_mid !== $gateway->get_mid() ) {
				throw new Exception(
					'MID mismatch. Our setting: \'%s\', notified: \'%s\'',
					$gateway->get_mid(),
					$post_mid
				);
			} elseif ( $post_mk !== $gateway->get_merchant_key() ) {
				throw new Exception(
					'Merchant key mismatch. Our setting: \'%s\', notified: \'%s\'',
					substr( $gateway->get_merchant_key(), 0, 5 ) . '...',
					substr( $post_mk, 0, 5 ) . '...'
				);
			}

			// Check if TID and MOID are identical.
			$result = shpg_get_payment_result( $order );

			$post_tid    = $post['TID'] ?? '';
			$result_tid  = $result['TID'] ?? '';
			$post_moid   = $post['MOID'] ?? '';
			$result_moid = $result['Moid'] ?? ''; // Beware of cases.

			if ( ! $post_tid || $post_tid !== $result_tid ) {
				throw new Exception(
					'TID mismatch. Payment result: \'%s\', notified: \'%s\'',
					$result_tid,
					$post_tid
				);
			} elseif ( ! $post_moid || $post_moid !== $result_moid ) {
				throw new Exception(
					'MOID mismatch. Payment result: \'%s\', notified: \'%s\'',
					$result_moid,
					$post_moid
				);
			}

			// Result code 4110 means that payment is successful.
			// We trust that amount would be okay.
			$order->set_transaction_id( $post['TID'] );

			// Update order status.
			$gateway->update_order_status_by_setting( $order );

			// DO NOT expose merchant key.
			if ( isset( $post['MerchantKey'] ) ) {
				$post['MerchantKey'] = '';
			}

			shpg_post_meta()->notification_result->update( $order, $post );
		}
	}
}
