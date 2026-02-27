<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;
use OwwCommerce\Repositories\AttributeRepository;
use OwwCommerce\Models\Attribute;
use OwwCommerce\Models\AttributeTerm;

class AttributeController extends WP_REST_Controller {

    private AttributeRepository $repository;

    public function __construct() {
        $this->namespace = 'owwc/v1';
        $this->rest_base = 'attributes';
        $this->repository = new AttributeRepository();

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_item' ],
                'permission_callback' => [ $this, 'permissions_check' ],
                'args'                => [
                    'name' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/terms', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_terms' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_term' ],
                'permission_callback' => [ $this, 'permissions_check' ],
                'args'                => [
                    'name' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ] );
    }

    public function permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    public function get_items( $request ) {
        $attributes = $this->repository->get_all();
        return rest_ensure_response( array_map( fn($a) => $a->to_array(), $attributes ) );
    }

    public function create_item( $request ) {
        $name = $request->get_param('name');
        $attribute = new Attribute([ 'name' => $name ]);
        $saved = $this->repository->save( $attribute );
        return rest_ensure_response( $saved->to_array() );
    }

    public function get_terms( $request ) {
        $attribute_id = (int) $request['id'];
        $terms = $this->repository->get_terms( $attribute_id );
        return rest_ensure_response( array_map( fn($t) => $t->to_array(), $terms ) );
    }

    public function create_term( $request ) {
        $attribute_id = (int) $request['id'];
        $name = $request->get_param('name');
        
        $term = new AttributeTerm([
            'attribute_id' => $attribute_id,
            'name' => $name
        ]);

        $saved = $this->repository->save_term( $term );
        return rest_ensure_response( $saved->to_array() );
    }
}
