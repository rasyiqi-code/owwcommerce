<?php
namespace OwwCommerce\WooCommerce;

/**
 * Handles migration of Orders and Customers from WooCommerce.
 */
class OrderImporter {

    public static function run_batch( $limit = 50, $offset = 0 ) {
        global $wpdb;

        $order_table = Importer::get_order_table_name();
        
        if ( $order_table === $wpdb->posts ) {
            // Legacy Storage
            $wc_orders = $wpdb->get_results( $wpdb->prepare(
                "SELECT ID as id, post_date as date_created, post_status as status FROM {$wpdb->posts} 
                WHERE post_type = 'shop_order' 
                ORDER BY ID DESC
                LIMIT %d OFFSET %d",
                $limit, $offset
            ));
        } else {
            // HPOS Storage
            $wc_orders = $wpdb->get_results( $wpdb->prepare(
                "SELECT id, date_created_gmt as date_created, status FROM {$order_table} 
                ORDER BY id DESC
                LIMIT %d OFFSET %d",
                $limit, $offset
            ));
        }

        if ( empty( $wc_orders ) ) {
            return 0;
        }

        $imported = 0;
        foreach ( $wc_orders as $wc_order ) {
            $order_id = $wc_order->id;

            // 1. Handle Customer
            $customer_id = self::get_or_create_customer( $order_id, $order_table );
            if ( ! $customer_id ) continue;

            // 2. Get Order Meta/Data
            $total = self::get_order_total( $order_id, $order_table );
            $payment_method = self::get_order_meta( $order_id, '_payment_method', $order_table );
            $shipping_method = self::get_order_meta( $order_id, '_shipping_method_title', $order_table );
            
            // Map status (Simplified mapping)
            $status = str_replace( 'wc-', '', $wc_order->status );
            if ( $status === 'completed' ) $status = 'completed';
            elseif ( $status === 'processing' ) $status = 'processing';
            else $status = 'pending';

            $data = [
                'customer_id'    => $customer_id,
                'status'         => $status,
                'total_amount'   => (float) $total,
                'payment_method' => $payment_method ?: 'manual',
                'shipping_method'=> $shipping_method ?: 'flat_rate',
                'created_at'     => $wc_order->date_created,
            ];

            // Check if already exists
            $existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}oww_orders WHERE id = %d", $order_id ) );

            if ( $existing ) {
                $wpdb->update( $wpdb->prefix . 'oww_orders', $data, [ 'id' => $existing ] );
                $oww_order_id = $existing;
            } else {
                // Keep original ID if possible
                $data['id'] = $order_id;
                $wpdb->insert( $wpdb->prefix . 'oww_orders', $data );
                $oww_order_id = $wpdb->insert_id;
            }

            // 3. Migrate Order Items
            self::migrate_order_items( $order_id, $oww_order_id );

            $imported++;
        }

        return $imported;
    }

    private static function get_or_create_customer( $order_id, $order_table ) {
        global $wpdb;

        $user_id = self::get_order_meta( $order_id, '_customer_user', $order_table );
        $email = self::get_order_meta( $order_id, '_billing_email', $order_table );
        $first_name = self::get_order_meta( $order_id, '_billing_first_name', $order_table );
        $last_name = self::get_order_meta( $order_id, '_billing_last_name', $order_table );

        if ( ! $email ) return false;

        $existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}oww_customers WHERE email = %s", $email ) );

        if ( $existing ) return $existing;

        $wpdb->insert( $wpdb->prefix . 'oww_customers', [
            'wp_user_id' => $user_id ?: null,
            'first_name' => $first_name ?: 'Guest',
            'last_name'  => $last_name ?: '',
            'email'      => $email,
            'total_spent'=> 0, // Will be updated later or ignored for MVP
        ]);

        return $wpdb->insert_id;
    }

    private static function migrate_order_items( $wc_order_id, $oww_order_id ) {
        global $wpdb;

        // WC Order items are in wp_woocommerce_order_items and wp_woocommerce_order_itemmeta
        $items = $wpdb->get_results( $wpdb->prepare(
            "SELECT order_item_id, order_item_name FROM {$wpdb->prefix}woocommerce_order_items 
            WHERE order_id = %d AND order_item_type = 'line_item'",
            $wc_order_id
        ));

        // Clear existing items
        $wpdb->delete( $wpdb->prefix . 'oww_order_items', [ 'order_id' => $oww_order_id ] );

        foreach ( $items as $item ) {
            $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d AND meta_key = '_product_id'", $item->order_item_id ) );
            $qty = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d AND meta_key = '_qty'", $item->order_item_id ) );
            $line_total = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d AND meta_key = '_line_total'", $item->order_item_id ) );

            // Try to find Oww Product ID by mapping from WC Product ID (SKU or Slug sync)
            $oww_product_id = $wpdb->get_var( $wpdb->prepare( 
                "SELECT id FROM {$wpdb->prefix}oww_products p 
                INNER JOIN {$wpdb->posts} wp ON p.slug = wp.post_name 
                WHERE wp.ID = %d", 
                $product_id 
            ));

            if ( $oww_product_id ) {
                $wpdb->insert( $wpdb->prefix . 'oww_order_items', [
                    'order_id'   => $oww_order_id,
                    'product_id' => $oww_product_id,
                    'qty'        => (int) $qty,
                    'unit_price' => $qty > 0 ? (float) $line_total / $qty : 0,
                    'total_price'=> (float) $line_total,
                ]);
            }
        }
    }

    private static function get_order_meta( $order_id, $key, $table ) {
        global $wpdb;
        if ( $table === $wpdb->posts ) {
            return get_post_meta( $order_id, $key, true );
        }
        // In HPOS, some data is in the table itself, some in meta
        // For simplicity, we assume WC meta table still exists for HPOS (which it does via wc_orders_meta)
        return $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}wc_orders_meta WHERE order_id = %d AND meta_key = %s", $order_id, $key ) );
    }

    private static function get_order_total( $order_id, $table ) {
        global $wpdb;
        if ( $table === $wpdb->posts ) {
            return get_post_meta( $order_id, '_order_total', true );
        }
        return $wpdb->get_var( $wpdb->prepare( "SELECT total_amount FROM {$table} WHERE id = %d", $order_id ) );
    }
}
