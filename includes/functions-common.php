<?php
/**
 * SHPG: Common functions
 *
 * @package shpg
 */

if ( ! defined( 'shpg_get_auto_updatable_order_statuses' ) ) {
	/**
	 * Return auto-updatable order statuses.
	 *
	 * @return array<string, string>
	 */
	function shpg_get_auto_updatable_order_statuses(): array {
		$status = array_diff_key(
			wc_get_order_statuses(),
			[
				'wc-cancelled' => '',
				'wc-failed'    => '',
				'wc-on-hold'   => '',
				'wc-pending'   => '',
				'wc-refunded'  => '',
			]
		);

		return apply_filters( 'shpg_get_auto_updatable_order_statuses', $status );
	}
}


if ( ! function_exists( 'shpg_sanitize_api_call_result' ) ) {
	/**
	 * Sanitize and stringify API call result.
	 *
	 * @param mixed $value Input value.
	 *
	 * @return string JSON encoded string
	 */
	function shpg_sanitize_api_call_result( $value ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			$encoded = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
			if ( $encoded ) {
				return $encoded;
			} else {
				return '';
			}
		} elseif ( is_string( $value ) ) {
			return $value;
		} else {
			return '';
		}
	}
}


if ( ! function_exists( 'shpg_summarize_order' ) ) {
	/**
	 * Summarize order for payment.
	 *
	 * Generally, you can enter a description about your customer's order when you call payment API.
	 * This function is for the parameter.
	 *
	 * @param WC_Order $order The order instance.
	 *
	 * @return string A string like: 'Foo product' (single), or 'Foo product and N items' (multiple).
	 */
	function shpg_summarize_order( WC_Order $order ): string {
		$names = array_values(
			array_map(
				function ( $item ) {
					return $item->get_name();
				},
				$order->get_items()
			)
		);

		if ( count( $names ) > 1 ) {
			return sprintf(
			/* translators: %1$s: the first item name, %2$d: the number of other items. */
				__( '%1$s and %2$d items', 'shoplic-pg' ),
				$names[0],
				count( $names ) - 1
			);
		} else {
			return $names[0];
		}
	}
}


if ( ! function_exists( 'shpg_get_customer_name' ) ) {
	/**
	 * Extract a customer name from a given order.
	 *
	 * @param WC_Order $order Input order.
	 * @param string   $field 'billing', or 'shipping'.
	 *
	 * @return string Customer name.
	 */
	function shpg_get_customer_name( WC_Order $order, string $field = 'billing' ): string {
		if ( 'billing' !== $field && 'shipping' !== $field ) {
			$field = 'billing';
		}

		$last  = trim( $order->{"get_{$field}_last_name"}() );
		$first = trim( $order->{"get_{$field}_first_name"}() );

		if ( $first && $last ) {
			return ( shpg_is_korean( $first ) && shpg_is_korean( $last ) ) ? "$last$first" : "$first $last";
		} elseif ( $first ) {
			return $first;
		} elseif ( $last ) {
			return $last;
		} else {
			return '';
		}
	}
}


if ( ! function_exists( 'shpg_get_total_quantity' ) ) {
	/**
	 * Return total number of order item quantities.
	 *
	 * @param WC_Order $order
	 *
	 * @return int
	 */
	function shpg_get_total_quantity( WC_Order $order ): int {
		return array_reduce(
			$order->get_items(),
			fn( int $carry, WC_Order_Item $item ) => $carry + $item->get_quantity(),
			0
		);
	}
}


if ( ! function_exists( 'shpg_is_korean' ) ) {
	/**
	 * Test if input string is purely consited of korean alphabets.
	 *
	 * @param string $input Input string.
	 *
	 * @return bool
	 */
	function shpg_is_korean( string $input ): bool {
		return preg_match( '/^[ㄱ-ㅣ|가-힣]+$/', $input );
	}
}


if ( ! function_exists( 'shpg_str_limit' ) ) {
	/**
	 * Limit string by length.
	 *
	 * @param string $input Input string.
	 * @param int    $limit Maximum byte.
	 *
	 * @return string
	 */
	function shpg_str_limit( string $input, int $limit ): string {
		return substr( $input, 0, $limit );
	}
}


