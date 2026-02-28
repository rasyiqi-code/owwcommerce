<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1><?php esc_html_e( 'Katalog Produk', 'owwcommerce' ); ?></h1>
        <div style="display: flex; gap: 10px;">
            <button id="owwc-bulk-stock-btn" class="owwc-admin-btn owwc-btn-secondary">Update Stok Massal</button>
            <a href="?page=owwc-products&action=add" class="owwc-admin-btn" style="text-decoration: none;">Tambah Produk Baru</a>
        </div>
    </div>
    
    <!-- Render Table via Javascript API -->
    <div id="owwc-products-app">
        <div class="owwc-admin-card" style="padding: 0; overflow:hidden;">
            <p style="padding: 24px; text-align: center; color: #666;">Memuat data produk...</p>
        </div>
    </div>
</div>
