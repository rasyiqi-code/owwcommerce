<?php
namespace OwwCommerce\Models;

/**
 * Class ProductVariation
 * Representative model for OwwCommerce Product Variations.
 */
class ProductVariation {
    public ?int $id;
    public int $product_id;
    public ?string $sku;
    public float $price;
    public ?float $sale_price;
    public int $stock_qty;
    public array $attributes; // Array of [attribute_slug => term_slug]

    public function __construct( array $data = [] ) {
        $this->id         = isset( $data['id'] ) ? (int) $data['id'] : null;
        $this->product_id = isset( $data['product_id'] ) ? (int) $data['product_id'] : 0;
        $this->sku        = $data['sku'] ?? null;
        $this->price      = isset( $data['price'] ) ? (float) $data['price'] : 0.0;
        $this->sale_price = isset( $data['sale_price'] ) ? (float) $data['sale_price'] : null;
        $this->stock_qty  = isset( $data['stock_qty'] ) ? (int) $data['stock_qty'] : 0;
        
        // Handle attributes (stored as JSON string in DB, but as array in model)
        if ( isset( $data['attributes'] ) ) {
            $this->attributes = is_string( $data['attributes'] ) 
                ? json_decode( $data['attributes'], true ) 
                : (array) $data['attributes'];
        } else {
            $this->attributes = [];
        }
    }

    public function to_array(): array {
        return [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'sku'        => $this->sku,
            'price'      => $this->price,
            'sale_price' => $this->sale_price,
            'stock_qty'  => $this->stock_qty,
            'attributes' => $this->attributes,
        ];
    }
}
