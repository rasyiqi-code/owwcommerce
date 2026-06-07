<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="margin-bottom: 20px;">
        <h1 style="display:flex; align-items:center; gap: 10px;">
            <?php esc_html_e( 'Daftar Pelanggan', 'owwcommerce' ); ?>
            <span style="font-size: 14px; font-weight: normal; color: #666; background: #eee; padding: 2px 8px; border-radius: 20px;"><?php echo (int) $total_customers; ?> Total</span>
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

    <!-- Navigasi Paginasi -->
    <?php if ( isset($total_pages) && $total_pages > 1 ) : ?>
        <div class="owwc-admin-pagination" style="margin-top:20px; display:flex; justify-content:space-between; align-items:center; background:white; padding:15px; border-radius:8px; border:1px solid #e5e7eb;">
            <div class="pagination-info" style="color:#6b7280; font-size:14px;">
                Menampilkan halaman <strong><?php echo (int) $paged; ?></strong> dari <strong><?php echo (int) $total_pages; ?></strong> (Total: <?php echo (int) $total_customers; ?> pelanggan)
            </div>
            <div class="pagination-controls" style="display: flex; gap: 8px;">
                <?php if ( $paged > 1 ) : ?>
                    <a href="?page=owwc-customers&paged=<?php echo $paged - 1; ?>" class="owwc-admin-btn owwc-btn-secondary" style="text-decoration: none;">&laquo; Sebelumnya</a>
                <?php endif; ?>
                <?php if ( $paged < $total_pages ) : ?>
                    <a href="?page=owwc-customers&paged=<?php echo $paged + 1; ?>" class="owwc-admin-btn owwc-btn-secondary" style="text-decoration: none;">Selanjutnya &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
