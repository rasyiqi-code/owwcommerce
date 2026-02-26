<?php
namespace OwwCommerce\Models;

/**
 * Class Order Item Entity
 */
class OrderItem {
    public ?int $id;
    public int $order_id;
    public int $product_id;
    public int $qty;
    public float $unit_price;
    public float $total_price;

    public function __construct( array $data = [] ) {
        $this->id          = isset( $data['id'] ) ? (int) $data['id'] : null;
        $this->order_id    = isset( $data['order_id'] ) ? (int) $data['order_id'] : 0;
        $this->product_id  = isset( $data['product_id'] ) ? (int) $data['product_id'] : 0;
        $this->qty         = isset( $data['qty'] ) ? (int) $data['qty'] : 1;
        $this->unit_price  = isset( $data['unit_price'] ) ? (float) $data['unit_price'] : 0.00;
        $this->total_price = isset( $data['total_price'] ) ? (float) $data['total_price'] : 0.00;
    }

    public function to_array(): array {
        return [
            'id'          => $this->id,
            'order_id'    => $this->order_id,
            'product_id'  => $this->product_id,
            'qty'         => $this->qty,
            'unit_price'  => $this->unit_price,
            'total_price' => $this->total_price,
        ];
    }
}
