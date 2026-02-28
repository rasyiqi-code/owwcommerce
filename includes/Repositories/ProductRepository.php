<?php
namespace OwwCommerce\Repositories;

use OwwCommerce\Models\Product;

/**
 * Class ProductRepository
 * Menangani operasi CRUD tabel oww_products (Zero Bloatware Database interaction).
 */
class ProductRepository {
    private string $table_name;
    private string $table_variations;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'oww_products';
        $this->table_variations = $wpdb->prefix . 'oww_product_variations';
    }

    /**
     * Menyimpan produk baru atau memperbarui jika ada ID.
     * Termasuk field image_url untuk gambar produk.
     */
    public function save( Product $product ): Product {
        global $wpdb;

        $data = [
            'title'          => $product->title,
            'slug'           => $product->slug ?: sanitize_title( $product->title ),
            'description'    => $product->description,
            'type'           => $product->type,
            'status'         => $product->status,
            'price'          => $product->price,
            'sale_price'     => $product->sale_price,
            'sku'            => $product->sku,
            'image_url'      => $product->image_url,
            'gallery_ids'    => ! empty( $product->gallery_ids ) ? implode( ',', $product->gallery_ids ) : null,
            'upsell_ids'     => $product->upsell_ids,
            'cross_sell_ids' => $product->cross_sell_ids,
            'created_by'     => $product->created_by,
            'sales_count'    => $product->sales_count,
            'stock_qty'      => $product->stock_qty,
        ];

        // Format placeholders (harus 14 sesuai urutan $data)
        $format = [ 
            '%s', // title
            '%s', // slug
            '%s', // description
            '%s', // type
            '%s', // status
            '%f', // price
            '%f', // sale_price
            '%s', // sku
            '%s', // image_url
            '%s', // gallery_ids
            '%s', // upsell_ids
            '%s', // cross_sell_ids
            '%d', // created_by
            '%d', // sales_count
            '%d'  // stock_qty
        ];

        if ( $product->id ) {
            $updated = $wpdb->update( $this->table_name, $data, [ 'id' => $product->id ], $format, [ '%d' ] );
            if ( $updated === false ) {
                error_log( "OwwCommerce SQL Error (Update): " . $wpdb->last_error );
            }
        } else {
            $inserted = $wpdb->insert( $this->table_name, $data, $format );
            if ( $inserted === false ) {
                error_log( "OwwCommerce SQL Error (Insert): " . $wpdb->last_error );
            }
            $product->id = $wpdb->insert_id;
        }

        // Simpan Variasi jika tipe produk adalah Variable
        if ( ! empty( $product->type ) && $product->type === 'variable' && ! empty( $product->variations ) ) {
            $this->save_variations( $product->id, $product->variations );
        }

        $saved = $this->find( $product->id );
        return $saved ?: $product;
    }

    /**
     * Helper untuk menyimpan daftar variasi.
     */
    private function save_variations( int $product_id, array $variations ): void {
        global $wpdb;

        // Hapus variasi lama (Simple sync strategy)
        $wpdb->delete( $this->table_variations, [ 'product_id' => $product_id ], [ '%d' ] );

        foreach ( $variations as $v ) {
            $wpdb->insert( $this->table_variations, [
                'product_id' => $product_id,
                'sku'        => $v->sku,
                'price'      => $v->price,
                'sale_price' => $v->sale_price,
                'stock_qty'  => $v->stock_qty,
                'attributes' => json_encode( $v->attributes ),
            ], [ '%d', '%s', '%f', '%f', '%d', '%s' ] );
        }
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

        $product = new Product( $row );

        // Load Variations
        $variation_rows = $wpdb->get_results( $wpdb->prepare( 
            "SELECT * FROM {$this->table_variations} WHERE product_id = %d", 
            $id 
        ), ARRAY_A );

        if ( $variation_rows ) {
            $product->variations = array_map( fn($v) => new \OwwCommerce\Models\ProductVariation( $v ), $variation_rows );
        }

        return $product;
    }

    /**
     * Mencari produk berdasarkan slug.
     */
    public function find_by_slug( string $slug ): ?Product {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE slug = %s", $slug ), ARRAY_A );

        if ( ! $row ) {
            return null;
        }

        return new Product( $row );
    }

    /**
     * Memuat semua atau beberapa produk dengan Limit, Pencarian, dan Filter Kategori.
     */
    public function get_all( int $limit = 50, int $offset = 0, array $filters = [] ): array {
        global $wpdb;
        
        $sql = "SELECT p.* FROM {$this->table_name} p";
        $where = ["p.status = 'publish'"];
        $params = [];

        // Filter Kategori (Slug)
        if ( ! empty( $filters['category'] ) ) {
            $table_rel = $wpdb->prefix . 'oww_product_category_rel';
            $table_cat = $wpdb->prefix . 'oww_categories';
            $sql .= " JOIN {$table_rel} rel ON p.id = rel.product_id";
            $sql .= " JOIN {$table_cat} cat ON rel.category_id = cat.id";
            $where[] = "cat.slug = %s";
            $params[] = $filters['category'];
        }

        // Search Keyword
        if ( ! empty( $filters['s'] ) ) {
            $where[] = "p.title LIKE %s";
            $params[] = '%' . $wpdb->esc_like( $filters['s'] ) . '%';
        }

        if ( ! empty( $where ) ) {
            $sql .= " WHERE " . implode( " AND ", $where );
        }

        // Sorting Logic
        $orderby_map = [
            'newest'       => 'p.created_at DESC',
            'oldest'       => 'p.created_at ASC',
            'title_az'     => 'p.title ASC',
            'title_za'     => 'p.title DESC',
            'best_selling' => 'p.sales_count DESC',
            'trending'     => 'p.sales_count DESC',
        ];

        $orderby = $orderby_map[ $filters['orderby'] ?? 'newest' ] ?? 'p.created_at DESC';

        $sql .= " ORDER BY {$orderby} LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        $results = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

        return array_map( fn( $row ) => new Product( $row ), $results );
    }

    /**
     * Menghapus produk.
     */
    public function delete( int $id ): bool {
        global $wpdb;
        // Hapus variasi terkait terlebih dahulu
        $wpdb->delete( $this->table_variations, [ 'product_id' => $id ], [ '%d' ] );
        return (bool) $wpdb->delete( $this->table_name, [ 'id' => $id ], [ '%d' ] );
    }

    /**
     * Mengurangi stok produk setelah checkout berhasil.
     * Menggunakan query atomik untuk mencegah race condition.
     *
     * @param int $product_id ID produk
     * @param int $qty Jumlah yang dikurangi
     * @return bool True jika berhasil, false jika stok tidak cukup
     */
    public function reduce_stock( int $product_id, int $qty ): bool {
        global $wpdb;

        // Query atomik: hanya kurangi jika stok >= qty (anti-overselling)
        $result = $wpdb->query( $wpdb->prepare(
            "UPDATE {$this->table_name} SET stock_qty = stock_qty - %d WHERE id = %d AND stock_qty >= %d",
            $qty,
            $product_id,
            $qty
        ) );

        return (bool) $result;
    }

    /**
     * Mendapatkan rekomendasi produk otomatis (Smart Algorithms).
     * 
     * @param int    $product_id ID produk saat ini
     * @param string $type       'upsell' (lebih mahal) atau 'cross-sell' (serupa/lebih murah)
     * @param int    $limit      Jumlah produk yang diambil
     * @return array Array of Product objects
     */
    public function get_related_products( int $product_id, string $type = 'cross-sell', int $limit = 4 ): array {
        global $wpdb;

        // 1. Dapatkan kategori produk saat ini
        $table_rel = $wpdb->prefix . 'oww_product_category_rel';
        $category_ids = $wpdb->get_col( $wpdb->prepare( 
            "SELECT category_id FROM {$table_rel} WHERE product_id = %d", 
            $product_id 
        ) );

        if ( empty( $category_ids ) ) return [];

        // 2. Dapatkan data produk saat ini untuk referensi harga
        $current_product = $this->find( $product_id );
        if ( ! $current_product ) return [];
        $price = $current_product->price;

        $category_placeholder = implode( ',', array_fill( 0, count( $category_ids ), '%d' ) );
        
        $sql = "SELECT DISTINCT p.* FROM {$this->table_name} p 
                JOIN {$table_rel} rel ON p.id = rel.product_id 
                WHERE p.id != %d 
                AND p.status = 'publish' 
                AND rel.category_id IN ($category_placeholder)";
        
        $params = array_merge( [ $product_id ], $category_ids );

        if ( $type === 'upsell' ) {
            // Upsell: Harga di atas produk saat ini (max 200% harga asal)
            $sql .= " AND p.price > %f AND p.price <= %f";
            $sql .= " ORDER BY p.price ASC, p.sales_count DESC";
            $params[] = $price;
            $params[] = $price * 2;
        } else {
            // Cross-sell: Harga serupa atau lebih murah
            $sql .= " ORDER BY p.sales_count DESC, RAND()";
        }

        $sql .= " LIMIT %d";
        $params[] = $limit;

        $results = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

        return array_map( fn( $row ) => new Product( $row ), $results );
    }

    /**
     * Menghitung total produk.
     */
    public function count(): int {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
    }
}
