<?php
/**
 * Locale related functions.
 *
 * @package shouyaku
 */

/**
 * Get available language codes.
 *
 * @return array
 */
function shouyaku_get_locales() {
	$defaults = [
		'ja'    => '日本語',
		'en_US' => 'English'
	];
	return apply_filters( 'shouyaku_get_locale', $defaults );
}

/**
 * Get original locale.
 *
 * @param string $locale
 *
 * @return string
 */
function shouyaku_original_locale( $locale = '' ) {
	static $original_locale;
	if ( $locale ) {
		$original_locale = $locale;
	} elseif ( ! $original_locale ) {
		$original_locale = get_locale();
	}
	return $original_locale;
}

/**
 * Get current user locale.
 *
 * @return string
 */
function shouyaku_user_locale() {
	if ( ! is_user_logged_in() ) {
		// TODO: user language.
		return get_locale();
	} else {
		return get_user_locale();
	}
}

/**
 * Detect if change locale for
 *
 * @return bool
 */
function shouyaku_should_change_locale() {
	static $done          = false;
	static $should_change = false;
	if ( ! $done ) {
		$should_change = get_locale() != shouyaku_user_locale();
		$done = true;
	}
	return $should_change;
}

/**
 * Detect if post should be translated.
 *
 * @return string
 */
function shouyaku_should_translate_to() {
	$locale        = '';
	$orig_locale = shouyaku_original_locale();
	$user_locale   = shouyaku_user_locale();
	
	if ( ( $query_lang = get_query_var( 'locale' ) ) && array_key_exists( $query_lang, shouyaku_get_locales() ) ) {
		// Check global flag.
		$locale = $query_lang;
	} elseif ( is_user_logged_in() ) {
		$locale = $user_locale;
	}
	return $locale;
}

/**
 *
 *
 * @param string $to_locale
 *
 * @return string
 */
function shouyaku_page_translated( $to_locale = '' ) {
	static $translated_to = '';
	if ( $to_locale ) {
		$translated_to = $to_locale;
	}
	return $translated_to;
}
