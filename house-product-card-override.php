<?php
/**
 * Plugin Name:       House Product Card Override
 * Description:       Globally overrides the WooCommerce product loop UI with the custom product card design from the House Products Carousel Block.
 * Version:           1.0.0
 * Author:            Steven Ayo
 * Text Domain:       house-product-card-override
 * License:           GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define Constants
 */
define( 'HPCO_VERSION', '1.0.0' );
define( 'HPCO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HPCO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'HPCO_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load Plugin
 */
require_once HPCO_PLUGIN_DIR . 'includes/class-plugin.php';

function hpco_run_plugin() {
	$plugin = new HPCO_Plugin();
	$plugin->run();
}

add_action( 'plugins_loaded', 'hpco_run_plugin' );
