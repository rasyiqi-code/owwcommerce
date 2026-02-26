<?php
namespace OwwCommerce\Repositories;

use OwwCommerce\Models\Order;
use OwwCommerce\Models\OrderItem;

/**
 * Class OrderRepository
 * Menangani operasi transaksi Checkout pada tabel oww_orders & oww_order_items.
 */
class OrderRepository {
    private string $table_orders;
    private string $table_items;

    public function __construct() {
        global $wpdb;
        $this->table_orders = $wpdb->prefix . 'oww_orders';
        $this->table_items  = $wpdb->prefix . 'oww_order_items';
    }

    /**
     * Membuat pesanan utuh (Master & Detail). Menggunakan skema transaksional sederhana.
     */
    public function create( Order $order ): Order {
        global $wpdb;

        // Idealnya gunakan transaction isolation Level jika innodb
        $wpdb->query('START TRANSACTION');

        try {
            $order_data = [
                'customer_id'     => $order->customer_id,
                'status'          => $order->status,
                'total_amount'    => $order->total_amount,
                'payment_method'  => $order->payment_method,
                'shipping_method' => $order->shipping_method,
            ];
            
            $wpdb->insert( $this->table_orders, $order_data );
            $order_id = $wpdb->insert_id;
            $order->id = $order_id;

            // Masukkan Item satu per satu (bisa batch insert utk optimalisasi ke depan)
            foreach ( $order->items as $item ) {
                $item_data = [
                    'order_id'    => $order_id,
                    'product_id'  => $item->product_id,
                    'qty'         => $item->qty,
                    'unit_price'  => $item->unit_price,
                    'total_price' => $item->total_price,
                ];
                $wpdb->insert( $this->table_items, $item_data );
                $item->id = $wpdb->insert_id;
                $item->order_id = $order_id;
            }

            $wpdb->query('COMMIT');
            return clone $order;

        } catch ( \Exception $e ) {
            $wpdb->query('ROLLBACK');
            throw $e;
        }
    }

    /**
     * Memuat pesanan berdasarkan ID (lengkap dengan relasi Items).
     */
    public function find( int $id ): ?Order {
        global $wpdb;
        $order_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_orders} WHERE id = %d", $id ), ARRAY_A );
        
        if ( ! $order_row ) {
            return null;
        }

        $items_rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_items} WHERE order_id = %d", $id ), ARRAY_A );
        
        $order_row['items'] = [];
        foreach ( $items_rows as $i_row ) {
            $order_row['items'][] = new OrderItem( $i_row );
        }

        return new Order( $order_row );
    }

    /**
     * Mengambil daftar pesanan untuk Dashboard Admin.
     */
    public function get_all( int $limit = 50, int $offset = 0 ): array {
        global $wpdb;
        $results = $wpdb->get_results( 
            $wpdb->prepare( "SELECT * FROM {$this->table_orders} ORDER BY created_at DESC LIMIT %d OFFSET %d", $limit, $offset ), 
            ARRAY_A 
        );

        return array_map( fn( $row ) => new Order( $row ), $results );
    }

    /**
     * Memperbarui status pesanan.
     */
    public function update_status( int $id, string $status ): bool {
        global $wpdb;
        return (bool) $wpdb->update( $this->table_orders, [ 'status' => $status ], [ 'id' => $id ], [ '%s' ], [ '%d' ] );
    }
}
