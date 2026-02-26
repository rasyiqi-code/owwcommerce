<?php
namespace OwwCommerce\Models;

/**
 * Entitas Category
 */
class Category {
    public int $id;
    public string $name;
    public string $slug;
    public int $parent_id;
    public ?string $description;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct( array $data = [] ) {
        $this->id          = isset( $data['id'] ) ? (int) $data['id'] : 0;
        $this->name        = $data['name'] ?? '';
        $this->slug        = $data['slug'] ?? '';
        $this->parent_id   = isset( $data['parent_id'] ) ? (int) $data['parent_id'] : 0;
        $this->description = $data['description'] ?? null;
        $this->created_at  = $data['created_at'] ?? null;
        $this->updated_at  = $data['updated_at'] ?? null;
    }
}
