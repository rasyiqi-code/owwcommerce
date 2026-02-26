<?php
namespace OwwCommerce\Payment;

/**
 * Interface dasar untuk Payment Gateway OwwCommerce.
 */
interface PaymentGateway {

    /**
     * ID Gateway (e.g. 'bacs', 'cod')
     */
    public function get_id(): string;

    /**
     * Nama Gateway untuk ditampilkan di checkout.
     */
    public function get_title(): string;

    /**
     * Deskripsi cara pembayaran untuk pelanggan.
     */
    public function get_description(): string;

    /**
     * Apakah gateway ini membutuhkan proses redirect ke halaman luar?
     * @return bool
     */
    public function needs_redirect(): bool;

    /**
     * Proses pembayaran setelah pesanan dibuat di database.
     * 
     * @param int $order_id
     * @return array [ 'success' => bool, 'redirect_url' => string, 'message' => string ]
     */
    public function process_payment( int $order_id ): array;
}
