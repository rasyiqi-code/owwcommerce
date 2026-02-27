<?php
namespace OwwCommerce\Models;

/**
 * Class AttributeTerm
 * Representative model for OwwCommerce Product Attribute Terms (Values).
 */
class AttributeTerm {
    public ?int $id;
    public int $attribute_id;
    public string $name;
    public string $slug;

    public function __construct( array $data = [] ) {
        $this->id           = isset( $data['id'] ) ? (int) $data['id'] : null;
        $this->attribute_id = isset( $data['attribute_id'] ) ? (int) $data['attribute_id'] : 0;
        $this->name         = $data['name'] ?? '';
        $this->slug         = $data['slug'] ?? '';
    }

    public function to_array(): array {
        return [
            'id'           => $this->id,
            'attribute_id' => $this->attribute_id,
            'name'         => $this->name,
            'slug'         => $this->slug,
        ];
    }
}
