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
use OwwCommerce\Repositories\CustomerRepository;
use OwwCommerce\Repositories\CouponRepository;
use OwwCommerce\Emails\EmailSender;

/**
 * Class CheckoutController
 *
 * Memproses checkout: validasi stok, simpan customer, buat order,
 * kurangi stok, proses payment gateway.
 */
class CheckoutController extends WP_REST_Controller {

    private Cart $cart;
    private OrderRepository $order_repo;
    private ProductRepository $product_repo;
    private CustomerRepository $customer_repo;
    private Container $container;

    public function __construct( Container $container ) {
        $this->container     = $container;
        $this->namespace     = 'owwc/v1';
        $this->rest_base     = 'checkout';
        $this->cart          = new Cart();
        $this->order_repo    = new OrderRepository();
        $this->product_repo  = new ProductRepository();
        $this->customer_repo = new CustomerRepository();

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'process_checkout' ],
                'permission_callback' => '__return_true',
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/confirm', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'confirm_payment' ],
                'permission_callback' => '__return_true',
            ],
        ] );
    }

    /**
     * Proses checkout lengkap:
     * 1. Validasi keranjang & stok
     * 2. Simpan/update data customer
     * 3. Buat order + alamat
     * 4. Kurangi stok produk
     * 5. Proses payment gateway
     * 6. Bersihkan cart
     */
    public function process_checkout( $request ) {
        $data       = $request->get_json_params() ?: $request->get_params();
        $cart_items = $this->cart->get_items();

        if ( empty( $cart_items ) ) {
            return new WP_Error( 'empty_cart', 'Tidak bisa checkout dengan keranjang kosong.', [ 'status' => 400 ] );
        }

        // === STEP 1: Validasi stok + hitung ulang total (anti-tamper) ===
        $total_amount = 0;
        $order_items  = [];
        $stock_errors = [];

        foreach ( $cart_items as $item ) {
            $product = $this->product_repo->find( $item['product_id'] );
            if ( ! $product ) {
                continue;
            }

            // Validasi stok cukup
            if ( $product->stock_qty < $item['qty'] ) {
                $stock_errors[] = sprintf(
                    '%s (stok tersisa: %d, diminta: %d)',
                    $product->title,
                    $product->stock_qty,
                    $item['qty']
                );
                continue;
            }

            $price      = $product->sale_price ?? $product->price;
            $line_total = $price * $item['qty'];
            $total_amount += $line_total;

            $order_item              = new OrderItem();
            $order_item->product_id  = $product->id;
            $order_item->qty         = $item['qty'];
            $order_item->unit_price  = $price;
            $order_item->total_price = $line_total;

            $order_items[] = $order_item;
        }

        // Jika ada masalah stok, tolak checkout
        if ( ! empty( $stock_errors ) ) {
            return new WP_Error(
                'insufficient_stock',
                'Stok tidak mencukupi untuk: ' . implode( '; ', $stock_errors ),
                [ 'status' => 400, 'stock_errors' => $stock_errors ]
            );
        }

        if ( empty( $order_items ) ) {
            return new WP_Error( 'invalid_items', 'Semua produk di keranjang tidak valid.', [ 'status' => 400 ] );
        }

        // === STEP 2: Simpan/update customer ===
        $customer_email = sanitize_email( $data['email'] ?? '' );
        $customer_data  = [
            'first_name' => sanitize_text_field( $data['first_name'] ?? '' ),
            'last_name'  => sanitize_text_field( $data['last_name'] ?? '' ),
            'email'      => $customer_email,
            'phone'      => sanitize_text_field( $data['phone'] ?? '' ),
            'wp_user_id' => get_current_user_id() ?: null,
        ];

        // Cek apakah customer sudah ada (berdasarkan email)
        $existing_customer = $this->customer_repo->find_by_email( $customer_email );
        $customer_id       = 0;

        if ( $existing_customer ) {
            $customer_id = (int) $existing_customer['id'];
            
            // Authorization Check: prevent IDOR when updating existing customer data
            $is_owner = is_user_logged_in() && (int) $existing_customer['wp_user_id'] === get_current_user_id();
            
            // Only update customer data if the user is logged in and owns the email, 
            // OR if it's a completely guest account (no wp_user_id associated) and no one is logged in
            if ( $is_owner || ( empty( $existing_customer['wp_user_id'] ) && ! is_user_logged_in() ) ) {
                $this->customer_repo->update( $customer_id, $customer_data );
            }
        } else if ( ! empty( $customer_email ) ) {
            $customer_id = $this->customer_repo->create( $customer_data );
        }

        // === STEP 3: Susun alamat ===
        $billing_address = wp_json_encode( [
            'first_name' => sanitize_text_field( $data['first_name'] ?? '' ),
            'last_name'  => sanitize_text_field( $data['last_name'] ?? '' ),
            'email'      => $customer_email,
            'phone'      => sanitize_text_field( $data['phone'] ?? '' ),
            'address'    => sanitize_textarea_field( $data['address'] ?? '' ),
            'city'       => sanitize_text_field( $data['city'] ?? '' ),
            'province'   => sanitize_text_field( $data['province'] ?? '' ),
            'postcode'   => sanitize_text_field( $data['postcode'] ?? '' ),
        ] );

        // === STEP 4: Hitung biaya pengiriman ===
        $shipping_id      = sanitize_text_field( $data['shipping_method'] ?? '' );
        $payment_id       = sanitize_text_field( $data['payment_method'] ?? '' );
        $shipping_methods = $this->container->get( 'shipping_methods' );
        $shipping_cost    = 0;

        if ( isset( $shipping_methods[ $shipping_id ] ) ) {
            $shipping_cost = $shipping_methods[ $shipping_id ]->calculate_shipping( $cart_items );
        }

        $total_amount += $shipping_cost;

        // === STEP 4.5: Proses Kupon Diskon ===
        $coupon_code    = sanitize_text_field( $data['coupon_code'] ?? '' );
        $discount_total = 0;
        $coupon_repo    = new CouponRepository();

        if ( ! empty( $coupon_code ) ) {
            $coupon = $coupon_repo->find_by_code( $coupon_code );
            if ( $coupon && $coupon->is_valid() ) {
                $discount_total = $coupon->calculate_discount( $total_amount );
                $total_amount  = max( 0, $total_amount - $discount_total );
                // Increment usage moved to after successful order creation
            }
        }

        // === STEP 5: Buat order ===
        $order                   = new Order();
        $order->customer_id      = $customer_id;
        $order->status           = 'pending';
        $order->total_amount     = $total_amount;
        $order->payment_method   = $payment_id;
        $order->shipping_method  = $shipping_id;
        $order->coupon_code      = $coupon_code ?: null;
        $order->discount_total   = $discount_total;
        $order->billing_address  = $billing_address;
        $order->shipping_address = $billing_address; // Sama dengan billing untuk MVP
        $order->items            = $order_items;

        try {
            $saved_order = $this->order_repo->create( $order );

            // === STEP 6: Kurangi stok produk ===
            foreach ( $order_items as $oi ) {
                $reduced = $this->product_repo->reduce_stock( $oi->product_id, $oi->qty );
                if ( ! $reduced ) {
                    throw new \Exception( 'Stok produk tidak mencukupi saat proses finalisasi.' );
                }
            }

            // === STEP 6.5: Increment usage kupon jika ada ===
            if ( ! empty( $coupon_code ) && isset($coupon) && $coupon->id ) {
                $coupon_repo->increment_usage( $coupon->id );
            }

            // Update total_spent customer
            if ( $customer_id > 0 ) {
                $this->customer_repo->add_spent( $customer_id, $total_amount );
            }

            // === STEP 7: Proses payment gateway ===
            $payment_gateways = $this->container->get( 'payment_gateways' );
            $payment_result   = [ 'success' => true, 'redirect_url' => '', 'message' => 'Pesanan berhasil dibuat.' ];

            if ( isset( $payment_gateways[ $payment_id ] ) ) {
                // Security check for COD
                if ( $payment_id === 'cod' && ! get_option( 'owwc_enable_cod' ) ) {
                    return new WP_Error( 'payment_method_disabled', 'Metode pembayaran COD saat ini dinonaktifkan.', [ 'status' => 400 ] );
                }

                $gateway        = $payment_gateways[ $payment_id ];
                $payment_result = $gateway->process_payment( $saved_order->id );

                if ( ! $payment_result['success'] ) {
                    return new WP_Error( 'payment_failed', $payment_result['message'] ?? 'Pembayaran gagal.', [ 'status' => 400 ] );
                }
            }

            // === STEP 8: Kirim Notifikasi Email ===
            $email_sender = new EmailSender();
            $email_sender->send_order_confirmation( $saved_order );
            $email_sender->send_admin_new_order( $saved_order );

            // Bersihkan keranjang belanja
            $this->cart->clear();

            return rest_ensure_response( [
                'success'  => true,
                'order_id' => $saved_order->id,
                'message'  => $payment_result['message'] ?? 'Pesanan berhasil dibuat.',
                'order'    => array_merge(
                    $saved_order->to_array(),
                    [ 'redirect_url' => $payment_result['redirect_url'] ?? '' ]
                ),
            ] );

        } catch ( \Exception $e ) {
            return new WP_Error( 'checkout_failed', 'Gagal menyimpan pesanan: ' . $e->getMessage(), [ 'status' => 500 ] );
        }
    }

    /**
     * POST /owwc/v1/checkout/confirm
     * Konfirmasi pembayaran manual oleh pelanggan.
     */
    public function confirm_payment( $request ) {
        $data     = $request->get_params(); // get_params() includes $_FILES for multipart
        $order_id = (int) ( $data['order_id'] ?? 0 );
        $note     = sanitize_textarea_field( $data['note'] ?? '' );
        $email    = sanitize_email( $data['email'] ?? '' );
        $proof_url = null;

        if ( ! $order_id ) {
            return new WP_Error( 'invalid_order', 'Order ID tidak valid.', [ 'status' => 400 ] );
        }

        $order = $this->order_repo->find( $order_id );
        if ( ! $order ) {
            return new WP_Error( 'not_found', 'Pesanan tidak ditemukan.', [ 'status' => 404 ] );
        }
        
        // Authorization check: User must own the order or provide correct billing email
        $customer = $this->customer_repo->find( $order->customer_id );
        $is_authorized = false;
        
        if ( is_user_logged_in() && $customer && (int) $customer['wp_user_id'] === get_current_user_id() ) {
            $is_authorized = true; // Logged in and owns the order
        } else if ( ! empty( $email ) && $customer && strtolower( $customer['email'] ) === strtolower( $email ) ) {
            $is_authorized = true; // Guest order confirmation via email check
        }
        
        if ( ! current_user_can( 'manage_options' ) && ! $is_authorized ) {
             return new WP_Error( 'forbidden', 'Anda tidak diizinkan mengubah pesanan ini.', [ 'status' => 403 ] );
        }

        // Hanya izinkan konfirmasi jika status masih pending/on-hold
        if ( ! in_array( $order->status, [ 'pending', 'on-hold' ] ) ) {
            return new WP_Error( 'invalid_status', 'Pesanan ini sudah dikonfirmasi atau diproses.', [ 'status' => 400 ] );
        }

        // Handle File Upload
        if ( ! empty( $_FILES['proof'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            
            $uploaded_file = $_FILES['proof'];
            $upload_overrides = [ 
                'test_form' => false,
                'mimes'     => [
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'gif'          => 'image/gif',
                    'png'          => 'image/png',
                    'webp'         => 'image/webp',
                ],
            ];
            
            $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

            if ( $movefile && ! isset( $movefile['error'] ) ) {
                $proof_url = $movefile['url'];
            } else {
                return new WP_Error( 'upload_error', 'Gagal mengunggah bukti: ' . ( $movefile['error'] ?? 'Unknown error' ), [ 'status' => 500 ] );
            }
        }

        $updated = $this->order_repo->update_status( $order_id, 'awaiting-confirmation', $proof_url, $note );

        if ( ! $updated ) {
            return new WP_Error( 'update_failed', 'Gagal memperbarui status pesanan.', [ 'status' => 500 ] );
        }

        return rest_ensure_response( [
            'success'   => true,
            'message'   => 'Konfirmasi pembayaran berhasil dikirim. Produk Anda akan segera diproses oleh tim kami.',
            'proof_url' => $proof_url,
        ] );
    }
}
