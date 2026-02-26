<?php
namespace OwwCommerce\Core;

/**
 * Main Plugin Class
 */
use OwwCommerce\Frontend\Router;
use OwwCommerce\Frontend\Shortcodes;
use OwwCommerce\Frontend\FloatingCart;

class Plugin {

    private static ?Plugin $instance = null;
    private Container $container;

    private function __construct() {
        $this->container = new Container();
        $this->setup_modules();
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

        // Frontend Scripts
        add_action('wp_enqueue_scripts', function() {
            // Hanya nge-load style jika ada post yang punya shortcode, tapi untuk MVP kita enqueue dasar.
            // Idealnya dicocokkan dengan global $post => has_shortcode('owwcommerce_...').
            wp_enqueue_style(
                'owwc-frontend-style',
                OWWCOMMERCE_PLUGIN_URL . 'assets/css/frontend.css',
                [],
                OWWCOMMERCE_VERSION
            );

            wp_enqueue_style(
                'owwc-frontend-pages',
                OWWCOMMERCE_PLUGIN_URL . 'assets/css/frontend-pages.css',
                ['owwc-frontend-style'],
                OWWCOMMERCE_VERSION
            );

            wp_enqueue_script(
                'owwc-cart-engine',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/cart.js',
                [],
                OWWCOMMERCE_VERSION,
                true
            );
            
            wp_localize_script('owwc-cart-engine', 'owwcSettings', [
                'restUrl' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest')
            ]);

            // Misal: Check halaman checkout atau hanya diload semua
            // Demi "Zero Bloatware", ideally it's conditional, but we enqueue it globally for this demo MVP
            wp_enqueue_script(
                'owwc-checkout-app',
                OWWCOMMERCE_PLUGIN_URL . 'assets/js/checkout.js',
                [],
                OWWCOMMERCE_VERSION,
                true
            );
            
            wp_localize_script('owwc-checkout-app', 'owwcSettings', [
                'restUrl' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest')
            ]);
        });
    }
}
