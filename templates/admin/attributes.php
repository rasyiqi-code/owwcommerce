<div class="owwc-admin-wrap">
    <div class="owwc-admin-header">
        <h1><?php esc_html_e( 'Atribut Produk', 'owwcommerce' ); ?></h1>
    </div>

    <div class="owwc-admin-split">
        <!-- Kolom Kiri: Form -->
        <div class="owwc-admin-split-form">
            <div class="owwc-admin-card">
                <h3>Tambah Atribut Baru</h3>
                <form id="owwc-attribute-form">
                    <div class="owwc-form-group">
                        <label for="attr-name">Nama Atribut</label>
                        <input type="text" id="attr-name" name="name" class="owwc-admin-input" placeholder="Misal: Warna" required>
                        <p style="font-size: 11px; color: #666; margin-top: 5px;">Nama untuk atribut (tampil di frontend).</p>
                    </div>
                    
                    <button type="submit" class="owwc-admin-btn" style="width: 100%;">Tambah Atribut</button>
                </form>
            </div>

            <!-- Form Tambah Term (Hidden by default, shown when attr selected) -->
            <div id="owwc-term-form-card" class="owwc-admin-card" style="display: none;">
                <h3>Tambah Nilai untuk <span id="current-attr-name"></span></h3>
                <form id="owwc-term-form">
                    <input type="hidden" id="current-attr-id" name="attribute_id">
                    <div class="owwc-form-group">
                        <label for="term-name">Nama Nilai</label>
                        <input type="text" id="term-name" name="name" class="owwc-admin-input" placeholder="Misal: Merah" required>
                    </div>
                    <button type="submit" class="owwc-admin-btn owwc-btn-secondary" style="width: 100%;">Tambah Nilai</button>
                    <button type="button" id="cancel-term-mode" class="owwc-admin-btn owwc-btn-danger" style="width: 100%; margin-top: 10px; font-size: 12px; background: transparent;">Batal</button>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel -->
        <div class="owwc-admin-split-table">
            <div class="owwc-admin-card" style="padding: 0;">
                <table class="owwc-admin-table" id="owwc-attributes-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th>Nilai (Terms)</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data dimuat via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
