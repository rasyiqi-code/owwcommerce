<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="margin-bottom: 20px;">
        <h1 style="display:flex; align-items:center; gap: 10px;">
            <?php esc_html_e( 'Daftar Pelanggan', 'owwcommerce' ); ?>
            <span style="font-size: 14px; font-weight: normal; color: #666; background: #eee; padding: 2px 8px; border-radius: 20px;"><?php echo count($customers); ?> Total</span>
        </h1>
    </div>

    <div class="owwc-admin-card" style="padding: 0; overflow:hidden;">
        <?php if ( empty( $customers ) ) : ?>
            <p style="padding: 24px; text-align: center; color: #666;">Belum ada pelanggan.</p>
        <?php else : ?>
            <table class="owwc-admin-table" style="margin:0; border:none; width: 100%; min-width: 600px;">
                <thead>
                    <tr>
                        <th style="width: 25%;">Nama Pelanggan</th>
                        <th style="width: 25%;">Email</th>
                        <th style="width: 15%;">Telepon</th>
                        <th style="width: 20%;">Terdaftar Sejak</th>
                        <th style="width: 15%; text-align: right;">Total Belanja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $customers as $customer ) : 
                        $full_name = trim( $customer['first_name'] . ' ' . $customer['last_name'] );
                        $total_spent = (float) $customer['total_spent'];
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $full_name ?: 'Tanpa Nama' ); ?></strong>
                                <?php if ( ! empty($customer['wp_user_id']) ) : ?>
                                    <span class="owwc-badge" style="background:#eee; color:#666; font-size:10px; margin-left: 5px;">Member</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="mailto:<?php echo esc_attr( $customer['email'] ); ?>" style="color: var(--owwc-admin-primary); text-decoration: none;"><?php echo esc_html( $customer['email'] ); ?></a></td>
                            <td><?php echo esc_html( $customer['phone'] ?: '-' ); ?></td>
                            <td><?php echo wp_date( 'd M Y', strtotime( $customer['created_at'] ) ); ?></td>
                            <td style="text-align: right; font-weight: 600; color: <?php echo $total_spent > 0 ? 'var(--owwc-admin-success)' : '#666'; ?>;">
                                <?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $total_spent ) ); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
