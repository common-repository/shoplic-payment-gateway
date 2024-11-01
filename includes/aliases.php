<?php
/**
 * SHPG: Alias functions.
 *
 * @package sphg
 */

if ( ! function_exists( 'shpg_api_kakao_pay' ) ) {
	/**
	 * Return Kakao Pay API module instance.
	 *
	 * @return SHPG_API_Kakao_Pay
	 */
	function shpg_api_kakao_pay(): SHPG_API_Kakao_Pay {
		return shpg()->api->kakao_pay;
	}
}


if ( ! function_exists( 'shpg_api_nicepay' ) ) {
	/**
	 * Return NicePay API module instance.
	 *
	 * @return SHPG_API_NicePay
	 */
	function shpg_api_nicepay(): SHPG_API_NicePay {
		return shpg()->api->nicepay;
	}
}


if ( ! function_exists( 'shpg_api_nicepay_billing' ) ) {
	/**
	 * Return NicePay billing API module instance.
	 *
	 * @return SHPG_API_NicePay_Billing
	 */
	function shpg_api_nicepay_billing(): SHPG_API_NicePay_Billing {
		return shpg()->api->nicepay_billing;
	}
}


if ( ! function_exists( 'shpg_get_nicepay_notification_url' ) ) {
	/**
	 * Return NicePay virtual account notification URL.
	 *
	 * @return string
	 */
	function shpg_get_nicepay_notification_url(): string {
		return SHPG_Gateway_NicePay_VBank::get_notification_url();
	}
}
