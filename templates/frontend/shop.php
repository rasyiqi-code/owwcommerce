<?php
/**
 * Template Utama: Shop / Daftar Produk
 * Dapat di-override di tema: owwcommerce/shop.php
 *
 * Grid produk menggunakan CSS dari frontend-pages.css.
 */

use OwwCommerce\Core\Plugin;

// Ambil Repository dari container plugin
$container     = Plugin::get_instance()->get_container();
$product_repo  = $container->get( 'product_repository' );
$category_repo = $container->get( 'category_repository' );

// Fallback jika belum di-register di container
if ( ! $product_repo ) {
    $product_repo = new \OwwCommerce\Repositories\ProductRepository();
}
if ( ! $category_repo ) {
    $category_repo = new \OwwCommerce\Repositories\CategoryRepository();
}

// Ambil parameter filter dari URL
$search_query = sanitize_text_field( $_GET['s'] ?? '' );
$cat_slug     = sanitize_text_field( $_GET['category'] ?? '' );
$orderby      = sanitize_text_field( $_GET['orderby'] ?? 'newest' );

$filters = [
    's'        => $search_query,
    'category' => $cat_slug,
    'orderby'  => $orderby,
];

$products   = $product_repo->get_all( 50, 0, $filters );
$categories = $category_repo->get_all();
?>

