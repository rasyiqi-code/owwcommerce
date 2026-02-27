<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use OwwCommerce\Core\Container;
use OwwCommerce\Models\Coupon;
use OwwCommerce\Repositories\CouponRepository;

/**
 * Class CouponController
 * REST API untuk manajemen dan validasi kupon.
 */
class CouponController extends WP_REST_Controller {

    private CouponRepository $coupon_repo;

    public function __construct( Container $container ) {
        $this->namespace   = 'owwc/v1';
        $this->rest_base   = 'coupons';
        $this->coupon_repo = new CouponRepository();

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        // GET /owwc/v1/coupons — daftar kupon (Admin)
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'admin_check' ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_item' ],
                'permission_callback' => [ $this, 'admin_check' ],
            ],
        ] );

        // GET /owwc/v1/coupons/validate — cek validitas kupon (Public)
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/validate', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'validate_coupon' ],
                'permission_callback' => '__return_true', 
            ],
        ] );

        // DELETE /owwc/v1/coupons/{id} — hapus kupon (Admin)
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_item' ],
                'permission_callback' => [ $this, 'admin_check' ],
            ],
        ] );
    }

    public function admin_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * List kupon untuk Admin.
     */
    public function get_items( $request ) {
        $coupons = $this->coupon_repo->get_all();
        $data    = array_map( fn( $c ) => $c->to_array(), $coupons );

        return rest_ensure_response( $data );
    }

    /**
     * Buat kupon baru.
     */
    public function create_item( $request ) {
        $params = $request->get_json_params() ?: $request->get_params();
        
        if ( empty( $params['code'] ) ) {
            return new WP_Error( 'missing_code', 'Kode kupon wajib diisi.', [ 'status' => 400 ] );
        }

        $existing = $this->coupon_repo->find_by_code( $params['code'] );
        if ( $existing ) {
            return new WP_Error( 'duplicate_code', 'Kode kupon sudah digunakan.', [ 'status' => 400 ] );
        }

        $coupon = new Coupon( $params );
        $saved  = $this->coupon_repo->save( $coupon );

        return rest_ensure_response( $saved->to_array() );
    }

    /**
     * Validasi kupon untuk penggunaan di Frontend.
     */
    public function validate_coupon( $request ) {
        $code = $request->get_param( 'code' );
        
        if ( ! $code ) {
            return new WP_Error( 'missing_code', 'Kode kupon wajib diisi.', [ 'status' => 400 ] );
        }

        $coupon = $this->coupon_repo->find_by_code( $code );

        if ( ! $coupon ) {
            return new WP_Error( 'invalid_coupon', 'Kupon tidak ditemukan atau sudah kadaluarsa.', [ 'status' => 404 ] );
        }

        if ( ! $coupon->is_valid() ) {
            return new WP_Error( 'invalid_coupon', 'Kupon sudah tidak berlaku.', [ 'status' => 400 ] );
        }

        return rest_ensure_response( [
            'success' => true,
            'coupon'  => [
                'code'   => $coupon->code,
                'type'   => $coupon->type,
                'amount' => $coupon->amount,
            ],
            'message' => 'Kupon berhasil diterapkan!'
        ] );
    }

    /**
     * Hapus kupon.
     */
    public function delete_item( $request ) {
        $id      = (int) $request['id'];
        $deleted = $this->coupon_repo->delete( $id );

        return rest_ensure_response( [ 'success' => $deleted ] );
    }
}
