<?php
/**
 * SHPG: Script register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Script' ) ) {
	class SHPG_Register_Script extends SHPG_Register_Base_Script {
		public function get_items(): Generator {
			if ( is_admin() ) {
				// Admin-only scripts.
				yield new SHPG_Reg_Script(
					'shpg-nicepay-cred-import',
					$this->src_helper( 'nicepay/cred-import.js' ),
					[ 'jquery' ],
					null,
					true
				);
			} else {
				// Front-only scripts.
				yield new SHPG_Reg_Script(
					'shpg-nicepay',
					'https://web.nicepay.co.kr/v3/webstd/js/nicepay-3.0.js',
					[],
					false,
					true
				);

				yield new SHPG_Reg_Script(
					'shpg-nicepay-payment-request',
					$this->src_helper( 'nicepay/payment-request.js' ),
					[],
					null,
					true
				);

				// NicePay billing payment field.
				yield new SHPG_Reg_Script(
					'shpg-nicepay-billing',
					'dist/payment-fields.js',
					SHPG_Reg_Script::WP_SCRIPT,
					null,
					true
				);
			}
		}
	}
}
