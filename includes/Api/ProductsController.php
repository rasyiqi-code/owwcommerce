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
                'args'                => [
                    'title' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'price' => [
                        'type'              => 'number',
                        'sanitize_callback' => 'floatval',
                    ],
                    'sku' => [
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'stock_qty' => [
                        'type'              => 'integer',
                        'sanitize_callback' => 'intval',
                    ],
                ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_item' ],
                'permission_callback' => [ $this, 'get_items_permissions_check' ],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE, // POST, PUT, PATCH
                'callback'            => [ $this, 'update_item' ],
                'permission_callback' => [ $this, 'update_item_permissions_check' ],
            ],
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

    public function update_item_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    public function delete_item_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    public function get_items( $request ) {
        $page     = (int) $request->get_param( 'page' ) ?: 1;
        $per_page = (int) $request->get_param( 'per_page' ) ?: 10;
        $offset   = ( $page - 1 ) * $per_page;

        $filters = [
            's'       => $request->get_param( 's' ),
            'orderby' => $request->get_param( 'orderby' ),
        ];

        $products    = $this->repository->get_all( $per_page, $offset, $filters );
        $total_items = $this->repository->count();
        $total_pages = ceil( $total_items / $per_page );

        $formatted = array_map( fn($p) => $p->to_array(), $products );

        return rest_ensure_response( [
            'items'        => $formatted,
            'total_items'  => $total_items,
            'total_pages'  => (int) $total_pages,
            'current_page' => $page,
            'per_page'     => $per_page,
        ] );
    }

    public function get_item( $request ) {
        $id = (int) $request['id'];
        $product = $this->repository->find( $id );

        if ( ! $product ) {
            return new WP_Error( 'rest_product_not_found', 'Product not found.', [ 'status' => 404 ] );
        }

        return rest_ensure_response( $product->to_array() );
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
            'type'        => sanitize_text_field( $data['type'] ?? 'simple' ),
            'status'      => sanitize_text_field( $data['status'] ?? 'publish' ),
            'price'       => floatval( $data['price'] ?? 0 ),
            'sale_price'  => isset( $data['sale_price'] ) && $data['sale_price'] !== '' ? floatval( $data['sale_price'] ) : null,
            'sku'         => sanitize_text_field( $data['sku'] ?? '' ),
            'stock_qty'   => intval( $data['stock_qty'] ?? 0 ),
            'image_url'   => esc_url_raw( $data['image_url'] ?? '' ) ?: null,
            'gallery_ids' => isset( $data['gallery_ids'] ) ? array_map( 'intval', (array) $data['gallery_ids'] ) : [],
        ] );

        // Handle variations if type is variable
        if ( $product->type === 'variable' && ! empty( $data['variations'] ) && is_array( $data['variations'] ) ) {
            foreach ( $data['variations'] as $v ) {
                $product->variations[] = new \OwwCommerce\Models\ProductVariation( (array) $v );
            }
        }

        $saved_product = $this->repository->save( $product );

        return rest_ensure_response( $saved_product->to_array() );
    }

    public function update_item( $request ) {
        $id = (int) $request['id'];
        $product = $this->repository->find( $id );

        if ( ! $product ) {
            return new WP_Error( 'rest_product_not_found', 'Product not found.', [ 'status' => 404 ] );
        }

        $data = $request->get_json_params() ?: $request->get_params();

        // Update fields jika dikirim
        if ( isset( $data['title'] ) ) {
            $product->title = sanitize_text_field( $data['title'] );
            $product->slug  = sanitize_title( $data['title'] );
        }
        if ( isset( $data['description'] ) ) $product->description = sanitize_textarea_field( $data['description'] );
        if ( isset( $data['type'] ) )        $product->type        = sanitize_text_field( $data['type'] );
        if ( isset( $data['status'] ) )      $product->status      = sanitize_text_field( $data['status'] );
        if ( isset( $data['price'] ) )       $product->price       = floatval( $data['price'] );
        if ( isset( $data['sale_price'] ) )  $product->sale_price  = ( $data['sale_price'] !== '' ) ? floatval( $data['sale_price'] ) : null;
        if ( isset( $data['sku'] ) )         $product->sku         = sanitize_text_field( $data['sku'] );
        if ( isset( $data['stock_qty'] ) )   $product->stock_qty   = intval( $data['stock_qty'] );
        if ( isset( $data['image_url'] ) )   $product->image_url   = esc_url_raw( $data['image_url'] ) ?: null;
        if ( isset( $data['gallery_ids'] ) ) $product->gallery_ids = array_map( 'intval', (array) $data['gallery_ids'] );

        if ( $product->type === 'variable' && isset( $data['variations'] ) && is_array( $data['variations'] ) ) {
            $product->variations = [];
            foreach ( $data['variations'] as $v ) {
                $product->variations[] = new \OwwCommerce\Models\ProductVariation( (array) $v );
            }
        }

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
