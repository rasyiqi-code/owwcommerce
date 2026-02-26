<?php
namespace OwwCommerce\Admin;

use OwwCommerce\Core\Container;

/**
 * Handle Admin Menu registration
 */
class Menu {

    private Container $container;

    public function __construct( Container $container ) {
        $this->container = $container;
        add_action( 'admin_menu', [ $this, 'register_menus' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_settings() {
        register_setting( 'owwc_settings_group', 'owwc_floating_cart_style', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'style-1'
        ] );

        register_setting( 'owwc_settings_group', 'owwc_floating_cart_position', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'bottom-right'
        ] );

        register_setting( 'owwc_settings_group', 'owwc_floating_cart_icon', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'cart'
        ] );
    }

    public function register_menus() {
        // Enqueue admin scripts for OwwCommerce pages
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        add_menu_page(
            __( 'OwwCommerce Dashboard', 'owwcommerce' ),
            __( 'OwwCommerce', 'owwcommerce' ),
            'manage_options',
            'owwcommerce',
            [ $this, 'render_dashboard' ],
            'dashicons-cart',
            55
        );

        add_submenu_page(
            'owwcommerce',
            __( 'Products', 'owwcommerce' ),
            __( 'Products', 'owwcommerce' ),
            'manage_options',
            'owwc-products',
            [ $this, 'render_products' ]
        );

        add_submenu_page(
            'owwcommerce',
            __( 'Orders', 'owwcommerce' ),
            __( 'Orders', 'owwcommerce' ),
            'manage_options',
            'owwc-orders',
            [ $this, 'render_orders' ]
        );

        add_submenu_page(
            'owwcommerce',
            __( 'Categories', 'owwcommerce' ),
            __( 'Categories', 'owwcommerce' ),
            'manage_options',
            'owwc-categories',
            [ $this, 'render_categories' ]
        );

        add_submenu_page(
            'owwcommerce',
            __( 'Customers', 'owwcommerce' ),
            __( 'Customers', 'owwcommerce' ),
            'manage_options',
            'owwc-customers',
            [ $this, 'render_placeholder' ]
        );

        add_submenu_page(
            'owwcommerce',
            __( 'Settings', 'owwcommerce' ),
            __( 'Settings', 'owwcommerce' ),
            'manage_options',
            'owwc-settings',
            [ $this, 'render_settings' ]
        );
    }

    /**
     * Render Halaman Dashboard Utama
     */
    public function render_dashboard() {
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/dashboard.php';
        if ( file_exists( $template_path ) ) {
            require_once $template_path;
        } else {
            echo '<div class="wrap"><h1>OwwCommerce</h1><p>Template dashboard.php belum dibuat!</p></div>';
        }
    }

    /**
     * Enqueue JS and CSS for Admin Pages
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'owwc') !== false || $hook === 'toplevel_page_owwcommerce') {
            // CSS Admin Framework OwwCommerce
            wp_enqueue_style(
                'owwc-admin-style',
                OWWCOMMERCE_PLUGIN_URL . 'assets/css/admin.css',
                [],
                OWWCOMMERCE_VERSION
            );

            wp_enqueue_script(
                'owwc-admin-products',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/admin-products.js',
                [],
                OWWCOMMERCE_VERSION,
                true
            );
            
            wp_localize_script('owwc-admin-products', 'owwcSettings', [
                'restUrl' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest')
            ]);
        }

        if (strpos($hook, 'owwc-categories') !== false) {
            wp_enqueue_script(
                'owwc-admin-categories',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/admin-categories.js',
                [],
                OWWCOMMERCE_VERSION,
                true
            );
            
            wp_localize_script('owwc-admin-categories', 'owwcSettings', [
                'restUrl' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest')
            ]);
        }
    }

    /**
     * Render Halaman Products
     */
    public function render_products() {
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/products.php';
        if ( file_exists( $template_path ) ) {
            require_once $template_path;
        }
    }

    /**
     * Render Halaman Orders
     */
    public function render_orders() {
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/orders.php';
        if ( file_exists( $template_path ) ) {
            require_once $template_path;
        }
    }

    /**
     * Render Halaman Categories
     */
    public function render_categories() {
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/categories.php';
        if ( file_exists( $template_path ) ) {
            require_once $template_path;
        } else {
            echo '<div class="wrap"><h1>OwwCommerce Categories</h1><p>Template categories.php belum dibuat!</p></div>';
        }
    }

    /**
     * Render Halaman Pengaturan (Settings)
     */
    public function render_settings() {
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/settings.php';
        if ( file_exists( $template_path ) ) {
            require_once $template_path;
        } else {
            echo '<div class="wrap"><h1>OwwCommerce Settings</h1><p>Template settings.php belum dibuat!</p></div>';
        }
    }

    /**
     * Render Halaman Placeholder untuk menu lainnya
     */
    public function render_placeholder() {
        echo '<div class="wrap"><h1>OwwCommerce Page</h1><p>Halaman ini dalam tahap pengembangan...</p></div>';
    }
}
