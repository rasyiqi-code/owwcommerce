<?php
namespace OwwCommerce\Core;

/**
 * Main Plugin Class
 */
use OwwCommerce\Frontend\Router;
use OwwCommerce\Frontend\Shortcodes;
use OwwCommerce\Frontend\FloatingCart;
use OwwCommerce\Frontend\Customizer;

class Plugin {

    private static ?Plugin $instance = null;
    private Container $container;

    private function __construct() {
        $this->container = new Container();

        // Auto-upgrade: jalankan Installer jika versi plugin berubah
        // Ini menangani penambahan kolom baru (image_url, billing_address, dll.)
        // tanpa perlu deaktifasi/aktifasi manual.
        $this->maybe_upgrade();

        $this->setup_modules();
    }

    /**
     * Cek apakah versi plugin berubah dan jalankan Installer jika perlu.
     * dbDelta() aman dipanggil berulang kali — hanya menambah kolom baru.
     */
    private function maybe_upgrade(): void {
        $stored_version = get_option( 'owwcommerce_version', '0' );
        if ( version_compare( $stored_version, OWWCOMMERCE_VERSION, '<' ) ) {
            \OwwCommerce\Database\Installer::install();
            
            // Fix: Set usage_limit to NULL where it was incorrectly saved as 0
            // This is required for coupons like DISKON10% and DISKON20% created with older versions.
            global $wpdb;
            $table_coupons = $wpdb->prefix . 'oww_coupons';
            $wpdb->query( "UPDATE $table_coupons SET usage_limit = NULL WHERE usage_limit = 0" );

            update_option( 'owwcommerce_version', OWWCOMMERCE_VERSION );
        }
    }

