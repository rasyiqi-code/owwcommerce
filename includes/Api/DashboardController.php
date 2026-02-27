<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class DashboardController extends WP_REST_Controller {
    
    protected $rest_base = 'dashboard';
    protected $namespace = 'owwc/v1';

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/update-profile', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_profile' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/update-address', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_address' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
        ] );
    }

    public function check_permission() {
        return is_user_logged_in();
    }

    public function update_profile( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $first_name = sanitize_text_field( $request->get_param( 'first_name' ) );
        $last_name  = sanitize_text_field( $request->get_param( 'last_name' ) );
        $display_name = sanitize_text_field( $request->get_param( 'display_name' ) );
        $email = sanitize_email( $request->get_param( 'email' ) );

        // Update default user data
        $user_data = [
            'ID'           => $user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => $display_name ?: $first_name . ' ' . $last_name,
        ];

        if ( ! empty( $email ) && $email !== get_the_author_meta( 'user_email', $user_id ) ) {
            if ( email_exists( $email ) ) {
                return new WP_REST_Response( [ 'success' => false, 'message' => 'Email sudah digunakan.' ], 400 );
            }
            $user_data['user_email'] = $email;
        }

        $result = wp_update_user( $user_data );

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => $result->get_error_message() ], 400 );
        }

        return new WP_REST_Response( [ 'success' => true, 'message' => 'Profil berhasil diperbarui.' ] );
    }

    public function update_address( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $type = $request->get_param( 'type' ); // billing or shipping
        
        if ( ! in_array( $type, [ 'billing', 'shipping' ] ) ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => 'Tipe alamat tidak valid.' ], 400 );
        }

        $address_data = [
            'first_name' => sanitize_text_field( $request->get_param( 'first_name' ) ),
            'last_name'  => sanitize_text_field( $request->get_param( 'last_name' ) ),
            'phone'      => sanitize_text_field( $request->get_param( 'phone' ) ),
            'address'    => sanitize_textarea_field( $request->get_param( 'address' ) ),
            'city'       => sanitize_text_field( $request->get_param( 'city' ) ),
            'province'   => sanitize_text_field( $request->get_param( 'province' ) ),
            'postcode'   => sanitize_text_field( $request->get_param( 'postcode' ) ),
        ];

        // Store as separate meta keys for compatibility with WooCommerce or future extensions
        foreach ( $address_data as $key => $value ) {
            update_user_meta( $user_id, 'owwc_' . $type . '_' . $key, $value );
        }

        // Also store as JSON for easier retrieval in some contexts
        update_user_meta( $user_id, 'owwc_' . $type . '_address_json', json_encode( $address_data ) );

        return new WP_REST_Response( [ 'success' => true, 'message' => 'Alamat berhasil diperbarui.' ] );
    }
}
