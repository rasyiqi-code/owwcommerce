<?php
use OwwCommerce\Repositories\OrderRepository;
use OwwCommerce\Repositories\CustomerRepository;
use OwwCommerce\Repositories\ProductRepository;

$order_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;

if ( ! $order_id ) {
    echo '<div class="wrap"><p>Order ID tidak valid.</p></div>';
    return;
}

$order_repo   = new OrderRepository();
$customer_repo = new CustomerRepository();
$product_repo = new ProductRepository();

$order = $order_repo->find( $order_id );

if ( ! $order ) {
    echo '<div class="wrap"><p>Pesanan tidak ditemukan.</p></div>';
    return;
}

$customer = $customer_repo->find( $order->customer_id );
$cust_name = $customer ? esc_html( $customer['first_name'] . ' ' . $customer['last_name'] ) : 'Tamu';
$cust_email = $customer ? esc_html( $customer['email'] ) : '-';
$cust_phone = $customer ? esc_html( $customer['phone'] ) : '-';

$date_fmt = wp_date( 'l, d F Y - H:i', strtotime( $order->created_at ) );

$status_options = [
    'pending'               => 'Menunggu Pembayaran',
    'awaiting-confirmation' => 'Menunggu Konfirmasi',
    'processing'            => 'Diproses',
    'completed'  => 'Selesai',
    'cancelled'  => 'Dibatalkan',
    'failed'     => 'Gagal',
];

function owwc_get_detail_status_class( $status ) {
    $map = [
        'pending'               => 'pending',
        'awaiting-confirmation' => 'processing', // Use orange/processing color
        'processing'            => 'completed', // Or keep consistent
        'cancelled'  => 'failed',
        'failed'     => 'failed',
    ];
    return $map[ $status ] ?? 'pending';
}

$billing_address = $order->get_billing_array();
$shipping_address = $order->get_shipping_array();

