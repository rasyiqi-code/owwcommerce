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

        // Settings BACS
        register_setting( 'owwc_settings_group', 'owwc_bacs_account', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => "Bank BCA\nNo. Rek: 1234567890\nA.n: PT OwwCommerce Indonesia"
        ] );

        register_setting( 'owwc_settings_group', 'owwc_bacs_instructions', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => 'Silakan transfer sejumlah total tagihan ke rekening berikut:'
        ] );

        // Settings Ongkir
        register_setting( 'owwc_settings_group', 'owwc_flat_rate_cost', [
            'type'              => 'number',
            'sanitize_callback' => 'absint',
            'default'           => 15000
        ] );

        // Settings Lokalisasi
        register_setting( 'owwc_settings_group', 'owwc_currency_symbol', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'Rp'
        ] );

        register_setting( 'owwc_settings_group', 'owwc_thousand_sep', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '.'
        ] );

        register_setting( 'owwc_settings_group', 'owwc_decimal_sep', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ','
        ] );

        // Info Toko
        register_setting( 'owwc_settings_group', 'owwc_store_name', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => get_bloginfo( 'name' )
        ] );

        register_setting( 'owwc_settings_group', 'owwc_store_address', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => ''
        ] );

        // Payment Logic
        register_setting( 'owwc_settings_group', 'owwc_enable_cod', [
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false
        ] );

        register_setting( 'owwc_settings_group', 'owwc_free_shipping_threshold', [
            'type'              => 'number',
            'sanitize_callback' => 'absint',
            'default'           => 0
        ] );

        register_setting( 'owwc_settings_group', 'owwc_whatsapp_number', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ] );

        // Permalinks & Page Slugs
        register_setting( 'owwc_settings_group', 'owwc_page_shop_id', [
            'type'              => 'number',
            'sanitize_callback' => 'absint',
            'default'           => 0
        ] );

        register_setting( 'owwc_settings_group', 'owwc_page_myaccount_id', [
            'type'              => 'number',
            'sanitize_callback' => 'absint',
            'default'           => 0
        ] );

        // Flush rewrite rules saat struktur URL diubah
        add_action( 'update_option_owwc_page_shop_id', 'flush_rewrite_rules' );
    }

    public function register_menus() {
        // Enqueue admin scripts for OwwCommerce pages
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        add_menu_page(
            __( 'OwwCommerce', 'owwcommerce' ),
            'OwwCommerce',
            'manage_options',
            'owwcommerce',
            [ $this, 'render_dashboard' ],
            'dashicons-cart',
            55
        );

        add_submenu_page(
            'owwcommerce',
            __( 'Overview', 'owwcommerce' ),
            __( 'Overview', 'owwcommerce' ),
            'manage_options',
            'owwcommerce',
            [ $this, 'render_dashboard' ]
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
            __( 'Attributes', 'owwcommerce' ),
            __( 'Attributes', 'owwcommerce' ),
            'manage_options',
            'owwc-attributes',
            [ $this, 'render_attributes' ]
        );

        add_submenu_page(
            'owwcommerce',
            __( 'Customers', 'owwcommerce' ),
            __( 'Customers', 'owwcommerce' ),
            'manage_options',
            'owwc-customers',
            [ $this, 'render_customers' ]
        );

        add_submenu_page(
            'owwcommerce',
            __( 'Coupons', 'owwcommerce' ),
            __( 'Coupons', 'owwcommerce' ),
            'manage_options',
            'owwc-coupons',
            [ $this, 'render_coupons' ]
        );

        add_submenu_page(
            'owwcommerce',
            __( 'Settings', 'owwcommerce' ),
            __( 'Settings', 'owwcommerce' ),
            'manage_options',
            'owwc-settings',
            [ $this, 'render_settings' ]
        );

        add_submenu_page(
            'owwcommerce',
            __( 'Migration', 'owwcommerce' ),
            __( 'Migration', 'owwcommerce' ),
            'manage_options',
            'owwc-migration',
            [ $this, 'render_migration' ]
        );

        // Halaman hidden untuk detail pesanan (tidak tampil di menu)
        add_submenu_page(
            null, // Parent null = hidden dari menu
            __( 'Order Detail', 'owwcommerce' ),
            __( 'Order Detail', 'owwcommerce' ),
            'manage_options',
            'owwc-order-detail',
            [ $this, 'render_order_detail' ]
        );
    }

    /**
     * Enqueue JS and CSS for Admin Pages
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'owwc') !== false || $hook === 'toplevel_page_owwcommerce') {
            // Enqueue WordPress Media Library untuk upload gambar produk
            wp_enqueue_media();

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

        // Enqueue admin-orders.js pada halaman detail order
        if (strpos($hook, 'owwc-order-detail') !== false || strpos($hook, 'owwc-orders') !== false) {
            wp_enqueue_script(
                'owwc-admin-orders',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/admin-orders.js',
                [],
                OWWCOMMERCE_VERSION,
                true
            );

            wp_localize_script('owwc-admin-orders', 'owwcSettings', [
                'restUrl' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest')
            ]);
        }

        if (strpos($hook, 'toplevel_page_owwcommerce') !== false) {
            // Load Chart.js dari CDN (Zero-Bloatware: hanya dimuat di dashboard)
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.1', true);
            
            wp_enqueue_script(
                'owwc-admin-dashboard',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/admin-dashboard.js',
                ['chart-js'],
                OWWCOMMERCE_VERSION,
                true
            );

            wp_localize_script('owwc-admin-dashboard', 'owwcSettings', [
                'restUrl'         => esc_url_raw(rest_url()),
                'nonce'           => wp_create_nonce('wp_rest'),
                'currencySymbol'  => get_option( 'owwc_currency_symbol', 'Rp' ),
                'thousandSep'     => get_option( 'owwc_thousand_sep', '.' ),
                'decimalSep'      => get_option( 'owwc_decimal_sep', ',' ),
            ]);
        }

        if (strpos($hook, 'owwc-coupons') !== false) {
            wp_enqueue_script(
                'owwc-admin-coupons',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/admin-coupons.js',
                [],
                OWWCOMMERCE_VERSION,
                true
            );

            wp_localize_script('owwc-admin-coupons', 'owwcSettings', [
                'restUrl' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest')
            ]);
        }

        if (strpos($hook, 'owwc-migration') !== false) {
            wp_enqueue_script(
                'owwc-admin-migration',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/admin-migration.js',
                [],
                OWWCOMMERCE_VERSION,
                true
            );

            wp_localize_script('owwc-admin-migration', 'owwcSettings', [
                'restUrl' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest')
            ]);
        }

        if (strpos($hook, 'owwc-attributes') !== false) {
            wp_enqueue_script(
                'owwc-admin-attributes',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/admin-attributes.js',
                [],
                OWWCOMMERCE_VERSION,
                true
            );

            wp_localize_script('owwc-admin-attributes', 'owwcSettings', [
                'restUrl' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest')
            ]);
        }
    }

    /**
     * Render Halaman Products
     */
    public function render_products() {
        $action = $_GET['action'] ?? '';
        
        if ( in_array( $action, ['add', 'edit'], true ) ) {
            $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/product-form.php';
        } else {
            $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/products.php';
        }

        if ( file_exists( $template_path ) ) {
            require_once $template_path;
        } else {
            echo '<div class="wrap"><p>Template not found: ' . esc_html( basename($template_path) ) . '</p></div>';
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
     * Render Halaman Migration
     */
    public function render_migration() {
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/migration.php';
        if ( file_exists( $template_path ) ) {
            include $template_path;
        }
    }

    /**
     * Render Halaman Attributes
     */
    public function render_attributes() {
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/attributes.php';
        if ( file_exists( $template_path ) ) {
            include $template_path;
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
     * Render Halaman Kupon
     */
    public function render_coupons() {
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/coupons.php';
        if ( file_exists( $template_path ) ) {
            require_once $template_path;
        }
    }

    /**
     * Render Halaman Placeholder untuk menu lainnya
     */
    public function render_customers() {
        $customer_repo = new \OwwCommerce\Repositories\CustomerRepository();
        $customers = $customer_repo->get_all();

        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/customers.php';
        if ( file_exists( $template_path ) ) {
            require_once $template_path;
        } else {
            echo '<div class="wrap"><h1>OwwCommerce Customers</h1><p>Template customers.php belum dibuat!</p></div>';
        }
    }

    /**
     * Render Halaman Dashboard (Overview)
     */
    public function render_dashboard() {
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/dashboard.php';
        if ( file_exists( $template_path ) ) {
            require_once $template_path;
        }
    }

    /**
     * Render Halaman Detail Pesanan (Order Detail)
     */
    public function render_order_detail() {
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/admin/order-detail.php';
        if ( file_exists( $template_path ) ) {
            require_once $template_path;
        } else {
            echo '<div class="wrap"><h1>Order Detail</h1><p>Template order-detail.php belum dibuat!</p></div>';
        }
    }

}
