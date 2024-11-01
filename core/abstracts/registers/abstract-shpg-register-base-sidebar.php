<?php
/**
 * SHPG: Sidebar register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_Sidebar' ) ) {
	abstract class SHPG_Register_Base_Sidebar implements SHPG_Register {
		use SHPG_Hook_Impl;

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'widgets_init', 'register' );
		}

		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Sidebar ) {
					$item->register();
				}
			}
		}
	}
}
