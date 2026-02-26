<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use OwwCommerce\Core\Container;
use OwwCommerce\Checkout\Cart;
use OwwCommerce\Models\Order;
use OwwCommerce\Models\OrderItem;
use OwwCommerce\Repositories\OrderRepository;
use OwwCommerce\Repositories\ProductRepository;

class CheckoutController extends WP_REST_Controller {

    private Cart $cart;
    private OrderRepository $order_repo;
    private ProductRepository $product_repo;
    private Container $container;

    public function __construct( Container $container ) {
        $this->container = $container;
        $this->namespace = 'owwc/v1';
        $this->rest_base = 'checkout';
        $this->cart = new Cart();
        $this->order_repo = new OrderRepository();
        $this->product_repo = new ProductRepository();

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'process_checkout' ],
                'permission_callback' => '__return_true', // Public endpoint untuk Guest & Logged in
            ],
        ] );
    }

    public function process_checkout( $request ) {
        $data = $request->get_json_params() ?: $request->get_params();
        $cart_items = $this->cart->get_items();

        if ( empty( $cart_items ) ) {
            return new WP_Error( 'empty_cart', 'Cannot process checkout with an empty cart.', [ 'status' => 400 ] );
        }

        // Hitung ulang total untuk memastikan validitas (anti-tamper)
        $total_amount = 0;
        $order_items = [];

        foreach ( $cart_items as $item ) {
            $product = $this->product_repo->find( $item['product_id'] );
            if ( ! $product ) {
                continue;
            }

            $price = $product->sale_price ?? $product->price;
            $line_total = $price * $item['qty'];
            $total_amount += $line_total;

            $order_item = new OrderItem();
            $order_item->product_id  = $product->id;
            $order_item->qty         = $item['qty'];
            $order_item->unit_price  = $price;
            $order_item->total_price = $line_total;

            $order_items[] = $order_item;
        }

        if ( empty( $order_items ) ) {
            return new WP_Error( 'invalid_items', 'All products in cart are invalid or out of stock.', [ 'status' => 400 ] );
        }

        // Siapkan entitas Order
        $order = new Order();
        $order->customer_id     = get_current_user_id() ?: 0; // 0 for guest (idealnya ada repo customernya)
        $order->status          = 'pending'; // Default awalnya pending
        
        $shipping_id = sanitize_text_field( $data['shipping_method'] ?? '' );
        $payment_id  = sanitize_text_field( $data['payment_method'] ?? '' );

        // Hitung biaya pengiriman
        $shipping_methods = $this->container->get( 'shipping_methods' );
        $shipping_cost = 0;
        if ( isset( $shipping_methods[ $shipping_id ] ) ) {
            $shipping_cost = $shipping_methods[ $shipping_id ]->calculate_shipping( $cart_items );
        }

        $total_amount += $shipping_cost;
        
        $order->total_amount    = $total_amount;
        $order->payment_method  = $payment_id;
        $order->shipping_method = $shipping_id;
        $order->items           = $order_items;

        try {
            $saved_order = $this->order_repo->create( $order );
            
            // Eksekusi gateway pembayaran jika pesanan berhasil dibuat
            $payment_gateways = $this->container->get( 'payment_gateways' );
            $payment_result = [ 'success' => true, 'redirect_url' => '', 'message' => 'Pesanan berhasil dibuat.' ];
            
            if ( isset( $payment_gateways[ $payment_id ] ) ) {
                $gateway = $payment_gateways[ $payment_id ];
                $payment_result = $gateway->process_payment( $saved_order->id );
                
                if ( ! $payment_result['success'] ) {
                    // Gateway menolak proses (misal validasi kartu gagal)
                    // Anda mungkin ingin menghapus order yang telanjur terbuat atau set ke failed.
                    return new WP_Error( 'payment_failed', $payment_result['message'] ?? 'Pembayaran gagal.', [ 'status' => 400 ] );
                }
            }

            // Bersihkan keranjang belanja setelah konversi dan bayar sukses
            $this->cart->clear();

            return rest_ensure_response( [
                'success'  => true,
                'order_id' => $saved_order->id,
                'message'  => $payment_result['message'] ?? 'Order placed successfully.',
                'order'    => array_merge( $saved_order->to_array(), [ 'redirect_url' => $payment_result['redirect_url'] ?? '' ] )
            ] );

        } catch ( \Exception $e ) {
            return new WP_Error( 'checkout_failed', 'Failed to save order: ' . $e->getMessage(), [ 'status' => 500 ] );
        }
    }
}
