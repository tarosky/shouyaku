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
} );