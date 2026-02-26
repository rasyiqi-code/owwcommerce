<div class="owwc-admin-wrap">
    <div class="owwc-admin-header">
        <h1><?php esc_html_e( 'Products', 'owwcommerce' ); ?></h1>
    </div>

    <div class="owwc-admin-split">
        <!-- Form Tambah Produk -->
        <div class="owwc-admin-split-form">
            <div class="owwc-admin-card">
                <h2>Tambah Produk</h2>
                <form id="owwc-add-product-form">
                    <div class="form-field">
                        <label for="prod-name">Nama Produk *</label>
                        <input type="text" id="prod-name" required class="owwc-admin-input">
                    </div>
                    <div class="form-field">
                        <label for="prod-price">Harga (Rp) *</label>
                        <input type="number" id="prod-price" required class="owwc-admin-input" min="0">
                    </div>
                    <div class="form-field">
                        <label for="prod-stock">Stok Awal</label>
                        <input type="number" id="prod-stock" class="owwc-admin-input" min="0" value="10">
                    </div>
                    <div style="margin-top: 20px;">
                        <button type="submit" class="owwc-admin-btn" style="width:100%;">Simpan Produk</button>
                    </div>
                    <span id="prod-form-message" style="margin-left: 10px; color: var(--owwc-admin-success); display: none;">Berhasil Disimpan!</span>
                </form>
            </div>
        </div>

        <!-- Tabel Produk -->
        <div class="owwc-admin-split-table">
            <div class="owwc-admin-card" style="padding: 0; overflow:hidden;">
                <div id="owwc-products-app" style="padding: 24px;">
                    <p>Loading products...</p>
                </div>
            </div>
        </div>
    </div>
</div>
