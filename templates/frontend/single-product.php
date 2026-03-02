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
            <?php 
            $author_name = 'Penerbit KBM'; // Default fallback
            if ( ! empty( $product->created_by ) ) {
                $user_data = get_userdata( $product->created_by );
                if ( $user_data ) {
                    $author_name = $user_data->display_name;
                }
            }
            ?>
            <p class="owwc-single-product-brand"><?php echo esc_html( $author_name ); ?></p>
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

            <!-- Tombol Aksi -->
            <div class="owwc-product-actions-wrap">
                <?php 
                $enable_cart = get_option( 'owwc_enable_cart_checkout', 1 );
                $enable_ext  = get_option( 'owwc_enable_external_checkout', 1 );
                $enable_wa   = get_option( 'owwc_enable_whatsapp_checkout', 1 );
                
                $wa_link = '';
                if ( $enable_wa ) {
                    $wa_number = ! empty( $product->whatsapp_url ) ? $product->whatsapp_url : get_option( 'owwc_whatsapp_number' );
                    if ( ! empty( $wa_number ) ) {
                        if ( is_numeric( str_replace(['+', ' '], '', $wa_number) ) ) {
                            $clean_number = str_replace(['+', ' '], '', $wa_number);
                            $msg = sprintf( 'Halo Admin, saya tertarik dengan produk *%s* (%s). Mohon info lebih lanjut.', $product->title, home_url( add_query_arg( [], $wp->request ) ) );
                            $wa_link = "https://wa.me/{$clean_number}?text=" . rawurlencode( $msg );
                        } else {
                            $wa_link = $wa_number;
                        }
                    }
                }
                ?>
                <div class="owwc-add-to-cart-form">
                    <?php if ( $enable_cart ) : ?>
                        <div class="owwc-cart-action-group">
                            <div class="owwc-qty-wrapper">
                                <button type="button" class="owwc-qty-btn owwc-qty-minus" aria-label="Kurangi jumlah">&minus;</button>
                                <input
                                    type="number"
                                    id="owwc-single-qty"
                                    class="owwc-qty-input"
                                    value="1"
                                    min="1"
                                    max="<?php echo (int) $product->stock_qty; ?>"
                                    aria-label="Jumlah">
                                <button type="button" class="owwc-qty-btn owwc-qty-plus" aria-label="Tambah jumlah">&plus;</button>
                            </div>

                            <button
                                id="owwc-single-add-btn"
                                class="owwc-add-to-cart-btn"
                                data-product-id="<?php echo esc_attr( $product->id ); ?>"
                                data-variation-id="0"
                                data-qty="1"
                                <?php echo $product->stock_qty <= 0 && $product->type !== 'variable' ? 'disabled' : ''; ?>>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                <span class="btn-text"><?php echo $product->stock_qty > 0 || $product->type === 'variable' ? 'Tambah ke Keranjang' : 'Stok Habis'; ?></span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ( ( $enable_ext && ! empty( $product->checkout_url ) ) || $enable_wa ) : ?>
                        <div class="owwc-external-buttons-group">
                            <?php if ( $enable_ext && ! empty( $product->checkout_url ) ) : ?>
                                <a href="<?php echo esc_url( $product->checkout_url ); ?>" 
                                   class="owwc-add-to-cart-btn owwc-marketplace-btn" 
                                   target="_blank" 
                                   rel="nofollow noopener">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                    <span>Beli di Marketplace</span>
                                </a>
                            <?php endif; ?>

                            <?php if ( $enable_wa && ! empty( $wa_link ) ) : ?>
                                    <a href="<?php echo esc_url( $wa_link ); ?>" 
                                       class="owwc-add-to-cart-btn owwc-whatsapp-btn" 
                                       target="_blank" 
                                       rel="nofollow noopener">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                        <span>Tanya via WhatsApp</span>
                                    </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Status stok -->
                <div class="owwc-stock-status-wrap">
                    <p class="owwc-stock-status <?php echo $product->stock_qty > 0 ? 'owwc-stock-status--in-stock' : 'owwc-stock-status--out-stock'; ?>">
                        <?php if ( $product->stock_qty > 0 ) : ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Ready Stock (<?php echo (int) $product->stock_qty; ?> unit)
                        <?php else : ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            Maaf, stok sedang kosong
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Recommendations Section -->
    <div id="owwc-product-recommendations" class="owwc-recommendations-section" style="margin-top: 60px;">
        <div id="owwc-upsells-wrap" style="display: none; margin-bottom: 40px;">
            <h2 style="font-size: 20px; margin-bottom: 24px;">Anda Mungkin Juga Suka</h2>
            <div id="owwc-upsells-list" class="owwc-products-grid">
                <!-- Diisi via JS -->
            </div>
        </div>
    </div>

    <!-- Halaman Ulasan (Reviews) -->
    <div class="owwc-reviews-section">
        <div class="owwc-reviews-header">
            <h2 class="owwc-reviews-title">Ulasan Pembeli (0)</h2>
            <div class="owwc-average-rating">
                <div class="owwc-rating-stars-wrap">
                    <div class="owwc-rating-stars" id="owwc-avg-stars">
                        <!-- Stars via JS -->
                    </div>
                </div>
                <strong id="owwc-avg-num">0.0</strong>
            </div>
        </div>

        <div id="owwc-review-list" class="owwc-review-list">
            <p style="text-align: center; color: #999; padding: 40px 0;">Belum ada ulasan untuk produk ini. Jadilah yang pertama memberikan ulasan!</p>
        </div>

        <div class="owwc-review-form-card">
            <h3>Tulis Ulasan</h3>
            <p class="form-subtitle">Bagikan pengalaman Anda tentang produk ini untuk membantu pembeli lain.</p>
            
            <form id="owwc-review-form">
                <div class="owwc-review-form-field">
                    <label>Rating Anda *</label>
                    <div class="owwc-rating-picker">
                        <input type="radio" name="rating" id="star5" value="5" required><label for="star5">★</label>
                        <input type="radio" name="rating" id="star4" value="4"><label for="star4">★</label>
                        <input type="radio" name="rating" id="star3" value="3"><label for="star3">★</label>
                        <input type="radio" name="rating" id="star2" value="2"><label for="star2">★</label>
                        <input type="radio" name="rating" id="star1" value="1"><label for="star1">★</label>
                    </div>
                </div>

                <div class="owwc-review-form-grid">
                    <?php if ( ! is_user_logged_in() ) : ?>
                        <div class="owwc-review-form-field">
                            <label>Nama *</label>
                            <input type="text" name="author_name" required placeholder="Nama Anda" class="owwc-admin-input">
                        </div>
                        <div class="owwc-review-form-field">
                            <label>Email *</label>
                            <input type="email" name="author_email" required placeholder="admin@example.com" class="owwc-admin-input">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="owwc-review-form-field">
                    <label>Komentar *</label>
                    <textarea name="comment" required rows="5" placeholder="Tulis komentar ulasan Anda di sini..." class="owwc-admin-input"></textarea>
                </div>

                <div class="owwc-review-submit-wrap">
                    <button type="submit" id="owwc-submit-review" class="owwc-btn">
                        Kirim Ulasan
                    </button>
                    <span id="owwc-review-msg" class="owwc-review-msg"></span>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sticky Mobile Action Bar -->
