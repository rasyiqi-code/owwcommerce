<?php
namespace OwwCommerce\Frontend;

/**
 * Handle Floating Cart UI
 */
class FloatingCart {

    public function __construct() {
        // Cetak floating cart di area footer
        add_action( 'wp_footer', [ $this, 'render_floating_cart' ] );
    }

    /**
     * Render floating cart if not already rendered manually via shortcode
     */
    public function render_floating_cart() {
        // 0. Jangan merender jika fitur keranjang / checkout dinonaktifkan secara global
        if ( ! get_option( 'owwc_enable_cart_checkout', 1 ) ) {
            return;
        }

        // Jika developer sudah mengeksekusi shortcode [owwcommerce_cart_icon] secara manual di header/page, 
        // global flag ini akan bernilai true, jadi hentikan eksekusi floating ini.
        if ( isset( $GLOBALS['owwc_cart_icon_rendered'] ) && $GLOBALS['owwc_cart_icon_rendered'] === true ) {
            return;
        }

        // 1. Jangan merender di halaman Admin
        if ( is_admin() ) {
            return;
        }

        // 2. Deteksi halaman bawaan OwwCommerce dan Checkout
        $shop_page_id      = (int) get_option( 'owwc_page_shop_id' );
        $cart_page_id      = (int) get_option( 'owwc_page_cart_id' );
        $checkout_page_id  = (int) get_option( 'owwc_page_checkout_id' );

        global $post;
        $has_shop_shortcode     = false;
        $has_cart_shortcode     = false;
        $has_checkout_shortcode = false;
        $has_product_shortcode  = false;

        if ( $post && ! empty( $post->post_content ) ) {
            $content = $post->post_content;
            if ( strpos( $content, '[owwcommerce_products]' ) !== false || strpos( $content, '[owwcommerce_shop]' ) !== false ) {
                $has_shop_shortcode = true;
            }
            if ( strpos( $content, '[owwcommerce_cart]' ) !== false ) {
                $has_cart_shortcode = true;
            }
            if ( strpos( $content, '[owwcommerce_checkout]' ) !== false ) {
                $has_checkout_shortcode = true;
            }
            if ( strpos( $content, '[owwcommerce_single_product]' ) !== false ) {
                $has_product_shortcode = true;
            }
        }

        $is_shop_page           = ( $shop_page_id && is_page( $shop_page_id ) ) || $has_shop_shortcode;
        $is_cart_page           = ( $cart_page_id && is_page( $cart_page_id ) ) || $has_cart_shortcode;
        $is_checkout_page       = ( $checkout_page_id && is_page( $checkout_page_id ) ) || $has_checkout_shortcode;
        $is_single_product_page = get_query_var( 'owwc_product_slug' ) || $has_product_shortcode;

        // Cek fallback URI untuk checkout dan keranjang jika halaman belum diset
        $request_uri   = trim( $_SERVER['REQUEST_URI'], '/' );
        $checkout_slug = $checkout_page_id ? get_post_field( 'post_name', $checkout_page_id ) : 'checkout';
        $cart_slug     = $cart_page_id ? get_post_field( 'post_name', $cart_page_id ) : 'keranjang';

        $is_uri_checkout = strpos( $request_uri, $checkout_slug ) === 0 || strpos( $request_uri, 'checkout' ) !== false;
        $is_uri_cart     = strpos( $request_uri, $cart_slug ) === 0 || strpos( $request_uri, 'keranjang' ) !== false || strpos( $request_uri, 'cart' ) !== false;

        $is_checkout = $is_checkout_page || $is_uri_checkout;
        $is_cart     = $is_cart_page || $is_uri_cart;

        // Hanya tampilkan di halaman checkout dan toko bawaan OwwCommerce (Shop, Single Product, Cart, Checkout)
        if ( ! $is_checkout && ! $is_cart && ! $is_shop_page && ! $is_single_product_page ) {
            return;
        }

        $style = get_option('owwc_floating_cart_style', 'style-1');
        $position = get_option('owwc_floating_cart_position', 'bottom-right');
        
        $cart_html = do_shortcode( '[owwcommerce_cart_icon]' );

        echo '<div class="owwc-floating-cart-wrapper ' . esc_attr($style) . ' ' . esc_attr($position) . '">';
        echo $cart_html;
        echo '</div>';
    }
}
