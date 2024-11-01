<?php
/**
 * SHPG: Post meta register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SHPG_Register_Post_Meta' ) ) {
	/**
	 * Post meta reg class
	 *
	 * @property-read SHPG_Reg_Meta $payment_result
	 * @property-read SHPG_Reg_Meta $refund_result
	 * @property-read SHPG_Reg_Meta $notification_result
	 * @property-read SHPG_Reg_Meta $test_mode
	 * @property-read SHPG_Reg_Meta $token_id
	 * @property-read SHPG_Reg_Meta $temp_tid
	 */
	class SHPG_Register_Post_Meta extends SHPG_Register_Base_Meta {
		/**
		 * Define items here.
		 *
		 * To use alias, do not forget to return generator as 'key => value' form!
		 *
		 * @return Generator
		 */
		public function get_items(): Generator {
			// Common: Payment API call result response.
			yield 'payment_result' => new SHPG_Reg_Meta(
				'post',
				'_shpg_payment_result',
				[
					'object_subtype'    => 'shop_order',
					'type'              => 'string',
					'description'       => 'Payment API call result.',
					'default'           => '',
					'single'            => true,
					'sanitize_callback' => 'shpg_sanitize_api_call_result',
					'auth_callback'     => null,
					'show_in_rest'      => false,
				]
			);

			// Common: Refund API call result response.
			yield 'refund_result' => new SHPG_Reg_Meta(
				'post',
				'_shpg_refund_result',
				[
					'object_subtype'    => 'shop_order',
					'type'              => 'string',
					'description'       => 'Refund API call result.',
					'default'           => '',
					'single'            => false,
					'sanitize_callback' => 'shpg_sanitize_api_call_result',
					'auth_callback'     => null,
					'show_in_rest'      => false,
				]
			);

			// NicePay: Notification message.
			yield 'notification_result' => new SHPG_Reg_Meta(
				'post',
				'_shpg_noti_result',
				[
					'object_subtype'    => 'shop_order',
					'type'              => 'string',
					'description'       => 'Notification message.',
					'default'           => '',
					'single'            => true,
					'sanitize_callback' => 'shpg_sanitize_api_call_result',
					'auth_callback'     => null,
					'show_in_rest'      => false,
				]
			);

			// Common: Test mode payment flag.
			yield 'test_mode' => new SHPG_Reg_Meta(
				'post',
				'_shpg_test_mode',
				[
					'object_subtype'    => 'shop_order',
					'type'              => 'boolean',
					'description'       => 'Payment is done in test mode or not.',
					'default'           => false,
					'single'            => true,
					'sanitize_callback' => function ( $var ) {
						return filter_var( $var, FILTER_VALIDATE_BOOLEAN );
					},
					'auth_callback'     => null,
					'show_in_rest'      => false,
				]
			);

			// NicePay: Billing key reference.
			yield 'token_id' => new SHPG_Reg_Meta(
				'post',
				'_shpg_token_id',
				[
					'object_subtype'    => 'shop_subscription',
					'type'              => 'integer',
					'description'       => 'woocommerce_payment_tokens.token_id',
					'default'           => 0,
					'single'            => true,
					'sanitize_callback' => 'absint',
					'auth_callback'     => null,
					'show_in_rest'      => false,
				]
			);

			// KakaoPay: Temorary transaction id.
			yield 'temp_tid' => new SHPG_Reg_Meta(
				'post',
				'_shpg_temp_tid',
				[
					'object_subtype'    => 'shop_order',
					'type'              => 'string',
					'description'       => '카카오페이 결제 처리 중 받는 tid 값을 임시 저장하는 필드.',
					'default'           => '',
					'single'            => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => null,
					'show_in_rest'      => false,
				]
			);
		}
	}
}
