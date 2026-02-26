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
        if ( session_status() === PHP_SESSION_NONE ) {
            session_start();
        }
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

    public function add_item( int $product_id, int $quantity = 1, array $data = [] ): void {
        if ( isset( $this->items[ $product_id ] ) ) {
            $this->items[ $product_id ]['qty'] += $quantity;
        } else {
            $this->items[ $product_id ] = array_merge( [
                'product_id' => $product_id,
                'qty'        => $quantity,
            ], $data );
        }
        $this->save_cart();
    }

    public function remove_item( int $product_id ): void {
        if ( isset( $this->items[ $product_id ] ) ) {
            unset( $this->items[ $product_id ] );
            $this->save_cart();
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
