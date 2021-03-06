<?php
/**
 * Document setting
 * @package shouyaku
 */

defined( 'ABSPATH' ) || die();

// Add translations.
add_action( 'init', function() {
	$args = [
		'label' => __( 'Translations', 'shouyaku' ),
		'menu_icon'         => 'dashicons-translation',
		'menu_position'     => 20,
		'public'            => false,
		'show_ui'           => true,
		'supports'          => [ 'title', 'editor', 'excerpt' ],
		'show_in_rest'      => true,
		'show_in_nav_menus' => false,
		'show_in_admin_bar' => false,
		'labels' => [
			'singular_name'      => __( 'Translation', 'shouyaku' ),
		],
		'delete_with_user'  => false,
		'capability_type'   => 'post',
		'map_meta_cap'      => true,
		'capabilities'      => [
			'create_posts' => 'create_translations',
		],
	];
	register_post_type( shouyaku_translation_post_type(), $args );
} );

// Add post locale if user choose other language.
add_action( 'wp_insert_post', function( $post_id, WP_Post $post, $update ) {
	// Check if this post should be updated.
	if ( $update || ! shouyaku_post_should_translate( $post ) ) {
		return;
	}
	// Check user choose default locale.
	$user_locale = get_user_locale( $post->post_author );
	if ( shouyaku_original_locale() === $user_locale ) {
		return;
	}
	// User selected other language, so apply it to post.
	update_post_meta( $post_id, '_locale', $user_locale );
}, 10, 3 );

// Add admin column.
add_action( 'admin_init', function() {
	add_filter( 'manage_' . shouyaku_translation_post_type() . '_posts_columns', function( $columns ) {
		$new_column = [];
		foreach ( $columns as $key => $label ) {
			$new_column[ $key ] = $label;
			if ( 'title' === $key ) {
				$new_column[ 'translation_parent' ] = __( 'Parent' );
				$new_column[ 'locale' ] = __( 'Language' );
				$new_column[ 'status' ] = __( 'Status' );
			}
		}
		return $new_column;
	} );
	add_action( 'manage_' . shouyaku_translation_post_type() . '_posts_custom_column', function( $column, $post_id ) {
		switch ( $column ) {
			case 'locale':
				echo esc_html( get_post_meta( $post_id, '_locale', true ) );
				break;
			case 'translation_parent':
				$parent = wp_get_post_parent_id( $post_id );
				printf(
					'<a href="%s">%s</a>',
					esc_url( get_edit_post_link( $parent ) ),
					esc_html( get_the_title( $parent ) )
				);
				break;
			case 'status':
				if ( shouyaku_maybe_older_than_original( $post_id ) ) {
					printf( '<span class="dashicons dashicons-no" style="color: red;"></span> %s', esc_html__( 'Maybe Old', 'shouyaku' ) );
				} else {
					printf( '<span class="dashicons dashicons-yes" style="color: green;"></span> %s', esc_html__( 'Up to date', 'shouyaku' ) );
				}
				break;
		}
	}, 10, 2 );
} );

