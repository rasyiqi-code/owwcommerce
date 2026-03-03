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

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/public', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_public_items' ],
                'permission_callback' => '__return_true',
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

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)/recommendations', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_recommendations' ],
                'permission_callback' => '__return_true',
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/bulk-update-stock', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'bulk_update_stock' ],
                'permission_callback' => [ $this, 'update_item_permissions_check' ],
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

    public function get_public_items( $request ) {
        $page     = (int) $request->get_param( 'page' ) ?: 1;
        $per_page = (int) $request->get_param( 'per_page' ) ?: 10;
        $offset   = ( $page - 1 ) * $per_page;

        $filters = [
            's'        => $request->get_param( 'q' ), // Gunakan 'q' untuk konsistensi dengan shop search
            'category' => $request->get_param( 'category' ),
            'orderby'  => $request->get_param( 'orderby' ),
        ];

        $products    = $this->repository->get_all( $per_page, $offset, $filters );
        $total_items = $this->repository->count(); // Idealnya count dengan filter, tapi get_all kita cukup fleksibel
        
        // Render HTML card untuk setiap produk (Server Side Rendering for AJAX)
        $html = '';
        foreach ( $products as $product ) {
            ob_start();
            include OWWCOMMERCE_PLUGIN_DIR . 'templates/frontend/parts/product-card.php';
            $html .= ob_get_clean();
        }

        return rest_ensure_response( [
            'html'         => $html,
            'total_items'  => $total_items,
            'has_more'     => ( $offset + count( $products ) ) < $total_items,
            'received'     => count( $products ),
        ] );
    }

    public function get_items( $request ) {
        $page     = (int) $request->get_param( 'page' ) ?: 1;
        $per_page = (int) $request->get_param( 'per_page' ) ?: 10;
        $offset   = ( $page - 1 ) * $per_page;

        $filters = [
            's'       => $request->get_param( 's' ),
            'orderby' => $request->get_param( 'orderby' ),
        ];

        try {
            $products    = $this->repository->get_all( $per_page, $offset, $filters );
            $total_items = $this->repository->count();
            $total_pages = ceil( $total_items / $per_page );

            $formatted = array_map( fn($p) => $p->to_array(), (array) $products );

            return rest_ensure_response( [
                'items'        => $formatted,
                'total_items'  => $total_items,
                'total_pages'  => (int) $total_pages,
                'current_page' => $page,
                'per_page'     => $per_page,
            ] );
        } catch (\Throwable $e) {
            error_log( "OwwCommerce REST Error: " . $e->getMessage() );
            return new WP_Error( 'rest_internal_error', $e->getMessage(), [ 'status' => 500 ] );
        }
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
            'created_by'  => (int) get_current_user_id(),
            'upsell_ids'  => sanitize_text_field( $data['upsell_ids'] ?? '' ) ?: null,
            'cross_sell_ids' => sanitize_text_field( $data['cross_sell_ids'] ?? '' ) ?: null,
            'checkout_url' => esc_url_raw( $data['checkout_url'] ?? '' ) ?: null,
            'whatsapp_url' => sanitize_text_field( $data['whatsapp_url'] ?? '' ) ?: null,
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
        if ( isset( $data['upsell_ids'] ) )  $product->upsell_ids  = sanitize_text_field( $data['upsell_ids'] ) ?: null;
        if ( isset( $data['cross_sell_ids'] ) ) $product->cross_sell_ids = sanitize_text_field( $data['cross_sell_ids'] ) ?: null;
        if ( isset( $data['checkout_url'] ) ) $product->checkout_url = esc_url_raw( $data['checkout_url'] ) ?: null;
        if ( isset( $data['whatsapp_url'] ) ) $product->whatsapp_url = sanitize_text_field( $data['whatsapp_url'] ) ?: null;

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

    public function bulk_update_stock( $request ) {
        $data = $request->get_json_params();

        if ( ! is_array( $data ) ) {
            return new WP_Error( 'rest_invalid_data', 'Expected an array of objects.', [ 'status' => 400 ] );
        }

        $updated_count = 0;
        foreach ( $data as $item ) {
            $id    = (int) ( $item['id'] ?? 0 );
            $stock = (int) ( $item['stock'] ?? 0 );

            if ( $id <= 0 ) continue;

            $product = $this->repository->find( $id );
            if ( $product ) {
                error_log( "OwwCommerce: Updating stock for product $id to $stock" );
                $product->stock_qty = $stock; 
                $this->repository->save( $product );
                $updated_count++;
            }
        }

        return rest_ensure_response( [
            'success' => true,
            'updated' => $updated_count,
        ] );
    }

    public function get_recommendations( $request ) {
        $id = (int) $request['id'];
        $product = $this->repository->find( $id );

        if ( ! $product ) {
            return new WP_Error( 'rest_not_found', 'Product not found.', [ 'status' => 404 ] );
        }

        // 1. Ambil Upsells
        $upsells = [];
        if ( ! empty( $product->upsell_ids ) ) {
            $ids = array_map( 'intval', explode( ',', $product->upsell_ids ) );
            foreach ( $ids as $u_id ) {
                $p = $this->repository->find( $u_id );
                if ( $p ) $upsells[] = $p->to_array();
            }
        }
        
        // Fallback ke Algoritma jika manual masih kurang
        if ( count( $upsells ) < 4 ) {
            $auto_upsells = $this->repository->get_related_products( $id, 'upsell', 4 - count( $upsells ) );
            foreach ( $auto_upsells as $p ) {
                $upsells[] = $p->to_array();
            }
        }

        // 2. Ambil Cross-sells
        $cross_sells = [];
        if ( ! empty( $product->cross_sell_ids ) ) {
            $ids = array_map( 'intval', explode( ',', $product->cross_sell_ids ) );
            foreach ( $ids as $c_id ) {
                $p = $this->repository->find( $c_id );
                if ( $p ) $cross_sells[] = $p->to_array();
            }
        }

        if ( count( $cross_sells ) < 4 ) {
            $auto_cross = $this->repository->get_related_products( $id, 'cross-sell', 4 - count( $cross_sells ) );
            foreach ( $auto_cross as $p ) {
                $cross_sells[] = $p->to_array();
            }
        }

        return rest_ensure_response( [
            'upsells'    => $upsells,
            'cross_sells' => $cross_sells,
        ] );
    }
}
