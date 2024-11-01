<?php
/**
 * NBPC: uninstall script.
 */

if ( ! ( defined( 'WP_UNINSTALL_PLUGIN' ) && WP_UNINSTALL_PLUGIN ) ) {
	exit;
}

require_once __DIR__ . '/index.php';
require_once __DIR__ . '/core/uninstall-functions.php';

$shpg_uninstall = shpg()->registers->uninstall;
if ( $shpg_uninstall ) {
	$shpg_uninstall->register();
}

// You may use these functions to purge data.
// shpg_cleanup_option();
// shpg_cleanup_meta();
// shpg_cleanup_terms();
// shpg_cleanup_posts();
