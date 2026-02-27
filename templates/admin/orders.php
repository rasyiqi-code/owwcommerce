<?php
use OwwCommerce\Repositories\OrderRepository;
use OwwCommerce\Repositories\CustomerRepository;

$order_repo = new OrderRepository();
$customer_repo = new CustomerRepository();

// Ambil 20 order terbaru
$orders = $order_repo->get_all( 20, 0 );

function owwc_get_status_label( $status ) {
    $map = [
        'pending'               => 'Menunggu Pembayaran',
        'awaiting-confirmation' => 'Menunggu Konfirmasi',
        'processing'            => 'Diproses',
        'completed'             => 'Selesai',
        'cancelled'             => 'Dibatalkan',
        'failed'                => 'Gagal',
    ];
    return $map[ $status ] ?? ucfirst( $status );
}

function owwc_get_status_class( $status ) {
    $map = [
        'pending'               => 'pending',
        'awaiting-confirmation' => 'processing',
        'processing'            => 'processing',
        'completed'             => 'completed',
        'cancelled'             => 'failed',
        'failed'                => 'failed',
    ];
    return $map[ $status ] ?? 'pending';
}
?>
<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="margin-bottom: 20px;">
        <h1><?php esc_html_e( 'Daftar Pesanan', 'owwcommerce' ); ?></h1>
    </div>

    <div class="owwc-admin-card" style="padding: 0; overflow:x-auto;">
        <?php if ( empty( $orders ) ) : ?>
            <p style="padding: 24px; text-align: center; color: #666;">Belum ada pesanan.</p>
        <?php else : ?>
            <table class="owwc-admin-table" style="margin:0; border:none; width: 100%; min-width: 800px;">
                <thead>
                    <tr>
                        <th style="width: 10%;">ID Pesanan</th>
                        <th style="width: 25%;">Pelanggan</th>
                        <th style="width: 15%;">Tanggal</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Total</th>
                        <th style="width: 10%; text-align: center;">Bukti</th>
                        <th style="width: 10%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $orders as $order ) : 
                        $customer = $customer_repo->find( $order->customer_id );
                        $cust_name = $customer ? esc_html( $customer['first_name'] . ' ' . $customer['last_name'] ) : 'Tamu';
                        $date_fmt = wp_date( 'd M Y, H:i', strtotime( $order->created_at ) );
                    ?>
                        <tr>
                            <td><strong>#<?php echo esc_html( $order->id ); ?></strong></td>
                            <td><?php echo esc_html( $cust_name ); ?></td>
                            <td><?php echo esc_html( $date_fmt ); ?></td>
                            <td>
                                <span class="owwc-badge <?php echo esc_attr( owwc_get_status_class( $order->status ) ); ?>">
                                    <?php echo esc_html( owwc_get_status_label( $order->status ) ); ?>
                                </span>
                            </td>
                             <td><strong><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $order->total_amount ) ); ?></strong></td>
                             <td style="text-align: center;">
                                 <?php if ( ! empty( $order->payment_proof ) ) : ?>
                                     <a href="<?php echo esc_url( $order->payment_proof ); ?>" target="_blank" title="Lihat Bukti">
                                         <svg style="width: 20px; height: 20px; color: var(--owwc-admin-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                     </a>
                                 <?php else : ?>
                                     <span style="color: #ccc;">-</span>
                                 <?php endif; ?>
                             </td>
                             <td style="text-align: right;">
                                <a href="?page=owwc-order-detail&id=<?php echo esc_attr( $order->id ); ?>" class="owwc-admin-btn owwc-btn-secondary" style="padding: 6px 12px; font-size: 12px; text-decoration:none;">Lihat Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
