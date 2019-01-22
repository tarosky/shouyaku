<?php
/**
 * Change locale on front screen.
 */

defined( 'ABSPATH' ) || die();

add_action( 'init', function() {
	if ( is_user_logged_in() ) {
		if ( shouyaku_should_change_locale() ) {
			shouyaku_original_locale();
			switch_to_locale( shouyaku_user_locale() );
		}
	} else {
		// Check if cookie is set.
		$locale = str_replace( '-', '_', isset( $_COOKIE['locale'] ) ? $_COOKIE['locale'] : '' );
		if ( $locale && array_key_exists( $locale, shouyaku_get_locales() ) ) {
			shouyaku_original_locale();
			switch_to_locale( $locale );
		}
	}
}, 1 );

/**
 * Enqueue user locale.
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'shouyaku-user-locale' );
	wp_enqueue_style( 'shouyaku-notice' );
} );

/**
 * Add user language attributes to html element.
 */
add_filter( 'language_attributes', function ( $attributes ) {
	if ( is_user_logged_in() ) {
		$attributes .= sprintf( ' data-profile-locale="%s"', esc_attr( shouyaku_user_locale() ) );
	}
	return $attributes;
}, 11 );

/**
 * Display footer.
 */
add_action( 'wp_footer', function() {
	$locales = shouyaku_get_locales();
	if ( ! $locales ) {
		return;
	}
	$login = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'edit-account' ) : admin_url( 'profile.php' );
	?>
	<aside class="shouyaku-language-recommend">
		<div class="shouyaku-language-recommend-container">
			<div class="shouyaku-language-recommend-row">
				<div class="shouyaku-language-recommend-message">
					<p>
						<?php if ( is_user_logged_in() ) : ?>
							<?php echo wp_kses( sprintf( __( 'You can change language setting at <a href="%s">account page</a>.', 'shouyaku' ), esc_url( $login ) ), [ 'a' => [ 'href' => true ] ] ) ?>
						<?php else : ?>
							<?php esc_html_e( 'Select your preferred language.', 'shouyaku' ) ?>
						<?php endif; ?>
					</p>
				</div>
				<div class="shouyaku-language-recommend-selector">
					<button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fas fa-globe"></i>
						<?php esc_html_e( 'Language', 'shoyaku' ) ?>
					</button>
					<div class="dropdown-menu">
						<?php foreach ( $locales as $code => $label ) : $code = str_replace( '_', '-', $code ); ?>
						<a class="dropdown-item shouyaku-language-recommend-item" href="#" data-locale="<?php echo esc_attr( $code ) ?>">
							<?php echo esc_html( $label ) ?>
						</a>
						<?php endforeach; ?>
					</div>
					
				</div>
			</div>
		</div>
	</aside>
	<?php
}, 9999 );

/**
 * Register front end language switcher
 */
add_action( 'rest_api_init', function() {
	register_rest_route( 'shouyaku/v1', 'user', [
		[
			'methods' => 'POST',
			'args'    => [
				'locale' => [
					'type' => 'string',
					'description' => 'User locale.',
					'validate_callback' => function( $var ) {
						return '' === $var || array_key_exists( $var, shouyaku_get_locales() );
					},
					'default'  => '',
				],
			],
			'permission_callback' => function() {
				return current_user_can( 'read' );
			},
			'callback' => function( WP_REST_Request $request ) {
				$locale = $request->get_param( 'locale' );
				if ( $locale ) {
					update_user_meta( get_current_user_id(), 'locale', $locale );
				} else {
					delete_user_meta( get_current_user_id(), 'locale' );
				}
				return new WP_REST_Response( [
					'success' => true,
					'message' => __( 'Your language setting is updated.', 'shouyaku' ),
				] );
			},
		]
	] );
} );
