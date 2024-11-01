<?php
/**
 * SHPG: Uninstall register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Uninstall' ) ) {
	class SHPG_Register_Uninstall extends SHPG_Register_Base_Uninstall {
		public function get_items(): Generator {
			yield new SHPG_Reg_Uninstall( 'registers.custom_table@unregister' );
		}
	}
}
