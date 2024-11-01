<?php
/**
 * SHPG: Register interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'SHPG_Register' ) ) {
	interface SHPG_Register {
		/**
		 * Get list of regs.
		 *
		 * @return Generator
		 */
		public function get_items(): Generator;

		/**
		 * Register all regs.
		 */
		public function register();
	}
}
