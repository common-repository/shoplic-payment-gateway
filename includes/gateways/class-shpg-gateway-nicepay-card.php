<?php
/**
 * SHPG: NicePay payment gateway: credit card.
 *
 * @package shpg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Gateway_NicePay_Card' ) ) {
	/**
	 * NicePay Credit Card Payment Gateway
	 */
	class SHPG_Gateway_NicePay_Card extends SHPG_Gateway_NicePay {
		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->id                 = 'shpg_nicepay_card';
			$this->method_title       = _x( 'Credit Card', 'Method title', 'shoplic-pg' );
			$this->method_description = _x( 'Proceed with payment by credit card.', 'Method description', 'shoplic-pg' );
			$this->pay_method         = 'CARD';

			parent::__construct();
		}
	}
}
