<?php
/**
 * SHPG: Main base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Main_Base' ) ) {
	/**
	 * Class SHPG_Main_Base
	 */
	abstract class SHPG_Main_Base implements SHPG_Module {
		use SHPG_Hook_Impl;
		use SHPG_Submodule_Impl;

		/**
		 * Singleton instance
		 *
		 * @var SHPG_Main_Base|null
		 */
		private static ?SHPG_Main_Base $instance = null;

		/**
		 * Free storage for the plugin.
		 *
		 * @var array
		 */
		private array $storage = [];

		/**
		 * Parsed module cache.
		 * Key:   input string notation.
		 * Value: found module, or false.
		 *
		 * @var array
		 */
		private array $parsed_cache = [];

		/**
		 * Module's constructor parameters.
		 * Key:   module name
		 * Value: Array for constructor.
		 *
		 * @var array
		 */
		private array $constructor_params = [];

		/**
		 * Get instance method.
		 *
		 * @return static
		 */
		public static function get_instance(): self {
			if ( is_null( self::$instance ) ) {
				self::$instance = new static();
				self::$instance->initialize();
			}
			return self::$instance;
		}

		/**
		 * SHPG_Main_Base constructor.
		 */
		protected function __construct() {
		}

		/**
		 * Return plugin main file.
		 *
		 * @return string
		 */
		public function get_main_file(): string {
			return SHPG_MAIN_FILE;
		}

		/**
		 * Get default priority
		 *
		 * @return int
		 */
		public function get_priority(): int {
			return SHPG_PRIORITY;
		}

		/**
		 * Retrieve submodule by given string notation.
		 *
		 * @param string $module_notation Notation string.
		 *
		 * @return object|false
		 */
		public function get_module_by_notation( string $module_notation ) {
			if ( class_exists( $module_notation ) ) {
				return $this->new_instance( $module_notation );
			}

			if ( $module_notation ) {
				if ( ! isset( $this->parsed_cache[ $module_notation ] ) ) {
					$module = $this;
					foreach ( explode( '.', $module_notation ) as $crumb ) {
						if ( isset( $module->{$crumb} ) ) {
							$module = $module->{$crumb};
						} else {
							$module = false;
							break;
						}
					}
					$this->parsed_cache[ $module_notation ] = $module;
				}

				return $this->parsed_cache[ $module_notation ];
			}

			return false;
		}

		/**
		 * Return submodule's callback method by given string notation.
		 *
		 * @param Closure|array|string $item Module and callback notation string.
		 *
		 * @return Closure|array|string
		 * @throws SHPG_Callback_Exception Thrown if callback is invalid.
		 * @example foo.bar@baz ---> array( shpg()->foo->bar, 'baz' )
		 */
		public function parse_callback( $item ) {
			if ( is_callable( $item ) ) {
				return $item;
			}

			if ( is_string( $item ) && false !== strpos( $item, '@' ) ) {
				[ $module_part, $method ] = explode( '@', $item, 2 );

				$module = $this->get_module_by_notation( $module_part );

				if ( $module && is_callable( [ $module, $method ] ) ) {
					return [ $module, $method ];
				}
			}

			throw new SHPG_Callback_Exception(
				sprintf(
				/* translators: formatted module name. */
					__( '%s is invalid for callback.', 'shoplic-pg' ),
					shpg_format_callback( $item )
				),
				100
			);
		}

		/**
		 * Get the theme version
		 *
		 * @return string
		 */
		public function get_version(): string {
			return SHPG_VERSION;
		}

		/**
		 * Get something from storage.
		 *
		 * @param string $key     Indexing key string.
		 * @param mixed  $default Value when key is missing in the storage.
		 */
		public function get( string $key, $default = '' ) {
			return $this->storage[ $key ] ?? $default;
		}

		/**
		 * Set something to storage.
		 *
		 * @param string $key   Indexing key string.
		 * @param mixed  $value Value to store.
		 */
		public function set( string $key, $value ): void {
			$this->storage[ $key ] = $value;
		}

		/**
		 * Load textdomain
		 *
		 * @used-by initialize()
		 */
		public function load_textdomain(): void {
			load_plugin_textdomain( 'shoplic-pg', false, wp_basename( dirname( $this->get_main_file() ) ) . '/languages' );
		}

		/**
		 * Return constructor params.
		 *
		 * @return array
		 */
		public function get_constructor_params(): array {
			return $this->constructor_params;
		}

		public function activation(): void {
			do_action( 'shpg_activation' );
		}

		public function deactivation(): void {
			do_action( 'shpg_deactivation' );
		}

		/**
		 * Callback for modules that should be started after init action.
		 *
		 * @return void
		 */
		public function assign_later_modules() {
			$this->assign_modules( $this->get_late_modules() );
		}

		/**
		 * Initialize plugin.
		 *
		 * This method is called only once.
		 *
		 * @uses SHPG_Main_Base::init_conditional_modules()
		 * @uses SHPG_Main_Base::load_textdomain()
		 */
		protected function initialize(): void {
			$this
				->setup_activation_deactivation()
				->assign_constructors( $this->get_constructors() )
				// Higher priority modules.
				->assign_modules( $this->get_early_modules() )
				// Lower priority modules.
				->add_action( 'init', 'assign_later_modules', $this->get_priority() + 10 )
			;

			if ( shpg_is_theme() ) {
				$this->add_action( 'after_setup_theme', 'load_textdomain', $this->get_priority() + 10 );
			} else {
				$this->add_action( 'plugins_loaded', 'load_textdomain', $this->get_priority() + 10 );
			}

			// Add 'init_conditional_modules' method if exists.
			if ( method_exists( $this, 'init_conditional_modules' ) ) {
				$this->add_action( 'wp', 'init_conditional_modules' );
			}

			$this->extra_initialize();

			do_action( 'shpg_initialized' );
		}

		/**
		 * Setup activation-deactivation hook.
		 *
		 * @return void
		 */
		protected function setup_activation_deactivation(): SHPG_Main_Base {
			if ( shpg_is_theme() ) {
				$this
					->add_action( 'after_switch_theme', 'activation' )
					->add_action( 'switch_theme', 'deactivation' )
				;
			} else {
				$file = plugin_basename( $this->get_main_file() );
				$this
					->add_action( "activate_$file", 'activation' )
					->add_action( "deactivate_$file", 'deactivation' )
				;
			}

			return $this;
		}

		/**
		 * Assign constructors
		 *
		 * @param array<string, array|callable> $constructors Each key should be FQCN, and value can be an array, or a callable which returns an array.
		 *
		 * @return $this
		 */
		protected function assign_constructors( array $constructors ): SHPG_Main_Base {
			$this->constructor_params = $constructors;
			return $this;
		}

		/**
		 * Return modules that are initialized before 'init' action.
		 *
		 * @return array
		 */
		abstract protected function get_early_modules(): array;

		/**
		 * Return modules that should be initialized after 'init' action.
		 *
		 * @return array
		 */
		abstract protected function get_late_modules(): array;

		/**
		 * Return constructor params
		 *
		 * @return array
		 */
		abstract protected function get_constructors(): array;

		/**
		 * Do SHPG_Main specific initialization.
		 */
		abstract protected function extra_initialize(): void;
	}
}
