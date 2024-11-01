<?php
/**
 * SHPG: EJS enqueue
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_EJS_Queue' ) ) {
	class SHPG_EJS_Queue implements SHPG_Module {
		use SHPG_Template_Impl;

		/**
		 * EJS data store.
		 *
		 * - Key: relative template path.
		 * - Value: array. 'context', and 'variant' keys are stored.
		 *
		 * @var array<string, array>
		 */
		private array $queue = [];

		/**
		 * Constructor method.
		 */
		public function __construct() {
			if ( is_admin() ) {
				if ( ! has_action( 'admin_print_footer_scripts', [ $this, 'do_template' ] ) ) {
					add_action( 'admin_print_footer_scripts', [ $this, 'do_template' ], shpg()->get_priority() );
				}
			} elseif ( ! has_action( 'wp_print_footer_scripts', [ $this, 'do_template' ] ) ) {
				add_action( 'wp_print_footer_scripts', [ $this, 'do_template' ], shpg()->get_priority() );
			}
		}

		/**
		 * Enqueue EJS template.
		 *
		 * @param string $relpath Relative path to EJS template.
		 * @param array  $data    Context and variant info.
		 *
		 * @return void
		 * @see    SHPG_Template_Impl::enqueue_ejs()
		 */
		public function enqueue( string $relpath, array $data = [] ): void {
			$this->queue[ $relpath ] = $data;
		}

		/**
		 * Create EJS script tags.
		 *
		 * @return void
		 */
		public function do_template(): void {
			// Output a EJS template. Escaping is too much here.
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

			foreach ( $this->queue as $relpath => $data ) {
				$tmpl_id = 'tmpl-' . pathinfo( wp_basename( $relpath ), PATHINFO_FILENAME );
				$content = $this->render_file(
					$this->locate_file( 'ejs', $relpath, $data['variant'] ),
					$data['context'],
					false
				);
				$content = preg_replace( '/\s+/', ' ', $content );
				$content = trim( str_replace( '> <', '><', $content ) );

				if ( ! empty( $content ) ) {
					echo "\n<script type='text/template' id='" . esc_attr( $tmpl_id ) . "'>\n";
					echo $content;
					echo "\n</script>\n";
				}
			}

			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
