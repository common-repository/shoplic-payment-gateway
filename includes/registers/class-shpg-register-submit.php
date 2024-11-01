<?php
/**
 * SHPG: Submit (admin-post.php) register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Submit' ) ) {
	/**
	 * Admin-post.php register class
	 */
	class SHPG_Register_Submit extends SHPG_Register_Base_Submit {
		/**
		 * Get submit (admin-post) reg items
		 *
		 * @return Generator
		 */
		public function get_items(): Generator {
			/**
			 * Raw data display request
			 *
			 * @uses SHPG_Admin_Shop_Order::display_raw_data()
			 */
			yield new SHPG_Reg_Submit( 'shpg_display_raw_data', 'admins.shop_order@display_raw_data' );

			/**
			 * Notification guide request
			 *
			 * @uses SHPG_Admin_Shop_Order::display_vbank_nodification_setup_guide()
			 */
			yield new SHPG_Reg_Submit(
				'shpg_display_vbank_notification_setup_guide',
				'admins.shop_order@display_vbank_nodification_setup_guide'
			);
		}
	}
}
