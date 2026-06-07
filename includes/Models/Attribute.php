<?php
namespace OwwCommerce\Models;

/**
 * Class Attribute
 * Representative model for OwwCommerce Product Attributes.
 */
class Attribute {
    public ?int $id;
    public string $name;
    public string $slug;
    /** @var AttributeTerm[] */
    public array $terms = [];

    public function __construct( array $data = [] ) {
        $this->id   = isset( $data['id'] ) ? (int) $data['id'] : null;
        $this->name = $data['name'] ?? '';
        $this->slug = $data['slug'] ?? '';
        
        if ( isset( $data['terms'] ) && is_array( $data['terms'] ) ) {
            foreach ( $data['terms'] as $term ) {
                $this->terms[] = $term instanceof AttributeTerm ? $term : new AttributeTerm( (array) $term );
            }
        }
    }

    public function to_array(): array {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'slug'  => $this->slug,
            'terms' => array_map( fn($t) => $t->to_array(), $this->terms ),
        ];
    }
}
