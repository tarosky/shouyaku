<?php
/**
 * Rewrite rules
 *
 * @package shouyaku
 */

defined( 'ABSPATH' ) || die();

/**
 * Add rewrite rules for locale
 */
add_filter( 'query_vars', function( $vars ) {
	$vars[] = 'locale';
	return $vars;
}, 9999 );

add_action( 'pre_get_posts', function( WP_Query &$wp_query ) {
	if ( $locale = $wp_query->get( 'locale' ) ) {
	
	}
} );


