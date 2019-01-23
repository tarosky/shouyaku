<?php

namespace Tarosky\Shouyaku;

/**
 *
 *
 * @package Tarosky\Shouyaku
 */
class NavMenu extends \Walker_Nav_Menu_Edit {
	
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		parent::start_el( $output, $item, $depth, $args, $id );
		if ( ! shouyaku_get_locales() ) {
			return;
		}
		ob_start();
		?>
		<p class="shouyaku-menu-locales description description-wide">
			<?php foreach ( shouyaku_get_locales() as $code => $label ) :
				if ( $code === shouyaku_original_locale() ) {
					continue;
				}
				$meta_key = strtolower( '_locale_title_' . $code );
				?>
			<label class="shouyaku-menu-locales-label">
				<?php echo esc_html( sprintf( __( 'Navigation Label in %s', 'shouyaku' ), $label ) ) ?><br />
				<input type="text" class="widefat" name="<?php echo esc_attr( sprintf( 'shouyaku%s[%d]', $meta_key, $item->ID ) ) ?>" value="<?php echo esc_attr( get_post_meta( $item->ID, $meta_key, true ) ) ?>" />
			</label>
			<?php endforeach; ?>
		</p>
		<?php
		$translations = ob_get_contents();
		ob_end_clean();
		$output = preg_replace_callback( '#(<label for="edit-menu-item-title-' . $item->ID . '">)(.*?)(</label>)(.*?</p>)#us', function( $matches ) use ( $translations ) {
			array_shift( $matches );
			$matches[3] .= $translations;
			return implode( '', $matches );
		}, $output );
	}
}