?>
<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="display: flex; align-items: center; gap: 10px;">
            <?php printf( esc_html__( 'Detail Pesanan #%d', 'owwcommerce' ), $order->id ); ?>
            <span class="owwc-badge <?php echo esc_attr( owwc_get_detail_status_class( $order->status ) ); ?>" style="font-size: 13px; font-weight: 500;">
                <?php echo esc_html( $status_options[ $order->status ] ?? ucfirst( $order->status ) ); ?>
            </span>
        </h1>
        <a href="?page=owwc-orders" class="owwc-admin-btn owwc-btn-outline" style="padding: 8px 16px; text-decoration:none;">Kembali ke Daftar</a>
    </div>

    <div class="owwc-admin-2col-layout" style="margin-top: 20px;">
        <!-- Kolom Utama (Kiri) -->
        <div class="owwc-admin-main">
            <!-- Informasi Pesanan -->
            <div class="owwc-admin-card" style="margin-bottom: 24px;">
                <h2 style="font-size: 16px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--owwc-admin-border);">Rincian Pesanan</h2>
                
                <table class="owwc-admin-table" style="margin:0; border:none; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Produk</th>
                            <th style="width: 15%; text-align: center;">Harga</th>
                            <th style="width: 15%; text-align: center;">Qty</th>
                            <th style="width: 20%; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $order->items as $item ) : 
                            $product = $product_repo->find( $item->product_id );
                            $prod_title = $product ? $product->title : '(Produk Dihapus)';
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html( $prod_title ); ?></strong></td>
                                <td style="text-align: center;"><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $item->unit_price ) ); ?></td>
                                <td style="text-align: center;"><?php echo esc_html( $item->qty ); ?></td>
                                <td style="text-align: right;"><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $item->total_price ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Ringkasan Total -->
                        <tr>
                            <td colspan="3" style="text-align: right; border-top: 2px solid var(--owwc-admin-border); padding-top: 15px;"><strong>Metode Pembayaran:</strong></td>
                            <td style="text-align: right; border-top: 2px solid var(--owwc-admin-border); padding-top: 15px;">
                                <?php echo esc_html( strtoupper( $order->payment_method ) ); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Metode Pengiriman:</strong></td>
                            <td style="text-align: right;">
                                <?php echo esc_html( str_replace('_', ' ', ucfirst( $order->shipping_method )) ); ?>
                            </td>
                        </tr>
                        <?php if ( ! empty( $order->coupon_code ) ) : ?>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Kupon (<?php echo esc_html( $order->coupon_code ); ?>):</strong></td>
                            <td style="text-align: right; color: #ef4444;">
                                - <?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $order->discount_total ) ); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr>
                            <td colspan="3" style="text-align: right; font-size: 16px;"><strong>Total Pembayaran:</strong></td>
                            <td style="text-align: right; font-size: 16px; color: var(--owwc-admin-primary);">
                                <strong><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $order->total_amount ) ); ?></strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Alamat -->
            <div style="display: flex; gap: 24px;">
                <div class="owwc-admin-card" style="flex: 1;">
                    <h2 style="font-size: 14px; margin-bottom: 12px;">Alamat Penagihan (Billing)</h2>
                    <p style="margin: 0; line-height: 1.6; color: #444;">
                        <strong><?php echo esc_html( ($billing_address['first_name'] ?? '') . ' ' . ($billing_address['last_name'] ?? '') ); ?></strong><br>
                        <?php echo esc_html( $billing_address['address'] ?? '-' ); ?><br>
                        <?php echo esc_html( $billing_address['city'] ?? '-' ); ?>, <?php echo esc_html( $billing_address['postcode'] ?? '-' ); ?><br>
                        Email: <?php echo esc_html( $billing_address['email'] ?? '-' ); ?><br>
                        Telp: <?php echo esc_html( $billing_address['phone'] ?? '-' ); ?>
                    </p>
                </div>
                
                <div class="owwc-admin-card" style="flex: 1;">
                    <h2 style="font-size: 14px; margin-bottom: 12px;">Alamat Pengiriman (Shipping)</h2>
                    <p style="margin: 0; line-height: 1.6; color: #444;">
                        <strong><?php echo esc_html( ($shipping_address['first_name'] ?? '') . ' ' . ($shipping_address['last_name'] ?? '') ); ?></strong><br>
                        <?php echo esc_html( $shipping_address['address'] ?? '-' ); ?><br>
                        <?php echo esc_html( $shipping_address['city'] ?? '-' ); ?>, <?php echo esc_html( $shipping_address['postcode'] ?? '-' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Kolom Sidebar (Kanan) -->
        <div class="owwc-admin-sidebar">
            <div class="owwc-admin-card">
                <h2 style="font-size: 16px; margin-bottom: 15px;">Pelanggan</h2>
                <p style="margin: 0 0 10px 0;"><strong>Nama:</strong> <?php echo $cust_name; ?></p>
                <p style="margin: 0 0 10px 0;"><strong>Email:</strong> <a href="mailto:<?php echo $cust_email; ?>"><?php echo $cust_email; ?></a></p>
                <p style="margin: 0 0 15px 0;"><strong>Telp:</strong> <?php echo $cust_phone; ?></p>
                <div style="padding-top: 15px; border-top: 1px solid var(--owwc-admin-border);">
                    <a href="?page=owwc-customers" style="font-size: 13px; text-decoration: none; color: var(--owwc-admin-primary);">Lihat Profil Pelanggan &rarr;</a>
                </div>
                <!-- Bukti Pembayaran -->
            <?php if ( ! empty( $order->payment_proof ) ) : ?>
                <div class="owwc-card" style="margin-top: 20px;">
                    <h3>Bukti Pembayaran</h3>
                    <div style="text-align: center; padding: 15px; border: 1px dashed #ddd; border-radius: 4px;">
                        <a href="<?php echo esc_url( $order->payment_proof ); ?>" target="_blank">
                            <img src="<?php echo esc_url( $order->payment_proof ); ?>" style="max-width: 100%; max-height: 300px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        </a>
                        <p style="margin-top: 10px; margin-bottom: 0;">
                            <a href="<?php echo esc_url( $order->payment_proof ); ?>" target="_blank" class="button">Lihat Gambar Penuh</a>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $order->payment_note ) ) : ?>
                <div class="owwc-card" style="margin-top: 20px;">
                    <h3>Catatan Pembayaran</h3>
                    <p style="font-style: italic; background: #fff8e1; padding: 10px; border-left: 4px solid #ffc107; border-radius: 4px;">
                        "<?php echo esc_html( $order->payment_note ); ?>"
                    </p>
                </div>
            <?php endif; ?>
            </div>

            <div class="owwc-admin-card">
                <h2 style="font-size: 16px; margin-bottom: 15px;">Aksi Pesanan</h2>
                <p style="margin: 0 0 10px 0; font-size: 13px; color: #666;">Dibuat: <?php echo $date_fmt; ?></p>
                
                <div class="form-field" style="margin-bottom: 15px;">
                    <label for="owwc-order-status">Ubah Status</label>
                    <select id="owwc-order-status" class="owwc-admin-select">
                        <?php foreach ( $status_options as $val => $label ) : ?>
                            <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $order->status, $val ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <span id="owwc-status-message" style="display: none; font-weight: 500; font-size: 13px;"></span>
                </div>
                
                <button type="button" id="owwc-update-status-btn" data-order-id="<?php echo esc_attr( $order->id ); ?>" class="owwc-admin-btn" style="width: 100%; padding: 10px;">Simpan Status</button>
            </div>
        </div>
    </div>
</div>
