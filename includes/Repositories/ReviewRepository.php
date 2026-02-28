<?php
namespace OwwCommerce\Repositories;

use OwwCommerce\Models\Review;

/**
 * Review Repository Class
 */
class ReviewRepository {
    private string $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'oww_reviews';
    }

    /**
     * Get reviews by product ID
     */
    public function get_by_product( int $product_id, string $status = 'approved' ): array {
        global $wpdb;
        $items = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE product_id = %d AND status = %s ORDER BY created_at DESC",
            $product_id,
            $status
        ), ARRAY_A );

        if ( ! is_array( $items ) ) {
            return [];
        }

        return array_map( function( $data ) {
            return new \OwwCommerce\Models\Review( $data );
        }, $items );
    }

    /**
     * Save a new review
     */
    public function save( Review $review ): int|bool {
        global $wpdb;

        $data = [
            'product_id'   => $review->product_id,
            'customer_id'  => $review->customer_id,
            'rating'       => $review->rating,
            'comment'      => $review->comment,
            'author_name'  => $review->author_name,
            'author_email' => $review->author_email,
            'status'       => $review->status,
        ];

        $format = [ '%d', '%d', '%d', '%s', '%s', '%s', '%s' ];

        if ( $review->id > 0 ) {
            $updated = $wpdb->update( $this->table_name, $data, [ 'id' => $review->id ], $format, [ '%d' ] );
            return $updated !== false ? $review->id : false;
        }

        $inserted = $wpdb->insert( $this->table_name, $data, $format );
        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Get average rating for a product
     */
    public function get_average_rating( int $product_id ): float {
        global $wpdb;
        $average = $wpdb->get_var( $wpdb->prepare(
            "SELECT AVG(rating) FROM {$this->table_name} WHERE product_id = %d AND status = 'approved'",
            $product_id
        ) );

        return $average ? round( (float) $average, 1 ) : 0.0;
    }

    /**
     * Get all reviews (pagination can be added later)
     */
    public function get_all(): array {
        global $wpdb;
        $items = $wpdb->get_results( "SELECT * FROM {$this->table_name} ORDER BY created_at DESC", ARRAY_A );

        if ( ! is_array( $items ) ) {
            return [];
        }

        return array_map( function( $data ) {
            return new \OwwCommerce\Models\Review( $data );
        }, $items );
    }

    /**
     * Delete a review
     */
    public function delete( int $id ): bool {
        global $wpdb;
        $deleted = $wpdb->delete( $this->table_name, [ 'id' => $id ], [ '%d' ] );
        return $deleted !== false;
    }

    /**
     * Get review count for a product
     */
    public function get_review_count( int $product_id ): int {
        global $wpdb;
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(id) FROM {$this->table_name} WHERE product_id = %d AND status = 'approved'",
            $product_id
        ) );

        return (int) $count;
    }
}
