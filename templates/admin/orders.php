<?php
/**
 * OwwCommerce Orders Admin View
 * Menampilkan pesanan secara statis dengan WPDB Repositori Langsung tanpa API (untuk admin sederhana).
 */

use OwwCommerce\Repositories\OrderRepository;

if ( ! defined( 'ABSPATH' ) ) exit;

$repo = new OrderRepository();
// Dalam versi production, tambahkan logic paginasi di sini ($limit, $offset)
$orders = $repo->get_all( 100 ); 

?>
<div class="owwc-admin-wrap">
    <div class="owwc-admin-header">
        <h1><?php esc_html_e( 'Orders', 'owwcommerce' ); ?></h1>
    </div>

    <div class="owwc-admin-card" style="padding: 0; overflow:hidden;">
        <?php if ( empty( $orders ) ) : ?>
            <p style="padding: 24px; color: var(--owwc-admin-text-muted);">Belum ada pesanan masuk.</p>
        <?php else: ?>
            <table class="owwc-admin-table" style="border: none; border-radius: 0; box-shadow: none;">
                <thead>
                    <tr>
                        <th style="width: 25%;">ID Pesanan</th>
                        <th style="width: 25%;">Status</th>
                        <th style="width: 25%;">Total</th>
                        <th style="width: 25%;">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $orders as $order ) : ?>
                        <tr>
                            <td><strong>#<?php echo esc_html( $order->id ); ?></strong></td>
                            <td>
                                <?php 
                                    // Mapping status to badge class
                                    $badge_class = 'pending';
                                    if ( $order->status === 'completed' ) $badge_class = 'completed';
                                    if ( $order->status === 'processing' ) $badge_class = 'processing';
                                    if ( $order->status === 'failed' || $order->status === 'cancelled' ) $badge_class = 'failed';
                                ?>
                                <span class="owwc-badge <?php echo esc_attr($badge_class); ?>">
                                    <?php echo esc_html( ucfirst( $order->status ) ); ?>
                                </span>
                            </td>
                            <td><strong>Rp<?php echo esc_html( number_format( $order->total_amount, 0, ',', '.' ) ); ?></strong></td>
                            <td style="color: var(--owwc-admin-text-muted);"><?php echo esc_html( date( 'd M Y', strtotime( $order->created_at ) ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
