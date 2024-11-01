<?php
/**
 * SHPG: WooCommerce module
 *
 * @package shpg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_WooCommerce' ) ) {
	/**
	 * Module for WooCommerce
	 */
	class SHPG_WooCommerce implements SHPG_Module {
		use SHPG_Hook_Impl;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this
				->add_filter( 'woocommerce_payment_gateways' )
				->add_filter( 'woocommerce_data_stores' )
				->add_filter( 'woocommerce_payment_token_class', null, null, 2 )
			;
		}

		/**
		 * Append our payment gateways
		 *
		 * @param WC_Payment_Gateway[] $gateways Currently defined payement gateways.
		 *
		 * @return WC_Payment_Gateway[]
		 */
		public function woocommerce_payment_gateways( array $gateways ): array {
			return [
				...$gateways,
				SHPG_Gateway_NicePay_Card::class,
				SHPG_Gateway_NicePay_Bank::class,
				SHPG_Gateway_NicePay_VBank::class,
				SHPG_Gateway_NicePay_CellPhone::class,
				SHPG_Gateway_NicePay_Billing::class,
				SHPG_Gateway_Kakao_Pay::class,
			];
		}

		/**
		 * Add our datastore class
		 *
		 * @callback
		 * @filter   woocommerce_data_stores
		 *
		 * @param array $stores
		 *
		 * @return array
		 */
		public function woocommerce_data_stores( array $stores ): array {
			$stores['shpg-payment-token'] = SHPG_Payment_Token_Data_Store::class;

			return $stores;
		}

		/**
		 * Make WC_Payment_Tokens::get() create an instance of SHPG_Payment_Token_Billing if $type = 'shpg_billing'
		 *
		 * @callback
		 * @filter   woocommerce_payment_token_class
		 *
		 * @param string $class
		 * @param string $type
		 *
		 * @return string
		 * @see      WC_Payment_Tokens::get()
		 */
		public function woocommerce_payment_token_class( string $class, string $type ): string {
			if ( 'shpg_nicepay_billing' === $type ) {
				$class = SHPG_Payment_Token_Billing::class;
			}

			return $class;
		}

		/**
		 * Import MID from other payment method setup.
		 *
		 * @callback
		 * @action    wp_ajax_shpg_request_import_mid
		 *
		 * @return void
		 *
		 * @used-by   SHPG_Register_Ajax::get_items()
		 */
		public function response_import_mid() {
			check_ajax_referer( 'shpg-nicepay-cred-import' );

			$value = $this->get_option_value( 'mid', sanitize_key( $_POST['from'] ?? '' ) );

			if ( current_user_can( 'manage_woocommerce' ) && $value ) {
				wp_send_json_success( [ 'mid' => $value ] );
			}
		}

		/**
		 * Import merchant key from other payment method setup.
		 *
		 * @callback
		 * @action    wp_ajax_shpg_request_import_merchant_key
		 *
		 * @return void
		 *
		 * @used-by   SHPG_Register_Ajax::get_items()
		 */
		public function response_import_merchant_key() {
			check_ajax_referer( 'shpg-nicepay-cred-import' );

			$value = $this->get_option_value( 'merchant_key', sanitize_key( $_POST['from'] ?? '' ) );
			if ( current_user_can( 'manage_woocommerce' ) && $value ) {
				wp_send_json_success( [ 'merchantKey' => $value ] );
			}
		}

		/**
		 * Respond to NicePay notification.
		 *
		 * @callback
		 *
		 * @used-by SHPG_Register_Rewrite_Rule::get_items()
		 */
		public function noti_vbank() {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( 'POST' === wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
				try {
					SHPG_Gateway_NicePay_VBank::handle_notification_response();
					echo 'OK';
				} catch ( Exception $e ) {
					die( esc_html( $e->getMessage() ) );
				}
			} else {
				echo '<h1>Shoplic NicePlay VBANK notification</h1>';
			}
		}

		/**
		 * Respond to wc-ajax=shpg_get_user_cards
		 *
		 * @callback
		 * @action    wc_ajax_shpg_get_user_cards
		 *
		 * @used-by   SHPG_Register_Ajax::get_items()
		 */
		public function get_user_cards() {
			check_ajax_referer( 'shpg-nonce', 'nonce' );

			$cards = [];

			if ( is_user_logged_in() ) {
				$tokens = SHPG_Payment_Tokens::get_tokens( [ 'user_id' => get_current_user_id() ] );

				if ( $tokens ) {
					$cards = array_map(
						fn( SHPG_Payment_Token_Billing $token ) => [
							'id'        => $token->get_id(),
							'card_name' => $token->get_card_name(),
							'card_no'   => shpg_format_card_no( $token->get_card_no() ),
							'is_check'  => $token->get_card_cl(),
						],
						$tokens,
					);
				}
			}

			wp_send_json_success( array_values( $cards ) );
		}

		/**
		 * @callback
		 * @action   wc_ajax_shpg_remove_user_card
		 *
		 * @used-by  SHPG_Register_Ajax::get_items()
		 *
		 * @throws Exception
		 */
		public function remove_billing_key() {
			check_ajax_referer( 'shpg-nonce', 'nonce' );

			$user     = wp_get_current_user();
			$token_id = intval( $_REQUEST['token_id'] ?? '0' );
			$token    = SHPG_Payment_Tokens::get( $token_id );

			if ( $user->exists() && $token && $token->get_user_id() === $user->ID ) {
				$option_name  = SHPG_Gateway_NicePay::get_raw_option_name( 'billing' );
				$option_value = get_option( $option_name, [] );

				$mid          = $option_value['mid'] ?? '';
				$merchant_key = $option_value['merchant_key'] ?? '';
				$test_mode    = 'yes' === ( $option_value['test_mode'] ?? 'no' );

				if ( $test_mode ) {
					$mid          = SHPG_Gateway_NicePay_Billing::TEST_MID;
					$merchant_key = SHPG_Gateway_NicePay_Billing::TEST_MERCHANT_KEY;
				}

				try {
					$result = shpg()->api->nicepay_billing->delete(
						$mid,
						$merchant_key,
						[ 'BID' => $token->get_token() ]
					);

					$result_code = $result['ResultCode'] ?? '';
					$result_msg  = $result['ResultMsg'] ?? '';

					if ( 'F101' !== $result_code ) {
						throw new Exception( sprintf( '[%s] %s', $result_code, $result_msg ) );
					}
				} catch ( Exception $e ) {
					wp_send_json_error( new WP_Error( 'error', $e->getMessage() ) );
				}

				SHPG_Payment_Tokens::delete( $token->get_id() );
				wp_send_json_success();
			}
		}

		/**
		 * Get option value from another option.
		 *
		 * @param string $key  Key name.
		 * @param string $from Another payment method name.
		 *
		 * @return mixed
		 */
		protected function get_option_value( string $key, string $from ) {
			if ( in_array( $from, SHPG_Gateway_NicePay::get_all_avail_types(), true ) ) {
				$source = get_option( SHPG_Gateway_NicePay::get_raw_option_name( $from ), [] );
				if ( $source && isset( $source[ $key ] ) ) {
					return $source[ $key ];
				}
			}

			return false;
		}
	}
}
