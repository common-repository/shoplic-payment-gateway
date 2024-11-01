<?php
/**
 * SHPG: Style register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Style' ) ) {
	/**
	 * Style register class
	 */
	class SHPG_Register_Style extends SHPG_Register_Base_Style {
		/**
		 * Get reg items.
		 *
		 * @return Generator
		 */
		public function get_items(): Generator {
			yield new SHPG_Reg_Style(
				'shpg-shop-order-payment-data',
				$this->src_helper( 'admins/shop-order-payment-data.css' )
			);

			yield new SHPG_Reg_Style(
				'shpg-payment-fields',
				plugins_url( 'assets/js/dist/payment-fields.css', shpg()->get_main_file() )
			);
		}
	}
}
