<?php
namespace OwwCommerce\Models;

/**
 * Class Order Entity
 *
 * Merepresentasikan baris dalam tabel oww_orders.
 * Mendukung alamat billing dan shipping (JSON encoded).
 */
class Order {
    public ?int $id;
    public int $customer_id;
    public string $status;
    public float $total_amount;
    public string $payment_method;
    public string $shipping_method;
    public ?string $coupon_code;
    public float $discount_total;
    /** Alamat penagihan (JSON encoded string) */
    public ?string $billing_address;
    /** Alamat pengiriman (JSON encoded string) */
    public ?string $shipping_address;
    public ?string $payment_proof;
    public ?string $payment_note;
    public ?string $created_at;
    public ?string $updated_at;

    /** @var OrderItem[] */
    public array $items = [];

    public function __construct( array $data = [] ) {
        $this->id               = isset( $data['id'] ) ? (int) $data['id'] : null;
        $this->customer_id      = isset( $data['customer_id'] ) ? (int) $data['customer_id'] : 0;
        $this->status           = $data['status'] ?? 'pending';
        $this->total_amount     = isset( $data['total_amount'] ) ? (float) $data['total_amount'] : 0.00;
        $this->payment_method   = $data['payment_method'] ?? 'bacs';
        $this->shipping_method  = $data['shipping_method'] ?? 'flat_rate';
        $this->coupon_code      = $data['coupon_code'] ?? null;
        $this->discount_total   = isset( $data['discount_total'] ) ? (float) $data['discount_total'] : 0.00;
        $this->billing_address  = $data['billing_address'] ?? null;
        $this->shipping_address = $data['shipping_address'] ?? null;
        $this->payment_proof    = $data['payment_proof'] ?? null;
        $this->payment_note     = $data['payment_note'] ?? null;
        $this->created_at       = $data['created_at'] ?? null;
        $this->updated_at       = $data['updated_at'] ?? null;

        if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
            foreach ( $data['items'] as $item ) {
                $this->items[] = $item instanceof OrderItem ? $item : new OrderItem( $item );
            }
        }
    }

    /**
     * Mendapatkan alamat billing sebagai array asosiatif.
     */
    public function get_billing_array(): array {
        return $this->billing_address ? (array) json_decode( $this->billing_address, true ) : [];
    }

    /**
     * Mendapatkan alamat shipping sebagai array asosiatif.
     */
    public function get_shipping_array(): array {
        return $this->shipping_address ? (array) json_decode( $this->shipping_address, true ) : [];
    }

    public function to_array(): array {
        return [
            'id'               => $this->id,
            'customer_id'      => $this->customer_id,
            'status'           => $this->status,
            'payment_method'   => $this->payment_method,
            'shipping_method'  => $this->shipping_method,
            'coupon_code'      => $this->coupon_code,
            'discount_total'   => $this->discount_total,
            'billing_address'  => $this->get_billing_array(),
            'shipping_address' => $this->get_shipping_array(),
            'payment_proof'    => $this->payment_proof,
            'payment_note'     => $this->payment_note,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'items'            => array_map( fn( $i ) => $i->to_array(), $this->items ),
        ];
    }
}
