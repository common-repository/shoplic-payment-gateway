<?php
/**
 * SHPG: Admin module interface
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'SHPG_Admin_Module' ) ) {
	interface SHPG_Admin_Module extends SHPG_Module {
	}
}
