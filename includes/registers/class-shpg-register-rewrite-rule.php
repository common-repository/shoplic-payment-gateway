<?php
/**
 * SHPG: rewrite rule register
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Rewrite_Rule' ) ) {
	/**
	 * Rewrite rule register class
	 */
	class SHPG_Register_Rewrite_Rule extends SHPG_Register_Base_Rewrite_Rule {
		/**
		 * Get rewrite rule regs.
		 *
		 * @return Generator
		 *
		 * @uses SHPG_WooCommerce::noti_vbank()
		 */
		public function get_items(): Generator {
			yield new SHPG_Reg_Rewrite_Rule(
				'^shpg-noti/vbank/?$',
				'index.php?shpg-noti=vbank',
				'top',
				'wc@noti_vbank',
				'shpg-noti'
			);
		}
	}
}
