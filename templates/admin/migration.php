<div class="owwc-admin-wrap">
    <div class="owwc-admin-header">
        <h1><?php esc_html_e( 'Migrasi WooCommerce', 'owwcommerce' ); ?></h1>
        <p style="color: #666; margin-top: 5px;">Pindahkan data Anda dari WooCommerce ke OwwCommerce dalam hitungan menit.</p>
    </div>

    <div class="owwc-admin-card" id="owwc-migration-stats-card">
        <h3>Statistik Data WooCommerce</h3>
        <div id="owwc-migration-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #eee;">
                <p style="margin: 0; color: #888; font-size: 12px; text-transform: uppercase;">Produk</p>
                <h2 id="stats-products" style="margin: 10px 0 0;">0</h2>
            </div>
            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #eee;">
                <p style="margin: 0; color: #888; font-size: 12px; text-transform: uppercase;">Kategori</p>
                <h2 id="stats-categories" style="margin: 10px 0 0;">0</h2>
            </div>
            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #eee;">
                <p style="margin: 0; color: #888; font-size: 12px; text-transform: uppercase;">Pesanan</p>
                <h2 id="stats-orders" style="margin: 10px 0 0;">0</h2>
            </div>
            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #eee;">
                <p style="margin: 0; color: #888; font-size: 12px; text-transform: uppercase;">Pelanggan</p>
                <h2 id="stats-customers" style="margin: 10px 0 0;">0</h2>
            </div>
        </div>

        <div style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;">
            <div id="migration-progress-wrap" style="display: none; margin-bottom: 20px;">
                <p id="migration-status-text" style="font-weight: 600; margin-bottom: 10px;">Sedang memigrasi Produk...</p>
                <div style="width: 100%; height: 20px; background: #eee; border-radius: 10px; overflow: hidden;">
                    <div id="migration-bar" style="width: 0%; height: 100%; background: var(--owwc-admin-primary); transition: width 0.3s;"></div>
                </div>
                <p id="migration-percentage" style="text-align: right; font-size: 12px; color: #888; margin-top: 5px;">0%</p>
            </div>

            <div id="migration-actions">
                <button id="owwc-start-migration" class="owwc-admin-btn" style="padding: 12px 30px;">
                    Mulai Migrasi Sekarang
                </button>
                <p style="font-size: 12px; color: #888; margin-top: 10px;">
                    * Proses ini akan menyalin data dari WooCommerce. Data asli Anda di WooCommerce tidak akan dihapus.
                </p>
            </div>
        </div>
    </div>

    <div class="owwc-admin-card" id="owwc-migration-log-card" style="display: none;">
        <h3>Log Aktivitas</h3>
        <div id="migration-log" style="max-height: 200px; overflow-y: auto; background: #000; color: #0f0; padding: 15px; font-family: monospace; font-size: 12px; border-radius: 4px;">
            <div>> Menunggu perintah...</div>
        </div>
    </div>
</div>
