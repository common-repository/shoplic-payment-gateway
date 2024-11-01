<?php
/**
 * SHPG: Uninstall register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_Uninstall' ) ) {
	abstract class SHPG_Register_Base_Uninstall implements SHPG_Register {
		/**
		 * Method name can mislead, but it does uninstall callback jobs.
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Uninstall ) {
					$item->register();
				}
			}
		}
	}
}
