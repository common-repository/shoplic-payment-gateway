<?php
/**
 * SHPG: Submit (admin-post.php) register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Base_Submit' ) ) {
	abstract class SHPG_Register_Base_Submit implements SHPG_Register {
		use SHPG_Hook_Impl;

		private array $inner_handlers = [];

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		/**
		 * @callback
		 * @actin       init
		 *
		 * @return void
		 */
		public function register(): void {
			$dispatch = [ $this, 'dispatch' ];

			foreach ( $this->get_items() as $item ) {
				if (
					$item instanceof SHPG_Reg_Submit &&
					$item->action &&
					! isset( $this->inner_handlers[ $item->action ] )
				) {
					$this->inner_handlers[ $item->action ] = $item->callback;
					$item->register( $dispatch );
				}
			}
		}

		public function dispatch(): void {
			// Boilerplate code cannot check nonce values.
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$action = sanitize_key( $_REQUEST['action'] ?? '' );

			if ( $action && isset( $this->inner_handlers[ $action ] ) ) {
				try {
					$callback = shpg_parse_callback( $this->inner_handlers[ $action ] );
					if ( is_callable( $callback ) ) {
						$callback();
					}
				} catch ( SHPG_Callback_Exception $e ) {
					$error = new WP_Error();
					$error->add(
						'shpg_submit_error',
						sprintf(
							'Submit callback handler `%s` is invalid. Please check your submit register items.',
							shpg_format_callback( $this->inner_handlers[ $action ] )
						)
					);
					// $error is a WP_Error instance.
					// phpcs:ignore WordPress.Security.EscapeOutput
					wp_die( $error, 404 );
				}
			}

			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}
	}
}
