<?php
/**
 * Term related functions.
 *
 * @package shouyaku
 */


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