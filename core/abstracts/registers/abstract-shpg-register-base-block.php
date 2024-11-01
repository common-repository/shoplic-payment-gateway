<?php
/**
 * SHPG: Block register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_Block' ) ) {
	abstract class SHPG_Register_Base_Block implements SHPG_Register {
		use SHPG_Hook_Impl;

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		/**
		 * Register reg items.
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Block ) {
					$item->register();
				}
			}
		}
	}
}
