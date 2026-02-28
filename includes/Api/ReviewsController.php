<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;
use OwwCommerce\Models\Review;
use OwwCommerce\Repositories\ReviewRepository;

/**
 * Reviews API Controller
 */
class ReviewsController extends WP_REST_Controller {
    private ReviewRepository $repository;

    public function __construct() {
        $this->namespace = 'owwc/v1';
        $this->rest_base = 'products';
        try {
            $this->repository = new ReviewRepository();
        } catch ( \Throwable $e ) {
            error_log( 'OwwCommerce Debug: Failed to init ReviewRepository: ' . $e->getMessage() );
        }

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // reviews for a specific product
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/reviews', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_reviews' ],
                'permission_callback' => '__return_true',
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_review' ],
                'permission_callback' => [ $this, 'create_review_permissions_check' ],
            ],
        ]);

        // global reviews (admin)
        register_rest_route( $this->namespace, '/reviews', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_all_reviews' ],
                'permission_callback' => [ $this, 'admin_permissions_check' ],
            ],
        ]);

        register_rest_route( $this->namespace, '/reviews/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_review' ],
                'permission_callback' => [ $this, 'admin_permissions_check' ],
            ],
        ]);

        register_rest_route( $this->namespace, '/reviews/(?P<id>[\d]+)/approve', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'approve_review' ],
                'permission_callback' => [ $this, 'admin_permissions_check' ],
            ],
        ]);
    }

    /**
     * Admin permissions check
     */
    public function admin_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * Get all reviews (Admin)
     */
    public function get_all_reviews( $request ) {
        $reviews = $this->repository->get_all();
        $formatted = [];
        
        foreach ( $reviews as $r ) {
            $data = $r->to_array();
            // Get product title
            $product_repo = new \OwwCommerce\Repositories\ProductRepository();
            $product = $product_repo->find( $r->product_id );
            $data['product_title'] = $product ? $product->title : 'Unknown Product';
            $formatted[] = $data;
        }

        return new WP_REST_Response( $formatted, 200 );
    }

    /**
     * Delete a review
     */
    public function delete_review( $request ) {
        $id = (int) $request['id'];
        $deleted = $this->repository->delete( $id );
        
        if ( ! $deleted ) {
            return new WP_Error( 'delete_failed', 'Gagal menghapus ulasan.', [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [ 'success' => true ], 200 );
    }

    /**
     * Approve a review
     */
    public function approve_review( $request ) {
        $id = (int) $request['id'];
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'oww_reviews';
        $updated = $wpdb->update( $table_name, [ 'status' => 'approved' ], [ 'id' => $id ], [ '%s' ], [ '%d' ] );

        if ( $updated === false ) {
            return new WP_Error( 'approve_failed', 'Gagal menyetujui ulasan.', [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [ 'success' => true ], 200 );
    }

    /**
     * Get reviews for a product
     */
    public function get_reviews( $request ) {
        $product_id = (int) $request['id'];
        
        try {
            $reviews = $this->repository->get_by_product( $product_id );
        } catch ( \Throwable $e ) {
            return new WP_Error( 'db_error', $e->getMessage(), [ 'status' => 500 ] );
        }

        $items = [];
        if ( is_array( $reviews ) ) {
            foreach ( $reviews as $r ) {
                if ( $r instanceof Review ) {
                    $items[] = $r->to_array();
                }
            }
        }

        return new WP_REST_Response([
            'items'          => $items,
            'average_rating' => (float) $this->repository->get_average_rating( $product_id ),
            'review_count'   => (int) $this->repository->get_review_count( $product_id )
        ], 200);
    }

    /**
     * Create review permissions check
     */
    public function create_review_permissions_check( $request ) {
        // Allow anyone to submit reviews, but we can restrict to logged in users if needed
        // For premium feel, let's allow everyone but provide fields for name/email if not logged in
        return true;
    }

    /**
     * Create a new review
     */
    public function create_review( $request ) {
        $product_id = (int) $request['id'];
        $params     = $request->get_json_params();

        if ( empty( $params['rating'] ) || empty( $params['comment'] ) ) {
            return new WP_Error( 'missing_fields', 'Rating dan komentar wajib diisi.', [ 'status' => 400 ] );
        }

        $review_data = [
            'product_id'   => $product_id,
            'customer_id'  => get_current_user_id() ?: null,
            'rating'       => (int) $params['rating'],
            'comment'      => sanitize_textarea_field( $params['comment'] ),
            'author_name'  => sanitize_text_field( $params['author_name'] ?? '' ),
            'author_email' => sanitize_email( $params['author_email'] ?? '' ),
            'status'       => 'pending', // Reviews need approval now
            'created_at'   => current_time('mysql'),
        ];

        // If logged in, get name/email from user data
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $review_data['author_name']  = $user->display_name;
            $review_data['author_email'] = $user->user_email;
        }

        $review = new Review( $review_data );
        $id = $this->repository->save( $review );

        if ( ! $id ) {
            return new WP_Error( 'save_failed', 'Gagal menyimpan ulasan.', [ 'status' => 500 ] );
        }

        $review->id = $id;
        return new WP_REST_Response( $review->to_array(), 201 );
    }
}
