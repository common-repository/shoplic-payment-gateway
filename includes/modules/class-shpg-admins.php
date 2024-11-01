<?php
/**
 * SHPG: Admin modules group
 *
 * Manage all admin modules
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Admins' ) ) {
	/**
	 * Admin modules group class
	 *
	 * @property-read SHPG_Admin_Shop_Order $shop_order
	 */
	class SHPG_Admins implements SHPG_Module {
		use SHPG_Submodule_Impl;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->assign_modules(
				[
					'shop_order' => SHPG_Admin_Shop_Order::class,
				]
			);
		}
	}
}
