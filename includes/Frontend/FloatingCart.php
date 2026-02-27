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

        // 1. Jangan merender di halaman Admin
        if ( is_admin() ) {
            return;
        }

        // 2. Jangan merender jika Page ID My Account/Checkout sudah diset
        $myaccount_page_id = (int) get_option('owwc_page_myaccount_id');
        $checkout_page_id  = (int) get_option('owwc_page_checkout_id');
        
        if ( is_page($myaccount_page_id) || is_page($checkout_page_id) ) {
            return;
        }

        // 3. Fallback: Cek konten post untuk shortcode
        global $post;
        if ( is_singular() && $post ) {
            if ( 
                strpos( $post->post_content, '[owwcommerce_my_account]' ) !== false ||
                strpos( $post->post_content, '[owwcommerce_checkout]' )   !== false
            ) {
                return;
            }
        }

        // 4. Fallback Terakhir: Cek URI (misal jika user belum set setting tapi slug-nya /my-account)
        $request_uri = trim($_SERVER['REQUEST_URI'], '/');
        // Ambil slug dari ID jika ada
        $myaccount_slug = $myaccount_page_id ? get_post_field('post_name', $myaccount_page_id) : 'my-account';
        $checkout_slug  = $checkout_page_id ? get_post_field('post_name', $checkout_page_id) : 'checkout';

        if ( 
            strpos($request_uri, $myaccount_slug) === 0 || 
            strpos($request_uri, $checkout_slug) === 0 ||
            strpos($request_uri, 'my-account') !== false ||
            strpos($request_uri, 'checkout') !== false
        ) {
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
