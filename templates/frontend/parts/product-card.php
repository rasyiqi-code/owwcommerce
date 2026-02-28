<?php
/**
 * Template Part: Product Card
 * Variable $product (Product object) must be available.
 */
if ( ! isset( $product ) ) return;

$hover_bg = '#f9f9f9';
if ( stripos( $product->title, 'car' ) !== false ) $hover_bg = '#FFF9E3';
if ( stripos( $product->title, 'glass' ) !== false ) $hover_bg = '#F0F4F8';
?>
<div class="owwc-product-card" style="--owwc-card-hover-bg: <?php echo esc_attr( $hover_bg ); ?>">
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
                <div class="owwc-product-image-placeholder">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                </div>
            <?php endif; ?>
        </a>

        <div class="owwc-product-badges">
            <?php if ( $product->sale_price ) : ?>
                <span class="owwc-badge owwc-badge--sale">SALE</span>
            <?php endif; ?>
            <?php if ( $product->sales_count > 10 ) : ?>
                <span class="owwc-badge owwc-badge--hot">TERLARIS</span>
            <?php endif; ?>
        </div>

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
