<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="margin-bottom: 20px;">
        <h1><?php esc_html_e( 'Kategori Produk', 'owwcommerce' ); ?></h1>
    </div>

    <div class="owwc-admin-2col-layout" style="display: flex; gap: 24px; flex-wrap: wrap-reverse;">
        <!-- Kolom Kiri: Form Tambah Kategori (approx 30%) -->
        <div style="flex: 1 1 300px; max-width: 400px;">
            <div class="owwc-admin-card">
                <h2 style="font-size: 16px; margin-bottom: 15px;">Tambah Kategori Baru</h2>
                <form id="owwc-add-category-form">
                    <div class="form-field" style="margin-bottom: 20px;">
                        <label for="cat-name">Nama Kategori <span style="color: var(--owwc-admin-danger);">*</span></label>
                        <input type="text" id="cat-name" required class="owwc-admin-input" placeholder="Misal: Pakaian Pria">
                    </div>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label for="cat-parent">Kategori Induk</label>
                        <select id="cat-parent" class="owwc-admin-select">
                            <option value="0">Tidak Ada (Top Level)</option>
                            <!-- Diisi via JS -->
                        </select>
                    </div>

                    <div class="form-field" style="margin-bottom: 24px;">
                        <label for="cat-description">Deskripsi</label>
                        <textarea id="cat-description" class="owwc-admin-textarea" rows="4" placeholder="Deskripsi opsional..."></textarea>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <span id="cat-form-message" style="display: none; color: var(--owwc-admin-success); font-weight: 500; font-size: 14px;">Tersimpan!</span>
                    </div>

                    <button type="submit" class="owwc-admin-btn" style="width: 100%; font-size: 14px; padding: 10px;">Tambah Kategori</button>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Kategori (approx 70%) -->
        <div style="flex: 2 1 500px;">
            <div class="owwc-admin-card" style="padding: 0; overflow:x-auto;">
                <table class="owwc-admin-table" style="margin:0; border:none; width: 100%; min-width: 500px;">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Nama</th>
                            <th style="width: 40%;">Deskripsi</th>
                            <th style="width: 20%;">Slug</th>
                            <th style="width: 10%; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="owwc-categories-body">
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px; color: #666;">Memuat kategori...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
