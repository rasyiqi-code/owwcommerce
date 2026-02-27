<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; }
        .header { background: #18181b; color: #fbbf24; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { font-size: 12px; color: #999; text-align: center; padding: 20px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 14px; font-weight: bold; background: #fbbf24; color: #18181b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>OwwCommerce</h1>
        </div>
        <div class="content">
            <h2>Update Status Pesanan #<?php echo esc_html( $order->id ); ?></h2>
            <p>Halo <?php echo esc_html( $customer['first_name'] ); ?>,</p>
            <p>Kami ingin menginformasikan bahwa status pesanan Anda telah diperbarui menjadi:</p>
            
            <p style="text-align: center; margin: 30px 0;">
                <span class="badge"><?php echo esc_html( strtoupper( $order->status ) ); ?></span>
            </p>

            <p>Jika Anda memiliki pertanyaan, silakan hubungi tim dukungan kami.</p>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. Powered by OwwCommerce.</p>
        </div>
    </div>
</body>
</html>
