<?php
namespace OwwCommerce\Shipping\Methods;

use OwwCommerce\Shipping\ShippingMethod;

/**
 * Metode pengiriman Flat Rate.
 */
class FlatRate implements ShippingMethod {
    
    private float $cost;
    private string $title;

    public function __construct() {
        // Biaya dan judul diambil dari opsi wp_options (diatur via halaman Settings)
        $this->cost = (float) get_option( 'owwc_flat_rate_cost', 15000 ); 
        $this->title = get_option( 'owwc_flat_rate_title', 'Flat Rate' );
    }

    public function get_id(): string {
        return 'flat_rate';
    }

    public function get_title(): string {
        return $this->title;
    }

    public function calculate_shipping( array $packages ): float {
        $threshold = (float) get_option( 'owwc_free_shipping_threshold', 0 );

        if ( $threshold > 0 ) {
            $subtotal = 0;
            // Packages biasanya berisi cart items
            foreach ( $packages as $item ) {
                $subtotal += ( $item['price'] * $item['qty'] );
            }

            if ( $subtotal >= $threshold ) {
                return 0.00;
            }
        }

        return $this->cost;
    }

    public function is_available( array $packages ): bool {
        return true; // Biasanya selalu tersedia kecuali ada radius tertentu
    }
}
