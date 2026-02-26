<?php
namespace OwwCommerce\Repositories;

use OwwCommerce\Models\Product;

/**
 * Class ProductRepository
 * Menangani operasi CRUD tabel oww_products (Zero Bloatware Database interaction).
 */
class ProductRepository {
    private string $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'oww_products';
    }

    /**
     * Menyimpan produk baru atau memperbarui jika ada ID.
     */
    public function save( Product $product ): Product {
        global $wpdb;

        $data = [
            'title'       => $product->title,
            'slug'        => $product->slug ?: sanitize_title( $product->title ),
            'description' => $product->description,
            'type'        => $product->type,
            'price'       => $product->price,
            'sale_price'  => $product->sale_price,
            'sku'         => $product->sku,
            'stock_qty'   => $product->stock_qty,
        ];

        $format = [ '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%d' ];

        if ( $product->id ) {
            $wpdb->update( $this->table_name, $data, [ 'id' => $product->id ], $format, [ '%d' ] );
        } else {
            $wpdb->insert( $this->table_name, $data, $format );
            $product->id = $wpdb->insert_id;
        }

        return $this->find( $product->id );
    }

    /**
     * Mengambil produk berdasarkan ID.
     */
    public function find( int $id ): ?Product {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id ), ARRAY_A );
        
        if ( ! $row ) {
            return null;
        }

        return new Product( $row );
    }

    /**
     * Memuat semua atau beberapa produk dengan Limit.
     */
    public function get_all( int $limit = 50, int $offset = 0 ): array {
        global $wpdb;
        $results = $wpdb->get_results( 
            $wpdb->prepare( "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d", $limit, $offset ), 
            ARRAY_A 
        );

        return array_map( fn( $row ) => new Product( $row ), $results );
    }

    /**
     * Menghapus produk.
     */
    public function delete( int $id ): bool {
        global $wpdb;
        return (bool) $wpdb->delete( $this->table_name, [ 'id' => $id ], [ '%d' ] );
    }
}
