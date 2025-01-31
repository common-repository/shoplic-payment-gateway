<?php
/**
 * SHPG: WP CLI register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_WP_CLI' ) ) {
	abstract class SHPG_Register_Base_WP_CLI implements SHPG_Register {
		use SHPG_Hook_Impl;

		/**
		 * Constructor method.
		 */
		public function __construct() {
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				$this->add_action( 'plugins_loaded', 'register' );
			}
		}

		/**
		 * @return void
		 *
		 * @throws Exception Thrown from WP_CLI.
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_WP_CLI ) {
					$item->register();
				}
			}
		}
	}
}
