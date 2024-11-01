<?php
/**
 * SHPG: NicePay payment gateway: bank
 *
 * @package shpg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Gateway_NicePay_Bank' ) ) {
	/**
	 * NicePay Bank Payment Gateway (실시간 계좌이체)
	 */
	class SHPG_Gateway_NicePay_Bank extends SHPG_Gateway_NicePay {
		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->id                 = 'shpg_nicepay_bank';
			$this->method_title       = _x( 'Bank Transfer', 'Method title', 'shoplic-pg' );
			$this->method_description = _x( 'You can pay by real-time account transfer.', 'Method description', 'shoplic-pg' );
			$this->pay_method         = 'BANK';

			parent::__construct();
		}

		/**
		 * Initialize form fields
		 *
		 * @return void
		 */
		public function init_form_fields() {
			parent::init_form_fields();

			$this->form_fields['title']['default']       = _x( 'Bank Transfer', 'Method title', 'shoplic-pg' );
			$this->form_fields['description']['default'] = _x( 'Shoplic NicePay payment gateway, bank transfer.', 'Method description', 'shoplic-pg' );
		}
	}
}