<div class="owwc-shop-container">

    <!-- Breadcrumb navigasi -->
    <nav class="owwc-breadcrumb" aria-label="Breadcrumb" style="margin-bottom: 12px; font-size: 11px; opacity: 0.6;">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Beranda</a>
        <span class="owwc-breadcrumb-sep">/</span>
        <span class="owwc-breadcrumb-current">Toko</span>
    </nav>

    <!-- Header halaman (Slim) -->
    <div class="owwc-shop-header">
        <div>
            <h1 class="owwc-section-title">Produk Pilihan</h1>
            <p class="owwc-section-subtitle">Koleksi kurasi terbaik dari kami.</p>
        </div>
    </div>

    <!-- Unified Search + External Sorting -->
    <div class="owwc-shop-controls-wrapper">
        
        <!-- Unified Compact Filter Bar (Search & Category) -->
        <div class="owwc-shop-filters owwc-shop-filter-bar">
            <form action="" method="GET" class="owwc-shop-filter-form">
                <!-- Search Icon -->
                <div class="owwc-shop-search-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                <!-- Search Input -->
                <input type="text" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Cari..." class="owwc-input owwc-shop-search-input">

                <div class="owwc-shop-filter-divider"></div>
                
                <!-- Category Filter -->
                <select name="category" class="owwc-select owwc-shop-category-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $cat_slug, $cat->slug ); ?>>
                            <?php echo esc_html( $cat->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Hidden orderby to keep current sort while searching/category change if it were stay, but here we submit the form -->
                <input type="hidden" name="orderby" value="<?php echo esc_attr( $orderby ); ?>">

                <button type="submit" class="owwc-btn owwc-shop-search-btn" title="Cari">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </form>
        </div>

        <!-- External Sorting Button Group -->
        <div class="owwc-sort-group owwc-shop-sort-group">
            <?php
            $sort_options = [
                'newest'       => [ 'label' => 'Terbaru', 'icon' => 'clock' ],
                'title_az'     => [ 'label' => 'A-Z', 'icon' => 'sort-alpha-down' ],
                'best_selling' => [ 'label' => 'Terlaris', 'icon' => 'zap' ],
                'trending'     => [ 'label' => 'Trending', 'icon' => 'trending-up' ],
            ];

            foreach ( $sort_options as $key => $opt ) : 
                $active = ( $orderby === $key );
                $url = add_query_arg( 'orderby', $key );
            ?>
                <a href="<?php echo esc_url( $url ); ?>" 
                   class="owwc-sort-btn <?php echo $active ? 'is-active' : ''; ?>" 
                   title="<?php echo esc_attr( $opt['label'] ); ?>">
                    <?php if ( $opt['icon'] === 'clock' ) : ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <?php elseif ( $opt['icon'] === 'sort-alpha-down' ) : ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5h10M11 9h7M11 13h4M3 17l3 3 3-3M6 18V4"></path></svg>
                    <?php elseif ( $opt['icon'] === 'zap' ) : ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    <?php elseif ( $opt['icon'] === 'trending-up' ) : ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
    <!-- Active Filters Tags -->
    <?php if ( ! empty( $search_query ) || ! empty( $cat_slug ) ) : ?>
        <div class="owwc-active-filters owwc-active-filter-bar">
            <span class="owwc-filter-label">Filter Aktif:</span>
            
            <?php if ( ! empty( $search_query ) ) : ?>
                <div class="owwc-filter-tag">
                    <span>"<?php echo esc_html( $search_query ); ?>"</span>
                    <a href="<?php echo esc_url( remove_query_arg( 's' ) ); ?>" class="owwc-filter-remove">&times;</a>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $cat_slug ) ) : 
                $active_cat = array_filter( $categories, fn($c) => $c->slug === $cat_slug );
                $cat_name = ! empty( $active_cat ) ? reset( $active_cat )->name : $cat_slug;
            ?>
                <div class="owwc-filter-tag">
                    <span>Kat: <?php echo esc_html( $cat_name ); ?></span>
                    <a href="<?php echo esc_url( remove_query_arg( 'category' ) ); ?>" class="owwc-filter-remove">&times;</a>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $orderby ) && $orderby !== 'newest' ) : 
                $sort_labels = [
                    'oldest'       => 'Terlama',
                    'trending'     => 'Trending',
                    'title_az'     => 'A-Z',
                    'title_za'     => 'Z-A',
                    'best_selling' => 'Terlaris',
                ];
                $sort_label = $sort_labels[$orderby] ?? $orderby;
            ?>
                <div class="owwc-filter-tag">
                    <span>Urut: <?php echo esc_html( $sort_label ); ?></span>
                    <a href="<?php echo esc_url( remove_query_arg( 'orderby' ) ); ?>" class="owwc-filter-remove">&times;</a>
                </div>
            <?php endif; ?>

            <a href="<?php echo esc_url( remove_query_arg( ['s', 'category', 'orderby'] ) ); ?>" class="owwc-filter-clear">Hapus Semua</a>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $products ) ) : ?>
        <div class="owwc-products-grid">
            <?php foreach ( $products as $product ) : 
                // Simple heuristic for demo: dynamic background based on product name/id if not in DB
                $hover_bg = '#f9f9f9'; // Default light
                if ( stripos($product->title, 'car') !== false ) $hover_bg = '#FFF9E3'; // Light yellow
                if ( stripos($product->title, 'glass') !== false ) $hover_bg = '#F0F4F8'; // Light blue/grey
            ?>
                <div class="owwc-product-card" style="--owwc-card-hover-bg: <?php echo esc_attr($hover_bg); ?>">
                    <div class="owwc-product-card-inner">
                        <a href="<?php echo esc_url( \OwwCommerce\Frontend\Router::get_product_link( $product->slug ) ); ?>" class="owwc-product-link" aria-label="Lihat <?php echo esc_attr( $product->title ); ?>">
                            <?php if ( ! empty( $product->image_url ) ) : ?>
                                <div class="owwc-product-image">
                                    <img src="<?php echo esc_url( $product->image_url ); ?>"
                                         alt="<?php echo esc_attr( $product->title ); ?>"
                                         loading="lazy"
                                         width="300" height="300">
                                </div>
                            <?php else : ?>
                                <!-- Placeholder jika belum ada gambar -->
                                <div class="owwc-product-image-placeholder">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </a>

                        <!-- Badges Container -->
                        <div class="owwc-product-badges">
                            <?php if ( $product->sale_price ) : ?>
                                <span class="owwc-badge owwc-badge--sale">SALE</span>
                            <?php endif; ?>
                            <?php if ( $product->sales_count > 10 ) : ?>
                                <span class="owwc-badge owwc-badge--hot">TERLARIS</span>
                            <?php endif; ?>
                        </div>

                        <!-- Hover Overlay Cart Button -->
                        <button
                            class="owwc-add-to-cart-btn owwc-btn-hover-icon"
                            data-product-id="<?php echo esc_attr( $product->id ); ?>"
                            data-qty="1"
                            title="Tambah ke Keranjang"
                            aria-label="Tambah ke Keranjang">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="owwc-product-info">
                        <h2 class="owwc-product-title">
                            <a href="<?php echo esc_url( \OwwCommerce\Frontend\Router::get_product_link( $product->slug ) ); ?>">
                                <?php echo esc_html( $product->title ); ?>
                            </a>
                        </h2>

                        <div class="owwc-product-price">
                            <?php if ( $product->sale_price ) : ?>
                                <div class="owwc-price-sale">
                                    <del class="owwc-price-old"><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->price ) ); ?></del>
                                    <ins class="owwc-price-current"><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->sale_price ) ); ?></ins>
                                </div>
                            <?php else : ?>
                                <span class="owwc-price-current"><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $product->price ) ); ?></span>
                            <?php endif; ?>
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
