<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use OwwCommerce\Core\Container;
use OwwCommerce\Repositories\OrderRepository;
use OwwCommerce\Repositories\ProductRepository;
use OwwCommerce\Emails\EmailSender;

/**
 * Class OrderController
 *
 * REST API controller untuk manajemen pesanan di Admin.
 * Endpoint:
 *   GET  /owwc/v1/orders         — daftar pesanan
 *   GET  /owwc/v1/orders/{id}    — detail pesanan (termasuk items + nama produk)
 *   PATCH /owwc/v1/orders/{id}/status — update status pesanan
 */
class OrderController extends WP_REST_Controller {

    private OrderRepository $order_repo;
    private ProductRepository $product_repo;

    public function __construct( Container $container ) {
        $this->namespace    = 'owwc/v1';
        $this->rest_base    = 'orders';
        $this->order_repo   = new OrderRepository();
        $this->product_repo = new ProductRepository();

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        // GET /owwc/v1/orders — daftar pesanan
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'admin_permissions_check' ],
            ],
        ] );

        // GET /owwc/v1/orders/{id} — detail pesanan
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_item' ],
                'permission_callback' => 'is_user_logged_in',
            ],
        ] );

        // PATCH /owwc/v1/orders/{id}/status — update status
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/status', [
            [
                'methods'             => 'PATCH',
                'callback'            => [ $this, 'update_status' ],
                'permission_callback' => [ $this, 'admin_permissions_check' ],
                'args'                => [
                    'status' => [
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => function( $param ) {
                            return in_array( $param, [ 'pending', 'processing', 'on-hold', 'awaiting-confirmation', 'completed', 'cancelled', 'refunded', 'failed' ], true );
                        },
                    ],
                ],
            ],
        ] );
    }

    /**
     * Hanya admin yang boleh mengakses endpoint orders.
     */
    public function admin_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * GET /owwc/v1/orders — daftar semua pesanan.
     */
    public function get_items( $request ) {
        $limit  = (int) ( $request->get_param( 'limit' ) ?? 50 );
        $offset = (int) ( $request->get_param( 'offset' ) ?? 0 );

        $orders = $this->order_repo->get_all( $limit, $offset );
        $total  = $this->order_repo->count();

        $formatted = array_map( fn( $o ) => $o->to_array(), $orders );

        return rest_ensure_response( [
            'data'  => $formatted,
            'total' => $total,
        ] );
    }

    /**
     * GET /owwc/v1/orders/{id} — detail pesanan lengkap.
     * Menyertakan nama produk pada setiap item.
     */
    public function get_item( $request ) {
        $id    = (int) $request['id'];
        $order = $this->order_repo->find( $id );

        if ( ! $order ) {
            return new WP_Error( 'not_found', 'Pesanan tidak ditemukan.', [ 'status' => 404 ] );
        }

        // Permission check: Admin OR Owner of the order
        if ( ! current_user_can( 'manage_options' ) && (int) $order->customer_id !== get_current_user_id() ) {
            return new WP_Error( 'forbidden', 'Anda tidak diizinkan melihat pesanan ini.', [ 'status' => 403 ] );
        }

        $order_data = $order->to_array();

        // Enrich items dengan nama produk
        foreach ( $order_data['items'] as &$item ) {
            $product = $this->product_repo->find( $item['product_id'] );
            $item['product_title'] = $product ? $product->title : '(Produk dihapus)';
            $item['product_image'] = $product ? $product->image_url : null;
        }

        return rest_ensure_response( $order_data );
    }

    /**
     * PATCH /owwc/v1/orders/{id}/status — update status pesanan.
     */
    public function update_status( $request ) {
        $id     = (int) $request['id'];
        $params = $request->get_json_params() ?: $request->get_params();
        $status = sanitize_text_field( $params['status'] ?? '' );

        // Validasi status yang diperbolehkan
        $allowed = [ 'pending', 'processing', 'on-hold', 'awaiting-confirmation', 'completed', 'cancelled', 'refunded', 'failed' ];
        if ( ! in_array( $status, $allowed, true ) ) {
            return new WP_Error( 'invalid_status', 'Status tidak valid. Pilih: ' . implode( ', ', $allowed ), [ 'status' => 400 ] );
        }

        $updated = $this->order_repo->update_status( $id, $status );

        if ( ! $updated ) {
            return new WP_Error( 'update_failed', 'Gagal memperbarui status pesanan.', [ 'status' => 500 ] );
        }

        // Kirim Notifikasi Email
        $order = $this->order_repo->find( $id );
        if ( $order ) {
            $email_sender = new EmailSender();
            $email_sender->send_status_update( $order );
        }

        return rest_ensure_response( [
            'success' => true,
            'id'      => $id,
            'status'  => $status,
        ] );
    }
}
