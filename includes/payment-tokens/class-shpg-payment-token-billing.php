<?php
/**
 *
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Payment_Token' ) ) {
	require_once dirname( WC_PLUGIN_FILE ) . '/includes/abstracts/abstract-wc-payment-token.php';
}

if ( ! class_exists( 'SHPG_Payment_Token_Billing' ) ) {
	class SHPG_Payment_Token_Billing extends WC_Payment_Token {
		protected $type = 'shpg_nicepay_billing';

		protected $extra_data = [
			/**
			 * Card number: 7-12 digits is masked as '*' for security.
			 */
			'card_no'        => '',

			/**
			 * Card code: 2 or 3 digits
			 */
			'card_code'      => '',

			/**
			 * Card name: string
			 */
			'card_name'      => '',

			/**
			 * AcquCard code (매입 카드사 코드): 2 or 3 digits
			 */
			'acqu_card_code' => '',

			/**
			 * AcquCard name (매입 카드사 명): string
			 */
			'acqu_card_name' => '',

			/**
			 * Card CL: 1: check card, 0: credit card.
			 */
			'card_cl'        => '0',

			/**
			 * Auth Date: Y-m-d (e.g. 2021-12-31)
			 */
			'auth_date'      => '',

			/**
			 * Transaction ID: identification for each API call.
			 */
			'tid'            => '',

			/**
			 * Card Hash: User's card identification.
			 */
			'card_hash'      => '',
		];

		public function get_display_name( $deprecated = '' ): string {
			return __( 'Shoplic NicePay billing token', 'shoplic-pg' );
		}

		public function get_card_no( $context = 'view' ): string {
			return $this->get_prop( 'card_no', $context );
		}

		public function set_card_no( string $card_no ) {
			$this->set_prop( 'card_no', shpg_mask_card_no( $card_no ) );
		}

		public function get_card_code( $context = 'view' ): string {
			return $this->get_prop( 'card_code', $context );
		}

		public function set_card_code( string $card_code ) {
			$this->set_prop( 'card_code', $card_code );
		}

		public function get_card_name( $context = 'view' ): string {
			return $this->get_prop( 'card_name', $context );
		}

		public function set_card_name( string $card_code ) {
			$this->set_prop( 'card_name', trim( $card_code, '[]' ) );
		}

		public function get_acqu_card_code( $context = 'view' ): string {
			return $this->get_prop( 'acqu_card_code', $context );
		}

		public function set_acqu_card_code( string $card_code ) {
			$this->set_prop( 'acqu_card_code', $card_code );
		}

		public function get_acqu_card_name( $context = 'view' ): string {
			return $this->get_prop( 'acqu_card_name', $context );
		}

		public function set_acqu_card_name( string $card_code ) {
			$this->set_prop( 'acqu_card_name', trim( $card_code, '[]' ) );
		}

		public function get_card_cl( $context = 'view' ): bool {
			return $this->get_prop( 'card_cl', $context );
		}

		/**
		 * @param string|int|bool $card_cl
		 */
		public function set_card_cl( $card_cl ) {
			$this->set_prop( 'card_cl', boolval( $card_cl ) );
		}

		public function get_auth_date( $context = 'view' ): string {
			return $this->get_prop( 'auth_date', $context );
		}

		public function set_auth_date( string $auth_date ) {
			if ( shpg_validate_datetime( $auth_date, 'Ymd' ) ) {
				$auth_date = substr( $auth_date, 0, 4 ) . '-' .
				             substr( $auth_date, 4, 2 ) . '-' .
				             substr( $auth_date, 6, 2 );
			}
			$this->set_prop( 'auth_date', $auth_date );
		}

		public function get_tid( $context = 'view' ): string {
			return $this->get_prop( 'tid', $context );
		}

		public function set_tid( string $tid ) {
			$this->set_prop( 'tid', $tid );
		}

		public function get_card_hash( $context = 'view' ): string {
			return $this->get_prop( 'card_hash', $context );
		}

		public function set_card_hash( string $card_hash ) {
			$this->set_prop( 'card_hash', $card_hash );
		}

		protected function get_hook_prefix(): string {
			return 'woocommerce_payment_token_' . $this->get_type();
		}
	}
}
