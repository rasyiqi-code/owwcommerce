<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="margin-bottom: 30px;">
        <h1><?php esc_html_e( 'Dashboard Overview', 'owwcommerce' ); ?></h1>
        <p style="color: #666; margin-top: 5px;">Ringkasan performa toko OwwCommerce Anda.</p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="owwc-dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div class="owwc-admin-card" style="border-left: 4px solid var(--owwc-admin-primary);">
            <p style="font-size: 13px; color: #666; margin-bottom: 10px;">Total Pendapatan</p>
            <h2 id="stat-revenue" style="font-size: 28px; margin: 0;"><?php echo esc_html( get_option('owwc_currency_symbol', 'Rp') ); ?> 0</h2>
        </div>
        <div class="owwc-admin-card" style="border-left: 4px solid #10b981;">
            <p style="font-size: 13px; color: #666; margin-bottom: 10px;">Total Pesanan</p>
            <h2 id="stat-orders" style="font-size: 28px; margin: 0;">0</h2>
        </div>
        <div class="owwc-admin-card" style="border-left: 4px solid #3b82f6;">
            <p style="font-size: 13px; color: #666; margin-bottom: 10px;">Total Produk</p>
            <h2 id="stat-products" style="font-size: 28px; margin: 0;">0</h2>
        </div>
    </div>

    <div class="owwc-dashboard-main-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <!-- Sales Chart -->
        <div class="owwc-admin-card">
            <h3 style="margin-bottom: 20px;">Grafik Pendapatan (7 Hari Terakhir)</h3>
            <div style="height: 300px; width: 100%;">
                <canvas id="owwc-sales-chart"></canvas>
            </div>
        </div>

        <!-- Top Products -->
        <div class="owwc-admin-card">
            <h3 style="margin-bottom: 20px;">Produk Terlaris</h3>
            <div id="top-products-list">
                <p style="color: #666; font-size: 13px;">Sedang memuat...</p>
            </div>
        </div>
    </div>
</div>

<style>
.owwc-top-product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}
.owwc-top-product-item:last-child {
    border-bottom: none;
}
.owwc-top-product-meta {
    font-size: 12px;
    color: #888;
}
</style>
