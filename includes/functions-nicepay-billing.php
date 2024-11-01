<?php
/**
 * SHPG: NicePay billing functions
 *
 * @package sphg
 */

if ( ! function_exists( 'shpg_generate_tid' ) ) {
	/**
	 * Generate TID
	 *
	 * @param string                                     $mid      MID. Usually 10 chars.
	 * @param int|string|DateTime|DateTimeImmutable|null $datetime Time info for shpg_get_datetime_string().
	 *                                                             Defaults to current time.
	 *
	 * @return string
	 */
	function shpg_generate_tid( string $mid, $datetime = null ): string {
		static $pad = 0;

		/*
		MID:       10 chars  'nictest00m'
		Fixed:      4 chars  '0116'
		Datetime:  12 chars  '211104165223'
		Padding     1 char
		Rest:       3 chars
		-----------------------------------
		           30 chars
		*/
		$mid      = shpg_str_limit( $mid, 10 );
		$datetime = shpg_get_datetime_string( $datetime, 'ymdHis' );
		$random   = wp_generate_password( 3, false );

		$output = shpg_str_limit( $mid . '0116' . $datetime . $pad . $random, 30 );
		$pad    = ( ++ $pad ) % 10;

		return $output;
	}
}
