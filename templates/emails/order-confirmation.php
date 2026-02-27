<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; }
        .header { background: #18181b; color: #fbbf24; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { font-size: 12px; color: #999; text-align: center; padding: 20px; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border-bottom: 1px solid #eee; padding: 10px; text-align: left; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>OwwCommerce</h1>
        </div>
        <div class="content">
            <h2>Halo <?php echo esc_html( $customer['first_name'] ); ?>,</h2>
            <p>Terima kasih atas pesanan Anda! Pesanan Anda telah kami terima dan sedang menunggu proses selanjutnya.</p>
            
            <div style="background: #fafafa; padding: 15px; border-radius: 8px;">
                <h3 style="margin-top: 0;">Ringkasan Pesanan #<?php echo esc_html( $order->id ); ?></h3>
                <p>Status: <span class="badge"><?php echo esc_html( ucfirst( $order->status ) ); ?></span></p>
                <p>Metode Pembayaran: <?php echo esc_html( strtoupper( $order->payment_method ) ); ?></p>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $order->items as $item ) : ?>
                    <tr>
                        <td><?php echo esc_html( $item->product_id ); // Di production sebaiknya ambil nama produk ?></td>
                        <td><?php echo esc_html( $item->qty ); ?></td>
                        <td><?php echo \OwwCommerce\Core\Formatter::format_price( $item->total_price ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" style="text-align: right;">Total Tagihan:</th>
                        <th><?php echo \OwwCommerce\Core\Formatter::format_price( $order->total_amount ); ?></th>
                    </tr>
                </tfoot>
            </table>

            <?php if ( $order->payment_method === 'bacs' ) : ?>
                <div style="border-left: 4px solid #fbbf24; padding-left: 15px; margin-top: 20px;">
                    <p><strong>Instruksi Transfer:</strong><br>
                    <?php echo nl2br( esc_html( get_option( 'owwc_bacs_account' ) ) ); ?></p>
                </div>
            <?php endif; ?>

            <p>Silakan hubungi kami jika ada pertanyaan terkait pesanan Anda.</p>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. Powered by OwwCommerce.</p>
        </div>
    </div>
</body>
</html>
