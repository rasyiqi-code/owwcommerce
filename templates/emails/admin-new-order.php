<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; }
        .header { background: #18181b; color: #fbbf24; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { font-size: 12px; color: #999; text-align: center; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>OwwCommerce</h1>
        </div>
        <div class="content">
            <h2>Pesanan Baru Masuk!</h2>
            <p>Ada pesanan baru dengan ID <strong>#<?php echo esc_html( $order->id ); ?></strong>.</p>
            <p>Total: <strong><?php echo \OwwCommerce\Core\Formatter::format_price( $order->total_amount ); ?></strong></p>
            <p>Metode Pembayaran: <?php echo esc_html( strtoupper( $order->payment_method ) ); ?></p>
            
            <p><a href="<?php echo admin_url( 'admin.php?page=owwc-order-detail&id=' . $order->id ); ?>" style="display: inline-block; padding: 10px 20px; background: #18181b; color: #fbbf24; text-decoration: none; border-radius: 4px;">Lihat Detail Pesanan</a></p>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. Powered by OwwCommerce.</p>
        </div>
    </div>
</body>
</html>
