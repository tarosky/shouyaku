<?php
/**
 * Plugin Name: Shouyaku
 * Plugin URI: https://github.com/tarosky/shouyaku
 * Description: Yet another multilingual plugin.
 * Author: Kunoichi INC.
 * Author URI: https://tarosky.co.jp
 * Version: 0.0.0
 * Text Domain: shouyaku
 * Domain Path: /languages/
 *
 */

defined( 'ABSPATH' ) || die( 'Do not load directly' );

/**
 * Initialize Shouyaku
 */
function shouyaku_init() {
	load_plugin_textdomain( 'shouyaku', false, basename( __DIR__ ) . '/languages' );
	require __DIR__ . '/vendor/autoload.php';
	foreach ( array( 'functions', 'includes' ) as $dir ) {
		$dir_path =  __DIR__ . '/' . $dir;
		if ( ! is_dir( $dir_path ) ) {
			continue;
		}
		foreach ( scandir( $dir_path ) as $file ) {
			if ( ! preg_match( '/^[^._](.*)\.php$/u', $file ) ) {
				continue;
			}
			include $dir_path . '/' . $file;
		}
	}
}
add_action( 'plugins_loaded', 'shouyaku_init', 1 );
