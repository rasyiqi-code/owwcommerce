<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Server;
use OwwCommerce\Core\Container;
use OwwCommerce\Repositories\OrderRepository;
use OwwCommerce\Repositories\ProductRepository;

/**
 * Class AnalyticsController
 * REST API untuk data analitik dashboard admin.
 */
class AnalyticsController extends WP_REST_Controller {

    private OrderRepository $order_repo;
    private ProductRepository $product_repo;

    public function __construct( Container $container ) {
        $this->namespace    = 'owwc/v1';
        $this->rest_base    = 'analytics';
        $this->order_repo   = new OrderRepository();
        $this->product_repo = new ProductRepository();

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/summary', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_summary' ],
                'permission_callback' => [ $this, 'admin_check' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/sales', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_sales_chart' ],
                'permission_callback' => [ $this, 'admin_check' ],
            ],
        ] );
    }

    public function admin_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Mendapatkan ringkasan statistik (Cards).
     */
    public function get_summary() {
        return rest_ensure_response( [
            'total_revenue'  => $this->order_repo->get_total_revenue(),
            'total_orders'   => $this->order_repo->count(),
            'total_products' => $this->product_repo->count(),
            'top_products'   => $this->order_repo->get_top_products( 5 ),
        ] );
    }

    /**
     * Mendapatkan data untuk grafik penjualan.
     */
    public function get_sales_chart( $request ) {
        $days = (int) $request->get_param( 'days' ) ?: 7;
        $data = $this->order_repo->get_sales_by_date( $days );

        return rest_ensure_response( $data );
    }
}
