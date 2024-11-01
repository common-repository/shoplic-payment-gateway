<?php
/**
 *
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Payment_Tokens' ) ) {
	require_once dirname( WC_PLUGIN_FILE ) . '/includes/class-wc-payment-tokens.php';
}

if ( ! class_exists( 'SHPG_Payment_Tokens' ) ) {
	class SHPG_Payment_Tokens extends WC_Payment_Tokens {
		/**
		 * Get token record by 'card_hash' meta value.
		 *
		 * @param int    $user_id
		 * @param string $hash
		 *
		 * @return SHPG_Payment_Token_Billing|null
		 * @see    SHPG_WooCommerce::token_class()
		 */
		public static function get_customer_token_by_card_hash( int $user_id, string $hash ): ?SHPG_Payment_Token_Billing {
			try {
				/** @var SHPG_Payment_Token_Data_Store $data_store */
				$data_store = WC_Data_Store::load( 'shpg-payment-token' );
				$token_id   = $data_store->get_token_id_by_card_hash( $user_id, $hash );

				/** @var SHPG_Payment_Token_Billing|null $result */
				$result = self::get( $token_id );
			} catch ( Exception $e ) {
				return null;
			}

			return $result;
		}

		public static function add_new_token_from_response(
			array $params,
			int $user_id,
			string $card_no,
			string $card_hash
		): SHPG_Payment_Token_Billing {
			$token = self::get_customer_token_by_card_hash( $user_id, $card_hash );

			if ( ! $token ) {
				$token = new SHPG_Payment_Token_Billing();

				// Base properties.
				$token->set_gateway_id( 'shpg_nicepay_billing' );
				$token->set_token( $params['BID'] ?? '' );
				$token->set_default( false );
				$token->set_type( 'shpg_nicepay_billing' );
				$token->set_user_id( $user_id );

				// Extended properties.
				$token->set_card_no( $card_no ); // set_card_no() will mask numbers.
				$token->set_card_code( $params['CardCode'] ?? '' );
				$token->set_card_name( $params['CardName'] ?? '' );
				$token->set_acqu_card_code( $paarams['AcquCardCode'] ?? '' );
				$token->set_acqu_card_name( $paarams['AcquCardName'] ?? '' );
				$token->set_card_cl( $paarams['CardCl'] ?? '' );
				$token->set_auth_date( $paarams['AuthDate'] ?? '' );
				$token->set_tid( $paarams['TID'] ?? '' );
				$token->set_card_hash( $card_hash );

				$token->save();
			}

			return $token;
		}
	}
}
