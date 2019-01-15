<?php
/**
 * Post related functions
 *
 * @package shouyaku
 */

/**
 * Get post type for translation
 *
 * @return string
 */
function shouyaku_translation_post_type() {
	return apply_filters( 'shouyaku_translation_post_type', 'translation' );
}

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
 * Get post type
 *
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function shouyaku_post_should_translate( $post = null ) {
	return in_array( get_post_type( $post ), shouyaku_transferable_post_types() );
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

/**
 * Get post's original locale.
 *
 * @param null|int|WP_Post $post
 *
 * @return string
 */
function shouyaku_post_locale( $post = null ) {
	$post = get_post( $post );
	$locale = get_post_meta( $post->ID, '_locale', true );
	return $locale ?: shouyaku_original_locale();
}

/**
 * Check if post has translations.
 *
 * @param string           $locale Default empty.
 * @param null|int|WP_Post $post   Default current post.
 * @param string           $status Default is 'any'
 *
 * @return bool
 */
function shouyaku_post_has_locale( $locale = '', $post = null, $status = 'any' ) {
	$posts = shouyaku_get_translations( $post, $locale, $status );
	return (bool) $posts;
}

/**
 * Get translated posts.
 *
 * @param null|int|WP_Post $post
 * @param string           $locale   If set, include only a post in specified locale.
 * @param string|array     $status   Post status in array or string.
 * @param int[]            $excludes If set, exludes specified post ids.
 *
 * @return WP_Post[]
 */
function shouyaku_get_translations( $post = null, $locale = '', $status = 'publish', $excludes = [] ) {
	$post = get_post( $post );
	$args = [
		'post_type'      => shouyaku_translation_post_type(),
		'post_parent'    => $post->ID,
		'posts_per_page' => -1,
		'post_status'    => $status,
	];
	if ( $excludes ) {
		$args['post__not_in'] = $excludes;
	}
	
	if ( $locale ) {
		$args['meta_query'] = [
			[
				'key'   => '_locale',
				'value' => $locale,
			]
		];
	}
	$args  = apply_filters( 'shouyaku_get_translations_args', $args, $post, $locale );
	$posts = get_posts( $args );
	return apply_filters( 'shouyaku_get_translations', $posts, $post, $args, $locale );
}

