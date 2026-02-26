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
        // Jika developer sudah mengeksekusi shortcode [owwcommerce_cart_icon] secara manual di header/page, 
        // global flag ini akan bernilai true, jadi hentikan eksekusi floating ini.
        if ( isset( $GLOBALS['owwc_cart_icon_rendered'] ) && $GLOBALS['owwc_cart_icon_rendered'] === true ) {
            return;
        }

        // Jangan merender di halaman Admin
        if ( is_admin() ) {
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
