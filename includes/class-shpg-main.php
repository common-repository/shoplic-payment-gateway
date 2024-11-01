<?php
/**
 * SHPG: Main class
 *
 * @package shpg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Main' ) ) {
	/**
	 * Class SHPG_Main
	 *
	 * @property-read SHPG_Admins      $admins
	 * @property-read SHPG_APIs        $api
	 * @property-read SHPG_My_Page     $my_page
	 * @property-read SHPG_Registers   $registers
	 * @property-read SHPG_WooCommerce $wc
	 */
	final class SHPG_Main extends SHPG_Main_Base {
        /**
         * Return root modules
         *
         * @return array
         * @used-by SHPG_Main_Base::initialize()
         */
		protected function get_early_modules(): array {
			return [
				'admins'    => SHPG_Admins::class,
				'registers' => SHPG_Registers::class,
			];
		}

		/**
		 * Return modules that should be initialized after 'init' action.
		 *
		 * Some features can be used properly after they are initialized,
		 *  and they are mostly done in the init callbacks.
		 *
		 * @return array
		 * @used-by SHPG_Main_Base::assign_init_modules()
		 */
		protected function get_late_modules(): array {
            return [
                'api'       => function () { return new SHPG_APIs(); },
                'my_page'   => SHPG_My_Page::class,
                'wc'        => SHPG_WooCommerce::class,
            ];
		}

		/**
		 * Return module's constructor.
		 *
		 * @return array
		 */
		protected function get_constructors(): array {
			return [];
		}

        /**
         * Do extra initialization.
         *
         * @return void
         */
		protected function extra_initialize(): void {
			// Do some plugin-specific initialization tasks.
			$plugin = plugin_basename( $this->get_main_file() );
			$this->add_filter( "plugin_action_links_$plugin", 'add_plugin_action_links' );
		}

		/**
		 * Predefined action links callback method.
		 *
		 * @param array $actions List of current plugin action links.
		 *
		 * @return array
		 */
		public function add_plugin_action_links( array $actions ): array {
			/* @noinspection HtmlUnknownTarget */
			return array_merge(
                [
                    'settings' => sprintf(
                    /* translators: %1$s: link to settings , %2$s: aria-label  , %3$s: text */
                        '<a href="%1$s" id="shpg-settings" aria-label="%2$s">%3$s</a>',
                        admin_url( 'admin.php?page=wc-settings&tab=checkout' ), // NOTE: You need to implement the page.
                        esc_attr__( 'Settings', 'shoplic-pg' ),
                        esc_html__( 'Settings', 'shoplic-pg' )
                    ),
                ],
                $actions
			);
		}
	}
}
