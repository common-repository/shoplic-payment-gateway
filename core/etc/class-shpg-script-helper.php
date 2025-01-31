<?php
/**
 * SHPG: Script method chain helper
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Script_Helper' ) ) {
	class SHPG_Script_Helper {
		/**
		 * Parent module object.
		 *
		 * @var SHPG_Template_Impl|SHPG_Module
		 */
		private $parent;

		/**
		 * Script handle name.
		 *
		 * @var string
		 */
		private string $handle;

		/**
		 * Constructor method.
		 *
		 * @param SHPG_Template_Impl|SHPG_Module $parent Parent object.
		 * @param string                         $handle Script handle name.
		 */
		public function __construct( $parent, string $handle ) {
			$this->parent = $parent;
			$this->handle = $handle;
		}

		/**
		 * Return another script helper.
		 *
		 * @param string $handle Handle string.
		 *
		 * @return SHPG_Script_Helper
		 */
		public function script( string $handle ): SHPG_Script_Helper {
			return new SHPG_Script_Helper( $this->parent, $handle );
		}

		/**
		 * Return another style helper.
		 *
		 * @param string $handle Handle string.
		 *
		 * @return SHPG_Style_Helper
		 */
		public function style( string $handle ): SHPG_Style_Helper {
			return new SHPG_Style_Helper( $this->parent, $handle );
		}

		/**
		 * Enqueue the script.
		 */
		public function enqueue(): self {
			wp_enqueue_script( $this->handle );
			return $this;
		}

		/**
		 * Function wp_localize_script() wrapper.
		 *
		 * @param array  $l10n        Localization data.
		 * @param string $object_name JS object name.
		 *
		 * @return self
		 */
		public function localize( array $l10n = [], string $object_name = '' ): self {
			if ( empty( $object_name ) ) {
				$split = preg_split( '/[-_]/', $this->handle );
				if ( $split ) {
					$object_name = $split[0] . implode( '', array_map( 'ucfirst', array_slice( $split, 1 ) ) );
				}
			}

			wp_localize_script( $this->handle, $object_name, $l10n );

			return $this;
		}

		/**
		 * Function wp_set_script_translations() wrapper.
		 *
		 * @param string      $domain Textdomain.
		 * @param string|null $path   Path to translation.
		 *
		 * @return self
		 */
		public function script_translations( string $domain = 'default', string $path = null ): self {
			wp_set_script_translations( $this->handle, $domain, $path );
			return $this;
		}

		/**
		 * Finish call chain
		 *
		 * @return SHPG_Template_Impl|SHPG_Module
		 */
		public function then() {
			return $this->parent;
		}
	}
}
