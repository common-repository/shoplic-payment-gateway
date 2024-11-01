<?php
/**
 * Plugin Name:       Shoplic Payment Gateways for WooCommerce
 * Plugin URI:        https://shoplic.kr
 * Description:       NicePay, and NicePay billing payment gateways for WooCommerce.
 * Version:           1.1.2
 * Requires at least: 5.3.0
 * Requires PHP:      7.4
 * Author:            Shoplic
 * Author URI:        https://shoplic.kr
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shoplic-pg
 * Domain Path:       /languages
 * CPBN version:      1.5.5
 *
 * @package shpg
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

const SHPG_MAIN_FILE = __FILE__;
const SHPG_VERSION   = '1.1.2';
const SHPG_PRIORITY  = 400;

shpg();
