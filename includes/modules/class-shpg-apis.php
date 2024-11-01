<?php
/**
 * SHPG: API submodule wrap
 *
 * @package shpg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_APIs' ) ) {
	/**
	 * SHPG_APIs class
	 *
	 * @property-read SHPG_API_Kakao_Pay       $kakao_pay
	 * @property-read SHPG_API_NicePay         $nicepay
	 * @property-read SHPG_API_NicePay_Billing $nicepay_billing
	 */
	class SHPG_APIs implements SHPG_Module {
		use SHPG_Submodule_Impl;

		/**
		 * Constructor
		 *
		 * Define submodules.
		 */
		public function __construct() {
			$this->assign_modules(
				[
					'kakao_pay'       => function () { return new SHPG_API_Kakao_Pay(); },
					'nicepay'         => function () { return new SHPG_API_NicePay(); },
					'nicepay_billing' => function () { return new SHPG_API_NicePay_Billing(); },
				]
			);
		}
	}
}