if ( ! function_exists( 'shpg_get_datetime_string' ) ) {
	/**
	 * Return a datetime string.
	 *
	 * @param int|string|DateTime|DateTimeImmutable|null $datetime Input data.
	 * @param string                                     $format   Output format. Defaults to 'YmdHis'.
	 *
	 * @return string
	 */
	function shpg_get_datetime_string( $datetime = null, string $format = 'YmdHis' ): string {
		if ( is_null( $datetime ) ) {
			$datetime = date_create_immutable( 'now', wp_timezone() );
		} elseif ( is_numeric( $datetime ) ) {
			$timestamp = intval( $datetime );
			if ( $timestamp ) {
				$datetime = date_create_immutable( "@$timestamp", wp_timezone() );
			}
		} elseif ( is_string( $datetime ) ) {
			$datetime = date_create_immutable( $datetime, wp_timezone() );
		}

		if ( ! $format ) {
			$format = 'YmdHis';
		}

		if ( $datetime instanceof DateTime || $datetime instanceof DateTimeImmutable ) {
			return $datetime->format( $format );
		} else {
			return '';
		}
	}
}


if ( ! function_exists( 'shpg_validate_datetime' ) ) {
	/**
	 * Validate input string is formatted datetime string.
	 *
	 * @param string $input  Input string.
	 * @param string $format Datetime format.
	 *
	 * @return bool
	 * @see https://www.php.net/manual/en/datetime.createfromformat.php#refsect1-datetime.createfromformat-parameters
	 */
	function shpg_validate_datetime( string $input, string $format = 'YmdHis' ): bool {
		$datetime = date_create_immutable_from_format( $format, $input );

		return $datetime && $datetime->format( $format ) === $input;
	}
}


if ( ! function_exists( 'shpg_strip_non_number' ) ) {
	/**
	 * Strip out non-number string.
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	function shpg_strip_non_number( string $input ): string {
		return preg_replace( '/[^0-9]/', '', $input );
	}
}


if ( ! function_exists( 'shpg_format_card_no' ) ) {
	/**
	 * Format card string.
	 *
	 * @param string $card_no Input card number.
	 * @param string $glue    Cancatenation string.
	 *
	 * @return string
	 */
	function shpg_format_card_no( string $card_no, string $glue = '-' ): string {
		$trim = preg_replace( '/[^0-9*]/', '', $card_no );

		if ( 16 === strlen( $trim ) ) {
			$output = implode(
				$glue,
				[
					substr( $trim, 0, 4 ),
					substr( $trim, 4, 4 ),
					substr( $trim, 8, 4 ),
					substr( $trim, 12, 4 ),
				]
			);
		} else {
			$output = $card_no;
		}

		return $output;
	}
}


if ( ! function_exists( 'shpg_mask_card_no' ) ) {
	/**
	 * Mask a card number
	 *
	 * @param string $card_no A card number to mask. It should be a 16-digit numbers ('*' is allowed)
	 *                        If not, returned as-is.
	 *
	 * @return string
	 */
	function shpg_mask_card_no( string $card_no ): string {
		$trimmed = preg_replace( '/[^0-9*]/', '', $card_no );

		if ( 16 === strlen( $trimmed ) ) {
			return substr( $trimmed, 0, 8 ) . '****' . substr( $trimmed, - 4 );
		}

		return $card_no;
	}
}


if ( ! function_exists( 'shpg_validate_card_expire' ) ) {
	/**
	 * Validate card_expire 'MM / YY'
	 *
	 * @param string $card_expire
	 *
	 * @return array
	 * @throws Exception
	 */
	function shpg_validate_card_expire( string $card_expire ): array {
		if ( ! preg_match( ';^(0[1-9]|1[0-2])\s*/\s*(\d{2})$;', $card_expire, $match ) ) {
			throw new Exception( __( 'Invalid card expire format', 'shoplic-pg' ) );
		}

		$m = absint( $match[1] );
		$y = absint( $match[2] );

		if ( ! $m || $m < 1 || $m > 12 ) {
			throw new Exception( __( 'Expire month is invalid.', 'shoplic-pg' ) );
		} elseif ( ! $y ) {
			throw new Exception( __( 'Expire year is invalid.', 'shoplic-pg' ) );
		}

		$exp_month = sprintf( '%02d', $m );
		$exp_year  = strval( $y );

		return [ $exp_month, $exp_year ];
	}
}
