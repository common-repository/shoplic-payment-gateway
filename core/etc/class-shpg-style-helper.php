<?php
/**
 * SHPG: Style method chain helper
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Style_Helper' ) ) {
	class SHPG_Style_Helper {
		/**
		 * Parent module object
		 *
		 * @var SHPG_Template_Impl|SHPG_Module
		 */
		private $parent;

		/**
		 * Script handle
		 *
		 * @var string
		 */
		private string $handle;

		/**
		 * Constructor method
		 *
		 * @param SHPG_Template_Impl|SHPG_Module $parent Parent module object.
		 * @param string                         $handle Script handle.
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
		 * Enqueue the style.
		 *
		 * @return self
		 */
		public function enqueue(): self {
			wp_enqueue_style( $this->handle );
			return $this;
		}

		/**
		 * Finish call chain
		 *
		 * @return SHPG_Module|SHPG_Template_Impl
		 */
		public function then() {
			return $this->parent;
		}
	}
}
