<?php
/**
 * SHPG: Registrable interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'SHPG_Reg' ) ) {
	interface SHPG_Reg {
		/**
		 * Register reg to core.
		 *
		 * @param mixed $dispatch Extra argument.
		 *
		 * @return mixed
		 */
		public function register( $dispatch = null );
	}
}
