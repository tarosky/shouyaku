<?php
/**
 * Asset related hooks.
 */

defined( 'ABSPATH' ) || die();

add_action( 'init', function() {
	$asset_dir_url = plugin_dir_url( __DIR__ ) . 'assets';
	$header = get_file_data( dirname( __DIR__ ) . '/shouyaku.php', [
		'version' => 'Version'
	] );
	wp_register_style( 'shouyaku-admin', $asset_dir_url . '/css/shouyaku-admin.css', [], $header['version'] );
	wp_register_script( 'shouyaku-term-editor', $asset_dir_url . '/js/shouyaku-term-editor.js', [ 'jquery' ], $header['version'], true );
	wp_register_script( 'shouyaku-post-selector', $asset_dir_url . '/js/shouyaku-post-locales.js', [ 'jquery', 'wp-element', 'wp-i18n' ], $header['version'], true );
	wp_register_script( 'shouyaku-user-locale', $asset_dir_url . '/js/shouyaku-user-locale.js', [ 'jquery', 'wp-i18n' ], $header['version'], true );
	wp_localize_script( 'shouyaku-user-locale', 'ShouyakuUserLocale', [
		'default'          => shouyaku_original_locale(),
		'availableLocales' => shouyaku_get_locales(),
		'endpoint'         => rest_url( 'shouyaku/v1' ),
		'nonce'            => wp_create_nonce( 'wp_rest' ),
	] );
	wp_register_style( 'shouyaku-notice', $asset_dir_url . '/css/shouyaku-notice.css', [], $header['version'] );
} );
