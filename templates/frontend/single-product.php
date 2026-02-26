<?php
/**
 * Template Halaman Single Product (Virtual Page via Rewrite API)
 * Dapat di-override di tema: owwcommerce/single-product.php
 *
 * Layout menggunakan CSS Grid dari frontend-pages.css.
 * Di mobile, gambar tampil di atas dan detail di bawah.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$slug = get_query_var( 'owwc_product_slug' );

// Ambil ProductRepository dari Plugin Container
$container    = \OwwCommerce\Core\Plugin::get_instance()->get_container();
$product_repo = $container->get( 'product_repository' );

// Fallback jika belum di-register
if ( ! $product_repo ) {
    $product_repo = new \OwwCommerce\Repositories\ProductRepository();
}

// Cari produk berdasarkan slug
global $wpdb;
$table_name = $wpdb->prefix . 'oww_products';
$row = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$table_name} WHERE slug = %s", $slug ) );

if ( ! $row ) {
    // Produk tidak ditemukan -> 404
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 );
    exit;
}

$product = $product_repo->find( (int) $row->id );

// URL toko dinamis
$shop_page_id = get_option( 'owwc_page_shop_id' );
$shop_url     = $shop_page_id ? get_permalink( $shop_page_id ) : site_url( '/toko' );

get_header(); ?>

<div class="owwc-single-product">

    <!-- Breadcrumb navigasi -->
    <nav class="owwc-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Beranda</a>
        <span class="owwc-breadcrumb-sep">›</span>
        <a href="<?php echo esc_url( $shop_url ); ?>">Toko</a>
        <span class="owwc-breadcrumb-sep">›</span>
        <span class="owwc-breadcrumb-current"><?php echo esc_html( $product->title ); ?></span>
    </nav>

    <!-- Layout produk: gambar kiri, detail kanan -->
    <div class="owwc-single-product-layout">

        <!-- Kolom Kiri: Gambar Produk -->
        <div class="owwc-single-product-image">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
        </div>

        <!-- Kolom Kanan: Detail Produk -->
        <div class="owwc-single-product-summary">
            <p class="owwc-single-product-brand">By OwwCommerce</p>
            <h1 class="owwc-single-product-title"><?php echo esc_html( $product->title ); ?></h1>

            <div class="owwc-single-product-price owwc-product-price">
                <?php if ( $product->sale_price ) : ?>
                    <del>Rp<?php echo number_format( $product->price, 0, ',', '.' ); ?></del>
                    <ins>Rp<?php echo number_format( $product->sale_price, 0, ',', '.' ); ?></ins>
                <?php else : ?>
                    <span>Rp<?php echo number_format( $product->price, 0, ',', '.' ); ?></span>
                <?php endif; ?>
            </div>

            <div class="owwc-single-product-description">
                <?php echo wp_kses_post( wpautop( $product->description ) ); ?>
            </div>

            <!-- Form tambah ke keranjang -->
            <div class="owwc-add-to-cart-form">
                <input
                    type="number"
                    id="owwc-single-qty"
                    class="owwc-qty-input"
                    value="1"
                    min="1"
                    max="<?php echo (int) $product->stock_qty; ?>"
                    aria-label="Jumlah">

                <button
                    id="owwc-single-add-btn"
                    class="owwc-add-to-cart-btn"
                    data-product-id="<?php echo esc_attr( $product->id ); ?>"
                    data-qty="1"
                    <?php echo $product->stock_qty <= 0 ? 'disabled' : ''; ?>>
                    <svg class="owwc-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <?php echo $product->stock_qty > 0 ? 'Tambah ke Keranjang' : 'Stok Habis'; ?>
                </button>
            </div>

            <!-- Status stok -->
            <p class="owwc-stock-status <?php echo $product->stock_qty > 0 ? 'owwc-stock-status--in-stock' : 'owwc-stock-status--out-stock'; ?>">
                <?php if ( $product->stock_qty > 0 ) : ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"></path></svg>
                    <?php echo (int) $product->stock_qty; ?> tersedia
                <?php else : ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    Stok habis
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<!-- Script sinkronisasi qty input dengan tombol add to cart -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('owwc-single-add-btn');
    const qtyInput = document.getElementById('owwc-single-qty');

    if (btn && qtyInput) {
        qtyInput.addEventListener('input', () => {
            btn.setAttribute('data-qty', qtyInput.value);
        });
    }
});
</script>

<?php get_footer(); ?>
