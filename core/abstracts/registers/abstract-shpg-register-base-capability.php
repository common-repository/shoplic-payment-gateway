<?php
/**
 * SHPG: Capability register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_Capability' ) ) {
	abstract class SHPG_Register_Base_Capability implements SHPG_Register {
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Capability ) {
					$item->register();
				}
			}
		}

		public function unregister(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Capability ) {
					$item->unregister();
				}
			}
		}
	}
}
