<?php
/**
 * SHPG: Capability reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Reg_Capability' ) ) {
	class SHPG_Reg_Capability implements SHPG_Reg {
		public string $role;

		public array $capabilities;

		/**
		 * Constructor method
		 *
		 * @param string $role
		 * @param array  $capabilities
		 */
		public function __construct( string $role, array $capabilities ) {
			$this->role         = $role;
			$this->capabilities = $capabilities;
		}

		public function register( $dispatch = null ): void {
			$role = get_role( $this->role );

			if ( $role ) {
				foreach ( $this->capabilities as $capability ) {
					$role->add_cap( $capability );
				}
			}
		}

		public function unregister(): void {
			$role = get_role( $this->role );

			if ( $role ) {
				foreach ( $this->capabilities as $capability ) {
					$role->remove_cap( $capability );
				}
			}
		}
	}
}
