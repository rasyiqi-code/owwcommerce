<?php
namespace OwwCommerce\Payment\Gateways;

use OwwCommerce\Payment\PaymentGateway;

/**
 * Metode pembayaran COD (Bayar di Tempat).
 */
class COD implements PaymentGateway {

    private string $id = 'cod';
    
    public function get_id(): string {
        return $this->id;
    }

    public function get_title(): string {
        return get_option( 'owwc_cod_title', 'Bayar di Tempat (COD)' );
    }

    public function get_description(): string {
        return get_option( 'owwc_cod_description', 'Bayar tunai kepada kurir saat pesanan Anda tiba.' );
    }

    public function needs_redirect(): bool {
        return false;
    }

    public function process_payment( int $order_id ): array {
        global $wpdb;

        // COD menandakan bahwa status order di-set ke processing atau pending langsung
        $wpdb->update(
            $wpdb->prefix . 'oww_orders',
            [ 'status' => 'processing' ],
            [ 'id' => $order_id ],
            [ '%s' ],
            [ '%d' ]
        );

        return [
            'success'      => true,
            'redirect_url' => home_url( '/checkout/order-received/' . $order_id ), // Path contoh
            'message'      => 'Pesanan diterima, siapkan uang tunai saat kurir tiba.'
        ];
    }
}
