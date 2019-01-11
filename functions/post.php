<?php
/**
 * Post related functions
 *
 * @package shouyaku
 */


/**
 * Returns transfer ready post types.
 *
 * @return array
 */
function shouyaku_transferable_post_types() {
	$defaults = [ 'post', 'page' ];
	return apply_filters( 'shouyaku_transferable_post_types', $defaults );
}

/**
 * Get post author's locale.
 *
 * @param null|int|WP_Post $post
 *
 * @return string
 */
function shouyaku_author_locale( $post = null ) {
	$post = get_post( $post );
	return get_user_locale( $post->post_author );
}

function shouyaku_post_locale( $post = null ) {

}

function shouyaku_post_has_locale( $locale = '', $post = null ) {
	$post = get_post( $post );
	
}