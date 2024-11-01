<?php
/**
 * SHPG: Shortcode reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Reg_Shortcode' ) ) {
	class SHPG_Reg_Shortcode implements SHPG_Reg {
		public string $tag;

		/**
		 * @var string|callable
		 */
		public $callback;

		/**
		 * @var string|callable|null
		 */
		public $heading_action;

		/**
		 * Constructor method
		 *
		 * @param string               $tag
		 * @param string|callable      $callback
		 * @param string|callable|null $heading_action
		 */
		public function __construct( string $tag, $callback, $heading_action = null ) {
			$this->tag            = $tag;
			$this->callback       = $callback;
			$this->heading_action = $heading_action;
		}

		public function register( $dispatch = null ): void {
			add_shortcode( $this->tag, $dispatch );
		}
	}
}
