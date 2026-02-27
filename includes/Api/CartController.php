<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use OwwCommerce\Core\Container;
use OwwCommerce\Checkout\Cart;
use OwwCommerce\Repositories\ProductRepository;

class CartController extends WP_REST_Controller {

    private Cart $cart;
    private ProductRepository $product_repo;

    public function __construct( Container $container ) {
        $this->namespace = 'owwc/v1';
        $this->rest_base = 'cart';
        $this->cart = new Cart();
        $this->product_repo = new ProductRepository();

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_cart' ],
                'permission_callback' => '__return_true', // Public
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'add_to_cart' ],
                'permission_callback' => '__return_true', // Public
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'clear_cart' ],
                'permission_callback' => '__return_true', // Public
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'remove_from_cart' ],
                'permission_callback' => '__return_true', // Public
            ],
        ] );
    }

    public function get_cart( $request ) {
        $cart_items = $this->cart->get_items();
        $enriched_items = [];
        $total_amount = 0;

        foreach ( $cart_items as $key => $item ) {
            $product = $this->product_repo->find( $item['product_id'] );
            if ( $product ) {
                $price = $product->sale_price ?? $product->price;
                $title = $product->title;

                // Jika ada variasi, ambil harganya dari variasi (jika ada)
                if ( ! empty( $item['variation_id'] ) ) {
                    foreach ( $product->variations as $v ) {
                        if ( $v->id == $item['variation_id'] ) {
                            $price = $v->sale_price ?: $v->price;
                            $attr_desc = implode( ', ', array_values( $v->attributes ) );
                            $title .= " ({$attr_desc})";
                            break;
                        }
                    }
                }

                $line_total = $price * $item['qty'];
                $total_amount += $line_total;

                $enriched_items[] = [
                    'key'          => $key,
                    'product_id'   => $product->id,
                    'variation_id' => $item['variation_id'] ?? 0,
                    'title'        => $title,
                    'price'        => $price,
                    'qty'          => $item['qty'],
                    'line_total'   => $line_total,
                ];
            }
        }

        return rest_ensure_response( [
            'items' => $enriched_items,
            'count' => $this->cart->get_count(),
            'total' => $total_amount,
        ] );
    }

    public function add_to_cart( $request ) {
        $data = $request->get_json_params() ?: $request->get_params();

        if ( empty( $data['product_id'] ) ) {
            return new WP_Error( 'invalid_product', 'Product ID is required.', [ 'status' => 400 ] );
        }

        $product_id   = (int) $data['product_id'];
        $variation_id = isset( $data['variation_id'] ) ? (int) $data['variation_id'] : 0;
        $qty          = isset( $data['qty'] ) ? (int) $data['qty'] : 1;

        // Validasi produk
        $product = $this->product_repo->find( $product_id );
        if ( ! $product ) {
            return new WP_Error( 'not_found', 'Product not found.', [ 'status' => 404 ] );
        }

        $this->cart->add_item( $product_id, $qty, $variation_id );

        return $this->get_cart( $request );
    }

    public function remove_from_cart( $request ) {
        $key = $request['id']; // Bisa berupa string "1_2" atau numeric "1"
        $this->cart->remove_item( $key );
        return $this->get_cart( $request );
    }

    public function clear_cart( $request ) {
        $this->cart->clear();
        return $this->get_cart( $request );
    }
}
