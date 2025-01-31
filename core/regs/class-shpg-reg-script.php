<?php
/**
 * SHPG: Script reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Reg_Script' ) ) {
	class SHPG_Reg_Script implements SHPG_Reg {
		public const WP_SCRIPT = 'shpg-wp-script-generated';

		public string $handle;

		public string $src;

		/** @var array|string */
		public $deps;

		/** @var string|bool */
		public $ver;

		public bool $in_footer;

		/**
		 * Constructor method
		 *
		 * NOTE: If a script is built from wp-scripts, check these:
		 * - 'src'  is relative to assets/js.
		 * - 'deps' must be 'WP_SCRIPT' constant.
		 *
		 * @param string           $handle
		 * @param string           $src
		 * @param array|string     $deps
		 * @param null|string|bool $ver null: Use plugin version / true: Use WordPress version / false: No version.
		 * @param bool             $in_footer
		 */
		public function __construct(
			string $handle,
			string $src,
			$deps = [],
			$ver = null,
			bool $in_footer = false
		) {
			$this->handle    = $handle;
			$this->src       = $src;
			$this->deps      = $deps;
			$this->ver       = is_null( $ver ) ? shpg()->get_version() : $ver;
			$this->in_footer = $in_footer;
		}

		public function register( $dispatch = null ): void {
			if ( $this->handle && $this->src && ! wp_script_is( $this->handle, 'registered' ) ) {
				if ( self::WP_SCRIPT === $this->deps ) {
					$is_theme = shpg_is_theme();

					// When WP_SCRIPT is used, $src must be a relative path to assets/js.
					// But why not if it is handled here?
					if ( $is_theme ) {
						$root = get_stylesheet_directory_uri() . '/assets/js/';
					} else {
						$root = plugin_dir_url( shpg()->get_main_file() ) . 'assets/js/';
					}
					if ( str_starts_with( $this->src, $root ) ) {
						$this->src = substr( $this->src, strlen( $root ) );
					}

					$dir  = trim( dirname( $this->src ), '/\\' );
					$file = pathinfo( $this->src, PATHINFO_FILENAME ) . '.asset.php';

					if ( $is_theme ) {
						$path = path_join( get_stylesheet_directory(), "assets/js/$dir/$file" );
					} else {
						$path = path_join( dirname( shpg()->get_main_file() ), "assets/js/$dir/$file" );
					}

					if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
						return;
					}

					$info = include $path;

					if ( $is_theme ) {
						$this->src = get_stylesheet_directory_uri() . "/assets/js/$this->src";
					} else {
						$this->src = plugins_url( "assets/js/$this->src", shpg()->get_main_file() );
					}

					$this->deps      = $info['dependencies'] ?? [];
					$this->ver       = $info['version'] ?? shpg()->get_version();
					$this->in_footer = true;
				}

				wp_register_script(
					$this->handle,
					$this->src,
					$this->deps,
					// Three cases.
					// 1. string:     As-is.
					// 2. true:       Use WordPress version string.
					// 3. null/false: Converted to null. An empty version string.
					$this->ver ?: null,
					$this->in_footer
				);
			}
		}

		/**
		 * Return script reg which is created by wp-scripts
		 *
		 * @param string $handle Script handle.
		 * @param string $src    Source path, relative to assets/js.
		 *
		 * @return SHPG_Reg_Script
		 */
		public static function wp_script( string $handle, string $src ): SHPG_Reg_Script {
			return new SHPG_Reg_Script( $handle, $src, self::WP_SCRIPT );
		}
	}
}
