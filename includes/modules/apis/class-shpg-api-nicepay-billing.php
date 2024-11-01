<?php
/**
 * SHPG: NicePay billing API
 *
 * @package shpg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_API_NicePay_Billing' ) ) {
	/**
	 * NicePay billing API class
	 */
	class SHPG_API_NicePay_Billing extends SHPG_API {
		const URL = 'https://webapi.nicepay.co.kr/webapi';

		/**
		 * Constructor method
		 *
		 * @throws Exception
		 */
		public function __construct() {
			if ( ! in_array( 'sha256', hash_algos(), true ) ) {
				throw new Exception( 'Hash: \'sha256\' is not available.' );
			} elseif ( ! function_exists( 'openssl_encrypt' ) ) {
				throw new Exception( 'Function \'openssl_encrypt()\' is not available.' );
			} elseif ( ! in_array( 'aes-128-ecb', openssl_get_cipher_methods(), true ) ) {
				throw new Exception( 'OpenSSL: \'aes-128-ecb\' is not available.' );
			} elseif ( ! function_exists( 'mb_convert_encoding' ) ) {
				throw new Exception( 'Function \'mb_convert_encoding()\' is not available.' );
			}
		}

		/**
		 * Request API without authorization.
		 *
		 * @param string $mid          Merchant ID.
		 * @param string $merchant_key Merchant key.
		 * @param array  $args         API arguments.
		 * @param array  $enc_data     Data to be encrypted.
		 *
		 * @return array
		 * @throws Exception
		 *
		 * @link
		 */
		public function request_no_auth( string $mid, string $merchant_key, array $args, array $enc_data ): array {
			// Be sure that keys are arranged in this order.
			$encdata_defaults = [
				'CardNo'   => '', // Required. Enter only numbers.
				'ExpYear'  => '', // Required. Fommat: YY.
				'ExpMonth' => '', // Required. Format: MM.
				'IDNo'     => '', // Format: YYMMDD as Birthday, or 10-digit as business number.
				'CardPw'   => '', // First 2-digits of the password.
			];

			$args_defaults = [
				'MID'      => $mid, // Required.
				'EdiDate'  => '', // Required. Generated.
				'Moid'     => '', // Required. Generated.
				'CharSet'  => 'utf-8',
				'EdiType'  => 'JSON',
				'SignData' => '',
				'EncData'  => '',
			];

			$enc_data = wp_parse_args( $enc_data, $encdata_defaults );
			$args     = wp_parse_args( $args, $args_defaults );

			if ( ! empty( $args['EdiDate'] ) ) {
				$args['EdiDate'] = shpg_get_datetime_string();
			}

			$args['SignData'] = $this->generate_sign_data( $mid, $args['EdiDate'], $args['Moid'], $merchant_key );
			$args['EncData']  = $this->generate_enc_data( $enc_data, $merchant_key );

			return $this->send_request( self::URL . '/billing/billing_regist.jsp', 'post', $args );
		}

		/**
		 * Payment API
		 *
		 * @param string $mid          Merchant ID.
		 * @param string $merchant_key Merchant key.
		 * @param array  $args         Arguments. See the documentation.
		 *
		 * @return array
		 * @throws Exception
		 */
		public function payment( string $mid, string $merchant_key, array $args ): array {
			$defaults = [
				'BID'          => '',   // Required.
				'MID'          => $mid, // Required.
				'TID'          => '',   // Required. Generated.
				'EdiDate'      => '',   // Required. Generated.
				'Moid'         => '',   // Required. Generated.
				'Amt'          => '',   // Required.
				'GoodsName'    => '',   // Required.
				'CardInterest' => '0',  // Required.
				'CardQuota'    => '00', // Required.
				'CardPoint'    => '0',
				'BuyerName'    => '',
				'BuyerEmail'   => '',
				'BuyerTel'     => '',
				'MallReserved' => '',
				'CharSet'      => 'utf-8',
				'EdiType'      => 'JSON',
				'SignData'     => '',
			];

			$args = wp_parse_args( $args, $defaults );

			// Trim arguments
			$args['GoodsName']    = shpg_str_limit( $args['GoodsName'], 40 );
			$args['BuyerName']    = shpg_str_limit( $args['BuyerName'], 30 );
			$args['BuyerEmail']   = shpg_str_limit( $args['BuyerEmail'], 60 );
			$args['BuyerTel']     = shpg_str_limit( $args['BuyerTel'], 40 );
			$args['MallReserved'] = shpg_str_limit( $args['MallReserved'], 500 );

			$args['SignData'] = $this->generate_sign_data(
				$mid,
				$args['EdiDate'],
				$args['Moid'],
				$args['Amt'],
				$args['BID'],
				$merchant_key
			);

			return $this->send_request( self::URL . '/billing/billing_approve.jsp', 'POST', $args );
		}

		/**
		 * Cancel API
		 *
		 * @param string $mid          Merchant ID.
		 * @param string $merchant_key Merchant key.
		 * @param array  $args         Arguments. See the documentation.
		 *
		 * @return array
		 * @throws Exception
		 */
		public function cancel( string $mid, string $merchant_key, array $args ): array {
			$defaults = [
				'TID'               => '',   // Required.
				'MID'               => $mid, // Required.
				'Moid'              => '',   // Required.
				'CancelAmt'         => '',   // Required.
				'CancelMsg'         => '',   // Required.
				'PartialCancelCode' => '',   // Required.
				'EdiDate'           => '',   // Required.
				'MallReserved'      => '',
				'CharSet'           => 'utf-8',
				'EdiType'           => 'JSON',
				'SignData'          => '',
			];

			$args = wp_parse_args( $args, $defaults );

			if ( empty( $args['EdiDate'] ) ) {
				$args['EdiDate'] = shpg_get_datetime_string();
			}

			// Trim arguments
			$args['CancelMsg']    = shpg_str_limit( $args['CancelMsg'], 100 );
			$args['MallReserved'] = shpg_str_limit( $args['MallReserved'], 500 );

			$args['SignData'] = $this->generate_sign_data(
				$mid,
				$args['CancelAmt'],
				$args['EdiDate'],
				$merchant_key
			);

			return $this->send_request( self::URL . '/cancel_process.jsp', 'POST', $args );
		}

		/**
		 * Billing key delete API
		 *
		 * @param string $mid          Merchant ID.
		 * @param string $merchant_key Merchant key.
		 * @param array  $args         Arguments. See the documentation.
		 *
		 * @return array
		 * @throws Exception
		 */
		public function delete( string $mid, string $merchant_key, array $args ): array {
			$defaults = [
				'BID'      => '',   // Required.
				'MID'      => $mid, // Required.
				'EdiDate'  => '',   // Required.
				'Moid'     => '',   // Required.
				'SignData' => '',   // Required.
				'CharSet'  => 'utf-8',
				'EdiType'  => 'JSON',
			];

			$args = wp_parse_args( $args, $defaults );

			if ( empty( $args['EdiDate'] ) ) {
				$args['EdiDate'] = shpg_get_datetime_string();
			}

			if ( empty( $args['Moid'] ) ) {
				$args['Moid'] = shpg_generate_moid( $mid, 0 );
			}

			$args['SignData'] = $this->generate_sign_data(
				$mid,
				$args['EdiDate'],
				$args['Moid'],
				$args['BID'],
				$merchant_key
			);

			return $this->send_request( self::URL . '/billing/billkey_remove.jsp', 'POST', $args );
		}

		/**
		 * Wrapper method of request(), and parse_body()
		 *
		 * @param string $url    URL to send.
		 * @param string $method Method.
		 * @param array  $args   Arguments.
		 *
		 * @return array
		 * @throws Exception
		 */
		protected function send_request( string $url, string $method, array $args ): array {
			// Convert charset: from utf-8 to euc-kr.
			$args = mb_convert_encoding( $args, 'EUC-KR', 'UTF-8' );

			// Filter empty keys and values.
			$args = array_filter( $args, fn( $arg ) => strlen( $arg ) > 0 );

			$body = $this->request( $url, $method, $args );

			return $this->parse_body( $body, $args['CharSet'], $args['EdiType'] );
		}

		/**
		 * Generate sign-data string
		 *
		 * @param array ...$args Input data.
		 *
		 * @return string
		 */
		protected function generate_sign_data( ...$args ): string {
			return hash( 'sha256', implode( '', $args ) );
		}

		/**
		 * Generate encrypted data
		 *
		 * @param array  $input        Input array.
		 * @param string $merchant_key Merchant key.
		 *
		 * @return string
		 */
		protected function generate_enc_data( array $input, string $merchant_key ): string {
			$data = openssl_encrypt(
				build_query( array_filter( $input ) ),
				'aes-128-ecb',
				substr( $merchant_key, 0, 16 ),
				OPENSSL_RAW_DATA
			);

			return bin2hex( $data );
		}
	}
}
