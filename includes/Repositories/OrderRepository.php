<?php
namespace OwwCommerce\Repositories;

use OwwCommerce\Models\Order;
use OwwCommerce\Models\OrderItem;

/**
 * Class OrderRepository
 * Menangani operasi transaksi Checkout pada tabel oww_orders & oww_order_items.
 * Mendukung field alamat billing/shipping.
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

        // Gunakan transaksi DB untuk atomisitas
        $wpdb->query('START TRANSACTION');

        try {
            $order_data = [
                'customer_id'      => $order->customer_id,
                'status'           => $order->status,
                'total_amount'     => $order->total_amount,
                'payment_method'   => $order->payment_method,
                'shipping_method'  => $order->shipping_method,
                'coupon_code'      => $order->coupon_code,
                'discount_total'   => $order->discount_total,
                'billing_address'  => $order->billing_address,
                'shipping_address' => $order->shipping_address,
            ];

            $wpdb->insert( $this->table_orders, $order_data );
            $order_id = $wpdb->insert_id;
            $order->id = $order_id;

            // Masukkan Item satu per satu
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

                // Update sales_count di tabel produk
                $table_products = $wpdb->prefix . 'oww_products';
                $wpdb->query( $wpdb->prepare(
                    "UPDATE {$table_products} SET sales_count = sales_count + %d WHERE id = %d",
                    $item->qty,
                    $item->product_id
                ) );
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
     * Memuat daftar pesanan berdasarkan ID pengguna (Client Dashboard).
     */
    public function find_by_user_id( int $user_id, int $limit = 20, int $offset = 0 ): array {
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare( 
                "SELECT * FROM {$this->table_orders} WHERE customer_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d", 
                $user_id, $limit, $offset 
            ),
            ARRAY_A
        );

        return array_map( fn( $row ) => new Order( $row ), $results );
    }

    /**
     * Menghitung total pesanan (untuk paginasi).
     */
    public function count(): int {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_orders}" );
    }

    /**
     * Memperbarui status pesanan.
     */
    public function update_status( int $id, string $status, ?string $proof_url = null, ?string $note = null ): bool {
        global $wpdb;
        $data = [ 'status' => $status ];
        $format = [ '%s' ];
        
        if ( $proof_url ) {
            $data['payment_proof'] = $proof_url;
            $format[] = '%s';
        }

        if ( $note ) {
            $data['payment_note'] = $note;
            $format[] = '%s';
        }

        return (bool) $wpdb->update(
            $this->table_orders,
            $data,
            [ 'id' => $id ],
            $format,
            [ '%d' ]
        );
    }

    /**
     * Menghitung total pendapatan dari semua pesanan yang berhasil (selesai).
     */
    public function get_total_revenue(): float {
        global $wpdb;
        $total = $wpdb->get_var( "SELECT SUM(total_amount) FROM {$this->table_orders} WHERE status IN ('completed', 'processing')" );
        return (float) $total;
    }

    /**
     * Mendapatkan data penjualan harian untuk rentang waktu tertentu.
     */
    public function get_sales_by_date( int $days = 30 ): array {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(created_at) as date, SUM(total_amount) as total 
             FROM {$this->table_orders} 
             WHERE status IN ('completed', 'processing') 
             AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            $days
        ), ARRAY_A );

        return $results ?: [];
    }

    /**
     * Mendapatkan daftar produk terlaris.
     */
    public function get_top_products( int $limit = 5 ): array {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT p.title, SUM(oi.qty) as total_qty, SUM(oi.total_price) as total_sales
             FROM {$this->table_items} oi
             JOIN {$wpdb->prefix}oww_products p ON oi.product_id = p.id
             JOIN {$this->table_orders} o ON oi.order_id = o.id
             WHERE o.status IN ('completed', 'processing')
             GROUP BY oi.product_id
             ORDER BY total_qty DESC
             LIMIT %d",
            $limit
        ), ARRAY_A );

        return $results ?: [];
    }
}
