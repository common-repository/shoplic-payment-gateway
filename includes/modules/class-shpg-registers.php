<?php
/**
 * SHPG: Registers module
 *
 * Manage all registers
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Registers' ) ) {
	/**
	 * You can remove unused registers.
	 *
	 * @property-read SHPG_Register_Ajax         $ajax
	 * @property-read SHPG_Register_Post_Meta    $post_meta
	 * @property-read SHPG_Register_Rewrite_Rule $rewrite_rule
	 * @property-read SHPG_Register_Script       $script
	 * @property-read SHPG_Register_Style        $style
	 * @property-read SHPG_Register_Submit       $submit
	 * @property-read SHPG_Register_Term_Meta    $term_meta
	 * @property-read SHPG_Register_Uninstall    $uninstall
	 */
	class SHPG_Registers implements SHPG_Module {
		use SHPG_Submodule_Impl;

		public function __construct() {
			/**
			 * You can remove unused registers.
			 */
			$this->assign_modules(
				[
					'ajax'         => SHPG_Register_Ajax::class,
					'post_meta'    => SHPG_Register_Post_Meta::class,
					'rewrite_rule' => SHPG_Register_Rewrite_Rule::class,
					'script'       => SHPG_Register_Script::class,
					'style'        => SHPG_Register_Style::class,
					'submit'       => SHPG_Register_Submit::class,
					'term_meta'    => SHPG_Register_Term_Meta::class,
					'uninstall'    => function () { return new SHPG_Register_Uninstall(); },
				]
			);
		}
	}
}
