<?php
/**
 * Plugin Name: OwwCommerce
 * Plugin URI: https://example.com/owwcommerce
 * Description: The Zero-Bloatware E-Commerce Engine for WordPress.
 * Version: 1.3.5
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: OwwCommerce Team
 * License: GPL v2 or later
 * Text Domain: owwcommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'OWWCOMMERCE_VERSION', '1.3.5' );
define( 'OWWCOMMERCE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OWWCOMMERCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load Composer autoloader
if ( file_exists( OWWCOMMERCE_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once OWWCOMMERCE_PLUGIN_DIR . 'vendor/autoload.php';
}

// Activation hook
register_activation_hook( __FILE__, [ 'OwwCommerce\Core\Activator', 'activate' ] );

// Deactivation hook
register_deactivation_hook( __FILE__, [ 'OwwCommerce\Core\Deactivator', 'deactivate' ] );

// Boot plugin
function owwcommerce_boot() {
    \OwwCommerce\Core\Plugin::get_instance();
}
add_action( 'plugins_loaded', 'owwcommerce_boot' );
