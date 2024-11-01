<?php
/**
 * SHPG: Deactivation register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_Deactivation' ) ) {
	abstract class SHPG_Register_Base_Deactivation implements SHPG_Register {
		use SHPG_Hook_Impl;

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'shpg_deactivation', 'register' );
		}

		/**
		 * Method name can mislead, but it does deactivation callback jobs.
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Deactivation ) {
					$item->register();
				}
			}
		}
	}
}