// Add language meta box.
add_action( 'add_meta_boxes', function( $post_type ) {
	if ( shouyaku_translation_post_type() === $post_type ) {
		add_meta_box( 'shouyaku-language', __( 'Language Setting', 'shouyaku' ), function( WP_Post $post ) {
			$locales = shouyaku_get_locales();
			$locale = get_post_meta( $post->ID, '_locale', true );
			?>
			<p>
				<?php echo wp_kses_post( sprintf( __( 'This translations is <strong>%1$s <small>(%2$s)</small></strong>', 'shouyaku' ), esc_html( $locales[ $locale ] ), esc_html( $locale ) ) ) ?>
			</p>
			<h4><?php esc_html_e( 'Original Post', 'shouyaku' ) ?></h4>
			<p>
				<a href="<?php echo get_edit_post_link( $post->post_parent ) ?>"><?php echo esc_html( get_the_title( $post->post_parent ) ) ?></a>
			</p>
			<h4><?php esc_html_e( 'Duplicated Time', 'shouyaku' ) ?></h4>
			<p>
				<?php
				$duplicated        = get_post_meta( $post->ID, '_latest_copied', true );
				$modified          = $post->post_modified_gmt;
				$original_modified = get_post( $post->post_parent )->post_date_gmt;
				echo wp_kses_post( sprintf( __( 'this post has been duplicated at <code>%s</code>.', 'shouyaku' ), mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $duplicated ) ) );
				?>
			</p>
			<p>
				<?php $original_modified_formatted =  mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $original_modified ); ?>
				<?php if ( shouyaku_maybe_older_than_original( $post ) ) : ?>
					<span class="dashicons dashicons-no" style="color: red"></span>
					<?php echo wp_kses_post( sprintf( __( 'Original post has been modified at <code>%s</code> and it might be newer than this post.', 'shouyaku' ), $original_modified_formatted ) ); ?>
				<?php else : ?>
					<span class="dashicons dashicons-yes" style="color:green"></span>
					<?php echo wp_kses_post( sprintf( __( 'This post is newer than original modified at <code>%s</code>.', 'shouyaku' ), $original_modified_formatted ) ); ?>
				<?php endif; ?>
			</p>
			<?php
		}, $post_type, 'side', 'high' );
		
	}
	
	if ( in_array( $post_type, shouyaku_transferable_post_types() ) ) {
		add_meta_box( 'shouyaku-language', __( 'Language Setting', 'shouyaku' ), function( WP_Post $post ) {
			wp_enqueue_script( 'shouyaku-post-selector' );
			wp_localize_script( 'shouyaku-post-selector', 'ShouyakuPostSelector', [
				'nonce'          => wp_create_nonce( 'wp_rest' ),
				'endpoint'       => rest_url( 'shouyaku/v1/translations/' . $post->ID ),
				'postId'         => $post->ID,
				'locales'        => shouyaku_get_locales(),
				'originalLocale' => shouyaku_original_locale(),
			] );
			wp_nonce_field( 'shouyaku_post_meta', '_shouyakupostnonce', false );
			$post_locale = shouyaku_post_locale( $post );
			?>
			<p>
				<label for="shouyaku-language-selector"><?php esc_html_e( 'Locale of this post', 'shouyaku_locale' ) ?></label><br/>
				<select name="shouyaku-locale" id="shouyaku-language-selector">
					<?php foreach ( shouyaku_get_locales() as $locale => $label ) : ?>
						<option value="<?php echo esc_attr( $locale === shouyaku_original_locale() ? '' : $locale ) ?>"<?php selected( $locale, $post_locale ) ?>>
							<?php echo esc_html( $label ) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
			<h4><?php esc_html_e( 'Translations', 'shouyaku' ) ?></h4>
			<div id="shouyaku-language-list"></div>
			<?php
		}, $post_type, 'side', 'high' );
	}
} );

// Save locale.
add_action( 'save_post', function( $post_id, $post ) {
	$nonce = filter_input( INPUT_POST, '_shouyakupostnonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'shouyaku_post_meta' ) ) {
		return;
	}
	$locale = filter_input( INPUT_POST, 'shouyaku-locale' );
	if ( $locale ) {
		update_post_meta( $post_id, '_locale', $locale );
	} else {
		delete_post_meta( $post_id, '_locale', $locale );
	}
}, 10, 2 );

