<?php
namespace OwwCommerce\Frontend;

/**
 * Handle Registrasi Shortcode OwwCommerce
 */
class Shortcodes {

    public function __construct() {
        add_shortcode( 'owwcommerce_shop', [ $this, 'render_shop' ] );
        add_shortcode( 'owwcommerce_cart', [ $this, 'render_cart' ] );
        add_shortcode( 'owwcommerce_checkout', [ $this, 'render_checkout' ] );
        add_shortcode( 'owwcommerce_my_account', [ $this, 'render_my_account' ] );
        add_shortcode( 'owwcommerce_cart_icon', [ $this, 'render_cart_icon' ] );

        // Sembunyikan judul halaman dari tema pada halaman milik OwwCommerce.
        // Plugin mengelola judul sendiri via template shortcode.
        add_filter( 'the_title', [ $this, 'hide_page_title_on_owwc_pages' ], 10, 2 );
    }

    /**
     * Sembunyikan judul halaman dari tema pada halaman milik OwwCommerce
     * agar tidak double (plugin sudah render <h1> sendiri).
     *
     * Filter ini HANYA aktif di main loop (bukan di menu/widget/sidebar),
     * sehingga judul di navigasi WP tetap tampil normal.
     * 
     * @param string $title Judul halaman dari WordPress
     * @param int    $id    ID post/page
     * @return string Judul kosong jika halaman OwwCommerce, atau judul asli
     */
    public function hide_page_title_on_owwc_pages( $title, $id = 0 ) {
        // Jangan sembunyikan judul di menu/widget
        if ( ! in_the_loop() ) {
            return $title;
        }

        // 1. Cek Virtual Pages (Single Product / Order Received) via Query Vars
        if ( get_query_var( 'owwc_product_slug' ) || get_query_var( 'owwc_order_received' ) ) {
            return '';
        }

        // 2. Daftar page ID yang dikelola OwwCommerce
        $owwc_page_ids = array_filter( [
            (int) get_option( 'owwc_page_cart_id' ),
            (int) get_option( 'owwc_page_checkout_id' ),
            (int) get_option( 'owwc_page_shop_id' ),
            (int) get_option( 'owwc_page_myaccount_id' ),
        ] );

        // Jika halaman ini milik OwwCommerce, kembalikan string kosong
        if ( in_array( (int) $id, $owwc_page_ids, true ) || ( is_page() && in_array( get_the_ID(), $owwc_page_ids, true ) ) ) {
            return '';
        }

        return $title;
    }

    /**
     * Render Halaman Toko (Shop)
     */
    public function render_shop( $atts ) {
        return TemplateLoader::get_template( 'shop.php' );
    }

    /**
     * Render Halaman Keranjang (Cart)
     */
    public function render_cart( $atts ) {
        return TemplateLoader::get_template( 'cart.php' );
    }

    /**
     * Render Halaman Checkout
     */
    public function render_checkout( $atts ) {
        return TemplateLoader::get_template( 'checkout.php' );
    }

    /**
     * Render Halaman Akun Saya (My Account)
     */
    public function render_my_account( $atts ) {
        return TemplateLoader::get_template( 'my-account.php' );
    }

    /**
     * Render Ikon Keranjang (Untuk Header / Widget)
     */
    public function render_cart_icon( $atts ) {
        // Tandai bahwa shortcode ini telah dieksekusi secara manual
        $GLOBALS['owwc_cart_icon_rendered'] = true;

        $cart_page_id  = get_option( 'owwc_page_cart_id' );
        $cart_page_url = $cart_page_id ? get_permalink( $cart_page_id ) : site_url( '/keranjang' );
        $cart_page_url = esc_url( $cart_page_url );
        $icon_type = get_option('owwc_floating_cart_icon', 'cart');
        
        // Pilihan model Vector Base SVG
        $svg_content = '';
        if ( $icon_type === 'bag' ) {
            $svg_content = '
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>';
        } elseif ( $icon_type === 'basket' ) {
            $svg_content = '
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
            </svg>';
        } else {
            // Default: 'cart'
            $svg_content = '
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>';
        }

        // Render simple icon SVG dengan badge
        return '<a href="' . $cart_page_url . '" class="owwc-cart-icon-link" aria-label="Lihat Keranjang Belanja">
            ' . $svg_content . '
            <span class="owwc-cart-badge owwc-cart-count">
                0
            </span>
        </a>';
    }
}
