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
            <div class="owwc-main-image-wrap">
                <?php if ( ! empty( $product->image_url ) ) : ?>
                    <img id="owwc-main-image" src="<?php echo esc_url( $product->image_url ); ?>"
                         alt="<?php echo esc_attr( $product->title ); ?>"
                         loading="lazy">
                <?php else : ?>
                    <div class="owwc-image-placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $product->gallery_ids ) ) : ?>
                <div class="owwc-product-gallery">
                    <?php 
                    // Add main image as the first thumbnail
                    if ( ! empty( $product->image_url ) ) : ?>
                        <div class="owwc-gallery-thumb active" data-large="<?php echo esc_url( $product->image_url ); ?>">
                            <img src="<?php echo esc_url( $product->image_url ); ?>" alt="Main Image">
                        </div>
                    <?php endif; ?>

                    <?php foreach ( $product->gallery_ids as $img_id ) : 
                        $thumb_url = wp_get_attachment_image_url( $img_id, 'thumbnail' );
                        $large_url = wp_get_attachment_image_url( $img_id, 'large' );
                        if ( $thumb_url ) : ?>
                            <div class="owwc-gallery-thumb" data-large="<?php echo esc_url( $large_url ); ?>">
                                <img src="<?php echo esc_url( $thumb_url ); ?>" alt="Gallery Image">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const mainImg = document.getElementById('owwc-main-image');
                    const thumbs = document.querySelectorAll('.owwc-gallery-thumb');
                    
                    thumbs.forEach(thumb => {
                        thumb.addEventListener('click', function() {
                            const newSrc = this.getAttribute('data-large');
                            if (mainImg && newSrc) {
                                mainImg.src = newSrc;
                                thumbs.forEach(t => t.classList.remove('active'));
                                this.classList.add('active');
                            }
                        });
                    });
                });
                </script>
            <?php endif; ?>
        </div>

        <!-- Kolom Kanan: Detail Produk -->
        <div class="owwc-single-product-summary">
            <p class="owwc-single-product-brand">By OwwCommerce</p>
            <h1 class="owwc-single-product-title"><?php echo esc_html( $product->title ); ?></h1>

            <div class="owwc-single-product-price owwc-product-price">
                <?php if ( $product->sale_price ) : ?>
                    <del><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->price ) ); ?></del>
                    <ins><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->sale_price ) ); ?></ins>
                <?php else : ?>
                    <span><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->price ) ); ?></span>
                <?php endif; ?>
            </div>

            <div class="owwc-single-product-description">
                <?php echo wp_kses_post( wpautop( $product->description ) ); ?>
            </div>

            <!-- Variasi Produk (Jika Ada) -->
            <?php if ( $product->type === 'variable' && ! empty( $product->variations ) ) : 
                $attr_repo = $container->get( 'attribute_repository' );
                if ( ! $attr_repo ) {
                    $attr_repo = new \OwwCommerce\Repositories\AttributeRepository();
                }
                $unique_attrs = []; // attribute_id => [term_name1, term_name2]
                
                foreach ( $product->variations as $v ) {
                    foreach ( $v->attributes as $attr_id => $term_name ) {
                        if ( ! isset( $unique_attrs[ $attr_id ] ) ) {
                            $unique_attrs[ $attr_id ] = [];
                        }
                        if ( ! in_array( $term_name, $unique_attrs[ $attr_id ] ) ) {
                            $unique_attrs[ $attr_id ][] = $term_name;
                        }
                    }
                }
            ?>
                <div class="owwc-variations-selection">
                    <?php foreach ( $unique_attrs as $attr_id => $terms ) : 
                        $attr_model = $attr_repo->find( (int) $attr_id );
                        $label = $attr_model ? $attr_model->name : 'Atribut';
                    ?>
                        <div class="owwc-variation-field">
                            <span class="owwc-variation-label"><?php echo esc_html( $label ); ?></span>
                            <div class="owwc-variation-options" data-attribute-id="<?php echo esc_attr( $attr_id ); ?>">
                                <?php foreach ( $terms as $term_name ) : ?>
                                    <div class="owwc-variation-pill" data-value="<?php echo esc_attr( $term_name ); ?>">
                                        <?php echo esc_html( $term_name ); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Form tambah ke keranjang -->
            <div class="owwc-add-to-cart-form">
                <div class="owwc-qty-wrapper">
                    <button type="button" class="owwc-qty-btn owwc-qty-minus">&minus;</button>
                    <input
                        type="number"
                        id="owwc-single-qty"
                        class="owwc-qty-input"
                        value="1"
                        min="1"
                        max="<?php echo (int) $product->stock_qty; ?>"
                        aria-label="Jumlah">
                    <button type="button" class="owwc-qty-btn owwc-qty-plus">&plus;</button>
                </div>

                <button
                    id="owwc-single-add-btn"
                    class="owwc-add-to-cart-btn"
                    data-product-id="<?php echo esc_attr( $product->id ); ?>"
                    data-variation-id="0"
                    data-qty="1"
                    <?php echo $product->stock_qty <= 0 && $product->type !== 'variable' ? 'disabled' : ''; ?>>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span class="btn-text"><?php echo $product->stock_qty > 0 || $product->type === 'variable' ? 'Beli Sekarang' : 'Stok Habis'; ?></span>
                </button>
            </div>

            <!-- Status stok -->
            <p class="owwc-stock-status <?php echo $product->stock_qty > 0 ? 'owwc-stock-status--in-stock' : 'owwc-stock-status--out-stock'; ?>">
                <?php if ( $product->stock_qty > 0 ) : ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"></path></svg>
                    Ready Stock (<?php echo (int) $product->stock_qty; ?> unit)
                <?php else : ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    Maaf, stok sedang kosong
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<!-- Sticky Mobile Action Bar -->
<div class="owwc-mobile-action-bar">
    <div class="owwc-mobile-price">
        <span class="owwc-mobile-price-label">Harga</span>
        <div class="owwc-mobile-price-value owwc-product-price">
            <?php if ( $product->sale_price ) : ?>
                <del style="font-size: 12px;"><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->price ) ); ?></del>
                <ins><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->sale_price ) ); ?></ins>
            <?php else : ?>
                <span><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->price ) ); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <button id="owwc-mobile-buy-trigger" class="owwc-add-to-cart-btn" style="flex: 0 0 60%; padding: 12px;">
        Beli Sekarang
    </button>
