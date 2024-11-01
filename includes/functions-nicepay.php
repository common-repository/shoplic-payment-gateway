<?php
/**
 * SHPG: NicePay functions
 *
 * @package sphg
 */

if ( ! function_exists( 'shpg_generate_moid' ) ) {
	/**
	 * Generate merchant order ID.
	 *
	 * Max 64 chars.
	 *
	 * @param string $mid      MID. Usually 10 chars.
	 * @param int    $order_id Order ID. Enter zero or negative number to generate random string.
	 *                         - MySQL BIGINT Unsigned: 0 ~ 18446744073709551615 (20 chars).
	 *                         - PHP_INT_MAX          : 0 ~  9223372036854775807 (19 chars).
	 *
	 * @return string
	 */
	function shpg_generate_moid( string $mid, int $order_id ): string {
		// MID:      10 chars  'nictest00m'
		// SEP:       1 char   ':'
		// OID:      20 chars
		// SEP:       1 char   ':'
		// Datetime: 19 chars  '2021-05-01-22-12-05'
		// SEP:       1 char   ':'
		// Remain:   12 chars
		// ---------------------------------------------
		// Total:    64 chars.
		$mid = shpg_str_limit( $mid, 10 );
		$sep = ':';

		if ( $order_id > 0 ) {
			$oid = sprintf( '%020d', $order_id );
		} else {
			$oid = sprintf( 'shpg-rand-%s', wp_generate_password( 10, false ) );
		}

		$date    = shpg_get_datetime_string();
		$padding = strtolower( wp_generate_password( 12, false ) );

		return shpg_str_limit( $mid . $sep . $oid . $sep . $date . $sep . $padding, 64 );
	}
}


if ( ! function_exists( 'shpg_get_payment_result' ) ) {
	/**
	 * Get payment result
	 *
	 * Wrapper function of get_post_meta( '_shpg_paymennt_result', ... )
	 *
	 * @param int|WC_Order $order Order.
	 */
	function shpg_get_payment_result( $order ): array {
		$result = shpg_post_meta()->payment_result->get_value( $order );

		if ( is_string( $result ) ) {
			$result = json_decode( $result, true );
		}

		return is_array( $result ) ? $result : [];
	}
}


if ( ! function_exists( 'shpg_get_refund_results' ) ) {
	/**
	 * Get refund results
	 *
	 * Wrapper function of get_post_meta( '_shpg_refund_result', ... )
	 *
	 * @param int|WC_Order $order Order.
	 */
	function shpg_get_refund_results( $order ): array {
		$result = shpg_post_meta()->refund_result->get_value( $order );

		if ( is_string( $result ) ) {
			$result = json_decode( $result, true );
		}

		return is_array( $result ) ? $result : [];
	}
}


if ( ! function_exists( 'shpg_get_notification_result' ) ) {
	/**
	 * Get notification result
	 *
	 * Wrapper function of get_post_meta( '_shpg_noti_result', ... )
	 *
	 * @param int|WC_Order $order Order.
	 */
	function shpg_get_notification_result( $order ): array {
		$result = shpg_post_meta()->notification_result->get_value( $order );

		if ( is_string( $result ) ) {
			$result = json_decode( $result, true );
		}

		return is_array( $result ) ? $result : [];
	}
}


if ( ! function_exists( 'shpg_get_receipt_url' ) ) {
	/**
	 * Return a receipt URL by given transaction ID.
	 *
	 * @param string $tid Transaction ID.
	 *
	 * @return string
	 */
	function shpg_get_receipt_url( string $tid ): string {
		if ( $tid ) {
			return add_query_arg(
				[
					'tid'      => rawurlencode( $tid ),
					'type'     => '0',
					'InnerWin' => 'N',
				],
				'https://npg.nicepay.co.kr/issue/IssueLoader.do'
			);
		} else {
			return '';
		}
	}
}


if ( ! function_exists( 'shpg_format_vbank_expiration' ) ) {
	/**
	 * Format virtual bank account expiration string.
	 *
	 * @param array $info Information array.
	 *
	 * @return string
	 */
	function shpg_format_vbank_expiration( array $info ): string {
		$date       = $info['VbankExpDate'] ?? '';
		$time       = $info['VbankExpTime'] ?? '';
		$expiration = '';

		if ( $date && $time ) {
			$datetime = date_create_immutable_from_format( 'YmdHis', $date . $time, new DateTimeZone( 'Asia/Seoul' ) );
			if ( $datetime ) {
				$expiration = wp_date(
					get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
					$datetime->getTimestamp()
				);
			}
		}

		return $expiration;
	}
}


if ( ! function_exists( 'shpg_get_receipt_type_string' ) ) {
	/**
	 * Return cash bill (receipt) type string from code.
	 *
	 * @param string $code Receipt code.
	 *
	 * @return string
	 */
	function shpg_get_receipt_type_string( string $code ): string {
		switch ( $code ) {
			case '0':
				$receipt_type = _x( 'Do not issue.', '현금영수증: 발행안함.', 'shoplic-pg' );
				break;

			case '1':
				$receipt_type = _x( 'Income deduction.', '현금영수증: 소득공제용.', 'shoplic-pg' );
				break;

			case '2':
				$receipt_type = _x( 'Proof of Expenditure.', '현금영수증: 지출증빙용.', 'shoplic-pg' );
				break;

			default:
				$receipt_type = '';
		}

		return $receipt_type;
	}
}


if ( ! function_exists( 'shpg_generate_card_hash' ) ) {
	/**
	 * Generate card hash string
	 *
	 * @param string $card_no      Card number.
	 * @param string $mid          Merchant ID.
	 * @param string $merchant_key Merchant key.
	 *
	 * @return string
	 */
	function shpg_generate_card_hash( string $card_no, string $mid, string $merchant_key ): string {
		$salt = "$mid:$merchant_key:" . substr( $card_no, - 4 );

		return md5( $salt . $card_no );
	}
}
