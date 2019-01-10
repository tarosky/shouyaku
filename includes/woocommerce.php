<?php
/**
 * WooCommerce glue
 *
 * @package shouyaku
 */

defined( 'ABSPATH' ) || die();

// Render language switcher.
add_action( 'woocommerce_edit_account_form', function () {
	$locales = shouyaku_get_locales();
	if ( ! $locales ) {
		return;
	}
	wp_nonce_field( 'shouyaku_woo_profile_updated', '_shouyakuwoononce', false );
	?>
	<fieldset>
		<legend><?php esc_html_e( 'Language Setting', 'shouyaku' ); ?></legend>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="locale"><?php esc_html_e( 'Your prefered Language', 'woocommerce' ); ?></label>
			<select class="form-control" id="locale" name="locale">
				<?php foreach ( $locales as $locale => $label ) : ?>
					<option value="<?php echo esc_attr( $locale ) ?>" <?php selected( get_user_locale( get_current_user_id() ), $locale ) ?>>
						<?php echo esc_html( $label ) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
	</fieldset>
	<div class="clear"></div>
	<?php
} );

// Save locale if woocommerce updates user account.
add_action( 'woocommerce_save_account_details', function( $user_id ) {
	if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_shouyakuwoononce' ), 'shouyaku_woo_profile_updated' ) ) {
		return;
	}
	update_user_meta( $user_id, 'locale', filter_input( INPUT_POST , 'locale' ) );
} );
