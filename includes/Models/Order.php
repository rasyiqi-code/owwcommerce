<?php
namespace OwwCommerce\Models;

/**
 * Class Order Entity
 */
class Order {
    public ?int $id;
    public int $customer_id;
    public string $status;
    public float $total_amount;
    public string $payment_method;
    public string $shipping_method;
    public ?string $created_at;
    public ?string $updated_at;

    /** @var OrderItem[] */
    public array $items = [];

    public function __construct( array $data = [] ) {
        $this->id              = isset( $data['id'] ) ? (int) $data['id'] : null;
        $this->customer_id     = isset( $data['customer_id'] ) ? (int) $data['customer_id'] : 0;
        $this->status          = $data['status'] ?? 'pending';
        $this->total_amount    = isset( $data['total_amount'] ) ? (float) $data['total_amount'] : 0.00;
        $this->payment_method  = $data['payment_method'] ?? 'bacs';
        $this->shipping_method = $data['shipping_method'] ?? 'flat_rate';
        $this->created_at      = $data['created_at'] ?? null;
        $this->updated_at      = $data['updated_at'] ?? null;
        
        if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
            foreach ( $data['items'] as $item ) {
                $this->items[] = $item instanceof OrderItem ? $item : new OrderItem( $item );
            }
        }
    }

    public function to_array(): array {
        return [
            'id'              => $this->id,
            'customer_id'     => $this->customer_id,
            'status'          => $this->status,
            'total_amount'    => $this->total_amount,
            'payment_method'  => $this->payment_method,
            'shipping_method' => $this->shipping_method,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'items'           => array_map( fn( $i ) => $i->to_array(), $this->items ),
        ];
    }
}
