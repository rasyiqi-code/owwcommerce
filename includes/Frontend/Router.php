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
    }

    /**
     * Add Custom Rewrite Rule untuk Single Product.
     * Match URL: site.com/product/slug-sepatu -> index.php?owwc_product_slug=slug-sepatu
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^product/([^/]*)/?',
            'index.php?owwc_product_slug=$matches[1]',
            'top'
        );
    }

    /**
     * Daftarkan Custom Query Var agar dikenali WP.
     */
    public function add_query_vars($vars) {
        $vars[] = 'owwc_product_slug';
        return $vars;
    }

    /**
     * Cebak pemanggilan Template jika query var terdeteksi.
     */
    public function load_custom_template($template) {
        $slug = get_query_var('owwc_product_slug');

        if ($slug) {
            $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/frontend/single-product.php';
            if (file_exists($template_path)) {
                return $template_path;
            }
        }

        return $template;
    }
}
