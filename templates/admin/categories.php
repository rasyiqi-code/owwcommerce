<div class="owwc-admin-wrap">
    <div class="owwc-admin-header">
        <h1><?php esc_html_e( 'Kategori Produk', 'owwcommerce' ); ?></h1>
    </div>
    
    <div id="owwc-categories-app">
        <!-- React/Vue or Vanilla JS will mount here. For now, simple Vanilla JS UI -->
        <div class="owwc-admin-split">
            
            <!-- Tambah Kategori Form -->
            <div class="owwc-admin-split-form">
                <div class="owwc-admin-card">
                    <h2>Tambah Kategori Baru</h2>
                    <form id="owwc-add-category-form">
                        <div class="form-field">
                            <label for="cat-name">Nama Kategori *</label>
                            <input type="text" id="cat-name" required class="owwc-admin-input">
                        </div>
                        <div class="form-field">
                            <label for="cat-parent">Parent Kategori</label>
                            <select id="cat-parent" class="owwc-admin-select">
                                <option value="0">Tidak Ada (Top Level)</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="cat-desc">Deskripsi</label>
                            <textarea id="cat-desc" rows="4" class="owwc-admin-textarea"></textarea>
                        </div>
                        <div style="margin-top: 20px;">
                            <button type="submit" class="owwc-admin-btn" style="width:100%;">Tambah Kategori</button>
                        </div>
                    <span id="cat-form-message" style="margin-left: 10px; color: green; display: none;">Tersimpan!</span>
                </form>
            </div>

            <!-- List Kategori -->
            <div class="owwc-admin-split-table">
                <div class="owwc-admin-card" style="padding: 0; overflow:hidden;">
                    <table class="owwc-admin-table" style="border: none; border-radius: 0; box-shadow: none;">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Slug</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="owwc-categories-body">
                            <tr><td colspan="4" style="padding: 24px;">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
