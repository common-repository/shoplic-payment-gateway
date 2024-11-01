<?php
/**
 * SHPG: Cron register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_Cron' ) ) {
	abstract class SHPG_Register_Base_Cron implements SHPG_Register {
		use SHPG_Hook_Impl;

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this
				->add_action( 'shpg_activation', 'register' )
				->add_action( 'shpg_deactivation', 'unregister' )
			;
		}

		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Cron ) {
					$item->register();
				}
			}
		}

		public function unregister(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Cron ) {
					$item->unregister();
				}
			}
		}
	}
}
