<?php
/**
 * SHPG: Role register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_Role' ) ) {
	abstract class SHPG_Register_Base_Role implements SHPG_Register {
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Role ) {
					$item->register();
				}
			}
		}

		public function unregister(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Role ) {
					$item->unregister();
				}
			}
		}
	}
}
