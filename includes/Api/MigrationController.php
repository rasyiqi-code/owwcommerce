<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use OwwCommerce\WooCommerce\Importer;
use OwwCommerce\WooCommerce\ProductImporter;
use OwwCommerce\WooCommerce\OrderImporter as WC_OrderImporter;

class MigrationController extends WP_REST_Controller {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'owwc/v1', '/migration/stats', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_stats' ],
            'permission_callback' => [ $this, 'permissions_check' ],
        ]);

        register_rest_route( 'owwc/v1', '/migration/run', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'run_migration' ],
            'permission_callback' => [ $this, 'permissions_check' ],
        ]);
    }

    public function permissions_check() {
        return current_user_can( 'manage_options' );
    }

    public function get_stats() {
        return new WP_REST_Response( Importer::get_stats(), 200 );
    }

    public function run_migration( $request ) {
        $type = $request->get_param( 'type' );
        $limit = $request->get_param( 'limit' ) ?: 50;
        $offset = $request->get_param( 'offset' ) ?: 0;

        $count = 0;
        if ( $type === 'products' ) {
            $count = ProductImporter::run_batch( $limit, $offset );
        } elseif ( $type === 'orders' ) {
            $count = WC_OrderImporter::run_batch( $limit, $offset );
        }

        return new WP_REST_Response( [
            'success' => true,
            'count'   => $count,
            'type'    => $type,
            'offset'  => $offset + $count
        ], 200 );
    }
}
