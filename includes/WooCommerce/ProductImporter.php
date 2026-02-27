<?php
namespace OwwCommerce\WooCommerce;

use OwwCommerce\Repositories\ProductRepository;
use OwwCommerce\Repositories\CategoryRepository;

/**
 * Handles migration of Products and Categories from WooCommerce.
 */
class ProductImporter {

    public static function run_batch( $limit = 50, $offset = 0 ) {
        global $wpdb;

        // 1. Migrate Categories first if offset is 0
        if ( $offset === 0 ) {
            self::migrate_categories();
        }

        // 2. Migrate Products
        $wc_products = $wpdb->get_results( $wpdb->prepare(
            "SELECT ID, post_title, post_name, post_content, post_status, post_date FROM {$wpdb->posts} 
            WHERE post_type = 'product' AND post_status = 'publish' 
            LIMIT %d OFFSET %d",
            $limit, $offset
        ));

        if ( empty( $wc_products ) ) {
            return 0;
        }

        $imported = 0;
        foreach ( $wc_products as $wc_product ) {
            $product_id = $wc_product->ID;
            
            // Get Meta
            $price = get_post_meta( $product_id, '_price', true );
            $sale_price = get_post_meta( $product_id, '_sale_price', true );
            $sku = get_post_meta( $product_id, '_sku', true );
            $stock = get_post_meta( $product_id, '_stock', true );
            
            // Get Thumbnail
            $thumb_id = get_post_meta( $product_id, '_thumbnail_id', true );
            $image_url = $thumb_id ? wp_get_attachment_url( $thumb_id ) : '';

            // Check if already exists by SKU or Slug
            $existing = $wpdb->get_var( $wpdb->prepare( 
                "SELECT id FROM {$wpdb->prefix}oww_products WHERE slug = %s OR (sku = %s AND sku IS NOT NULL AND sku != '')", 
                $wc_product->post_name, $sku 
            ));

            $data = [
                'title'       => $wc_product->post_title,
                'slug'        => $wc_product->post_name,
                'description' => $wc_product->post_content,
                'type'        => 'simple', // Basic migration only supports simple for now
                'status'      => 'publish',
                'price'       => $price ? (float) $price : 0,
                'sale_price'  => $sale_price ? (float) $sale_price : null,
                'sku'         => $sku,
                'stock_qty'   => $stock ? (int) $stock : 0,
                'image_url'   => $image_url,
            ];

            if ( $existing ) {
                $wpdb->update( $wpdb->prefix . 'oww_products', $data, [ 'id' => $existing ] );
                $oww_product_id = $existing;
            } else {
                $wpdb->insert( $wpdb->prefix . 'oww_products', $data );
                $oww_product_id = $wpdb->insert_id;
            }

            // Sync Categories relationships
            self::sync_product_categories( $product_id, $oww_product_id );

            $imported++;
        }

        return $imported;
    }

    private static function migrate_categories() {
        global $wpdb;

        $terms = $wpdb->get_results( "
            SELECT t.term_id, t.name, t.slug, tt.parent, tt.description 
            FROM {$wpdb->terms} t 
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
            WHERE tt.taxonomy = 'product_cat'
        " );

        foreach ( $terms as $term ) {
            // Check if exists
            $existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}oww_categories WHERE slug = %s", $term->slug ) );

            $data = [
                'name'        => $term->name,
                'slug'        => $term->slug,
                'description' => $term->description,
            ];

            if ( $existing ) {
                $wpdb->update( $wpdb->prefix . 'oww_categories', $data, [ 'id' => $existing ] );
            } else {
                $wpdb->insert( $wpdb->prefix . 'oww_categories', $data );
            }
        }
    }

    private static function sync_product_categories( $wc_id, $oww_id ) {
        global $wpdb;

        $terms = wp_get_post_terms( $wc_id, 'product_cat' );
        if ( is_wp_error( $terms ) || empty( $terms ) ) return;

        // Clear existing
        $wpdb->delete( $wpdb->prefix . 'oww_product_category_rel', [ 'product_id' => $oww_id ] );

        foreach ( $terms as $term ) {
            $cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}oww_categories WHERE slug = %s", $term->slug ) );
            if ( $cat_id ) {
                $wpdb->insert( $wpdb->prefix . 'oww_product_category_rel', [
                    'product_id'  => $oww_id,
                    'category_id' => $cat_id
                ]);
            }
        }
    }
}
