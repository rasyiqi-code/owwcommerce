<?php
namespace OwwCommerce\Repositories;

use OwwCommerce\Models\Attribute;
use OwwCommerce\Models\AttributeTerm;

/**
 * Class AttributeRepository
 * Handles DB operations for Attributes and their Terms.
 */
class AttributeRepository {
    private string $table_attr;
    private string $table_terms;

    public function __construct() {
        global $wpdb;
        $this->table_attr  = $wpdb->prefix . 'oww_attributes';
        $this->table_terms = $wpdb->prefix . 'oww_attribute_terms';
    }

    /**
     * Get all attributes.
     */
    public function get_all(): array {
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM {$this->table_attr} ORDER BY name ASC" );
        return array_map( fn( $row ) => new Attribute( (array) $row ), $results );
    }

    /**
     * Find an attribute by ID.
     */
    public function find( int $id ): ?Attribute {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_attr} WHERE id = %d", $id ) );
        return $row ? new Attribute( (array) $row ) : null;
    }

    /**
     * Get terms for a specific attribute.
     */
    public function get_terms( int $attribute_id ): array {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare( 
            "SELECT * FROM {$this->table_terms} WHERE attribute_id = %d ORDER BY name ASC", 
            $attribute_id 
        ) );
        return array_map( fn( $row ) => new AttributeTerm( (array) $row ), $results );
    }

    /**
     * Save an attribute.
     */
    public function save( Attribute $attribute ): Attribute {
        global $wpdb;
        
        $data = [
            'name' => $attribute->name,
            'slug' => $attribute->slug ?: sanitize_title( $attribute->name ),
        ];

        if ( $attribute->id ) {
            $wpdb->update( $this->table_attr, $data, [ 'id' => $attribute->id ] );
        } else {
            $wpdb->insert( $this->table_attr, $data );
            $attribute->id = $wpdb->insert_id;
        }

        return $attribute;
    }

    /**
     * Save an attribute term.
     */
    public function save_term( AttributeTerm $term ): AttributeTerm {
        global $wpdb;

        $data = [
            'attribute_id' => $term->attribute_id,
            'name'         => $term->name,
            'slug'         => $term->slug ?: sanitize_title( $term->name ),
        ];

        if ( $term->id ) {
            $wpdb->update( $this->table_terms, $data, [ 'id' => $term->id ] );
        } else {
            $wpdb->insert( $this->table_terms, $data );
            $term->id = $wpdb->insert_id;
        }

        return $term;
    }
}
