<?php
/**
 * SHPG: NicePay Payment API
 *
 * @package shpg
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_API_NicePay' ) ) {
	/**
	 * NicePay API class
	 */
	class SHPG_API_NicePay extends SHPG_API {
		const AUTH_URL   = 'https://webapi.nicepay.co.kr/webapi/pay_process.jsp';
		const CANCEL_URL = 'https://webapi.nicepay.co.kr/webapi/cancel_process.jsp';

		/**
		 * Get request form values.
		 *
		 * @param SHPG_Gateway_NicePay $gateway NicePay gateway instance.
		 * @param WC_Order             $order   Order instance.
		 *
		 * @return array
		 * @link   https://developers.nicepay.co.kr/manual-auth.php#parameter-auth-request-pc
		 * @link   https://developers.nicepay.co.kr/manual-auth.php#parameter-auth-request-mobile
		 */
		public function get_payment_request_params( SHPG_Gateway_NicePay $gateway, WC_Order $order ): array {
			$edi_date = shpg_get_datetime_string();

			$sign_data = static::generate_sign_data(
				$edi_date,
				$gateway->get_mid(),
				$order->get_total(),
				$gateway->get_merchant_key()
			);

			$has_virtual_products = array_reduce(
				$order->get_items(),
				fn( int $carry, WC_Order_Item_Product $item ) => $carry + intval( ( $product = $item->get_product() ) && $product->is_virtual() ),
				0
			);

			return [
				'GoodsName'   => shpg_summarize_order( $order ),
				'Amt'         => $order->get_total(),
				'MID'         => $gateway->get_mid(),
				'EdiDate'     => $edi_date,
				'Moid'        => shpg_generate_moid( $gateway->get_mid(), $order->get_id() ),
				'SignData'    => $sign_data,
				'ReqReserved' => $order->get_id(),
				'BuyerName'   => shpg_get_customer_name( $order ),
				'BuyerEmail'  => $order->get_billing_email(),
				'BuyerTel'    => $order->get_billing_phone(),
				'GoodsCl'     => $has_virtual_products > 0 ? '0' : '1',
				'PayMethod'   => $gateway->get_pay_method(),
				'CharSet'     => 'utf-8',
			];
		}

		/**
		 * Authorize request API
		 *
		 * @param string $mid          Merchant ID.
		 * @param string $merchant_key Merchant key.
		 * @param array  $args         Request API params.
		 *
		 * @return array
		 * @throws Exception Occurs when API call goes wrong.
		 *
		 * @link   https://developers.nicepay.co.kr/manual-auth.php#parameter-api-request
		 * @link   https://developers.nicepay.co.kr/manual-auth.php#parameter-api-response
		 */
		public function authorize( string $mid, string $merchant_key, array $args ): array {
			$defaults = [
				'TID'          => '',   // Required.
				'AuthToken'    => '',   // Required.
				'MID'          => $mid, // Required, but comes from $mid.
				'Amt'          => '',   // Required.
				'EdiDate'      => '',   // Required.
				'SignData'     => '',   // Required.
				'CharSet'      => 'utf-8',
				'EdiType'      => 'JSON',
				'MallReserved' => '', // Order ID must be present.
			];

			$args = wp_parse_args( $args, $defaults );

			if ( empty( $args['EdiDate'] ) ) {
				$args['EdiDate'] = shpg_get_datetime_string();
			}

			if ( empty( $args['SignData'] ) ) {
				$args['SignData'] = self::generate_sign_data(
					$args['AuthToken'] .
					$args['MID'] .
					$args['Amt'] .
					$args['EdiDate'] .
					$merchant_key
				);
			}

			$response = $this->parse_body(
				$this->request( self::AUTH_URL, 'post', $args ),
				$args['CharSet'],
				$args['EdiType'],
			);

			// Verify result code.
			$result_code = $response['ResultCode'] ?? '';
			if ( ! in_array( $result_code, [ '3001', '4000', '4100', 'A000', '7001' ], true ) ) {
				throw new Exception(
					sprintf(
					/* translators: 1: code, 2: message */
						__( 'Authorization call failed. %1$s: %2$s', 'shoplic-pg' ),
						$result_code,
						$response['ResultMsg']
					)
				);
			}

			// Verify signature.
			$expected = static::generate_sign_data(
				$response['TID'] ?? '',
				$response['MID'] ?? '',
				$response['Amt'] ?? '',
				$merchant_key
			);
			if ( ! hash_equals( $expected, $response['Signature'] ?? '' ) ) {
				throw new Exception( __( 'Authorization error. Signature mismatch.', 'shoplic-pg' ) );
			}

			return $response;
		}

		/**
		 * Cancel request API.
		 *
		 * @param string $mid          Merchant ID.
		 * @param string $merchant_key Merchant key.
		 * @param array  $args         Cancel API params.
		 *
		 * @return void
		 * @link   https://developers.nicepay.co.kr/manual-auth.php#parameter-netcancel-request
		 * @link   https://developers.nicepay.co.kr/manual-auth.php#parameter-netcancel-response
		 */
		public function cancel( string $mid, string $merchant_key, array $args ) {
			$defaults = [
				'TID'          => '', // Required.
				'AuthToken'    => '', // Required.
				'MID'          => $mid, // Required.
				'Amt'          => '', // Required.
				'EdiDate'      => '', // Required.
				'SignData'     => '', // Required.
				'NetCancel'    => '1',
				'EdiType'      => 'JSON',
				'CharSet'      => 'utf-8',
				'MallReserved' => '',
			];

			$args = wp_parse_args( $args, $defaults );

			if ( empty( $args['EdiDate'] ) ) {
				$args['EdiDate'] = shpg_get_datetime_string();
			}

			if ( empty( $args['SignData'] ) ) {
				$args['SignData'] = static::generate_sign_data(
					$args['AuthToken'],
					$mid,
					$args['Amt'],
					$args['EdiDate'],
					$merchant_key
				);
			}

			try {
				$this->parse_body(
					$this->request( self::CANCEL_URL, 'post', $args ),
					$args['CharSet'],
					$args['EdiType'],
				);
			} catch ( Exception $e ) {
				wp_die( esc_html( $e->getMessage() ) );
			}
		}

		/**
		 * Refund request API.
		 *
		 * @param string $mid          Merchant ID.
		 * @param string $merchant_key Merchant key.
		 * @param array  $args         Refund API params.
		 *
		 * @return array
		 *
		 * @throws Exception Occurs API call goes wrong.
		 *
		 * @link https://developers.nicepay.co.kr/manual-cancel.php#parameter-cancel-request
		 */
		public function refund( string $mid, string $merchant_key, array $args ): array {
			$defaults = [
				'TID'               => '', // Required.
				'MID'               => $mid, // Required.
				'Moid'              => '', // Required.
				'CancelAmt'         => '', // Required.
				'CancelMsg'         => '', // Required.
				'PartialCancelCode' => '', // Required.
				'EdiDate'           => '', // Required.
				'SignData'          => '', // Required.
				'CharSet'           => 'utf-8',
				'EdiType'           => 'JSON',
			];

			$args = wp_parse_args( $args, $defaults );

			if ( empty( $args['EdiDate'] ) ) {
				$args['EdiDate'] = shpg_get_datetime_string();
			}

			if ( empty( $args['SignData'] ) ) {
				$args['SignData'] = static::generate_sign_data(
					$args['MID'],
					$args['CancelAmt'],
					$args['EdiDate'],
					$merchant_key
				);
			}

			$body     = $this->request( self::CANCEL_URL, 'post', $args );
			$response = $this->parse_body( $body, $args['CharSet'], $args['EdiType'] );

			// Verify result code.
			$result_code = $response['ResultCode'] ?? '';
			if ( ! in_array( $result_code, [ '2001', '2211' ], true ) ) {
				throw new Exception(
					sprintf(
					/* translators: 1: code, 2: message */
						__( 'Refund call failed. %1$s: %2$s', 'shoplic-pg' ),
						$result_code,
						$response['ResultMsg']
					)
				);
			}

			return $response;
		}

		/**
		 * Generate a sign data.
		 *
		 * @param string ...$args Input strings.
		 *
		 * @return string
		 */
		protected static function generate_sign_data( ...$args ): string {
			return hash( 'sha256', implode( '', $args ) );
		}
	}
}
