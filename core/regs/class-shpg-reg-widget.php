<?php
/**
 * SHPG: Widget reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Reg_Widget' ) ) {
	class SHPG_Reg_Widget implements SHPG_Reg {
		/**
		 * @var object|string
		 */
		public $widget;

		/**
		 * Constructor method
		 *
		 * @param string|object $widget
		 */
		public function __construct( $widget ) {
			$this->widget = $widget;
		}

		public function register( $dispatch = null ): void {
			register_widget( $this->widget );
		}
	}
}