</div>

<!-- Script Variasi & Qty -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('owwc-single-add-btn');
    const mobileTrigger = document.getElementById('owwc-mobile-buy-trigger');
    const qtyInput = document.getElementById('owwc-single-qty');
    const pillGroups = document.querySelectorAll('.owwc-variation-options');
    const priceDisplay = document.querySelectorAll('.owwc-product-price'); // Both main and mobile
    const stockDisplay = document.querySelector('.owwc-stock-status');
    
    // Qty buttons logic
    const minusBtn = document.querySelector('.owwc-qty-minus');
    const plusBtn = document.querySelector('.owwc-qty-plus');

    if (minusBtn && plusBtn && qtyInput) {
        minusBtn.addEventListener('click', () => {
            if (qtyInput.value > 1) {
                qtyInput.value = parseInt(qtyInput.value) - 1;
                qtyInput.dispatchEvent(new Event('input'));
            }
        });
        plusBtn.addEventListener('click', () => {
            const max = parseInt(qtyInput.max) || 999;
            if (qtyInput.value < max) {
                qtyInput.value = parseInt(qtyInput.value) + 1;
                qtyInput.dispatchEvent(new Event('input'));
            }
        });
    }

    // Data variasi dari PHP
    const productType = <?php echo json_encode($product->type); ?>;
    const variations = <?php echo json_encode(array_map(fn($v) => $v->to_array(), $product->variations)); ?>;
    const originalPriceHtml = priceDisplay[0].innerHTML;
    const originalStockHtml = stockDisplay.innerHTML;

    // Sinkronisasi Qty
    if (qtyInput) {
        qtyInput.addEventListener('input', () => {
            btn.setAttribute('data-qty', qtyInput.value);
        });
    }

    // Mobile trigger scroll to form
    if (mobileTrigger) {
        mobileTrigger.addEventListener('click', () => {
            window.scrollTo({
                top: document.querySelector('.owwc-add-to-cart-form').offsetTop - 150,
                behavior: 'smooth'
            });
        });
    }

    // Logika Pemilihan Variasi via Pills
    if (productType === 'variable') {
        const selected = {};

        pillGroups.forEach(group => {
            const attrId = group.getAttribute('data-attribute-id');
            const pills = group.querySelectorAll('.owwc-variation-pill');

            pills.forEach(pill => {
                pill.addEventListener('click', () => {
                    // Remove active from peers
                    pills.forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    
                    selected[attrId] = pill.getAttribute('data-value');
                    checkVariations();
                });
            });
        });

        const checkVariations = () => {
            const allSelected = pillGroups.length === Object.keys(selected).length;

            if (!allSelected) {
                btn.disabled = true;
                btn.querySelector('.btn-text').textContent = 'Pilih Variasi';
                return;
            }

            // Cari variasi yang cocok
            const variation = variations.find(v => {
                return Object.entries(selected).every(([attrId, termName]) => {
                    return v.attributes[attrId] === termName;
                });
            });

            if (variation) {
                // Update Harga (Both places)
                const price = variation.sale_price || variation.price;
                const priceFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price).replace('IDR', 'Rp');
                
                let newPriceHtml = '';
                if (variation.sale_price) {
                    const oldPrice = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(variation.price).replace('IDR', 'Rp');
                    newPriceHtml = `<del>${oldPrice}</del> <ins>${priceFormatted}</ins>`;
                } else {
                    newPriceHtml = `<span>${priceFormatted}</span>`;
                }
                
                priceDisplay.forEach(pd => pd.innerHTML = newPriceHtml);

                // Update Stok & Button
                btn.setAttribute('data-variation-id', variation.id);
                if (variation.stock_qty > 0) {
                    stockDisplay.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"></path></svg> Ready Stock (${variation.stock_qty} unit)`;
                    stockDisplay.className = 'owwc-stock-status owwc-stock-status--in-stock';
                    btn.disabled = false;
                    btn.querySelector('.btn-text').textContent = 'Beli Sekarang';
                    qtyInput.max = variation.stock_qty;
                } else {
                    stockDisplay.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg> Maaf, stok habis`;
                    stockDisplay.className = 'owwc-stock-status owwc-stock-status--out-stock';
                    btn.disabled = true;
                    btn.querySelector('.btn-text').textContent = 'Stok Habis';
                }
            } else {
                priceDisplay.forEach(pd => pd.innerHTML = '<span>Habis/Tidak ada</span>');
                btn.disabled = true;
                btn.querySelector('.btn-text').textContent = 'Tidak Tersedia';
            }
        };
        
        // Initial state
        btn.disabled = true;
        btn.querySelector('.btn-text').textContent = 'Pilih Variasi';
    }
});
</script>

<?php get_footer(); ?>
