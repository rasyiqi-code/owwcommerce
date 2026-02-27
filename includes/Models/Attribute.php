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

    public function __construct( array $data = [] ) {
        $this->id   = isset( $data['id'] ) ? (int) $data['id'] : null;
        $this->name = $data['name'] ?? '';
        $this->slug = $data['slug'] ?? '';
    }

    public function to_array(): array {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
