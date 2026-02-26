<?php
/**
 * Template Utama: Keranjang Belanja (Cart)
 * Dapat di-override di tema: owwcommerce/cart.php
 *
 * Template ini menggunakan CSS class dari frontend-pages.css.
 * Konten keranjang di-render secara dinamis oleh cart.js.
 */

// URL checkout dinamis dari wp_options
$checkout_page_id = get_option( 'owwc_page_checkout_id' );
$checkout_url     = $checkout_page_id ? get_permalink( $checkout_page_id ) : site_url( '/checkout' );

// URL toko dinamis dari wp_options
$shop_page_id = get_option( 'owwc_page_shop_id' );
$shop_url     = $shop_page_id ? get_permalink( $shop_page_id ) : site_url( '/toko' );
?>

<div class="owwc-cart-container">

    <!-- Breadcrumb navigasi -->
    <nav class="owwc-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Beranda</a>
        <span class="owwc-breadcrumb-sep">›</span>
        <span class="owwc-breadcrumb-current">Keranjang Belanja</span>
    </nav>

    <!-- Header halaman -->
    <div class="owwc-cart-header">
        <h1 class="owwc-section-title">Keranjang Belanja</h1>
        <p class="owwc-section-subtitle">
            Kelola item yang ingin Anda beli sebelum melanjutkan ke checkout.
        </p>
    </div>

    <!-- 
        Area konten keranjang — dirender via JavaScript (cart.js).
        Saat halaman dimuat, tampilkan loading skeleton.
    -->
    <div id="owwc-cart-content">
        <!-- Loading skeleton: diganti oleh JS saat data cart dimuat -->
        <div class="owwc-skeleton owwc-skeleton--box" style="height: 80px; margin-bottom: 12px;"></div>
        <div class="owwc-skeleton owwc-skeleton--box" style="height: 80px; margin-bottom: 12px;"></div>
        <div class="owwc-skeleton owwc-skeleton--box" style="height: 80px;"></div>
    </div>

    <!-- Summary & tombol checkout — ditampilkan oleh JS ketika cart berisi item -->
    <div id="owwc-cart-summary" class="owwc-cart-summary" style="display: none;">
        <div class="owwc-cart-summary-total">
            <span>Total</span>
            <span id="owwc-cart-total">Rp0</span>
        </div>
        <div class="owwc-cart-actions">
            <a href="<?php echo esc_url( $shop_url ); ?>" class="owwc-btn owwc-btn--outline">
                ← Lanjut Belanja
            </a>
            <a href="<?php echo esc_url( $checkout_url ); ?>" class="owwc-btn">
                Lanjutkan Checkout →
            </a>
        </div>
    </div>
</div>
