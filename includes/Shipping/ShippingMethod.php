<?php
namespace OwwCommerce\Shipping;

/**
 * Interface dasar untuk metode pengiriman OwwCommerce.
 */
interface ShippingMethod {
    
    /**
     * ID pengiriman unik, misalnya 'flat_rate', 'free_shipping'.
     */
    public function get_id(): string;

    /**
     * Nama pengiriman untuk ditampilkan di checkout.
     */
    public function get_title(): string;

    /**
     * Menghitung total biaya pengiriman.
     * 
     * @param array $packages Data paket/keranjang yang akan dihitung.
     * @return float Biaya pengiriman.
     */
    public function calculate_shipping( array $packages ): float;

    /**
     * Cek apakah metode ini tersedia untuk pelanggan/keranjang saat ini.
     * 
     * @param array $packages Data paket/keranjang
     */
    public function is_available( array $packages ): bool;
}
