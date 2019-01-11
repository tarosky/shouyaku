<?php
/**
 * Term related hooks
 *
 * @package $terms
 */

defined( 'ABSPATH' ) || die();

// Register i18n
add_action( 'admin_init', function () {
	$locales = shouyaku_get_locales();
	if ( ! $locales ) {
		return;
	}
	wp_enqueue_style( 'shouyaku-admin' );
	foreach ( shouyaku_transferable_terms() as $taxonomy ) {
		// Make taxonomy available.
		// Display notification.
		add_action( $taxonomy . '_term_edit_form_top', function( $tag, $taxonomy ) {
			?>
			<p class="description">
				<?php echo wp_kses_post( sprintf( __( 'This term is ready for translation. Site Default is <code>%s</code>.', 'shouyaku' ), get_locale() ) ) ?>
			</p>
			<?php
		}, 10, 2 );
		// Display form item.
		add_action( "{$taxonomy}_edit_form_fields", function( $tag, $taxonomy ) use ( $locales ) {
			wp_enqueue_script( 'shouyaku-term-editor' );
			?>
			<tr>
				<th><label for="shouyaku-lang-selector"><?php esc_html_e( 'Translations', 'shouyaku' ) ?></label></th>
				<td class="shouyaku-translation">
					<?php wp_nonce_field( 'shouyaku_edit_tag', '_shouyakuedittagnonce', false ) ?>
					<select id="shouyaku-lang-selector">
						<?php foreach ( $locales as $locale => $label ) : ?>
							<option value="<?php echo esc_attr( $locale ) ?>" <?php selected( $locale, get_locale() ) ?>><?php echo esc_html( $label ) ?></option>
						<?php endforeach; ?>
					</select>
					<?php foreach ( $locales as $locale => $label ) : ?>
						<?php if ( $locale == get_locale() ) : ?>
							<div data-locale="<?php echo esc_attr( $locale ) ?>" class="shouyaku-term-editor">
								<p>
									<?php echo wp_kses_post( sprintf( __( '<code>%s</code> is default language.', 'shouyaku' ), $locale ) ) ?>
								</p>
							</div>
						<?php else : ?>
							<div data-locale="<?php echo esc_attr( $locale ) ?>" class="shouyaku-term-editor" hidden>
								<label>
									<?php echo esc_html( sprintf( __( 'Name in %s', 'shouyaku' ), $label ) ) ?><br />
									<input type="text" class="regular-text" name="term_name[<?php echo esc_attr( $locale ) ?>]"
										value="<?php echo esc_attr( get_term_meta( $tag->term_id, strtolower( "locale_name_{$locale}" ), true ) ) ?>"/>
								</label>
								<label>
									<?php echo esc_html( sprintf( __( 'Description in %s', 'shouyaku' ), $label ) ) ?><br />
									<textarea rows="5" class="shouyaku-term-description" name="term_desc[<?php echo esc_attr( $locale ) ?>]"><?php
										echo esc_textarea( get_term_meta( $tag->term_id, strtolower( "locale_desc_{$locale}" ), true ) );
									?></textarea>
								</label>
								<?php do_action( 'shouyaku_term_extras', $tag, $locale ) ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</td>
			</tr>
			<?php
		}, 10, 2 );
	}
} );

// Save term.
add_action( 'edit_term', function ( $term_id, $tt_id, $taxonomy ) {
	$nonce = filter_input( INPUT_POST, '_shouyakuedittagnonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'shouyaku_edit_tag' ) ) {
		return;
	}
	// Save all languages.
	$names        = (array) filter_input( INPUT_POST, 'term_name', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	$descriptions = (array) filter_input( INPUT_POST, 'term_desc', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	foreach ( shouyaku_get_locales() as $locale => $label ) {
		$name = $names[ $locale ] ?? '';
		update_term_meta( $term_id, strtolower( "locale_name_{$locale}" ), $name );
		$desc  = $descriptions[ $locale ] ?? '';
		update_term_meta( $term_id, strtolower( "locale_desc_{$locale}" ), $desc );
	}
	do_action( 'shouyaku_term_extras', $term_id, $taxonomy );
}, 10, 3 );


// Change term name.
add_filter( 'get_the_terms', function( $terms, $post_id, $taxonomy ) {
	if ( ! shouyaku_is_taransferable_taxonomy( $taxonomy ) || ! shouyaku_should_change_locale() ) {
		return $terms;
	}
	$locale = shouyaku_user_locale();
	return array_map( function( $term ) use ( $locale ) {
		// TODO: improve performance.
		//var_dump( $term, $locale );
		$term->name = shouyaku_term_name( $term, $locale );
		return $term;
	}, $terms );
}, 10, 3 );
