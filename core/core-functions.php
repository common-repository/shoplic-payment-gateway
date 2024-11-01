<?php
/**
 * SHPG: functions.php
 */

/* Skip ABSPATH check for unit testing. */

if ( ! function_exists( 'shpg' ) ) {
	/**
	 * SHPG_Main alias.
	 *
	 * @return SHPG_Main
	 */
	function shpg(): SHPG_Main {
		return SHPG_Main::get_instance();
	}
}


if ( ! function_exists( 'shpg_is_theme' ) ) {
	/**
	 * Check if it is used as theme.
	 *
	 * @return bool
	 */
	function shpg_is_theme(): bool {
		return defined( 'SHPG_THEME' ) && SHPG_THEME;
	}
}


if ( ! function_exists( 'shpg_is_plugin' ) ) {
	/**
	 * Check if it is used as plugin. (default)
	 *
	 * @return bool
	 */
	function shpg_is_plugin(): bool {
		return ! shpg_is_theme();
	}
}


if ( ! function_exists( 'shpg_parse_module' ) ) {
	/**
	 * Retrieve submodule by given string notation.
	 *
	 * @param string $module_notation Module notation string.
	 *
	 * @return object|false;
	 */
	function shpg_parse_module( string $module_notation ) {
		return shpg()->get_module_by_notation( $module_notation );
	}
}


if ( ! function_exists( 'shpg_parse_callback' ) ) {
	/**
	 * Return submodule's callback method by given string notation.
	 *
	 * @param Closure|array|string $maybe_callback Maybe something can be callback function.
	 *
	 * @return callable|array|string
	 * @throws SHPG_Callback_Exception Thrown if callback is invalid.
	 * @example foo.bar@baz ---> array( shpg()->foo->bar, 'baz' )
	 */
	function shpg_parse_callback( $maybe_callback ) {
		return shpg()->parse_callback( $maybe_callback );
	}
}


if ( ! function_exists( 'shpg_option' ) ) {
	/**
	 * Alias function for option.
	 *
	 * @return SHPG_Register_Option|null
	 */
	function shpg_option(): ?SHPG_Register_Option {
		return shpg()->registers->option;
	}
}


if ( ! function_exists( 'shpg_comment_meta' ) ) {
	/**
	 * Alias function for comment meta.
	 *
	 * @return SHPG_Register_Comment_Meta|null
	 */
	function shpg_comment_meta(): ?SHPG_Register_Comment_Meta {
		return shpg()->registers->comment_meta;
	}
}


if ( ! function_exists( 'shpg_post_meta' ) ) {
	/**
	 * Alias function for post meta.
	 *
	 * @return SHPG_Register_Post_Meta|null
	 */
	function shpg_post_meta(): ?SHPG_Register_Post_Meta {
		return shpg()->registers->post_meta;
	}
}


if ( ! function_exists( 'shpg_term_meta' ) ) {
	/**
	 * Alias function for term meta.
	 *
	 * @return SHPG_Register_Term_Meta|null
	 */
	function shpg_term_meta(): ?SHPG_Register_Term_Meta {
		return shpg()->registers->term_meta;
	}
}


if ( ! function_exists( 'shpg_user_meta' ) ) {
	/**
	 * Alias function for user meta.
	 *
	 * @return SHPG_Register_User_Meta|null
	 */
	function shpg_user_meta(): ?SHPG_Register_User_Meta {
		return shpg()->registers->user_meta;
	}
}


if ( ! function_exists( 'shpg_script_debug' ) ) {
	/**
	 * Return SCRIPT_DEBUG.
	 *
	 * @return bool
	 */
	function shpg_script_debug(): bool {
		return apply_filters( 'shpg_script_debug', defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}
}


if ( ! function_exists( 'shpg_format_callback' ) ) {
	/**
	 * Format callback method or function.
	 *
	 * This method does not care about $callable is actually callable.
	 *
	 * @param Closure|array|string $callback Callback method to be formatted.
	 *
	 * @return string
	 */
	function shpg_format_callback( $callback ): string {
		if ( is_string( $callback ) ) {
			return $callback;
		}

		if (
			( is_array( $callback ) && 2 === count( $callback ) ) &&
			( is_object( $callback[0] ) || is_string( $callback[0] ) ) &&
			is_string( $callback[1] )
		) {
			if ( method_exists( $callback[0], $callback[1] ) ) {
				try {
					$ref = new ReflectionClass( $callback[0] );
					if ( $ref->isAnonymous() ) {
						return "{AnonymousClass}::$callback[1]";
					}
				} catch ( ReflectionException $e ) {
					return "Error while reflecting $callback[0].";
				}
			}

			if ( is_string( $callback[0] ) ) {
				return "$callback[0]::$callback[1]";
			}

			if ( is_object( $callback[0] ) && 'stdClass' !== get_class( (object) $callback[0] ) ) {
				return get_class( (object) $callback[0] ) . '::' . $callback[1];
			}
		} elseif ( $callback instanceof Closure ) {
			return '{Closure}';
		}

		return '{Unknown}';
	}
}


if ( ! function_exists( 'shpg_get_front_module' ) ) {
	/**
	 * Get front module.
	 *
	 * The module is chosen in SHPG_Register_Theme_Support::map_front_modules().
	 *
	 * @return SHPG_Front_Module
	 *
	 * @see SHPG_Register_Theme_Support::map_front_modules()
	 */
	function shpg_get_front_module(): SHPG_Front_Module {
		$hierarchy    = SHPG_Theme_Hierarchy::get_instance();
		$front_module = $hierarchy->get_front_module();

		if ( ! $front_module ) {
			$front_module = $hierarchy->get_fallback();
		}

		if ( ! $front_module instanceof SHPG_Front_Module ) {
			throw new RuntimeException( __( '$instance should be a front module instance.', 'shoplic-pg' ) );
		}

		return $front_module;
	}
}


if ( ! function_exists( 'shpg_react_refresh_runtime' ) ) {
	/**
	 * Helper function for properly enqueueing 'wp-react-refresh-runtime'.
	 *
	 * Gutenberg plugin must be installed, but its activation is optional.
	 *
	 * @return Generator
	 */
	function shpg_react_refresh_runtime(): Generator {
		if ( ! wp_script_is( 'wp-react-refresh-runtime', 'registered' ) ) {
			$path = WP_PLUGIN_DIR . '/gutenberg/build/react-refresh-runtime/index.min.asset.php';

			if ( file_exists( $path ) && is_readable( $path ) ) {
				$asset = include $path;

				if ( is_array( $asset ) && isset( $asset['dependencies'], $asset['version'] ) ) {
					yield new SHPG_Reg_Script(
						'wp-react-refresh-runtime',
						WP_PLUGIN_URL . '/gutenberg/build/react-refresh-runtime/index.min.js',
						$asset['dependencies'],
						$asset['version'],
						true
					);

					return;
				}
			}
		}

		yield;
	}
}