    public static function get_instance(): Plugin {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_container(): Container {
        return $this->container;
    }

    /**
     * Registrasi modul-modul sistem di sini.
     */
    private function setup_modules(): void {
        // Modul API Endpoint
        $this->container->register( 'api_products', fn() => new \OwwCommerce\Api\ProductsController( $this->container ), true );
        $this->container->register( 'api_cart', fn() => new \OwwCommerce\Api\CartController( $this->container ), true );
        $this->container->register( 'api_checkout', fn() => new \OwwCommerce\Api\CheckoutController( $this->container ), true );
        $this->container->register( 'api_categories', fn() => new \OwwCommerce\Api\CategoryController( $this->container ), true );
        $this->container->register( 'api_orders', fn() => new \OwwCommerce\Api\OrderController( $this->container ), true );
        $this->container->register( 'api_coupons', fn() => new \OwwCommerce\Api\CouponController( $this->container ), true );
        $this->container->register( 'api_analytics', fn() => new \OwwCommerce\Api\AnalyticsController( $this->container ), true );
        $this->container->register( 'api_migration', fn() => new \OwwCommerce\Api\MigrationController(), true );
        $this->container->register( 'api_attributes', fn() => new \OwwCommerce\Api\AttributeController(), true );
        $this->container->register( 'api_dashboard', fn() => new \OwwCommerce\Api\DashboardController(), true );
        $this->container->register( 'api_import', fn() => new \OwwCommerce\Api\ImportController(), true );
        $this->container->register( 'api_reviews', fn() => new \OwwCommerce\Api\ReviewsController(), true );
        
        // Registrasi Repositories
        $this->container->register( 'product_repository', fn() => new \OwwCommerce\Repositories\ProductRepository(), true );
        $this->container->register( 'category_repository', fn() => new \OwwCommerce\Repositories\CategoryRepository(), true );
        $this->container->register( 'attribute_repository', fn() => new \OwwCommerce\Repositories\AttributeRepository(), true );
        $this->container->register( 'order_repository', fn() => new \OwwCommerce\Repositories\OrderRepository(), true );

        // Registrasi Engine Pengiriman & Pembayaran
        $this->container->register( 'payment_gateways', function() {
            return [
                'bacs' => new \OwwCommerce\Payment\Gateways\BACS(),
                'cod'  => new \OwwCommerce\Payment\Gateways\COD(),
            ];
        }, true );

        $this->container->register( 'shipping_methods', function() {
            return [
                'flat_rate'     => new \OwwCommerce\Shipping\Methods\FlatRate(),
                'free_shipping' => new \OwwCommerce\Shipping\Methods\FreeShipping(),
            ];
        }, true );

        // Triger API init (penting agar add_action 'rest_api_init' didaftarkan ketika plugin load untuk mem-bypass is_admin())
        $this->container->get( 'api_products' );
        $this->container->get( 'api_cart' );
        $this->container->get( 'api_checkout' );
        $this->container->get( 'api_categories' );
        $this->container->get( 'api_orders' );
        $this->container->get( 'api_coupons' );
        $this->container->get( 'api_analytics' );
        $this->container->get( 'api_migration' );
        $this->container->get( 'api_attributes' );
        $this->container->get( 'api_dashboard' );
        $this->container->get( 'api_import' );
        $this->container->get( 'api_reviews' );

        // Daftarkan Admin Menu jika sedang berada di dashboard wp-admin
        if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            $this->container->register( 'admin_menu', fn() => new \OwwCommerce\Admin\Menu( $this->container ), true );
            
            // Trigger inisiasi module admin agar hooknya dimuat
            $this->container->get( 'admin_menu' );
        }

        // Register Frontend Router
        $this->container->register( 'frontend_router', fn() => new Router( $this->container ) );
        $this->container->get( 'frontend_router' );

        // Inisialisasi Shortcodes & Floating Cart UI
        new Shortcodes();
        new FloatingCart();
        new Customizer();

        // Frontend Scripts
        add_action('wp_enqueue_scripts', function() {
            // 1. Assets yang harus dimuat secara GLOBAL (agar floating cart & add to cart berfungsi di mana saja)
            wp_enqueue_style(
                'owwc-frontend-style',
                OWWCOMMERCE_PLUGIN_URL . 'assets/css/frontend.css',
                [],
                OWWCOMMERCE_VERSION
            );

            wp_enqueue_script(
                'owwc-cart-engine',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/cart.js',
                [],
                OWWCOMMERCE_VERSION,
                true
            );

            wp_enqueue_script(
                'owwc-shop-load-more',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/shop-load-more.js',
                ['owwc-cart-engine'],
                OWWCOMMERCE_VERSION,
                true
            );

            wp_localize_script('owwc-cart-engine', 'owwcSettings', [
                'restUrl'         => esc_url_raw(rest_url()),
                'nonce'           => wp_create_nonce('wp_rest'),
                'currencySymbol'  => get_option( 'owwc_currency_symbol', 'Rp' ),
                'thousandSep'     => get_option( 'owwc_thousand_sep', '.' ),
                'decimalSep'      => get_option( 'owwc_decimal_sep', ',' ),
                'flatRateCost'    => (int) get_option( 'owwc_flat_rate_cost', 15000 ),
                'freeShippingThreshold' => (int) get_option( 'owwc_free_shipping_threshold', 0 ),
                'productBase'     => Router::get_product_base_static(),
                'homeUrl'         => esc_url( home_url('/') ),
            ]);

            // 2. Assets yang dimuat secara KONDISIONAL (Zero Bloatware)
            $is_owwcommerce_page = false;

            // Cek jika ini adalah halaman virtual (Single Product / Order Received)
            if ( get_query_var( 'owwc_product_slug' ) || get_query_var( 'owwc_order_received' ) ) {
                $is_owwcommerce_page = true;
            }
            
            if ( ! $is_owwcommerce_page ) {
                global $post;
                if ( is_page() || is_single() || ( is_front_page() && is_home() ) ) {
                    if ( $post && ! empty( $post->post_content ) ) {
                        $content = $post->post_content;
                        if ( 
                            strpos( $content, '[owwcommerce_products]' ) !== false ||
                            strpos( $content, '[owwcommerce_shop]' )     !== false ||
                            strpos( $content, '[owwcommerce_checkout]' ) !== false ||
                            strpos( $content, '[owwcommerce_cart]' )     !== false ||
                            strpos( $content, '[owwcommerce_single_product]' ) !== false ||
                            strpos( $content, '[owwcommerce_my_account]' ) !== false
                        ) {
                            $is_owwcommerce_page = true;
                        }
                    }
                }
            }

            // Tambahkan body class owwc-page untuk targeting CSS yang lebih akurat
            if ( $is_owwcommerce_page ) {
                add_filter( 'body_class', function( $classes ) {
                    $classes[] = 'owwc-page';
                    
                    global $post;
                    if ( is_page() && $post ) {
                        if ( strpos( $post->post_content, '[owwcommerce_my_account]' ) !== false ) {
                            $classes[] = 'owwc-my-account-page';
                        }
                        if ( strpos( $post->post_content, '[owwcommerce_checkout]' ) !== false ) {
                            $classes[] = 'owwc-checkout-page';
                        }
                    }
                    
                    return $classes;
                } );

                wp_enqueue_style(
                    'owwc-frontend-pages',
                    OWWCOMMERCE_PLUGIN_URL . 'assets/css/frontend-pages.css',
                    ['owwc-frontend-style'],
                    OWWCOMMERCE_VERSION
                );
            }

            // Cek khusus untuk checkout script (hanya di-load jika ada shortcode checkout)
            global $post;
            if ( $post && strpos( $post->post_content, '[owwcommerce_checkout]' ) !== false ) {
                wp_enqueue_script(
                    'owwc-checkout-app',
                    OWWCOMMERCE_PLUGIN_URL . 'assets/js/checkout.js',
                    ['owwc-cart-engine'],
                    OWWCOMMERCE_VERSION,
                    true
                );
            }
        });
    }
}
