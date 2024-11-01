<?php
/**
 * SHPG: Kakao Pay payment gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Gateway_Kakao_Pay' ) ) {
	class SHPG_Gateway_Kakao_Pay extends WC_Payment_Gateway implements SHPG_Module {
		private const TEST_CID = 'TC0ONETIME';

		use SHPG_Hook_Impl;
		use SHPG_Template_Impl;

		public function __construct() {
			$this->id                 = 'shpg_kakao_pay';
			$this->method_title       = _x( 'KakaoPay', 'Method title', 'shoplic-pg' );
			$this->method_description = _x( 'Proceed with payment by Kakao pay.', 'Method description', 'shoplic-pg' );
			$this->has_fields         = false;
			$this->icon               = plugins_url( 'assets/img/icon-kakao-pay.png', shpg()->get_main_file() );

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->supports    = [ 'products', 'refunds' ];

			$this
				// 옵션 저장시 저장되도록 처리.
				->add_action( "woocommerce_update_options_payment_gateways_$this->id", 'process_admin_options' )

				// wc-api: shoplic_kakao_pay - approval, URL: /?wc-api=shoplic_kakao_pay-approval
				->add_action( "woocommerce_api_$this->id-approval", 'callback_approval' )

				// wc-api: shoplic_kakao_pay - cancel, URL: /?wc-api=shoplic_kakao_pay-cancel
				->add_action( "woocommerce_api_$this->id-cancel", 'callback_cancel' )

				// wc-api: shoplic_kakao_pay - fail, URL: /?wc-api=shoplic_kakao_pay-fail
				->add_action( "woocommerce_api_$this->id-fail", 'callback_failure' )
			;
		}

		/**
		 * 게이트웨이 설정 폼 필드 초기화
		 *
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = [
				'enabled'       => [
					'title'   => __( 'Enable/Disable', 'shoplic-pg' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable This Payment Method', 'shoplic-pg' ),
					'default' => 'no',
				],
				'test_mode'     => [
					'title'       => __( 'Test Mode', 'shoplic-pg' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable Test Mode', 'shoplic-pg' ),
					'description' => __( 'All payments are made as a test.', 'shoplic-pg' ),
					'default'     => 'no',
					'desc_tip'    => true,
				],
				'title'         => [
					'title'       => __( 'Title', 'shoplic-pg' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'shoplic-pg' ),
					'default'     => __( 'KakaoPay', 'shoplic-pg' ),
					'desc_tip'    => true,
				],
				'description'   => [
					'title'       => __( 'Description', 'shoplic-pg' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'shoplic-pg' ),
					'default'     => __( 'Proceed with payment by Kakao pay.', 'shoplic-pg' ),
					'desc_tip'    => true,
				],
				'cid'           => [
					'title'             => __( 'CID', 'shoplic-pg' ),
					'type'              => 'text',
					'description'       => __( 'Your KakaoPay CID. This field value is required.', 'shoplic-pg' ),
					'default'           => '',
					'custom_attributes' => [ 'autocomplete' => 'off' ],
				],
				'admin_key'     => [
					'title'             => __( 'Admin Key', 'shoplic-pg' ),
					'type'              => 'text',
					'description'       => sprintf(
					/* translators: kakao developers URL. */
						__( 'Your KakaoPay admin key. This field value is required. To obtain admin key, please visit <a href="%s" target="_blank">Kakao developers</a>.', 'shoplic-pg' ),
						'https://developers.kakao.com/'
					),
					'default'           => '',
					'custom_attributes' => [ 'autocomplete' => 'off' ],
				],
				'update_status' => [
					'title'       => __( 'Update Order Status', 'shoplic-pg' ),
					'type'        => 'select',
					'description' => __( 'After successful payment, update order status to this value.', 'shoplic-pg' ),
					'options'     => shpg_get_auto_updatable_order_statuses(),
					'desc_tip'    => true,
					'default'     => 'wc-processing',
				],
			];
		}

		/**
		 * 결제 진행
		 *
		 * @param int $order_id
		 *
		 * @return array
		 * @throws Exception
		 */
		public function process_payment( $order_id ): array {
			$order = wc_get_order( $order_id );
			$order->update_status( 'pending' );

			$response = shpg_api_kakao_pay()
				->set_admin_key( $this->get_admin_key() )
				->ready(
					[
						'cid'              => $this->get_cid(),
						'partner_order_id' => sprintf( '%s#%s', get_bloginfo( 'url' ), $order->get_id() ),
						'partner_user_id'  => $order->get_customer_id(),
						'item_name'        => shpg_summarize_order( $order ),
						'quantity'         => shpg_get_total_quantity( $order ),
						'total_amount'     => $order->get_total(),
						'tax_free_amount'  => 0,
						'approval_url'     => $this->get_approval_url( $order_id ),
						'cancel_url'       => $this->get_cancel_url( $order_id ),
						'fail_url'         => $this->get_fail_url( $order_id ),
					]
				)
			;

			// Save if it is test mode.
			shpg_post_meta()->test_mode->update( $order, $this->is_test_mode() );

			// Save temp tid.
			if ( isset( $response['tid'] ) ) {
				shpg_post_meta()->temp_tid->update( $order, $response['tid'] );
			}

			if ( wp_is_mobile() ) {
				$redirect = $response['next_redirect_mobile_url'] ?? '';
			} else {
				$redirect = $response['next_redirect_pc_url'] ?? '';
			}

			if ( ! $redirect ) {
				throw new Exception( 'Redirect URL error.' );
			}

			return [
				'result'   => 'success',
				'redirect' => $redirect,
			];
		}

		/**
		 * 결제전, 필드의 정합성 확인.
		 *
		 * @throws Exception
		 */
		public function validate_fields(): bool {
			$reason = '';

			if ( ! $this->get_cid() ) {
				$reason = __( 'CID is missing.', 'shoplic-pg' );
			} elseif ( ! $this->get_admin_key() ) {
				$reason = __( 'Admin key is missing.', 'shoplic-pg' );
			}

			if ( ! empty( $reason ) ) {
				throw new Exception( sprintf( '%s: %s', _x( 'Please contact to the shop manager', 'shoplic-pg' ), $reason ) );
			}

			return true;
		}

		/**
		 * 승인 콜백.
		 *
		 * @callback
		 * @action    woocommerce_api_{$gateway_id}:approval
		 *
		 * @return void
		 */
		public function callback_approval() {
			try {
				$hash     = $_GET['hash'] ?? '';
				$order_id = $_GET['order_id'] ?? '';

				if ( ! $this->verify_hash( $hash, $order_id, 'approval' ) ) {
					throw new Exception( 'Hash mismatch.' );
				}

				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					throw new Exception( 'Invalid order ID.' );
				} elseif ( ! $order->needs_payment() ) {
					throw new Exception( 'The order does not need payment.' );
				}

				$response = shpg_api_kakao_pay()
					->set_admin_key( $this->get_admin_key() )
					->approve(
						[
							'cid'              => $this->get_cid(),
							'tid'              => shpg_post_meta()->temp_tid->get_value( $order ),
							'partner_order_id' => sprintf( '%s#%s', get_bloginfo( 'url' ), $order->get_id() ),
							'partner_user_id'  => $order->get_customer_id(),
							'pg_token'         => $_GET['pg_token'] ?? '',
							'payload'          => '',
							'total_amount'     => $order->get_total(),
						]
					)
				;

				// Delete temporary transaction id.
				shpg_post_meta()->temp_tid->delete( $order );

				// Set transaction id.
				$order->set_transaction_id( $response['tid'] ?? '' );

				// Log verbatim response.
				shpg_post_meta()->payment_result->update( $order, $response );

				// Update order status
				$status   = $this->get_option( 'update_status' );
				$statuses = wc_get_order_statuses();
				if ( isset( $statuses[ $status ] ) ) {
					$order->update_status( $status );
				} else {
					// In case of error.
					$order->update_status( 'processing' );
				}

				// Redirect to thankyou page.
				wp_safe_redirect( $order->get_checkout_order_received_url() );
				exit;
			} catch ( Exception $e ) {
				$args = [
					'response'  => 400,
					'link_url'  => wc_get_checkout_url(),
					'link_text' => __( 'Back to the checkout', 'shoplic-pg' ),
				];

				wp_die( 'Error: ' . $e->getMessage(), 'KakaoPay Error', $args );
			}
		}

		/**
		 * 취소 콜백.
		 *
		 * @callback
		 * @action    woocommerce_api_{$gateway_id}:cancel
		 *
		 * @return void
		 */
		public function callback_cancel() {
			try {
				$hash     = $_GET['hash'] ?? '';
				$order_id = $_GET['order_id'] ?? '';

				if ( ! $this->verify_hash( $hash, $order_id, 'cancel' ) ) {
					throw new Exception( 'Hash mismatch.' );
				}

				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					throw new Exception( 'Invalid order ID.' );
				}

				$order->update_status(
					'cancelled',
					__( 'The API server notified that the order is canceled.', 'shoplic-pg' ),
					true
				);
				exit;
			} catch ( Exception $e ) {
				wp_die( 'Error: ' . $e->getMessage() );
			}
		}

		/**
		 * 실패 콜백
		 *
		 * @callback
		 * @action    woocommerce_api_{$gateway_id}:fail
		 *
		 * @return void
		 */
		public function callback_failure() {
			try {
				$hash     = $_GET['hash'] ?? '';
				$order_id = $_GET['order_id'] ?? '';

				if ( ! $this->verify_hash( $hash, $order_id, 'fail' ) ) {
					throw new Exception( 'Hash mismatch.' );
				}

				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					throw new Exception( 'Invalid order ID.' );
				}

				$order->update_status(
					'failed',
					__( 'The API server notified that the order is failed.', 'shoplic-pg' ),
					true
				);
				exit;
			} catch ( Exception $e ) {
				wp_die( 'Error: ' . $e->getMessage() );
			}
		}

		/**
		 * 환불 진행.
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
			$tid   = $order->get_transaction_id();

			if ( ! $tid ) {
				throw new Exception( 'TID not found!' );
			}

			$response = shpg_api_kakao_pay()
				->set_admin_key( $this->get_admin_key() )
				->cancel(
					[
						'cid'                    => $this->get_cid(),
						'tid'                    => $tid,
						'cancel_amount'          => $amount,
						'cancel_tax_free_amount' => 0,
						'payload'                => $reason,
					]
				)
			;

			if ( $response ) {
				shpg_post_meta()->refund_result->add( $order, $response );
			}

			return true;
		}

		/**
		 * 설정에서 CID 값 리턴.
		 *
		 * @return string
		 */
		public function get_cid(): string {
			return $this->is_test_mode() ? self::TEST_CID : $this->get_option( 'cid' );
		}

		/**
		 * 설정에서 Admin Key 값 리턴.
		 *
		 * @return string
		 */
		public function get_admin_key(): string {
			return $this->get_option( 'admin_key' );
		}

		/**
		 * 테스트 모드인지 리턴.
		 *
		 * @return bool
		 */
		public function is_test_mode(): bool {
			return 'yes' === $this->get_option( 'test_mode' );
		}

		/**
		 * 카카오페이 승인 URL 리턴.
		 *
		 * @param int $order_id
		 *
		 * @return string
		 */
		public function get_approval_url( int $order_id ): string {
			return $this->generate_redirect_url( $order_id, 'approval' );
		}

		/**
		 * 카카오페이 취소 URL 리턴.
		 *
		 * @param int $order_id
		 *
		 * @return string
		 */
		public function get_cancel_url( int $order_id ): string {
			return $order_id ? wc_get_checkout_url() : '';
		}

		/**
		 * 카카오페이 실패 URL 리턴.
		 *
		 * @param int $order_id
		 *
		 * @return string
		 */
		public function get_fail_url( int $order_id ): string {
			return $this->generate_redirect_url( $order_id, 'fail' );
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
				return sprintf( __( '[Test Mode] %s', 'shoplic-pg' ), $description );
			} else {
				return $description;
			}
		}

		/**
		 * 승인, 취소, 실패 URL 제작 메소드.
		 *
		 * @param int    $order_id
		 * @param string $type
		 *
		 * @return string
		 */
		protected function generate_redirect_url( int $order_id, string $type = '' ): string {
			$string = home_url() . ':' . $this->get_cid() . ':' . $order_id . ':' . $type;
			$hash   = wp_hash( $string, 'nonce' );

			return add_query_arg(
				[
					'wc-api'   => $this->id . '-' . $type,
					'order_id' => $order_id,
					'hash'     => $hash,
				],
				home_url()
			);
		}

		/**
		 * Redirect URL 에 있는 해시 검증.
		 *
		 * @param string $hash
		 * @param int    $order_id
		 * @param string $type
		 *
		 * @return bool
		 */
		protected function verify_hash( string $hash, int $order_id, string $type = '' ): bool {
			$string   = home_url() . ':' . $this->get_cid() . ':' . $order_id . ':' . $type;
			$expected = wp_hash( $string, 'nonce' );

			return hash_equals( $expected, $hash );
		}
	}
} 
