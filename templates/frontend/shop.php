<?php
/**
 * Template Utama: Shop / Daftar Produk
 * Dapat di-override di tema: owwcommerce/shop.php
 *
 * Grid produk menggunakan CSS dari frontend-pages.css.
 */

use OwwCommerce\Core\Plugin;

// Ambil ProductRepository dari container plugin
$product_repo = Plugin::get_instance()->get_container()->get( 'product_repository' );

// Fallback jika belum di-register di container
if ( ! $product_repo ) {
    $product_repo = new \OwwCommerce\Repositories\ProductRepository();
}

$products = $product_repo->get_all();
?>

<div class="owwc-shop-container">

    <!-- Breadcrumb navigasi -->
    <nav class="owwc-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Beranda</a>
        <span class="owwc-breadcrumb-sep">›</span>
        <span class="owwc-breadcrumb-current">Toko</span>
    </nav>

    <!-- Header halaman -->
    <div class="owwc-shop-header">
        <h1 class="owwc-section-title">Produk Kami</h1>
        <p class="owwc-section-subtitle">Temukan produk terbaik yang kami sediakan untuk Anda.</p>
    </div>

    <?php if ( ! empty( $products ) ) : ?>
        <div class="owwc-products-grid">
            <?php foreach ( $products as $product ) : ?>
                <div class="owwc-product-card">
                    <a href="<?php echo esc_url( site_url( '/product/' . $product->slug ) ); ?>" aria-label="Lihat <?php echo esc_attr( $product->title ); ?>">
                        <!-- Placeholder gambar produk (ganti dengan get_the_post_thumbnail nanti) -->
                        <div class="owwc-product-image-placeholder">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                    </a>

                    <div class="owwc-product-info">
                        <h2 class="owwc-product-title">
                            <a href="<?php echo esc_url( site_url( '/product/' . $product->slug ) ); ?>">
                                <?php echo esc_html( $product->title ); ?>
                            </a>
                        </h2>

                        <div class="owwc-product-price">
                            <?php if ( $product->sale_price ) : ?>
                                <del>Rp<?php echo number_format( $product->price, 0, ',', '.' ); ?></del>
                                <ins>Rp<?php echo number_format( $product->sale_price, 0, ',', '.' ); ?></ins>
                            <?php else : ?>
                                <span>Rp<?php echo number_format( $product->price, 0, ',', '.' ); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="owwc-product-actions">
                            <button
                                class="owwc-add-to-cart-btn owwc-btn--block"
                                data-product-id="<?php echo esc_attr( $product->id ); ?>"
                                data-qty="1">
                                <svg class="owwc-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Tambah ke Keranjang
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="owwc-shop-empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <p>Belum ada produk untuk ditampilkan.</p>
        </div>
    <?php endif; ?>
</div>
