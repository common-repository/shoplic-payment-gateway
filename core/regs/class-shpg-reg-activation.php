<?php
/**
 * SHPG: Activation reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Reg_Activation' ) ) {
	class SHPG_Reg_Activation implements SHPG_Reg {
		/**
		 * Callback for activation.
		 *
		 * @var Closure|array|string
		 */
		public $callback;

		/**
		 * Arguments for callback function.
		 *
		 * @var array
		 */
		public array $args;

		/**
		 * Use error_log.
		 *
		 * @var bool
		 */
		public bool $error_log;

		/**
		 * Constructor method.
		 *
		 * @param Closure|array|string $callback  Callback for activation.
		 * @param array                $args      Arguments for callback function.
		 * @param bool                 $error_log Use error_log.
		 */
		public function __construct( $callback, array $args = [], bool $error_log = true ) {
			$this->callback  = $callback;
			$this->args      = $args;
			$this->error_log = $error_log;
		}

		/**
		 * Method name can mislead, but it does its activation callback job.
		 *
		 * @param null $dispatch Unused.
		 *
		 * @return void
		 */
		public function register( $dispatch = null ): void {
			try {
				$callback = shpg_parse_callback( $this->callback );
			} catch ( SHPG_Callback_Exception $e ) {
				$error = new WP_Error();
				$error->add(
					'shpg_activation_error',
					sprintf(
						'Activation callback handler `%s` is invalid. Please check your activation register items.',
						shpg_format_callback( $this->callback )
					)
				);
				// $error is a WP_Error instance.
				// phpcs:ignore WordPress.Security.EscapeOutput
				wp_die( $error );
			}

			if ( $callback ) {
				if ( $this->error_log ) {
					error_log( sprintf( 'Activation callback started: %s', shpg_format_callback( $this->callback ) ) );
				}

				call_user_func_array( $callback, $this->args );

				if ( $this->error_log ) {
					error_log( sprintf( 'Activation callback finished: %s', shpg_format_callback( $this->callback ) ) );
				}
			}
		}
	}
}
