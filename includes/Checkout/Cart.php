<?php
namespace OwwCommerce\Checkout;

/**
 * Class Cart
 * Native PHP Session based cart engine for Zero Bloatware performance.
 */
class Cart {
    
    private string $session_key = 'owwcommerce_cart';
    private array $items = [];

    public function __construct() {
        $this->load_cart();
    }

    private function load_cart(): void {
        if ( isset( $_SESSION[ $this->session_key ] ) && is_array( $_SESSION[ $this->session_key ] ) ) {
            $this->items = $_SESSION[ $this->session_key ];
        }
    }

    private function save_cart(): void {
        $_SESSION[ $this->session_key ] = $this->items;
    }

    public function add_item( int $product_id, int $quantity = 1, int $variation_id = 0, array $data = [] ): void {
        $key = $variation_id ? "{$product_id}_{$variation_id}" : (string) $product_id;

        if ( isset( $this->items[ $key ] ) ) {
            $this->items[ $key ]['qty'] += $quantity;
        } else {
            $this->items[ $key ] = array_merge( [
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
                'qty'          => $quantity,
            ], $data );
        }
        $this->save_cart();
    }

    public function remove_item( string $key ): void {
        if ( isset( $this->items[ $key ] ) ) {
            unset( $this->items[ $key ] );
            $this->save_cart();
        }
    }

    /**
     * Memperbarui kuantitas item di keranjang secara absolut.
     * Jika kuantitas kurang dari atau sama dengan 0, item akan dihapus.
     */
    public function update_item( string $key, int $quantity ): void {
        if ( isset( $this->items[ $key ] ) ) {
            if ( $quantity <= 0 ) {
                $this->remove_item( $key );
            } else {
                $this->items[ $key ]['qty'] = $quantity;
                $this->save_cart();
            }
        }
    }

    public function clear(): void {
        $this->items = [];
        $this->save_cart();
    }

    public function get_items(): array {
        return $this->items;
    }

    public function get_count(): int {
        return array_sum( array_column( $this->items, 'qty' ) );
    }
}
