<?php
/**
 * Template Wrapper Khusus Halaman OwwCommerce
 * Digunakan untuk halaman Cart, Shop, Checkout, dan My Account 
 * agar tidak terpengaruh oleh layout page.php milik tema.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<div class="owwc-page-wrapper owwc-page">
    <div class="owwc-container">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
    </div>
</div>

<?php get_footer(); ?>
