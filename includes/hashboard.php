<?php
/**
 * Hashboard related filters.
 *
 * @package shouyaku
 */

/**
 * Enable language.
 */
add_filter( 'hashboard_user_can_change_language', function() {
	return true;
} );

/**
 * Add language selection.
 */
add_filter( 'hashboard_locale_selector', function( $locales ) {
	foreach( shouyaku_get_locales() as $lang_code => $label ) {
		$locales[ $lang_code ] = $label;
	}
	return $locales;
} );
