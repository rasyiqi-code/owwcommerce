<?php
namespace OwwCommerce\Repositories;

use OwwCommerce\Models\Category;

/**
 * Repository untuk entitas Category
 */
class CategoryRepository {

    /**
     * Nama tabel kategori di database (dengan prefix wp_)
     */
    protected string $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'oww_categories';
    }

    /**
     * Menyimpan atau mengupdate kategori
     */
    public function save( Category $category ): int|false {
        global $wpdb;

        // Generate slug if empty
        if ( empty( $category->slug ) ) {
            $category->slug = sanitize_title( $category->name );
        }

        $data = [
            'name'        => sanitize_text_field( $category->name ),
            'slug'        => sanitize_title( $category->slug ),
            'parent_id'   => absint( $category->parent_id ),
            'description' => sanitize_textarea_field( $category->description ?? '' ),
        ];

        $format = [ '%s', '%s', '%d', '%s' ];

        if ( $category->id > 0 ) {
            // Update
            $data['updated_at'] = current_time( 'mysql' );
            $format[]           = '%s';
            
            $updated = $wpdb->update(
                $this->table,
                $data,
                [ 'id' => $category->id ],
                $format,
                [ '%d' ]
            );
            
            if ( $updated !== false ) {
                return $category->id;
            }
            return false;
        } else {
            // Insert
            $data['created_at'] = current_time( 'mysql' );
            $data['updated_at'] = current_time( 'mysql' );
            $format[]           = '%s';
            $format[]           = '%s';

            $inserted = $wpdb->insert(
                $this->table,
                $data,
                $format
            );

            if ( $inserted ) {
                $category->id = $wpdb->insert_id;
                return $category->id;
            }
            return false;
        }
    }

    /**
     * Mengambil kategori berdasarkan ID
     */
    public function get_by_id( int $id ): ?Category {
        global $wpdb;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ), ARRAY_A );

        if ( $row ) {
            return new Category( $row );
        }

        return null;
    }

    /**
     * Mengambil semua kategori (bisa ditambahkan argumen limit/offset untuk pagination nanti)
     *
     * @return Category[]
     */
    public function get_all(): array {
        global $wpdb;

        $rows = $wpdb->get_results( "SELECT * FROM {$this->table} ORDER BY name ASC", ARRAY_A );
        $categories = [];

        if ( $rows ) {
            foreach ( $rows as $row ) {
                $categories[] = new Category( $row );
            }
        }

        return $categories;
    }

    /**
     * Menghapus kategori berdasarkan ID
     */
    public function delete( int $id ): bool {
        global $wpdb;

        // Hapus juga relasi produk-kategori jika ada
        $rel_table = $wpdb->prefix . 'oww_product_category_rel';
        $wpdb->delete( $rel_table, [ 'category_id' => $id ], [ '%d' ] );

        $deleted = $wpdb->delete(
            $this->table,
            [ 'id' => $id ],
            [ '%d' ]
        );

        return $deleted !== false;
    }
}
