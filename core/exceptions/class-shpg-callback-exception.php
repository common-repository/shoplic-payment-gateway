<?php
/**
 * SHPG: Callback exception
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Callback_Exception' ) ) {
	class SHPG_Callback_Exception extends Exception {
	}
}
