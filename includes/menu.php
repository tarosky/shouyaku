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

/**
 * Add widget translation.
 */
add_action( 'in_widget_form', function( WP_Widget $widget, &$form, $instance ) {
	if ( ! isset( $instance['title'] ) ) {
		return;
	}
	foreach ( shouyaku_get_locales() as $code => $label ) {
		if ( ! $code || $code == shouyaku_original_locale() ) {
			continue;
		}
		$id    = strtolower( 'title_' . $code );
		$title = isset( $instance[ $id ] ) ? $instance[ $id ] : '';
		?>
		<p>
			<label for="<?php echo $widget->get_field_id( $id ) ?>">
				<?php echo esc_html( sprintf( __( 'Widget Title in %s', 'shouyaku' ), $label ) ) ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $widget->get_field_id( $id ) ) ?>" name="<?php echo esc_attr( $widget->get_field_name( $id ) ) ?>" value="<?php echo esc_attr( $title ) ?>" />
		</p>
		<?php
	}
}, 10, 3 );

/**
 * Save widget
 */
add_filter( 'widget_update_callback', function( $instance, $new_instance, $old_instance ) {
	foreach ( shouyaku_get_locales() as $code => $label ) {
		if ( ! $code || $code == shouyaku_original_locale() ) {
			continue;
		}
		$name = strtolower( 'title_' . $code );
		$instance[ $name ] = isset( $new_instance[ $name ] ) ? $new_instance[ $name ] : '';
	}
	return $instance;
}, 10, 3 );


/**
 * Filter widget title.
 */
add_filter( 'widget_title', function( $title, $instance, $id_base  ) {
	$locale = shouyaku_user_locale();
	if ( ! $locale || $locale == shouyaku_original_locale() ) {
		return $title;
	}
	$key = strtolower( 'title_' . $locale );
	return isset( $instance[ $key ] ) ? $instance[ $key ] : $title;
}, 10, 3 );

