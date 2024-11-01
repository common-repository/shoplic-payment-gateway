<?php
/**
 * SHPG: NicePay payment gateway: billing
 *
 * @package shpg
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Gateway_NicePay_Billing' ) ) {
	class SHPG_Gateway_NicePay_Billing extends SHPG_Gateway_NicePay {
		const TEST_MID          = 'nictest04m';
		const TEST_MERCHANT_KEY = 'b+zhZ4yOZ7FsH8pm5lhDfHZEb79tIwnjsdA0FBXh86yLc6BJeFVrZFXhAoJ3gEWgrWwN+lJMV0W4hvDdbe4Sjw==';

		/**
		 * Note that this value is null until validate_fields(), or subscription_payment() is called.
		 *
		 * @var SHPG_Payment_Token_Billing|null
		 * @see validate_fields()
		 */
		protected ?SHPG_Payment_Token_Billing $token;

		public function __construct() {
			$this->id                 = 'shpg_nicepay_billing';
			$this->method_title       = _x( 'NicePay billing', 'NicePay billing method', 'shoplic-pg' );
			$this->method_description = __( 'You can make a billing key payment or a subscription payment.', 'shoplic-pg' );
			$this->pay_method         = 'BILLLING';
			$this->token              = null;

			parent::__construct();

			// Override supports after __construct()
			$this->supports = [
				'products',
				'subscriptions',
				'subscription_cancellation',
				'subscription_suspension',
				'subscription_reactivation',
				'subscription_payment_method_change_customer',
				'refunds',
			];

			$this
				->add_action( 'wp_enqueue_scripts' )
				->add_action( "woocommerce_scheduled_subscription_payment_$this->id", 'subscription_payment', null, 2 )
			;
		}

		/**
		 * Initialize form fields
		 *
		 * @return void
		 */
		public function init_form_fields() {
			parent::init_form_fields();

			$this->form_fields['title']['default']       = _x( 'Billing', 'Method title', 'shoplic-pg' );
			$this->form_fields['description']['default'] = _x( 'Shoplic NicePay payment gateway, billing key.', 'Method description', 'shoplic-pg' );
		}

		/**
		 * Enqueue assets.
		 */
		public function wp_enqueue_scripts() {
			if ( is_checkout() && $this->is_available() ) {
				$this
					->script( 'shpg-nicepay-billing' )
					->localize(
						[
							'ajaxUrl'           => add_query_arg( 'wc-ajax', '###ENDPOINT###', home_url() ),
							// Do not expose this nonce value, because it is currently alpha stage feature.
							// 'nonce'             => wp_create_nonce( 'shpg-nonce' ),
							'isCheckoutPayPage' => is_checkout_pay_page() ? 'yes' : 'no',
						]
					)
					->script_translations(
						'shoplic-pg',
						plugin_dir_path( shpg()->get_main_file() ) . 'languages'
					)
					->enqueue()
					->then()
					->enqueue_style( 'shpg-payment-fields' )
				;

				// Turn on liverealod feature.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
					$this->add_action( 'wp_print_footer_scripts', function () {
						echo '<script>';
						// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
						echo 'document.write(\'<script src="http://\' + ( location . host || \'localhost\').split(\':\')[0] + \':35729/livereload.js?snipver=1"></' + 'script>\')';
						echo '</script>';
					} );
				}
			}
		}

		/**
		 * Initial payment process
		 *
		 * @param int $order_id
		 *
		 * @return array
		 * @throws Exception
		 */
		public function process_payment( $order_id ): array {
			if ( ! $this->token || ! $this->token->get_token() ) {
				throw new Exception( __( 'Error while getting customer\'s payment token.', 'shoplic-pg' ) );
			}

			$order = wc_get_order( $order_id );

			if ( wcs_is_subscription( $order ) ) {
				// Customer changed subscription's payment method. Real purchase will not occur in this case.
				shpg_post_meta()->token_id->update( $order, $this->token->get_id() );
			} else {
				// Subscription may have trial period. In the case, $total is 0;
				if ( $order->get_total() > 0 ) {
					$response = shpg_api_nicepay_billing()->payment(
						$this->get_mid(),
						$this->get_merchant_key(),
						[
							'BID'          => $this->token->get_token(),
							'TID'          => shpg_generate_tid( $this->get_mid() ),
							'EdiDate'      => shpg_get_datetime_string(),
							'Moid'         => shpg_generate_moid( $this->get_mid(), $order_id ),
							'Amt'          => $order->get_total(),
							'GoodsName'    => shpg_summarize_order( $order ),
							'BuyerName'    => shpg_get_customer_name( $order ),
							'BuyerEmail'   => $order->get_billing_email(),
							'MallReserved' => $order_id,
						]
					);

					$this->check_response( $response, '3001' );

					$order->set_transaction_id( $response['TID'] );
				} else {
					// Actually API is not called, but we need to keep client's payment information for administration.
					$response = [
						'CardNo'   => $this->token->get_card_no(),
						'CardCode' => $this->token->get_card_code(),
						'CardName' => $this->token->get_card_name(),
						'CardCl'   => $this->token->get_card_cl(),
					];
				}

				// Billing response does not have 'PayMethod' key. Add it.
				$response['PayMethod'] = 'BILLING';

				// Keep the API call result.
				shpg_post_meta()->payment_result->update( $order_id, $response );

				// Keep test mode status.
				shpg_post_meta()->test_mode->update( $order, $this->is_test_mode() );

				// Update subscription.
				$subscriptions = wcs_get_subscriptions_for_order( $order );
				foreach ( $subscriptions as $subscription ) {
					shpg_post_meta()->token_id->update( $subscription, $this->token->get_id() );
				}

				$this->update_order_status_by_setting( $order );
			}

			return [
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			];
		}

		/**
		 * Recurring payment process
		 *
		 * @param float|int|string $amount Pay amount.
		 * @param WC_Order         $order  A new order created by the subscription plugin.
		 *                                 Metadata records are cloned from the subscription metadata, that is,
		 *                                 the order also has a proper token id for payment.
		 *
		 * @throws Exception
		 * @see WC_Subscriptions_Payment_Gateways::gateway_scheduled_subscription_payment()
		 *
		 * @see WC_Subscriptions_Payment_Gateways::trigger_gateway_renewal_payment_hook()
		 */
		public function subscription_payment( $amount, WC_Order $order ) {
			try {
				$token_id = shpg_post_meta()->token_id->get_value( $order );
				if ( empty( $token_id ) ) {
					$message = sprintf(
					/* translators: %1$s: method name, %2$d: order id number. */
						__( '[Error] %1$s: Token id not found for order #%2$d.', 'shoplic-pg' ),
						__METHOD__,
						$order->get_id()
					);
					throw new Exception( $message );
				}

				$this->token = SHPG_Payment_Tokens::get( $token_id );
				if ( ! $this->token ) {
					$message = sprintf(
					/* translators: %1$s: method name, %2$d: token id number, %3$d: order id number. */
						__( '[Error] %1$s: Invalid token value. Token #%2$d, order #%3$d.', 'shoplic-pg' ),
						__METHOD__,
						$token_id,
						$order->get_id()
					);
					throw new Exception( $message );
				}

				// Subscription plugin does not schedule payment during free trial period,
				// so that $amount will always be larger than zero.
				$response = shpg_api_nicepay_billing()->payment(
					$this->get_mid(),
					$this->get_merchant_key(),
					[
						'BID'          => $this->token->get_token(),
						'TID'          => shpg_generate_tid( $this->get_mid() ),
						'EdiDate'      => shpg_get_datetime_string(),
						'Moid'         => shpg_generate_moid( $this->get_mid(), $order->get_id() ),
						'Amt'          => $amount,
						'GoodsName'    => shpg_summarize_order( $order ),
						'BuyerName'    => shpg_get_customer_name( $order ),
						'BuyerEmail'   => $order->get_billing_email(),
						'MallReserved' => $order->get_id(),
					]
				);

				$this->check_response( $response, '3001' );

				$order->set_transaction_id( $response['TID'] );

				// Keep the API call result.
				shpg_post_meta()->payment_result->update( $order, $response );

				// Keep test mode status.
				shpg_post_meta()->test_mode->update( $order, $this->is_test_mode() );

				$this->update_order_status_by_setting( $order );
			} catch ( Exception $e ) {
				error_log( $e->getMessage() );
				WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order );
			}

			WC_Subscriptions_Manager::process_subscription_payments_on_order( $order );
		}

		/**
		 * Refund process
		 *
		 * @param int        $order_id
		 * @param float|null $amount
		 * @param string     $reason
		 *
		 * @return bool
		 * @throws Exception
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ): bool {
			$order = wc_get_order( $order_id );

			$response = shpg_api_nicepay_billing()->cancel(
				$this->get_mid(),
				$this->get_merchant_key(),
				[
					'TID'               => $order->get_transaction_id(),
					'Moid'              => shpg_generate_moid( $this->get_mid(), $order_id ),
					'CancelAmt'         => $amount,
					'CancelMsg'         => $reason,
					'PartialCancelCode' => $order->get_total() > $amount ? '1' : '0',
					'EditDate'          => shpg_get_datetime_string(),
					'MallReserved'      => $order_id,
				]
			);

			$this->check_response( $response, '2001' );

			// *ADD* metadata, not update! This process might be a partial refund.
			shpg_post_meta()->refund_result->add( $order_id, $response );

			return true;
		}

		/**
		 * Called before process_payment(), and while changing payment method process.
		 *
		 * @return true
		 *
		 * @throws Exception
		 * @see    WC_Subscriptions_Change_Payment_Gateway::change_payment_method_via_pay_shortcode()
		 */
		public function validate_fields(): bool {
			if ( ! is_user_logged_in() ) {
				throw new Exception( __( 'The payment method requires login.', 'shoplic-pg' ) );
			}

			// phpcs:disable
			// NOTE: shpg_strip_non_number(), and shpg_validate_card_expire() sanitize values.
			$card_no = shpg_strip_non_number( wp_unslash( $_POST['shpg']['card_no'] ?? '' ) );
			$card_pw = shpg_strip_non_number( wp_unslash( $_POST['shpg']['card_pw'] ?? '' ) );
			$id_no   = shpg_strip_non_number( wp_unslash( $_POST['shpg']['id_no'] ?? '' ) );

			[ $exp_month, $exp_year ] = shpg_validate_card_expire( wp_unslash( $_POST['shpg']['card_expire'] ?? '' ) );
			// phpcs:enable

			$card_hash = shpg_generate_card_hash( $card_no, $this->get_mid(), $this->get_merchant_key() );
			$token     = SHPG_Payment_Tokens::get_customer_token_by_card_hash( get_current_user_id(), $card_hash );

			// Token not found. Create a new token.
			if ( ! $token ) {
				$response = shpg_api_nicepay_billing()->request_no_auth(
					$this->get_mid(),
					$this->get_merchant_key(),
					[
						'EdiDate' => shpg_get_datetime_string(),
						'Moid'    => shpg_generate_moid( $this->get_mid(), 0 ),
					],
					[
						'CardNo'   => $card_no,
						'ExpYear'  => $exp_year,
						'ExpMonth' => $exp_month,
						'IDNo'     => $id_no,
						'CardPw'   => $card_pw,
					]
				);

				$this->check_response( $response, 'F100' );

				$token = SHPG_Payment_Tokens::add_new_token_from_response(
					$response,
					get_current_user_id(),
					$card_no,
					$card_hash
				);
			}

			// Double check if the token is okay.
			if ( ! $token || ! $token->get_token() ) {
				throw new Exception(
					sprintf(
					// translators: token id.
						__( 'Billing key error. ID: %d', 'shoplic-pg' ), $token->get_id()
					)
				);
			}

			$this->token = $token;

			return true;
		}

		/**
		 * Render payment fields.
		 *
		 * Assets are enqueued in enqueue_checkout_scripts() method.
		 *
		 * @see SHPG_Gateway_Billing::enqueue_checkout_scripts()
		 */
		public function payment_fields() {
			parent::payment_fields();

			if ( $this->supports( 'subscriptions' ) ) {
				$this->render( 'nicepay/payment-fields' );
			}
		}

		public function is_available(): bool {
			return parent::is_available() && is_user_logged_in();
		}

		/**
		 * Check if response is successful, or raise an exception, or die.
		 *
		 * @param array|WP_Error $response
		 * @param string         $success_code
		 *
		 * @throws Exception
		 */
		protected function check_response( $response, string $success_code ) {
			if ( is_wp_error( $response ) ) {
				$error_msg = sprintf( '[%s] %s', $response->get_error_code(), $response->get_error_message() );

				// We do not need NONCE here. This is to check if the request is made by WooCommerce Subscriptions plugn.
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $_POST['woocommerce_change_payment'], $_POST['_wcsnonce'] ) ) {
					// WooCommerce Subscriptions plugin does not carefully catch exceptions.
					wp_die(
						sprintf(
							'%s: <p>%s</p>',
							esc_html__( 'API call has failed due to an error', 'shoplic-pg' ), esc_html( $error_msg )
						)
					);
				} else {
					// WooCommerce checkout page. Thrown exception is caught well.
					throw new Exception( $error_msg );
				}
			} elseif ( ( $response['ResultCode'] ?? '' ) !== $success_code ) {
				$error_msg = sprintf( '[%s] %s', $response['ResultCode'] ?? '', $response['ResultMsg'] ?? '' );

				// We do not need NONCE here. This is to check if the request is made by WooCommerce Subscriptions plugn.
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( isset( $_POST['woocommerce_change_payment'], $_POST['_wcsnonce'] ) ) {
					// WooCommerce Subscriptions plugin does not carefully catch exceptions.
					wp_die(
						sprintf(
							'%s: <p>%s</p>',
							esc_html__( 'API call has failed due to an error', 'shoplic-pg' ), esc_html( $error_msg )
						)
					);
				} else {
					// WooCommerce checkout page. Thrown exception is caught well.
					throw new Exception( $error_msg );
				}
			}
		}
	}
}
