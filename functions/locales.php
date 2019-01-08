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
 * Returns transfer ready post types.
 *
 * @return array
 */
function shouyaku_transferable_post_types() {
	$defaults = [ 'post', 'page' ];
	return apply_filters( 'shouyaku_transferable_post_types', $defaults );
}

/**
 * Get translation-ready taxonomies
 *
 * @return string[]
 */
function shouyaku_transferable_terms() {
	$defaults = [ 'post_tag', 'category' ];
	return (array) apply_filters( 'shouyaku_transferable_terms', $defaults );
}

/**
 * Detect if taxonomy is transfer ready.
 *
 * @param array $taxonomy
 *
 * @return bool
 */
function shouyaku_is_taransferable_taxonomy( $taxonomy ) {
	$taxonomies = shouyaku_transferable_terms();
	return in_array( $taxonomy, $taxonomies );
}

/**
 * Get post author's locale.
 *
 * @param null|int|WP_Post $post
 *
 * @return string
 */
function shouyaku_author_locale( $post = null ) {
	$post = get_post( $post );
	return get_user_locale( $post->post_author );
}

function shouyaku_post_locale( $post = null ) {

}

function shouyaku_post_has_locale( $locale = '', $post = null ) {
	$post = get_post( $post );
	
}

/**
 * Get localised term name.
 *
 * @param WP_Term|int $term
 * @param string      $locale
 * @param string      $taxonomy
 *
 * @return string
 */
function shouyaku_term_name( $term, $locale, $taxonomy = '' ) {
	$term = get_term( $term, isset( $term->taxonomy ) ? $term->taxonomy : $taxonomy );
	$name = $term->name;
	$term_meta = get_term_meta( $term->term_id, 'locale_name_' . strtolower( $locale ), true );
	//var_dump( $term_meta, $locale, $term->term_id );
	return $term_meta ?: $name;
}
