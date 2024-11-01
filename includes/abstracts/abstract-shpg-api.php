<?php
/**
 * SHPG: API Caller class
 *
 * @package shpg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_API' ) ) {
	abstract class SHPG_API implements SHPG_Module {
		/**
		 * Common API request method.
		 *
		 * @param string $url    Destination URL.
		 * @param string $method Method string. e.g. GET, POST.
		 * @param array  $args   Body.
		 *
		 * @return string
		 *
		 * @throws Exception When status is not 200.
		 * @see    wp_safe_remote_request()
		 */
		protected function request( string $url, string $method, array $args = [] ): string {
			$rp = self::get_default_args();

			$rp['method'] = strtoupper( $method );

			if ( 'GET' === $rp['method'] && ! empty( $args ) ) {
				$url = add_query_arg( urlencode_deep( $args ), $url );
			} elseif ( 'POST' === $rp['method'] ) {
				$rp['body'] = $args;
			}

			$response = wp_safe_remote_request( $url, $rp );

			if ( is_wp_error( $response ) ) {
				throw new Exception(
					__( 'API call failed due to an error.', 'shoplic-pg' ) . ' ' . $response->get_error_message()
				);
			}

			$status = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $status ) {
				throw new Exception( "$status: " . wp_remote_retrieve_response_message( $response ) );
			}

			return wp_remote_retrieve_body( $response );
		}

		/**
		 * Parse API call result.
		 *
		 * @param string $body     The server returns response in plain string.
		 * @param string $charset  Charset. 'euc-kr', or 'utf-8'.
		 * @param string $edi_type Content type. KV: key-value, JSON: json string.
		 *
		 * @return array The response anyway can be converted into an array.
		 */
		protected function parse_body( string $body, string $charset, string $edi_type ): array {
			$output = [];

			if ( 'euc-kr' === $charset ) {
				$body = mb_convert_encoding( $body, 'utf-8', 'euc-kr' );
			}

			if ( 'JSON' === $edi_type ) {
				$output = json_decode( $body, true );
			} elseif ( 'KV' === $edi_type ) {
				parse_str( $body, $output );
			}

			return $output;
		}

		/**
		 * Return our 'user-agent' header value, well, even though they never care about this.
		 *
		 * @return string
		 */
		protected static function get_user_agent(): string {
			return 'WordPress/' . get_bloginfo( 'version' ) . '; ' .
			       'Shoplic Payment Gateway/' . shpg()->get_version() . '; ' .
			       get_bloginfo( 'url' );
		}

		/**
		 * Return our default API call arguments.
		 *
		 * @return array<string, mixed>
		 */
		protected static function get_default_args(): array {
			return [
				'user-agent' => self::get_user_agent(),
				'body'       => '',
			];
		}
	}
}