<?php 
// Hanya tampilkan sticky bar jika minimal salah satu fitur checkout aktif
if ( $enable_cart || ( $enable_ext && ! empty( $product->checkout_url ) ) || $enable_wa ) : 
?>
    <div class="owwc-mobile-action-bar">
        <div class="owwc-mobile-price">
            <span class="owwc-mobile-price-label">Harga</span>
            <div class="owwc-mobile-price-value owwc-product-price">
                <?php if ( $product->sale_price ) : ?>
                    <del class="owwc-price-old"><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->price ) ); ?></del>
                    <ins><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->sale_price ) ); ?></ins>
                <?php else : ?>
                    <span><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->price ) ); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( $enable_cart ) : ?>
            <button id="owwc-mobile-buy-trigger" class="owwc-btn owwc-mobile-buy-btn">
                Beli Sekarang
            </button>
        <?php elseif ( $enable_ext && ! empty( $product->checkout_url ) ) : ?>
            <a href="<?php echo esc_url( $product->checkout_url ); ?>" class="owwc-btn owwc-mobile-buy-btn owwc-marketplace-btn" target="_blank" rel="nofollow noopener">
                Beli di Marketplace
            </a>
        <?php elseif ( $enable_wa && ! empty( $wa_link ) ) : ?>
            <a href="<?php echo esc_url( $wa_link ); ?>" class="owwc-btn owwc-mobile-buy-btn owwc-whatsapp-btn" target="_blank" rel="nofollow noopener">
                Tanya WhatsApp
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

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

    // Load Smart Recommendations
    const loadRecommendations = async () => {
        try {
            const apiRecBase = `<?php echo esc_url_raw( rest_url( 'owwc/v1/products/' . $product->id . '/recommendations' ) ); ?>`;
            const res = await fetch(apiRecBase);
            const data = await res.json();

            if (data.upsells && data.upsells.length > 0) {
                const upsellWrap = document.getElementById('owwc-upsells-wrap');
                const upsellList = document.getElementById('owwc-upsells-list');
                
                upsellWrap.style.display = 'block';
                upsellList.innerHTML = data.upsells.map(p => `
                    <div class="owwc-product-card owwc-recommendation-card">
                        <a href="${owwcSettings.homeUrl}${owwcSettings.productBase}/${p.slug}" style="text-decoration: none; color: inherit;">
                            <img src="${p.image_url || ''}" style="width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 4px; margin-bottom: 10px;">
                            <h3 style="font-size: 14px; margin-bottom: 8px;">${p.title}</h3>
                            <div class="owwc-product-price" style="font-weight: 600; color: var(--owwc-primary);">
                                ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p.price).replace('IDR', 'Rp')}
                            </div>
                        </a>
                    </div>
                `).join('');
            }
        } catch (e) {
            console.error("Gagal memuat rekomendasi:", e);
        }
    };

    loadRecommendations();

    // ================================================================
    // REVIEWS LOGIC
    // ================================================================
    const reviewList = document.getElementById('owwc-review-list');
    const reviewForm = document.getElementById('owwc-review-form');
    const avgStars = document.getElementById('owwc-avg-stars');
    const avgNum = document.getElementById('owwc-avg-num');
    const reviewTitle = document.querySelector('.owwc-reviews-title');

    const renderStars = (rating) => {
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += `<span class="owwc-star ${i > Math.round(rating) ? 'empty' : ''}">★</span>`;
        }
        return starsHtml;
    };

    const loadReviews = async () => {
        try {
            const apiReviewUrl = `<?php echo esc_url_raw( rest_url( 'owwc/v1/products/' . $product->id . '/reviews' ) ); ?>`;
            const res = await fetch(apiReviewUrl);
            const data = await res.json();

            if (data.review_count > 0) {
                reviewTitle.textContent = `Ulasan Pembeli (${data.review_count})`;
                avgNum.textContent = data.average_rating.toFixed(1);
                avgStars.innerHTML = renderStars(data.average_rating);

                reviewList.innerHTML = data.items.map(r => `
                    <div class="owwc-review-item">
                        <div class="owwc-review-avatar">
                            <img src="https://secure.gravatar.com/avatar/${btoa(r.author_email || 'default')}?s=60&d=mp" alt="Avatar">
                        </div>
                        <div class="owwc-review-content">
                            <div class="owwc-review-meta">
                                <span class="owwc-review-author">${r.author_name || 'Anonim'}</span>
                                <div class="owwc-rating-stars">
                                    ${renderStars(r.rating)}
                                </div>
                            </div>
                            <div class="owwc-review-date">${new Date(r.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</div>
                            <div class="owwc-review-comment">${r.comment}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                avgStars.innerHTML = renderStars(0);
            }
        } catch (e) {
            console.error("Gagal memuat ulasan:", e);
        }
    };

    if (reviewForm) {
        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('owwc-submit-review');
            const msgEl = document.getElementById('owwc-review-msg');
            const formData = new FormData(reviewForm);
            const data = Object.fromEntries(formData.entries());

            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengirim...';

            try {
                const apiReviewUrl = `<?php echo esc_url_raw( rest_url( 'owwc/v1/products/' . $product->id . '/reviews' ) ); ?>`;
                const res = await fetch(apiReviewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': owwcSettings.nonce
                    },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    msgEl.textContent = 'Ulasan berhasil dikirim!';
                    msgEl.style.color = '#2e7d32';
                    msgEl.style.display = 'inline';
                    reviewForm.reset();
                    loadReviews();
                } else {
                    const error = await res.json();
                    msgEl.textContent = error.message || 'Gagal mengirim ulasan.';
                    msgEl.style.color = '#d32f2f';
                    msgEl.style.display = 'inline';
                }
            } catch (e) {
                msgEl.textContent = 'Terjadi kesalahan jaringan.';
                msgEl.style.display = 'inline';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Kirim Ulasan';
                setTimeout(() => { msgEl.style.display = 'none'; }, 5000);
            }
        });
    }

    loadReviews();
});
</script>

<style>
.owwc-recommendations-section h2 { 
    border-bottom: 2px solid #eee; 
    padding-bottom: 10px;
    display: inline-block;
}
.owwc-product-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
</style>

<?php get_footer(); ?>
