<?php
/**
 * SHPG: Payment gateway, cellphone.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Gateway_NicePay_CellPhone' ) ) {
	/**
	 * NicePay CellPhone Payment Gateway
	 */
	class SHPG_Gateway_NicePay_CellPhone extends SHPG_Gateway_NicePay {
		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->id                 = 'shpg_nicepay_cellphone';
			$this->method_title       = _x( 'Mobile Phone', 'Method title', 'shoplic-pg' );
			$this->method_description = _x( 'Proceed with payment by mobile phone.', 'Method description', 'shoplic-pg' );
			$this->pay_method         = 'CELLPHONE';

			parent::__construct();
		}

		/**
		 * Initialize form fields
		 *
		 * @return void
		 */
		public function init_form_fields() {
			parent::init_form_fields();

			$this->form_fields['title']['default']       = _x( 'Mobile Phone', 'Method title', 'shoplic-pg' );
			$this->form_fields['description']['default'] = _x( 'Shoplic NicePay payment gateway, mobile phone.', 'Method description', 'shoplic-pg' );
		}
	}
}
