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

// Ambil parameter filter dari URL (Gunakan 'q' sebagai pengganti 's' agar tidak konflik dengan WP)
$search_query = sanitize_text_field( $_GET['q'] ?? '' );
$cat_slug     = sanitize_text_field( $_GET['category'] ?? '' );
$orderby      = sanitize_text_field( $_GET['orderby'] ?? 'newest' );

$filters = [
    's'        => $search_query, // Repositori mengharapkan 's', kita mapping dari $search_query (yang diambil dari $_GET['q'])
    'category' => $cat_slug,
    'orderby'  => $orderby,
];

$products_per_page = 10;
$products   = $product_repo->get_all( $products_per_page, 0, $filters );
$total_products = $product_repo->count(); // Idealnya count dengan filter
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

                <!-- Search Input (name="q") -->
                <input type="text" name="q" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Cari..." class="owwc-input owwc-shop-search-input">

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
                // Sinkronisasi filter saat sorting: pertahankan pencarian dan kategori
                $url = add_query_arg( [
                    'orderby'  => $key,
                    'q'        => $search_query ?: null,
                    'category' => $cat_slug ?: null
                ] );
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
                    <a href="<?php echo esc_url( remove_query_arg( 'q' ) ); ?>" class="owwc-filter-remove">&times;</a>
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

            <a href="<?php echo esc_url( remove_query_arg( ['q', 'category', 'orderby'] ) ); ?>" class="owwc-filter-clear">Hapus Semua</a>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $products ) ) : ?>
        <div class="owwc-products-grid" id="owwc-products-grid">
            <?php foreach ( $products as $product ) : 
                include OWWCOMMERCE_PLUGIN_DIR . 'templates/frontend/parts/product-card.php';
            endforeach; ?>
        </div>

        <?php if ( count( $products ) < $total_products ) : ?>
            <div class="owwc-load-more-container">
                <button id="owwc-load-more" class="owwc-load-more-btn" 
                        data-page="1" 
                        data-q="<?php echo esc_attr( $search_query ); ?>"
                        data-category="<?php echo esc_attr( $cat_slug ); ?>"
                        data-orderby="<?php echo esc_attr( $orderby ); ?>">
                    <span>Muat Lebih Banyak</span>
                    <div class="owwc-loader" style="display: none;"></div>
                </button>
            </div>
        <?php endif; ?>

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
