<?php
namespace OwwCommerce\Payment\Gateways;

use OwwCommerce\Payment\PaymentGateway;

/**
 * Metode pembayaran BACS (Transfer Bank).
 */
class BACS implements PaymentGateway {

    private string $id = 'bacs';
    
    public function get_id(): string {
        return $this->id;
    }

    public function get_title(): string {
        return get_option( 'owwc_bacs_title', 'Transfer Bank' );
    }

    public function get_description(): string {
        return get_option( 'owwc_bacs_description', 'Silahkan transfer langsung ke nomor rekening kami. Pesanan Anda tidak akan dikirim sebelum dana masuk.' );
    }

    public function needs_redirect(): bool {
        return false;
    }

    public function process_payment( int $order_id ): array {
        global $wpdb;

        // Ubah status order menjadi 'on-hold' (menunggu pembayaran)
        $wpdb->update(
            $wpdb->prefix . 'oww_orders',
            [ 'status' => 'on-hold' ],
            [ 'id' => $order_id ],
            [ '%s' ],
            [ '%d' ]
        );

        return [
            'success'      => true,
            'redirect_url' => home_url( '/checkout/order-received/' . $order_id ), // Path contoh
            'message'      => 'Pembayaran telah dipilih, silahkan lakukan transfer bank ke rekening yang tertera.'
        ];
    }
}
