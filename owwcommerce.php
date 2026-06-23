<?php
/**
 * Plugin Name: OwwCommerce
 * Plugin URI: https://crediblemark.com/
 * Description: The Zero-Bloatware E-Commerce Engine for WordPress.
 * Version: 1.4.0
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: Crediblemark & OwwCommerce Team
 * License: GPL v2 or later
 * Text Domain: owwcommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'OWWCOMMERCE_VERSION', '1.4.0' );
define( 'OWWCOMMERCE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OWWCOMMERCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load Composer autoloader or fallback to custom PSR-4 autoloader
if ( file_exists( OWWCOMMERCE_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once OWWCOMMERCE_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    spl_autoload_register( function ( $class ) {
        $prefix = 'OwwCommerce\\';
        $base_dir = OWWCOMMERCE_PLUGIN_DIR . 'includes/';

        $len = strlen( $prefix );
        if ( strncmp( $prefix, $class, $len ) !== 0 ) {
            return;
        }

        $relative_class = substr( $class, $len );
        $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    } );
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
