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
        // Nantinya cost dan title diambil dari options/settings
        $this->cost = (float) get_option( 'owwc_flat_rate_cost', 10.00 ); 
        $this->title = get_option( 'owwc_flat_rate_title', 'Flat Rate' );
    }

    public function get_id(): string {
        return 'flat_rate';
    }

    public function get_title(): string {
        return $this->title;
    }

    public function calculate_shipping( array $packages ): float {
        return $this->cost;
    }

    public function is_available( array $packages ): bool {
        return true; // Biasanya selalu tersedia kecuali ada radius tertentu
    }
}
