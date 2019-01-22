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
