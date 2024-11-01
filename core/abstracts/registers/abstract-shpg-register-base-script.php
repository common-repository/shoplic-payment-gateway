<?php
/**
 * SHPG: Script register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_Script' ) ) {
	abstract class SHPG_Register_Base_Script implements SHPG_Register {
		use SHPG_Hook_Impl;

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		/**
		 * @callback
		 * @action       init
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof SHPG_Reg_Script ) {
					$item->register();
				}
			}
		}

		/**
		 * @param string $rel_path
		 * @param bool   $replace_min
		 *
		 * @return string
		 */
		protected function src_helper( string $rel_path, bool $replace_min = true ): string {
			$rel_path = trim( $rel_path, '\\/' );

			if ( $replace_min && shpg_script_debug() && substr( $rel_path, - 7 ) === '.min.js' ) {
				$rel_path = substr( $rel_path, 0, - 7 ) . '.js';
			}

			if ( shpg_is_theme() ) {
				return get_stylesheet_directory_uri() . "/assets/js/$rel_path";
			} else {
				return plugin_dir_url( shpg()->get_main_file() ) . "assets/js/$rel_path";
			}
		}
	}
}
