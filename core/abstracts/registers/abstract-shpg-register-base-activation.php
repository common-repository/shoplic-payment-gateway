<?php
/**
 * SHPG: Activation register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_Activation' ) ) {
	abstract class SHPG_Register_Base_Activation implements SHPG_Register {
		use SHPG_Hook_Impl;
		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'shpg_activation', 'register' );
		}

		/**
		 * Method name can mislead, but it does activation callback jobs.
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Activation ) {
					$item->register();
				}
			}
		}
	}
}
