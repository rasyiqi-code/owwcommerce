<?php
namespace OwwCommerce\WooCommerce;

/**
 * Base Importer class for WooCommerce migration.
 */
class Importer {

    /**
     * Get counts of WooCommerce data.
     *
     * @return array
     */
    public static function get_stats() {
        global $wpdb;

        // Count Products (posts with post_type = product)
        $product_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'" );

        // Count Categories (terms with taxonomy = product_cat)
        $category_count = $wpdb->get_var( "SELECT COUNT(t.term_id) FROM {$wpdb->terms} t INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.taxonomy = 'product_cat'" );

        // Count Orders 
        // We check if HPOS is enabled or use legacy posts
        $order_table = self::get_order_table_name();
        if ( $order_table === $wpdb->posts ) {
            $order_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order'" );
        } else {
            $order_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$order_table}" );
        }

        // Count Customers
        $customer_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users} u INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id WHERE um.meta_key = 'wp_capabilities' AND um.meta_value LIKE '%customer%'" );

        return [
            'products'  => (int) $product_count,
            'categories' => (int) $category_count,
            'orders'    => (int) $order_count,
            'customers' => (int) $customer_count,
        ];
    }

    /**
     * Check if WooCommerce HPOS (High-Performance Order Storage) is enabled.
     *
     * @return string Table name for orders.
     */
    public static function get_order_table_name() {
        global $wpdb;

        // Check for WC HPOS table
        $hpos_table = $wpdb->prefix . 'wc_orders';
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $hpos_table ) );

        return $table_exists ? $hpos_table : $wpdb->posts;
    }

    /**
     * Mark migration as in progress or completed.
     */
    public static function update_migration_status( $status ) {
        update_option( 'owwc_migration_status', $status );
    }
}
