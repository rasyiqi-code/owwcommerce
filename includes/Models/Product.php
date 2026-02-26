<?php
namespace OwwCommerce\Models;

/**
 * Class Product Entity
 */
class Product {
    public ?int $id;
    public string $title;
    public string $slug;
    public string $description;
    public string $type;
    public float $price;
    public ?float $sale_price;
    public ?string $sku;
    public int $stock_qty;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct( array $data = [] ) {
        $this->id          = isset( $data['id'] ) ? (int) $data['id'] : null;
        $this->title       = $data['title'] ?? '';
        $this->slug        = $data['slug'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->type        = $data['type'] ?? 'simple';
        $this->price       = isset( $data['price'] ) ? (float) $data['price'] : 0.00;
        $this->sale_price  = isset( $data['sale_price'] ) ? (float) $data['sale_price'] : null;
        $this->sku         = $data['sku'] ?? null;
        $this->stock_qty   = isset( $data['stock_qty'] ) ? (int) $data['stock_qty'] : 0;
        $this->created_at  = $data['created_at'] ?? null;
        $this->updated_at  = $data['updated_at'] ?? null;
    }

    public function to_array(): array {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description,
            'type'        => $this->type,
            'price'       => $this->price,
            'sale_price'  => $this->sale_price,
            'sku'         => $this->sku,
            'stock_qty'   => $this->stock_qty,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
