<?php
/**
 * Options related functions.
 */

// Admin init.
add_action( 'admin_menu', function() {
	add_options_page( __( 'Translation', 'shouyaku' ), __( 'Translation', 'shouyaku' ), 'manage_options', 'shouyaku-option', function() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Site Translation', 'shouyaku' ) ?></h2>
			<form method="POST" action="options.php">
				<?php
				settings_fields( 'shouyaku-option' );
				do_settings_sections( 'shouyaku-option' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	} );
} );

// Register Setting field.
add_action( 'admin_init', function() {
	foreach ( shouyaku_get_locales() as $locale => $label ) {
		if ( $locale == get_locale() ) {
			continue;
		}
		$locale_s = strtolower( $locale );
		$section = "shouyaku_option_{$locale_s}";
		add_settings_section(
			$section,
			$label,
			function() {
			
			},
			'shouyaku-option'
		);
		// Register items.
		foreach ( [
			[ 'blogname', sprintf( __( 'Site Title in %s', 'shouyaku' ), $label ), 'text' ],
			[ 'blogdescription', sprintf( __( 'Tagline in %s', 'shouyaku' ), $label ), 'text' ],
			[ 'date_format', sprintf( __( 'Date format in %s', 'shouyaku' ), $label ), 'text' ],
			[ 'time_format', sprintf( __( 'Time format in %s', 'shouyaku' ), $label ), 'text' ],
		] as list( $option_key, $option_label, $type ) ) {
			$id = "shouyaku_opt_{$option_key}_{$locale_s}";
			add_settings_field( $id, $option_label, function() use ( $option_key, $id, $type ) {
				$original_value = get_option( $option_key );
				switch ( $type ) {
					default:
						printf( '<input class="regular-text" type="%1$s" name="%2$s" id="%2$s" value="%3$s" />', esc_attr( $type ), esc_attr( $id ), esc_attr( get_option( $id ) ) );
						break;
				}
				echo wp_kses_post( sprintf( '<p class="description">%s</p>', sprintf( __( 'Original Value: <code>%s</code>', 'shouyaku' ), $original_value ) ) );
			}, 'shouyaku-option', $section );
			register_setting( 'shouyaku-option', $id );
		}
	}
} );

/**
 * Change options.
 */
foreach ( [ 'blogname', 'blogdescription', 'date_format', 'time_format' ] as $option_key ) {
	add_filter( "pre_option_{$option_key}", function( $value, $option, $default ) {
		if ( ! shouyaku_should_change_locale() || is_admin() ) {
			return $value;
		}
		$locale = shouyaku_user_locale();
		$option_key = strtolower( "shouyaku_opt_{$option}_$locale" );
		return get_option( $option_key, $value );
	}, 10, 3 );
}
