<?php
/**
 * Translation menu.
 */

/**
 * Filter class name.
 */
add_filter( 'wp_edit_nav_menu_walker', function( $class_name ) {
	return \Tarosky\Shouyaku\NavMenu::class;
} );

/**
 * Save custom menu title.
 */
add_action( 'wp_update_nav_menu_item', function( $menu_id, $menu_item_db_id, $args ) {
	foreach ( shouyaku_get_locales() as $code => $lable ) {
		if ( $code === shouyaku_original_locale() ) {
			continue;
		}
		$meta_key = strtolower( '_locale_title_' . $code );
		$titles   = filter_input( INPUT_POST, 'shouyaku' . $meta_key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( isset( $titles[ $menu_item_db_id ] ) && $titles[ $menu_item_db_id ] ) {
			update_post_meta( $menu_item_db_id, $meta_key, $titles[ $menu_item_db_id ] );
		} else {
			delete_post_meta( $menu_item_db_id, $meta_key );
		}
	}
}, 10, 3 );

/**
 * Change nav menu title on front end.
 */
add_filter( 'wp_setup_nav_menu_item', function( $item ) {
	if ( is_admin() ) {
		return $item;
	}
	$locale = shouyaku_user_locale();
	if ( ! $locale || $locale == shouyaku_original_locale() ) {
		return $item;
	}
	$meta_key = strtolower( '_locale_title_' . $locale );
	if ( $title = get_post_meta( $item->ID, $meta_key, true ) ) {
		$item->title = $title;
	}
	return $item;
} );