<?php
/**
 * Template Halaman Katalog Produk OwwCommerce
 * Komentar dan deskripsi menggunakan Bahasa Indonesia.
 */
?>
<div class="owwc-admin-wrap">
    <div class="owwc-admin-header owwc-mb-3">
        <h1><?php esc_html_e( 'Katalog Produk', 'owwcommerce' ); ?></h1>
        <div class="owwc-flex-align-center owwc-flex-gap-2">
            <!-- Helper Update Stok Massal Compact -->
            <div id="owwc-bulk-set-all-wrap" class="owwc-flex-align-center owwc-flex-gap-2 owwc-p-2" style="display: none; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: var(--owwc-admin-radius); padding-top: 2px; padding-bottom: 2px; height: 26px;">
                <input type="number" id="owwc-bulk-stock-all-input" class="owwc-admin-input owwc-text-xs" placeholder="Stok..." style="width: 70px; height: 22px; padding: 2px 6px;">
                <button id="owwc-bulk-stock-all-btn" class="owwc-admin-btn owwc-btn-secondary owwc-admin-btn-sm" style="height: 22px; padding: 0 8px; line-height: 22px; background: white;">Set Semua</button>
            </div>
            <button id="owwc-bulk-stock-btn" class="owwc-admin-btn owwc-btn-secondary owwc-admin-btn-sm">Update Stok Massal</button>
            <a href="?page=owwc-products&action=add" class="owwc-admin-btn owwc-admin-btn-sm" style="text-decoration: none;">Tambah Produk Baru</a>
        </div>
    </div>
    
    <!-- Render Table via Javascript API -->
    <div id="owwc-products-app">
        <div class="owwc-admin-card owwc-p-0" style="overflow: hidden;">
            <p class="owwc-text-muted owwc-p-4" style="text-align: center; margin: 0;">Memuat data produk...</p>
        </div>
    </div>
</div>