// Add REST endpoint
add_action( 'rest_api_init', function() {
	$default_arg = [
		'post_id' => [
			'type'              => 'numeric',
			'description'       => 'Post ID to retrieve',
			'validate_callback' => function( $var ) {
				return is_numeric( $var ) && $var && get_post( $var );
			},
			'required'          => true,
		],
	];
	register_rest_route( 'shouyaku/v1', 'translations/(?P<post_id>\d+)', [
		[
			'methods'  => 'GET',
			'args'     => array_merge( $default_arg, [
				'status' => [
					'type'        => 'string',
					'description' => 'Post status in csv format.',
					'default'     => 'publish',
				],
				'locale' => [
					'type'        => 'string',
					'description' => 'Locale to retrieve. If not specified, get all locales.',
					'default'     => '',
				],
			] ),
			'callback' => function( WP_REST_Request $request ) {
				$post_id   = $request->get_param( 'post_id' );
				$is_author = current_user_can( 'edit_post', $post_id );
				$status = array_filter( array_map( 'trim', explode( ',', $request->get_param( 'status' ) ) ), function( $status ) use ( $is_author ) {
					switch ( $status ) {
						case 'publish':
							return true;
							break;
						default:
							return $is_author;
							break;
					}
				} );
				$post = get_post( $post_id );
				if ( ! $status || ! $post || ! ( $is_author || 'publish' === $post->post_status ) ) {
					return new WP_REST_Response( [] );
				}
				$posts = shouyaku_get_translations( $post, $request->get_param( 'locale' ), $status );
				return new WP_REST_Response( array_map( function( WP_Post $post ) {
					return [
						'id'               => $post->ID,
						'title'            => get_the_title( $post ),
						'edit_link'        => get_edit_post_link( $post, 'raw' ),
						'excerpt'          => $post->post_excerpt,
						'content'          => $post->post_content,
						'content_rendered' => apply_filters( 'the_content', $post->post_content ),
						'locale'           => get_post_meta( $post->ID, '_locale', true ),
					];
				}, $posts ) );
			},
		],
		[
			'methods'  => 'POST',
			'args'     => array_merge( $default_arg, [
				'locale' => [
					'type'     => 'string',
					'required' => true,
					'validate_callback' => function( $var, WP_REST_Request $request ) {
						return array_key_exists( $var, shouyaku_get_locales() ) && $var !== shouyaku_post_locale( $request->get_param( 'post_id' ) );
					}
				],
			] ),
			'callback' => function( WP_REST_Request $request ) {
				$post_id = $request->get_param( 'post_id' );
				$locale  = $request->get_param( 'locale' );
				if ( shouyaku_post_has_locale( $locale, $post_id ) ) {
					return new WP_Error( 'translation_exists', sprintf( __( 'Translation in %s exists.', 'shouyaku' ), $locale ), [
						'response' => 400,
						'status'   => 400,
					] );
				}
				$post = get_post( $post_id );
				$args = apply_filters( 'shouyaku_new_translation_args', [
					'post_type'    => shouyaku_translation_post_type(),
					'post_status'  => 'draft',
					'post_title'   => $post->post_title,
					'post_excerpt' => $post->post_excerpt,
					'post_content' => $post->post_content,
					'post_author'  => get_current_user_id(),
					'post_parent'  => $post->ID,
				], $post, $locale );
				$translation_id = wp_insert_post( $args, true );
				if ( is_wp_error( $translation_id ) ) {
					return $translation_id;
				}
				// Save locale.
				update_post_meta( $translation_id, '_locale', $locale );
				// Save time.
				update_post_meta( $translation_id, '_latest_copied', current_time( 'mysql', true ) );
				// If locale is specified, copy them.
				$meta_to_copy = apply_filters( 'shouyaku_meta_keys_to_copy', [], $post, $locale );
				foreach ( $meta_to_copy as $key ) {
					update_post_meta( $translation_id, $key, get_post_meta( $post->ID, $key, true ) );
				}
				return new WP_REST_Response( [
					'success' => true,
					'message' => __( 'New translation added.', 'shouyaku' ),
				] );
			},
			'permission_callback' => function( WP_REST_Request $request ) {
				return current_user_can( 'edit_post', $request->get_param( 'post_id' ) );
			}
		],
	] );
} );

