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
 * @param null $post
 *
 * @return array|WP_Post|null
 */
function shouyaku_get_post_translation( $post = null ) {
	$post = get_post( $post );
	if ( ! shouyaku_post_should_translate( $post ) ) {
		return null;
	} elseif ( ( $locale = shouyaku_should_translate_to() ) ) {
		// Post title should be translated.
		foreach ( shouyaku_get_translations( $post, $locale ) as $translate ) {
			return $translate;
		}
	}
	return null;
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
 * @param null|int|WP_Post|WP_Post[] $post
 * @param string                     $locale   If set, include only a post in specified locale.
 * @param string|array               $status   Post status in array or string.
 * @param int[]                      $excludes If set, exludes specified post ids.
 *
 * @return WP_Post[]
 */
function shouyaku_get_translations( $post = null, $locale = '', $status = 'publish', $excludes = [] ) {
	$args = [
		'post_type'      => shouyaku_translation_post_type(),
		'posts_per_page' => -1,
		'post_status'    => $status,
	];
	if ( is_array( $post ) ) {
		$parent_ids = array_filter( array_map( function( $p ) {
			return shouyaku_post_should_translate( $p ) ? $p->ID : 0;
		}, $post ) );
		if ( $parent_ids ) {
			$args['post_parent__in'] = $parent_ids;
		} else {
			$args['p'] = 0;
		}
	} else {
		$post = get_post( $post );
		$args['post_parent'] = $post->ID;
	}
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

/**
 * Check if post is older than original.
 *
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function shouyaku_maybe_older_than_original( $post = null ) {
	$post   = get_post( $post );
	$parent = get_post( $post->post_parent );
	if ( ! $post || ! $parent ) {
		return false;
	}
	$duplicated        = get_post_meta( $post->ID, '_latest_copied', true );
	$modified          = $post->post_modified_gmt;
	$original_modified = $parent->post_date_gmt;
	return ! ( $duplicated > $original_modified || $modified > $original_modified );
}

/**
 * Display notification.
 *
 * @param null|int|WP_Post $post
 */
function shouyaku_post_notification( $post = null ) {
	$post   = get_post( $post );
	$locale = shouyaku_user_locale();
	if ( ! in_array( $post->post_type, shouyaku_transferable_post_types() ) || shouyaku_post_has_locale( $locale, $post, 'publish' ) ) {
		return;
	}
	$template = '';
	foreach ( [
		get_template_directory(),
		get_stylesheet_directory(),
	] as $dir ) {
		$path = $dir . '/template-parts/alert-translation.php';
		if ( file_exists( $path ) ) {
			$template = $path;
		}
	}
	$template = apply_filters( 'shouyaku_post_notification_template', $template, $post );
	if ( $template && file_exists( $template ) ) {
		include $template;
	} else {
		?>
		<div class="mb-4 alert alert-warning shouyaku-alert-box">
			<?php esc_html_e( 'Sorry, but this post has no translation in your language.', 'shouyaku' ) ?>
		</div>
		<?php
	}
}
