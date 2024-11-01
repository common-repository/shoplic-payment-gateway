<?php
/**
 * SHPG: Payment data context trait
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! trait_exists( 'SHPG_Context_Impl' ) ) {
	trait SHPG_Context_Impl {
		/**
		 * Display NicePay payment data.
		 *
		 * @param WC_Order $order         Input order.
		 * @param string   $template_name Template name.
		 *
		 * @return void
		 */
		protected function render_payment_data( WC_Order $order, string $template_name ) {
			$gateway = wc_get_payment_gateway_by_order( $order );
			// Return if the gateway is not ours.
			if ( ! $gateway instanceof SHPG_Gateway_NicePay ) {
				return;
			}

			$info = shpg_get_payment_result( $order );
			// 거래 일시 중단 등으로 입금 대기 중 상태에서 넘어가지 않는 경우 이 정보가 없음.
			if ( empty( $info ) ) {
				return;
			}

			$test_mode    = (bool) shpg_post_meta()->test_mode->get_value( $order );
			$auth_code    = $info['AuthCode'] ?? '';
			$receipt_url  = shpg_get_receipt_url( $order->get_transaction_id() );
			$raw_data_url = add_query_arg(
				[
					'action'   => 'shpg_display_raw_data',
					'order_id' => $order->get_id(),
					'_wpnonce' => wp_create_nonce( 'shpg_display_raw_data' ),
				],
				admin_url( 'admin-post.php ' )
			);

			switch ( $info['PayMethod'] ?? '' ) {
				case 'CARD':
				case 'BILLING':
					$payment_method = _x( 'Credit Card', 'payment method', 'shoplic-pg' );
					$card_name      = $info['CardName'] ?? '';
					$card_no        = shpg_format_card_no( $info['CardNo'] ?? '' );
					$is_check       = ( $info['CardCl'] ?? '' ) === '1';

					$this->render(
						$template_name,
						[
							'payment_method' => $payment_method,
							'card_name'      => $card_name,
							'card_no'        => $card_no,
							'is_check'       => $is_check,
							'auth_code'      => $auth_code,
							'receipt_url'    => $receipt_url,
							'raw_data_url'   => $raw_data_url,
							'test_mode'      => $test_mode,
						],
						'card'
					);
					break;

				case 'BANK':
					$payment_method    = _x( 'Account Transfer', 'payment method', 'shoplic-pg' );
					$bank_name         = $info['BankName'] ?? '';
					$receipt_type      = shpg_get_receipt_type_string( $info['RcptType'] ?? '' );
					$receipt_tid       = $info['RcptTID'] ?? '';
					$receipt_auth_code = $info['RcptAuthCode'] ?? '';

					$this->render(
						$template_name,
						[
							'payment_method'    => $payment_method,
							'bank_name'         => $bank_name,
							'receipt_type'      => $receipt_type,
							'receipt_tid'       => $receipt_tid,
							'receipt_auth_code' => $receipt_auth_code,
							'auth_code'         => $auth_code,
							'receipt_url'       => $receipt_url,
							'raw_data_url'      => $raw_data_url,
							'test_mode'         => $test_mode,
						],
						'bank'
					);
					break;

				case 'VBANK':
					$payment_method = _x( 'Virtual bank account', 'payment method', 'shoplic-pg' );
					$bank_name      = $info['VbankBankName'] ?? '';
					$account_number = $info['VbankNum'] ?? '';
					$expiration     = shpg_format_vbank_expiration( $info );

					$notification = shpg_post_meta()->notification_result->get_value( $order );
					if ( is_string( $notification ) ) {
						$notification = json_decode( $notification, true );
					}
					$notification_result = $notification['ResultMsg'] ?? '';

					$this->render(
						$template_name,
						[
							'payment_method'      => $payment_method,
							'bank_name'           => $bank_name,
							'account_number'      => $account_number,
							'expiration'          => $expiration,
							'auth_code'           => $auth_code,
							'receipt_url'         => $receipt_url,
							'raw_data_url'        => $raw_data_url,
							'notification_result' => $notification_result,
							'test_mode'           => $test_mode,
						],
						'vbank'
					);
					break;

				case 'CELLPHONE':
					$payment_method = _x( 'Mobile Phone', 'payment method', 'shoplic-pg' );
					$this->render(
						$template_name,
						[
							'payment_method' => $payment_method,
							'auth_code'      => $auth_code,
							'receipt_url'    => $receipt_url,
							'raw_data_url'   => $raw_data_url,
							'test_mode'      => $test_mode,
						]
					);
					break;

				default:
					$payment_method = _x( 'Unknown', 'payment method', 'shoplic-pg' );
					$this->render(
						$template_name,
						[
							'payment_method' => $payment_method,
							'auth_code'      => $auth_code,
							'receipt_url'    => $receipt_url,
							'raw_data_url'   => $raw_data_url,
							'test_mode'      => $test_mode,
						]
					);
					break;
			}
		}
	}
}
