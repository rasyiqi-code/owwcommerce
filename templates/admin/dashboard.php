<?php
/**
 * Template Halaman Dashboard Overview OwwCommerce
 * Komentar dan deskripsi menggunakan Bahasa Indonesia.
 */
?>
<div class="owwc-admin-wrap">
    <div class="owwc-admin-header owwc-mb-3">
        <div>
            <h1><?php esc_html_e( 'Dashboard Overview', 'owwcommerce' ); ?></h1>
            <p class="owwc-text-muted owwc-mt-1 owwc-mb-0">Ringkasan performa toko OwwCommerce Anda secara real-time.</p>
        </div>
    </div>

    <!-- Quick Stats Cards (Grid 3 Kolom Compact) -->
    <div class="owwc-grid-3col owwc-mb-3">
        <div class="owwc-admin-card owwc-p-3" style="border-left: 4px solid var(--owwc-admin-primary); margin-bottom: 0;">
            <p class="owwc-text-muted owwc-mb-2">Total Pendapatan</p>
            <h2 id="stat-revenue" class="owwc-mb-0" style="font-size: 22px; font-weight: 700; border-bottom: none; padding-bottom: 0;">
                <?php echo esc_html( get_option('owwc_currency_symbol', 'Rp') ); ?> 0
            </h2>
        </div>
        <div class="owwc-admin-card owwc-p-3" style="border-left: 4px solid #10b981; margin-bottom: 0;">
            <p class="owwc-text-muted owwc-mb-2">Total Pesanan</p>
            <h2 id="stat-orders" class="owwc-mb-0" style="font-size: 22px; font-weight: 700; border-bottom: none; padding-bottom: 0;">0</h2>
        </div>
        <div class="owwc-admin-card owwc-p-3" style="border-left: 4px solid #3b82f6; margin-bottom: 0;">
            <p class="owwc-text-muted owwc-mb-2">Total Produk</p>
            <h2 id="stat-products" class="owwc-mb-0" style="font-size: 22px; font-weight: 700; border-bottom: none; padding-bottom: 0;">0</h2>
        </div>
    </div>

    <!-- Layout Utama (Dua Kolom: Chart + Sidebar Produk Terlaris) -->
    <div class="owwc-admin-2col-layout">
        <!-- Sales Chart -->
        <div class="owwc-admin-card">
            <h3 class="owwc-mb-3">Grafik Pendapatan (7 Hari Terakhir)</h3>
            <div style="height: 220px; width: 100%;">
                <canvas id="owwc-sales-chart"></canvas>
            </div>
        </div>

        <!-- Top Products -->
        <div class="owwc-admin-card">
            <h3 class="owwc-mb-3">Produk Terlaris</h3>
            <div id="top-products-list">
                <p class="owwc-text-muted">Sedang memuat data...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS khusus untuk item list produk terlaris agar lebih rapat */
.owwc-top-product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0; /* Padding diperkecil dari 12px ke 8px agar lebih compact */
    border-bottom: 1px solid #eee;
}
.owwc-top-product-item:last-child {
    border-bottom: none;
}
.owwc-top-product-meta {
    font-size: 11px;
    color: #888;
}
</style>