/**
 * Filter posts to add translations.
 *
 * @param WP_Post[] $posts
 * @param WP_Query $wp_query
 * @return WP_Post[]
 */
function shouyaku_change_post_results( $posts, $wp_query ) {
	if ( is_admin() ) {
		return $posts;
	}
	$locale = shouyaku_should_translate_to();
	if ( ! $locale ) {
		return $posts;
	}
	$translations = shouyaku_get_translations( $posts, $locale );
	foreach ( shouyaku_get_translations( $posts, $locale ) as $translation ) {
		foreach ( $posts as $index => $post ) {
			if ( $post->ID == $translation->post_parent ) {
				// Post is translated.
				// Main query.
				if ( $wp_query->is_main_query() ) {
					shouyaku_page_translated( $locale );
				}
				foreach ( [ 'post_title', 'post_excerpt', 'post_content' ] as $param ) {
					if ( $translation->{$param} ) {
						$posts[ $index ]->{$param} = $translation->{$param};
					}
				}
				break;
			}
		}
	}
	return $posts;
}
add_filter( 'posts_results', 'shouyaku_change_post_results', 10, 2 );

// Translate title tag.
add_filter( 'single_post_title', function( $title, $post ) {
	if ( $translation = shouyaku_get_post_translation( $post ) ) {
		$title = get_the_title( $translation );
	}
	return $title;
}, 10, 2 );

/**
 * Change lang attribute.
 */
add_filter( 'language_attributes', function ( $output, $doctype ) {
	if ( is_singular() || is_page() ) {
		if ( $locale = shouyaku_page_translated() ) {
			$new_locale = $locale;
		} else {
			$new_locale = get_post_meta( get_queried_object_id(), '_locale', true ) ?: shouyaku_original_locale();
		}
		$new_locale = str_replace( '_', '-', $new_locale );
		$output = preg_replace( '/lang="[^"]+"/u', sprintf( 'lang="%s"', esc_attr( $new_locale ) ), $output );
	}
	return $output;
}, 10, 2 );

/**
 * Add hreflang.
 */
add_action( 'wp_head', function() {
	if ( is_singular() || is_page() ) {
		$locales = [];
		// Get translations.
		$original = get_post_meta( get_queried_object_id(), '_locale', true ) ?: shouyaku_original_locale();
		$permalink = get_permalink( get_queried_object() );
		$locales[ $original ] = $permalink;
		foreach ( shouyaku_get_translations( get_queried_object() ) as $translation ) {
			$locale = get_post_meta( $translation->ID, '_locale', true );
			$locales[ $locale ] = add_query_arg( [
				'locale' => $locale,
			], $permalink );
		}
		foreach ( $locales as $locale => $url ) {
			printf( '<link rel="alternate" href="%s" hreflang="%s" />', esc_url( $url ), esc_attr( str_replace( '_', '-', $locale ) ) );
		}
	}
}, 99 );

/**
 * Trim words by character locale.
 */
add_filter( 'wp_trim_words', function( $text, $num_words, $more, $original_text ) {
	// If trimmed text seemed to be hang letter,
	$original_text = wp_strip_all_tags( $original_text );
	if ( \Tarosky\Shouyaku\Unicode::is_cjk( $original_text ) ) {
		$text = trim( preg_replace( "/[\n\r\t ]+/", ' ', $original_text ), ' ' );
		preg_match_all( '/./u', $text, $words_array );
		$words_array = array_slice( $words_array[0], 0, $num_words + 1 );
		$sep = '';
	} else {
		$words_array = preg_split( "/[\n\r\t ]+/", $original_text, $num_words + 1, PREG_SPLIT_NO_EMPTY );
		$sep = ' ';
	}
	
	if ( count( $words_array ) > $num_words ) {
		array_pop( $words_array );
		$text = implode( $sep, $words_array );
		$text = $text . $more;
	} else {
		$text = implode( $sep, $words_array );
	}
	return $text;
}, 10, 4 );
