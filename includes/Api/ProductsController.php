<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use OwwCommerce\Core\Container;
use OwwCommerce\Repositories\ProductRepository;
use OwwCommerce\Models\Product;

class ProductsController extends WP_REST_Controller {

    private ProductRepository $repository;

    public function __construct( Container $container ) {
        $this->namespace  = 'owwc/v1';
        $this->rest_base  = 'products';
        // Injeksi/Inisialisasi repositori
        $this->repository = new ProductRepository();
        
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'get_items_permissions_check' ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_item' ],
                'permission_callback' => [ $this, 'create_item_permissions_check' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_item' ],
                'permission_callback' => [ $this, 'delete_item_permissions_check' ],
            ],
        ] );
    }

    public function get_items_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    public function create_item_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    public function delete_item_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    public function get_items( $request ) {
        $products = $this->repository->get_all();
        $formatted = array_map( fn($p) => $p->to_array(), $products );
        return rest_ensure_response( $formatted );
    }

    public function create_item( $request ) {
        $data = $request->get_json_params() ?: $request->get_params();

        // Validasi dasar
        if ( empty( $data['title'] ) ) {
            return new WP_Error( 'rest_missing_title', 'Product title is required.', [ 'status' => 400 ] );
        }

        $product = new Product( [
            'title'       => sanitize_text_field( $data['title'] ),
            'slug'        => sanitize_title( $data['title'] ),
            'description' => sanitize_textarea_field( $data['description'] ?? '' ),
            'price'       => floatval( $data['price'] ?? 0 ),
            'sale_price'  => isset( $data['sale_price'] ) ? floatval( $data['sale_price'] ) : null,
            'sku'         => sanitize_text_field( $data['sku'] ?? '' ),
            'stock_qty'   => intval( $data['stock_qty'] ?? 0 )
        ] );

        $saved_product = $this->repository->save( $product );

        return rest_ensure_response( $saved_product->to_array() );
    }

    public function delete_item( $request ) {
        $id = (int) $request['id'];
        $deleted = $this->repository->delete( $id );

        if ( ! $deleted ) {
            return new WP_Error( 'rest_cannot_delete', 'The product cannot be deleted.', [ 'status' => 500 ] );
        }

        return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
    }
}
