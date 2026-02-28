<?php
/**
 * Template Frontend: Order Received (Thank You Page)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$order_id = (int) get_query_var( 'owwc_order_received' );

if ( ! $order_id ) {
    wp_redirect( home_url() );
    exit;
}

// Gunakan Repository
use OwwCommerce\Repositories\OrderRepository;
$order_repo = new OrderRepository();
$order = $order_repo->find( $order_id );

if ( ! $order ) {
    wp_redirect( home_url() );
    exit;
}

// Header Tema
get_header(); 
?>

<div class="owwc-order-received-wrap">
    
    <div class="owwc-or-header">
        <svg class="owwc-or-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <h1 class="owwc-or-title">Terima Kasih Atas Pesanan Anda!</h1>
        <p class="owwc-or-subtitle">Pesanan Anda telah kami terima dan sedang menunggu proses.</p>
    </div>

    <div class="owwc-or-info-box">
        <h2 class="owwc-or-info-title">Informasi Pesanan</h2>
        
        <ul class="owwc-or-details-list">
            <li>
                <span class="owwc-or-detail-label">Nomor Pesanan:</span>
                <strong class="owwc-or-detail-value">#<?php echo esc_html( $order->id ); ?></strong>
            </li>
            <li>
                <span class="owwc-or-detail-label">Tanggal:</span>
                <strong class="owwc-or-detail-value"><?php echo wp_date( 'd F Y', strtotime( $order->created_at ) ); ?></strong>
            </li>
            <li>
                <span class="owwc-or-detail-label">Total Tagihan:</span>
                <strong class="owwc-or-detail-value owwc-text-primary"><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $order->total_amount ) ); ?></strong>
            </li>
            <li>
                <span class="owwc-or-detail-label">Metode Pembayaran:</span>
                <strong class="owwc-or-detail-value"><?php echo esc_html( strtoupper( $order->payment_method ) ); ?></strong>
            </li>
            <li>
                <span class="owwc-or-detail-label">Status Pesanan:</span>
                <?php
                $status_labels = [
                    'pending'               => 'Menunggu Pembayaran',
                    'on-hold'               => 'Menunggu Pembayaran',
                    'awaiting-confirmation' => 'Menunggu Konfirmasi',
                    'processing'            => 'Sedang Diproses',
                    'completed'             => 'Selesai',
                    'cancelled'             => 'Dibatalkan',
                    'failed'                => 'Gagal',
                ];
                $status_colors = [
                    'pending'               => ['bg' => '#fef3c7', 'text' => '#92400e'], // Amber
                    'on-hold'               => ['bg' => '#fef3c7', 'text' => '#92400e'], // Amber
                    'awaiting-confirmation' => ['bg' => '#ede9fe', 'text' => '#5b21b6'], // Purple
                    'processing'            => ['bg' => '#dcfce7', 'text' => '#166534'], // Green
                    'completed'             => ['bg' => '#dbeafe', 'text' => '#1e40af'], // Blue
                    'cancelled'             => ['bg' => '#f1f5f9', 'text' => '#475569'], // Gray
                    'failed'                => ['bg' => '#fee2e2', 'text' => '#991b1b'], // Red
                ];
                $current_status = $order->status;
                $label = $status_labels[ $current_status ] ?? ucfirst( $current_status );
                $colors = $status_colors[ $current_status ] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                ?>
                <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; background: <?php echo $colors['bg']; ?>; color: <?php echo $colors['text']; ?>;">
                    <?php echo esc_html( $label ); ?>
                </span>
            </li>
        </ul>

        <?php if ( $order->payment_method === 'bacs' ) : 
            $bacs_instructions = get_option( 'owwc_bacs_instructions', 'Silakan transfer sejumlah total tagihan ke rekening berikut:' );
            $bacs_account      = get_option( 'owwc_bacs_account', "Bank BCA\nNo. Rek: 1234567890\nA.n: PT OwwCommerce Indonesia" );
        ?>
            <div class="owwc-bacs-instructions-box">
                <h3 class="owwc-bacs-title">Instruksi Transfer Bank</h3>
                <p class="owwc-bacs-desc"><?php echo esc_html( $bacs_instructions ); ?></p>
                <p class="owwc-bacs-amount">Jumlah: <strong><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $order->total_amount ) ); ?></strong></p>
                <pre class="owwc-bacs-account"><?php echo esc_html( $bacs_account ); ?></pre>
                <p class="owwc-bacs-note">* Jaga kerahasiaan bukti transfer Anda. Pesanan akan diproses setelah dana masuk.</p>
            </div>

            <!-- Form Konfirmasi Pembayaran -->
            <?php if ( in_array( $order->status, ['pending', 'on-hold'] ) ) : ?>
                <div id="owwc-confirmation-section" style="margin-top: 30px; padding: 30px; background: #fff; border: 1px solid #eaeaea; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; font-size: 20px; font-weight: 700; color: #111; margin-bottom: 10px;">Konfirmasi Pembayaran</h3>
                    <p style="font-size: 14px; color: #666; margin-bottom: 25px;">Sudah melakukan transfer? Silakan lengkapi data di bawah agar pesanan Anda segera diproses.</p>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 8px;">Unggah Bukti Pembayaran (Pilih Gambar):</label>
                        <div style="position: relative;">
                            <input type="file" id="owwc-confirm-proof" accept="image/*" style="opacity: 0; position: absolute; inset: 0; width: 100%; height: 100%; cursor: pointer; z-index: 2;">
                            <div id="owwc-file-dummy" style="padding: 20px 15px; border: 2px dashed #ddd; border-radius: 8px; text-align: center; background: #fafafa; transition: all 0.2s;">
                                <svg style="width: 32px; height: 32px; color: #999; margin-bottom: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <div style="font-size: 14px; color: #777; font-weight: 500;">Klik atau seret file gambar di sini</div>
                                <div id="owwc-filename" style="font-size: 13px; color: var(--owwc-primary); font-weight: 700; margin-top: 10px; display: none; background: #fefce8; padding: 5px 10px; border-radius: 4px; display: inline-block;"></div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 8px;">Catatan Tambahan (Opsional):</label>
                        <textarea id="owwc-confirm-note" placeholder="Contoh: Transfer atas nama Budi via BCA" style="width: 100%; min-height: 100px; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; background: #fff; box-shadow: inset 0 1px 3px rgba(0,0,0,0.02); resize: vertical; transition: border-color 0.2s;"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <button type="button" id="owwc-submit-confirmation" class="owwc-btn" style="padding: 14px; border-radius: 8px; font-weight: 700; font-size: 14px; background: #000; color: #fff; cursor: pointer; border: none; transition: transform 0.1s;">Kirim Konfirmasi</button>
                        
                        <?php
                        $wa_number = get_option( 'owwc_whatsapp_number', '' );
                        if ( $wa_number ) :
                            $order_url = home_url( add_query_arg( [], $GLOBALS['wp']->request ) );
                            $message = sprintf(
                                "Halo Admin, saya ingin konfirmasi pembayaran.\n\nNomor Pesanan: #%s\nTotal: %s\nMetode: %s\n\nLink Pesanan: %s",
                                $order->id,
                                \OwwCommerce\Core\Formatter::format_price( $order->total_amount ),
                                strtoupper( $order->payment_method ),
                                $order_url
                            );
                            $wa_link = "https://wa.me/" . preg_replace('/[^0-9]/', '', $wa_number) . "?text=" . urlencode($message);
                        ?>
                            <a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #25D366; color: #fff; text-decoration: none; padding: 14px; border-radius: 8px; font-weight: 700; font-size: 14px; transition: opacity 0.2s;">
                                <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                    <div id="owwc-confirm-message" class="owwc-confirm-msg"></div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const btn = document.getElementById('owwc-submit-confirmation');
                    const noteInput = document.getElementById('owwc-confirm-note');
                    const proofInput = document.getElementById('owwc-confirm-proof');
                    const msgBox = document.getElementById('owwc-confirm-message');
                    const fileDummy = document.getElementById('owwc-file-dummy');
                    const filenameDisplay = document.getElementById('owwc-filename');

                    if (proofInput) {
                        proofInput.addEventListener('change', function() {
                            if (this.files.length > 0) {
                                filenameDisplay.innerText = 'File terpilih: ' + this.files[0].name;
                                filenameDisplay.style.display = 'block';
                                fileDummy.classList.add('has-file');
                            } else {
                                filenameDisplay.style.display = 'none';
                                fileDummy.classList.remove('has-file');
                            }
                        });
                    }

                    if (btn) {
                        btn.addEventListener('click', async function() {
                            if (proofInput.files.length === 0) {
                                msgBox.style.display = 'block';
                                msgBox.innerText = 'Silakan pilih file bukti pembayaran terlebih dahulu.';
                                msgBox.className = 'owwc-confirm-msg error';
                                return;
                            }

                            btn.disabled = true;
                            const originalText = btn.innerText;
                            btn.innerText = 'Mengirim...';
                            btn.style.opacity = '0.7';
                            msgBox.style.display = 'none';

                            const formData = new FormData();
                            formData.append('order_id', <?php echo (int) $order->id; ?>);
                            formData.append('note', noteInput.value);
                            formData.append('proof', proofInput.files[0]);

                            try {
                                const response = await fetch('<?php echo esc_url_raw( rest_url( 'owwc/v1/checkout/confirm' ) ); ?>', {
                                    method: 'POST',
                                    body: formData
                                });

                                const data = await response.json();

                                msgBox.style.display = 'block';
                                if (response.ok && data.success) {
                                    msgBox.innerText = data.message;
                                    msgBox.className = 'owwc-confirm-msg success';
                                    
                                    // Sembunyikan form setelah sukses
                                    setTimeout(() => {
                                        document.getElementById('owwc-confirmation-section').innerHTML = `
                                            <div class="owwc-confirmation-success-state">
                                                <div class="owwc-cs-icon">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                                <p class="owwc-cs-title">Konfirmasi Berhasil!</p>
                                                <p class="owwc-cs-subtitle">Status Anda: <span class="owwc-badge processing">Menunggu Konfirmasi</span></p>
                                                <p class="owwc-cs-desc">Tim kami akan segera memverifikasi pembayaran Anda. Terima kasih telah berbelanja!</p>
                                            </div>
                                        `;
                                    }, 1500);
                                } else {
                                    msgBox.innerText = data.message || 'Gagal mengirim konfirmasi.';
                                    msgBox.className = 'owwc-confirm-msg error';
                                    btn.disabled = false;
                                    btn.innerText = originalText;
                                    btn.style.opacity = '1';
                                }
                            } catch (error) {
                                console.error('Confirmation error:', error);
                                msgBox.style.display = 'block';
                                msgBox.innerText = 'Terjadi kesalahan jaringan.';
                                msgBox.className = 'owwc-confirm-msg error';
                                btn.disabled = false;
                                btn.innerText = originalText;
                                btn.style.opacity = '1';
                            }
                        });
                    }
                });
                </script>
            <?php endif; ?>

        <?php elseif ( $order->payment_method === 'cod' ) : ?>
            <div class="owwc-cod-instructions-box">
                <h3 class="owwc-cod-title">Instruksi COD (Bayar di Tempat)</h3>
                <p class="owwc-cod-desc">Mohon siapkan uang tunai sejumlah <strong><?php echo esc_html( \OwwCommerce\Core\Formatter::format_price( $order->total_amount ) ); ?></strong> saat kurir kami tiba di alamat Anda.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="owwc-or-footer">
        <a href="<?php echo esc_url( home_url() ); ?>" class="owwc-btn">Kembali ke Beranda</a>
    </div>

</div>

<?php 
// Footer Tema
get_footer(); 
?>
