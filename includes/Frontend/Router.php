<?php
namespace OwwCommerce\Frontend;

use OwwCommerce\Core\Container;
use OwwCommerce\Repositories\ProductRepository;

/**
 * Handle Custom Rewrite Rules & Virtual Pages for OwwCommerce
 */
class Router {
    private Container $container;

    public function __construct( Container $container ) {
        $this->container = $container;

        // Register action & filters
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_filter('template_include', [$this, 'load_custom_template']);

        // Auto-flush rewrite rules if product base changed or requested via URL
        add_action('init', [$this, 'maybe_flush_rules'], 20);
    }

    /**
     * Flush rewrite rules if the base has changed or via debug parameter.
     */
    public function maybe_flush_rules() {
        $current_base = $this->get_current_product_base();
        $stored_base  = get_option('owwc_last_flushed_base');
        $force_flush  = isset($_GET['owwc_debug']) && $_GET['owwc_debug'] === 'flush';

        if ( $current_base !== $stored_base || $force_flush ) {
            flush_rewrite_rules(true);
            update_option('owwc_last_flushed_base', $current_base);
            
            if ($force_flush) {
                wp_die('OwwCommerce: Rewrite rules flushed successfully. <a href="' . home_url('/') . '">Go Back Home</a>');
            }
        }
    }

    /**
     * Helper to get current base from settings.
     * Always relies on the assigned Shop page.
     */
    private function get_current_product_base() {
        $shop_page_id = get_option('owwc_page_shop_id');
        $product_base = 'shop'; // Default fallback

        if ( $shop_page_id ) {
            $shop_page = get_post( $shop_page_id );
            if ( $shop_page ) {
                $product_base = $shop_page->post_name;
            }
        }
        return $product_base;
    }

    /**
     * Helper static untuk mendapatkan URL produk yang benar berdasarkan setting.
     */
    public static function get_product_link( $slug ) {
        $product_base = self::get_product_base_static();
        return home_url( user_trailingslashit( $product_base . '/' . $slug ) );
    }

    /**
     * Helper static untuk mendapatkan base produk.
     */
    public static function get_product_base_static() {
        $shop_page_id = get_option('owwc_page_shop_id');
        $product_base = 'shop';

        if ( $shop_page_id ) {
            $shop_page = get_post( $shop_page_id );
            if ( $shop_page ) {
                $product_base = $shop_page->post_name;
            }
        }
        return $product_base;
    }

    /**
     * Add Custom Rewrite Rule untuk Single Product dan Order Received.
     */
    public function add_rewrite_rules() {
        $product_base = $this->get_current_product_base();
        
        add_rewrite_rule(
            '^' . preg_quote($product_base) . '/([^/]+)/?$', 
            'index.php?owwc_product_slug=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^checkout/order-received/([0-9]+)/?',
            'index.php?owwc_order_received=$matches[1]',
            'top'
        );
    }

    /**
     * Daftarkan Custom Query Var agar dikenali WP.
     */
    public function add_query_vars($vars) {
        $vars[] = 'owwc_product_slug';
        $vars[] = 'owwc_order_received';
        return $vars;
    }

    /**
     * Cebak pemanggilan Template jika query var terdeteksi.
     */
    public function load_custom_template($template) {
        $slug = get_query_var('owwc_product_slug');
        $order_id = get_query_var('owwc_order_received');

        if ($slug) {
            $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/frontend/single-product.php';
            if (file_exists($template_path)) {
                return $template_path;
            }
        }
        
        if ($order_id) {
            $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/frontend/order-received.php';
            if (file_exists($template_path)) {
                return $template_path;
            }
        }

        // Detect Shortcodes on normal pages
        if ( is_page() ) {
            global $post;
            if ( $post && (
                strpos( $post->post_content, '[owwcommerce_shop]' ) !== false ||
                strpos( $post->post_content, '[owwcommerce_cart]' ) !== false ||
                strpos( $post->post_content, '[owwcommerce_checkout]' ) !== false ||
                strpos( $post->post_content, '[owwcommerce_my_account]' ) !== false
            ) ) {
                $custom_wrapper = OWWCOMMERCE_PLUGIN_DIR . 'templates/frontend/page-owwcommerce.php';
                if ( file_exists( $custom_wrapper ) ) {
                    return $custom_wrapper;
                }
            }
        }

        return $template;
    }
}
