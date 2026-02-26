<?php
namespace OwwCommerce\Core;

/**
 * Fired during plugin activation.
 */
class Activator {

    /**
     * Jalankan saat plugin diaktifkan.
     */
    public static function activate() {
        // Panggil installer database
        \OwwCommerce\Database\Installer::install();
        
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        $plugin_info = get_plugin_data( OWWCOMMERCE_PLUGIN_DIR . 'owwcommerce.php' );
        if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
            deactivate_plugins( plugin_basename( OWWCOMMERCE_PLUGIN_DIR . 'owwcommerce.php' ) );
            wp_die( sprintf( __( 'OwwCommerce requires PHP 8.1 or higher. You are running version %s.', 'owwcommerce' ), PHP_VERSION ) );
        }

        // Buat halaman-halaman penting (Toko, Keranjang, Checkout) secara otomatis
        self::create_pages();

        // Instance Router & Flush Rewrite Rules
        $container = new \OwwCommerce\Core\Container();
        $router = new \OwwCommerce\Frontend\Router( $container );
        $router->add_rewrite_rules();
        flush_rewrite_rules();
    }

    /**
     * Buat halaman-halaman WP yang dibutuhkan OwwCommerce secara otomatis.
     * 
     * Setiap halaman hanya dibuat jika belum ada halaman dengan slug yang sama.
     * ID halaman yang dibuat disimpan ke wp_options agar bisa direferensikan
     * secara dinamis (misal: untuk link keranjang di floating cart icon).
     */
    private static function create_pages(): void {
        $pages = [
            'toko' => [
                'title'   => 'Toko',
                'content' => '[owwcommerce_shop]',
                'option'  => 'owwc_page_shop_id',
            ],
            'keranjang' => [
                'title'   => 'Keranjang Belanja',
                'content' => '[owwcommerce_cart]',
                'option'  => 'owwc_page_cart_id',
            ],
            'checkout' => [
                'title'   => 'Checkout',
                'content' => '[owwcommerce_checkout]',
                'option'  => 'owwc_page_checkout_id',
            ],
        ];

        foreach ( $pages as $slug => $page_data ) {
            // Cek apakah halaman dengan slug ini sudah ada
            $existing_page = get_page_by_path( $slug );

            if ( $existing_page ) {
                // Halaman sudah ada, simpan ID-nya ke option jika belum
                if ( ! get_option( $page_data['option'] ) ) {
                    update_option( $page_data['option'], $existing_page->ID );
                }
                continue;
            }

            // Buat halaman baru
            $page_id = wp_insert_post( [
                'post_title'   => $page_data['title'],
                'post_name'    => $slug,
                'post_content' => $page_data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => get_current_user_id(),
                'comment_status' => 'closed',
            ] );

            // Simpan ID halaman ke wp_options jika berhasil dibuat
            if ( ! is_wp_error( $page_id ) && $page_id > 0 ) {
                update_option( $page_data['option'], $page_id );
            }
        }
    }
}
