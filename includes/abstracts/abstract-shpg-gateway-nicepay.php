<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * SHPG: Nicepay abstract class
 *
 * @package shpg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Gateway_NicePay' ) ) {
	/**
	 * NicePay payment gateway abstract class
	 */
	abstract class SHPG_Gateway_NicePay extends WC_Payment_Gateway implements SHPG_Module {
		const TEST_MID          = 'nicepay00m';
		const TEST_MERCHANT_KEY = 'EYzu8jGGMfqaDEp76gSckuvnaHHu+bC4opsSN6lHv3b2lurNYkVXrZ7Z1AoqQnXI3eLuaUFyoRNC6FkrzVjceg==';

		use SHPG_Hook_Impl;
		use SHPG_Template_Impl;

		/**
		 * NicePay-specific payment method
		 *
		 * @var string
		 */
		protected string $pay_method;

		/**
		 * Constructor
		 */
		public function __construct() {
			// Define these properties in child claseses.
			// - $id
			// - $method_title
			// - $methid_description
			// - $pay_method
			// Do not forget them!

			$this->has_fields = false;

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
			$this->add_import_feature();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->supports    = [ 'products', 'refunds' ];

			$this
				// Make admin update work as expected.
				->add_action( "woocommerce_update_options_payment_gateways_$this->id", 'process_admin_options' )
				// Append NicePay payment request form.
				->add_action( "woocommerce_receipt_$this->id", 'append_request_form' )
				// Extend wc-api so that we can authorize the payment request.
				->add_action( "woocommerce_api_$this->id", 'process_payment_request' )
			;
		}

		/**
		 * Initialise Gateway settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = [
				'enabled'       => [
					'title'   => __( 'Enable/Disable', 'shoplic-pg' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this payment method', 'shoplic-pg' ),
					'default' => 'no',
				],
				'test_mode'     => [
					'title'       => __( 'Test Mode', 'shoplic-pg' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable Test Mode', 'shoplic-pg' ),
					'description' => __( 'All payments are made as a test. Refunded in bulk at 23:30.', 'shoplic-pg' ),
					'default'     => 'no',
					'desc_tip'    => true,
				],
				'title'         => [
					'title'       => __( 'Title', 'shoplic-pg' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'shoplic-pg' ),
					'default'     => '',
					'desc_tip'    => true,
				],
				'description'   => [
					'title'       => __( 'Description', 'shoplic-pg' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'shoplic-pg' ),
					'default'     => '',
					'desc_tip'    => true,
				],
				'mid'           => [
					'title'             => __( 'MID', 'shoplic-pg' ),
					'type'              => 'text',
					'description'       => __( 'Your NicePay MID. This field value is required.', 'shoplic-pg' ),
					'default'           => '',
					'custom_attributes' => [ 'autocomplete' => 'off' ],
				],
				'merchant_key'  => [
					'title'             => __( 'Merchant Key', 'shoplic-pg' ),
					'type'              => 'text',
					'description'       => __( 'Your NicePay merchant key. This field value is required.', 'shoplic-pg' ),
					'default'           => '',
					'custom_attributes' => [ 'autocomplete' => 'off' ],
				],
				'update_status' => [
					'title'       => _x( 'Update Order Status', 'Settings field', 'shoplic-pg' ),
					'type'        => 'select',
					'description' => _x( 'After successful payment, update order status to this value.', 'Settings field', 'shoplic-pg' ),
					'options'     => shpg_get_auto_updatable_order_statuses(),
					'desc_tip'    => true,
					'default'     => 'wc-processing',
				],
			];
		}

		/**
		 * Process payment. Called by WooCommerce checkout.
		 *
		 * @param int $order_id The order ID.
		 *
		 * @return array
		 */
		public function process_payment( $order_id ): array {
			$order = wc_get_order( $order_id );
			$order->update_status( 'pending' );

			return [
				'result'   => 'success',
				// Redirect a customer to checkout-payemnt page because
				// NicePay requires form submit.
				'redirect' => $order->get_checkout_payment_url( true ),
			];
		}

		/**
		 * Validate field while payment. Called by WooCommerce checkout.
		 *
		 * @throws Exception Occurs when MID, or merchant key is missing.
		 */
		public function validate_fields(): bool {
			if ( 'yes' !== $this->get_option( 'test_mode' ) ) {
				$mid = $this->get_option( 'mid' );
				if ( ! $mid ) {
					throw new Exception( __( 'NicePay MID is missing', 'shoplic-pg' ) );
				}

				$merchant_key = $this->get_option( 'merchant_key' );
				if ( ! $merchant_key ) {
					throw new Exception( __( 'NicePay merchant key is missing', 'shoplic-pg' ) );
				}
			}

			return true;
		}

		/**
		 * Append HTML request form
		 *
		 * @callback
		 * @action    woocommerce_receipt_{$payment_method}
		 *
		 * @param int $order_id The order ID.
		 *
		 * @return void
		 *
		 * @link      https://developers.nicepay.co.kr/manual-auth.php#parameter-auth-request-pc
		 * @link      https://developers.nicepay.co.kr/manual-auth.php#parameter-auth-request-mobile
		 */
		public function append_request_form( int $order_id ) {
			$order      = wc_get_order( $order_id );
			$is_mobile  = wp_is_mobile();
			$return_url = trailingslashit( home_url() ) . "?wc-api=$this->id";

			if ( ! $order->needs_payment() || 'BILLING' === $this->pay_method ) {
				return;
			}

			if ( ! $is_mobile ) {
				// Enqueue PC-only script.
				$this->enqueue_script( 'shpg-nicepay' );
			}

			$this
				->script( 'shpg-nicepay-payment-request' )
				->localize(
					[
						'isMobile'              => $is_mobile ? 'yes' : 'no',
						'returnUrl'             => $return_url,
						'textPaymentIsCanceled' => __( 'Payment is canceled', 'shoplic-pg' ),
					],
					'shpgNicepayPaymentRequest'
				)
				->enqueue()
				->then()
				->render(
					'nicepay/request-form',
					[
						'action_url' => $is_mobile ? 'https://web.nicepay.co.kr/v3/v3Payment.jsp' : $return_url,
						'inputs'     => shpg_api_nicepay()->get_payment_request_params( $this, $order ),
					]
				)
			;
		}

		/**
		 * Callback of '?wc-api=$id'.
		 *
		 * @callback
		 * @action    woocommerce_api_{$api_request}
		 *
		 * @return void
		 *
		 * @see       SNP_Register_Submit::get_items()
		 * @see       WC_API::handle_api_requests()
		 */
		public function process_payment_request() {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$post  = mb_convert_encoding( $_POST, 'UTF-8', 'EUC-KR' );
			$order = wc_get_order( intval( $post['ReqReserved'] ?? '0' ) );

			if ( '0000' !== ( $post['AuthResultCode'] ?? '' ) ) {
				$this->die_on_error(
					sprintf( '%s: %s', $post['AuthResultCode'] ?? '', $post['AuthResultMsg'] ?? '' )
				);
			} elseif ( ! $order ) {
				$this->die_on_error( __( 'Wrong order ID received.', 'shoplic-pg' ) );
			}

			try {
				/**
				 * See the documentation.
				 *
				 * @link https://developers.nicepay.co.kr/manual-auth.php#parameter-api-response
				 */
				$response = shpg_api_nicepay()->authorize(
					$this->get_mid(),
					$this->get_merchant_key(),
					[
						'TID'          => $post['TxTid'] ?? '',
						'AuthToken'    => $post['AuthToken'] ?? '',
						'Amt'          => $post['Amt'] ?? '',
						'MallReserved' => $post['ReqReserved'] ?? '',
					]
				);

				$order->set_transaction_id( $response['TID'] ?? '' );

				// Write metadata.
				shpg_post_meta()->payment_result->update( $order, $response );
				shpg_post_meta()->test_mode->update( $order, $this->is_test_mode() );

				if ( 'VBANK' !== $this->pay_method ) {
					$this->update_order_status_by_setting( $order );
				}

				wp_safe_redirect( $order->get_checkout_order_received_url() );
				exit;
			} catch ( Exception $e ) {
				if ( isset( $response ) && is_array( $response ) ) {
					shpg_api_nicepay()->cancel(
						$this->get_mid(),
						$this->get_merchant_key(),
						[
							'TID'       => $post['TxTid'] ?? '',
							'AuthToken' => $post['AuthToken'] ?? '',
							'MID'       => $post['MID'] ?? '',
							'Amt'       => $post['Amt'] ?? '',
						]
					);
				}

				$this->die_on_error( $e->getMessage(), $order );
			}
		}

		/**
		 * Refund process
		 *
		 * @param int        $order_id The order id.
		 * @param float|null $amount   Refund amount.
		 * @param string     $reason   Reason of refund.
		 *
		 * @return bool
		 * @throws Exception Occurs when an API call goes wrong.
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ): bool {
			$order = wc_get_order( $order_id );
			$mid   = $this->get_mid();

			$response = shpg_api_nicepay()->refund(
				$this->get_mid(),
				$this->get_merchant_key(),
				[
					'TID'               => $order->get_transaction_id(),
					'Moid'              => shpg_generate_moid( $mid, $order_id ),
					'CancelAmt'         => $amount,
					'CancelMsg'         => $reason,
					'PartialCancelCode' => $order->get_total() === $amount ? '0' : '1',
					'MallReserved'      => $order_id,
				]
			);

			if ( $response ) {
				shpg_post_meta()->refund_result->add( $order_id, $response );
			}

			return true;
		}

		/**
		 * Override description
		 *
		 * Append '[Test Mode]' in checkout if it is test mode.
		 *
		 * @return string
		 */
		public function get_description(): string {
			$description = parent::get_description();

			if ( is_checkout() && $this->is_test_mode() ) {
				/* translators: current description of this payment method. */
				return sprintf( _x( '[Test Mode] %s', 'Payment method description', 'shoplic-pg' ), $description );
			} else {
				return $description;
			}
		}

		/**
		 * Return if it is test mode.
		 *
		 * @return bool
		 */
		public function is_test_mode(): bool {
			return 'yes' === $this->get_option( 'test_mode' );
		}

		/**
		 * Get MID.
		 *
		 * @return string
		 */
		public function get_mid(): string {
			return $this->is_test_mode() ? static::TEST_MID : $this->get_option( 'mid' );
		}

		/**
		 * Get merchant key.
		 *
		 * @return string
		 */
		public function get_merchant_key(): string {
			return $this->is_test_mode() ? static::TEST_MERCHANT_KEY : $this->get_option( 'merchant_key' );
		}

		/**
		 * Return NicePay-specific pay method string.
		 *
		 * @return string 'card', 'bank', 'vbank', or 'cellphone'.
		 */
		public function get_pay_method(): string {
			return $this->pay_method;
		}

		/**
		 * Common phase. Update the order status after successful purchase.
		 *
		 * @param WC_Order $order The current order.
		 *
		 * @return void
		 */
		protected function update_order_status_by_setting( WC_Order $order ) {
			$status   = $this->get_option( 'update_status' );
			$statuses = wc_get_order_statuses();

			if ( isset( $statuses[ $status ] ) ) {
				$order->update_status( $status );
			} else {
				// In case of error.
				$order->update_status( 'processing' );
			}
		}

		/**
		 * Function wp_die() wrapper.
		 *
		 * @param string        $message Error message.
		 * @param WC_Order|null $order   Current order.
		 *
		 * @return void
		 */
		protected function die_on_error( string $message, ?WC_Order $order = null ) {
			if ( $order ) {
				$args = [
					'link_url'  => $order->get_checkout_payment_url( true ),
					'link_text' => __( 'Go back', 'shoplic-pg' ),
				];
			} else {
				$args = [];
			}

			wp_die(
				esc_html( $message ),
				esc_html__( 'Payment failure', 'shoplic-pg' ),
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$args
			);
		}

		/**
		 * To resolve cumbersome copy-and-paste MID, Merchant Key for diverse payment methods.
		 *
		 * @return void
		 */
		protected function add_import_feature() {
			global $current_tab, $current_section;

			if ( 'checkout' !== $current_tab || $current_section !== $this->id ) {
				return;
			}

			$import_added      = false;
			$mid_type          = '';
			$merchant_key_type = '';

			if ( empty( $this->get_option( 'mid' ) ) ) {
				[ $mid, $type, $title ] = $this->check_and_get_alternative( 'mid' );

				if ( $mid ) {
					$this->form_fields['mid']['description'] .=
						' <a id="import-mid" href="#" style="text-decoration: none; font-style: italic">' .
						/* translators: %1$s payment method. */
						sprintf( __( 'Import from \'%1$s\'?', 'shoplic-pg' ), esc_html( $title ) ) . '</a>';

					$import_added = true;
					$mid_type     = $type;
				}
			}

			if ( empty( $this->get_option( 'merchant_key' ) ) ) {
				[ $merchant_key, $type, $title ] = $this->check_and_get_alternative( 'merchant_key' );

				if ( $merchant_key ) {
					$this->form_fields['merchant_key']['description'] .=
						' <a id="import-merchant_key" href="#" style="text-decoration: none; font-style: italic">' .
						/* translators: %1$s payment method. */
						sprintf( __( 'Import from \'%1$s\'?', 'shoplic-pg' ), esc_html( $title ) ) . '</a>';

					$import_added      = true;
					$merchant_key_type = $type;
				}
			}

			if ( $import_added ) {
				$this
					->script( 'shpg-nicepay-cred-import' )
					->localize(
						[
							'nonce'           => wp_create_nonce( 'shpg-nicepay-cred-import' ),
							'payMethod'       => strtolower( $this->pay_method ),
							'midFrom'         => $mid_type,
							'merchantKeyFrom' => $merchant_key_type,
						],
						'credImport'
					)
					->enqueue()
				;
			}
		}

		/**
		 * Get valid value by key from another payment method options.
		 *
		 * @param string $key The option name.
		 *
		 * @return string[]
		 */
		protected function check_and_get_alternative( string $key ): array {
			foreach ( static::get_all_avail_types() as $type ) {
				$option = get_option( self::get_raw_option_name( $type ), [] );
				$value  = $option[ $key ] ?? false;
				$title  = $option['title'] ?? '';

				if ( is_string( $value ) && $value ) {
					return [ $value, $type, $title ];
				}
			}

			return [ '', '', '' ];
		}

		/**
		 * This knows its descendants.
		 *
		 * @return string[]
		 */
		public static function get_all_avail_types(): array {
			return [ 'card', 'bank', 'vbank', 'cellphone', 'billing' ];
		}

		/**
		 * Return option name of each pament gateway.
		 *
		 * @param string $type Available type string. 'card', 'bank', 'vbank', 'cellphone', 'billing'.
		 *
		 * @return string
		 */
		public static function get_raw_option_name( string $type ): string {
			return "woocommerce_shpg_nicepay_{$type}_settings";
		}
	}
}
