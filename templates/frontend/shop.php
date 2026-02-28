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

/**
 * Template for Shop Page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Fetch Customizer Colors
$hero_bg_1       = get_theme_mod( 'owwc_hero_bg_color_1', '#14b8a6' );
$hero_bg_2       = get_theme_mod( 'owwc_hero_bg_color_2', '#0d9488' );
$hero_text_color = get_theme_mod( 'owwc_hero_text_color', '#ffffff' );
$hero_sub_color  = get_theme_mod( 'owwc_hero_subtitle_color', 'rgba(255, 255, 255, 0.9)' );
?>

<style>
    .owwc-shop-hero {
        background: linear-gradient(135deg, <?php echo esc_attr( $hero_bg_1 ); ?> 0%, <?php echo esc_attr( $hero_bg_2 ); ?> 100%) !important;
    }
    .owwc-hero-title, 
    .owwc-breadcrumb a, 
    .owwc-breadcrumb-current, 
    .owwc-breadcrumb-sep {
        color: <?php echo esc_attr( $hero_text_color ); ?> !important;
    }
    .owwc-hero-subtitle {
        color: <?php echo esc_attr( $hero_sub_color ); ?> !important;
    }
</style>

<div class="owwc-shop-container">

    <!-- Hero Section -->
    <div class="owwc-shop-hero">
        <div class="owwc-shop-hero-content">
            <?php $page_title = get_post_field( 'post_title', get_queried_object_id() ) ?: 'Toko'; ?>
            <!-- Breadcrumb navigasi -->
            <nav class="owwc-breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Beranda</a>
                <span class="owwc-breadcrumb-sep">/</span>
                <span class="owwc-breadcrumb-current"><?php echo esc_html( $page_title ); ?></span>
            </nav>

            <h1 class="owwc-hero-title"><?php echo esc_html( $page_title ); ?></h1>
            <p class="owwc-hero-subtitle">Temukan produk berkualitas dan koleksi menarik terbaru dari kami.</p>
        </div>

        <!-- Floating Search Bar -->
        <div class="owwc-shop-search-floating">
            <form action="" method="GET" class="owwc-shop-search-form">
                <input type="text" name="q" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Cari produk..." class="owwc-shop-search-input-new">
                <button type="submit" class="owwc-shop-search-submit">Cari</button>
                <input type="hidden" name="category" value="<?php echo esc_attr( $cat_slug ); ?>">
                <input type="hidden" name="orderby" value="<?php echo esc_attr( $orderby ); ?>">
            </form>
        </div>
    </div>

    <!-- Category Filter Tags -->
    <div class="owwc-category-tags-container">
        <div class="owwc-category-tags">
            <a href="<?php echo esc_url( remove_query_arg( ['category', 'q'] ) ); ?>" class="owwc-cat-tag <?php echo empty( $cat_slug ) ? 'is-active' : ''; ?>">All</a>
            <?php foreach ( $categories as $cat ) : 
                $active = ( $cat_slug === $cat->slug );
                $url = add_query_arg( [
                    'category' => $cat->slug,
                    'q'        => $search_query ?: null,
                    'orderby'  => $orderby ?: null
                ] );
            ?>
                <a href="<?php echo esc_url( $url ); ?>" class="owwc-cat-tag <?php echo $active ? 'is-active' : ''; ?>">
                    <?php echo esc_html( $cat->name ); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Active Filters Tags (Optional/Simplified) -->
    <?php if ( ! empty( $search_query ) ) : ?>
        <div class="owwc-search-result-info">
            <span class="owwc-filter-label">Menampilkan hasil untuk:</span>
            <div class="owwc-filter-tag">
                <span>"<?php echo esc_html( $search_query ); ?>"</span>
                <a href="<?php echo esc_url( remove_query_arg( 'q' ) ); ?>" class="owwc-filter-remove">&times;</a>
            </div>
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
