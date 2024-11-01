<?php
/**
 * SHPG: Kakao Pay API module
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_API_Kakao_Pay' ) ) {
	class SHPG_API_Kakao_Pay extends SHPG_API {
		private const URL = 'https://kapi.kakao.com/v1/payment';

		private string $admin_key = '';

		public function get_admin_key(): string {
			return $this->admin_key;
		}

		public function set_admin_key( string $admin_key ): self {
			$this->admin_key = $admin_key;
			return $this;
		}

		/**
		 * 결제 준비 API.
		 *
		 * @param array $args
		 *
		 * @return array
		 * @throws Exception
		 *
		 * @link https://developers.kakao.com/docs/latest/ko/kakaopay/single-payment#prepare
		 */
		public function ready( array $args ): array {
			return $this->call_api( 'ready', 'POST', $args );
		}

		/**
		 * 결제 승인 API.
		 *
		 * @param array $args
		 *
		 * @return array
		 * @throws Exception
		 *
		 * @link https://developers.kakao.com/docs/latest/ko/kakaopay/single-payment#approve
		 */
		public function approve( array $args ): array {
			return $this->call_api( 'approve', 'POST', $args );
		}

		/**
		 * 취소 요청 API.
		 *
		 * @param array $args
		 *
		 * @return array
		 * @throws Exception
		 *
		 * @link https://developers.kakao.com/docs/latest/ko/kakaopay/cancellation
		 */
		public function cancel( array $args ): array {
			return $this->call_api( 'cancel', 'POST', $args );
		}

		/**
		 * @throws Exception
		 */
		protected function call_api( string $endpoint, string $method = 'GET', array $args = [] ): array {
			$url = static::URL . '/' . ltrim( $endpoint, '/' );

			$params = [
				'method'     => $method,
				'user-agent' => static::get_user_agent(),
				'headers'    => [ 'Authorization' => 'KakaoAK ' . $this->get_admin_key() ],
			];

			if ( 'GET' == $method && ! empty( $args ) ) {
				$url = add_query_arg( urlencode_deep( $args ), $url );
			} else {
				$params['body'] = $args;
			}

			$r = wp_safe_remote_request( $url, $params );
			if ( is_wp_error( $r ) ) {
				throw new Exception( $r->get_error_message() );
			}

			$status = wp_remote_retrieve_response_code( $r );
			$body   = wp_remote_retrieve_body( $r );

			// Response is always a JSON.
			if ( is_string( $body ) ) {
				$body = json_decode( $body, true );
			}

			if ( 200 !== $status ) {
				$message  = wp_remote_retrieve_response_message( $r );
				$detailed = wp_remote_retrieve_body( $r );

				throw new Exception( "$status: $message, Body: $detailed", $status );
			}

			return $body ?: [];
		}
	}
} 
