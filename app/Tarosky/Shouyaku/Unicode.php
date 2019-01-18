<?php

namespace Tarosky\Shouyaku;


class Unicode {
	
	const CJK_REGEXP = '(\p{Han}|\p{Hangul}|\p{Katakana}|\p{Hiragana}|\p{Common})';
	
	/**
	 * @param string $string
	 * @param string $regexp
	 *
	 * @return float
	 */
	public static function ratio( $string, $regexp ) {
		if ( ! $len = mb_strlen( $string, 'utf-8' ) ) {
			return 0;
		} else if ( ! preg_match_all( '#' . $regexp . '#u', $string, $matches, PREG_SET_ORDER ) ) {
			return 0;
		} else {
			return count( $matches ) / mb_strlen( $string, 'utf-8' );
		}
	}
	
	/**
	 * Detect if text is CJK
	 *
	 * @param string $string String to Detect.
	 * @param float  $ratio  Threshold.
	 *
	 * @return bool
	 */
	public static function is_cjk( $string, $ratio = .5 ) {
		return $ratio < self::ratio( $string, self::CJK_REGEXP );
	}
	
}
