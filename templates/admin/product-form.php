<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 id="form-title"><?php esc_html_e( 'Tambah Produk Baru', 'owwcommerce' ); ?></h1>
        <a href="?page=owwc-products" class="owwc-admin-btn owwc-btn-outline" style="padding: 8px 16px;">Kembali ke Daftar</a>
    </div>

    <form id="owwc-add-product-form">
        <input type="hidden" id="prod-id" value="<?php echo esc_attr( $_GET['id'] ?? '' ); ?>">

        <div class="owwc-admin-2col-layout" style="margin-top: 20px;">
            <!-- Kolom Main (Kiri: approx 70%) -->
            <div class="owwc-admin-main">
                <div class="owwc-admin-card" style="margin-bottom: 24px;">
                    <div class="form-field">
                        <label for="prod-type">Tipe Produk</label>
                        <select id="prod-type" class="owwc-admin-select">
                            <option value="simple">Simple Product (Produk Biasa)</option>
                            <option value="variable">Variable Product (Produk dengan Variasi)</option>
                        </select>
                    </div>
                </div>

                <div class="owwc-admin-card">
                    <h2 style="font-size: 16px;">Detail Produk</h2>
                    
                    <div class="form-field" style="margin-bottom: 24px;">
                        <label for="prod-name">Nama Produk <span style="color: var(--owwc-admin-danger);">*</span></label>
                        <input type="text" id="prod-name" required class="owwc-admin-input" placeholder="Masukkan nama produk">
                    </div>

                    <div class="form-field" style="margin-bottom: 24px;">
                        <label for="prod-description">Deskripsi Produk</label>
                        <textarea id="prod-description" class="owwc-admin-textarea" rows="6" placeholder="Tuliskan keterangan detail mengenai produk ini..."></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 24px; margin-bottom: 24px;">
                        <div class="form-field" style="flex: 1;">
                            <label for="prod-price">Harga Standard (Rp) <span style="color: var(--owwc-admin-danger);">*</span></label>
                            <input type="number" id="prod-price" required class="owwc-admin-input" step="0.01" placeholder="0">
                        </div>
                        <div class="form-field" style="flex: 1;">
                            <label for="prod-sale-price">Harga Diskon (Rp)</label>
                            <input type="number" id="prod-sale-price" class="owwc-admin-input" step="0.01" placeholder="Harga coret...">
                        </div>
                    </div>

                    <div style="display: flex; gap: 24px;">
                        <div class="form-field" style="flex: 1;">
                            <label for="prod-sku">SKU (Kode Item)</label>
                            <input type="text" id="prod-sku" class="owwc-admin-input" placeholder="Contoh: BUKU-001">
                        </div>
                        <div class="form-field" style="flex: 1;">
                            <label for="prod-stock">Stok Gudang</label>
                            <input type="number" id="prod-stock" class="owwc-admin-input" value="0">
                        </div>
                    </div>
                </div>

                <!-- Section: Attributes & Variations (Hidden for simple product) -->
                <div id="owwc-variable-product-section" style="display: none; margin-top: 24px;">
                    <div class="owwc-admin-card">
                        <div class="owwc-tabs" style="display: flex; gap: 20px; border-bottom: 1px solid var(--owwc-admin-border); margin-bottom: 20px;">
                            <a href="#" class="owwc-tab-link active" data-target="owwc-tab-attributes" style="padding-bottom: 10px; text-decoration: none; color: inherit; font-weight: 600; border-bottom: 2px solid var(--owwc-admin-primary);">Atribut</a>
                            <a href="#" class="owwc-tab-link" data-target="owwc-tab-variations" style="padding-bottom: 10px; text-decoration: none; color: #666; font-weight: 600;">Variasi</a>
                            <a href="#" class="owwc-tab-link" data-target="owwc-tab-linked" style="padding-bottom: 10px; text-decoration: none; color: #666; font-weight: 600;">Linked Products</a>
                        </div>

                        <!-- Tab Atribut -->
                        <div id="owwc-tab-attributes" class="owwc-tab-content">
                            <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Pilih atribut global (seperti Warna atau Ukuran) untuk produk ini.</p>
                            
                            <div id="owwc-selected-attributes" style="margin-bottom: 20px;">
                                <!-- List atribut terpilih muncul di sini -->
                            </div>

                            <div style="display: flex; gap: 10px; align-items: flex-end;">
                                <div class="form-field" style="flex: 1; margin-bottom: 0;">
                                    <label>Tambah Atribut Global</label>
                                    <select id="owwc-available-attributes" class="owwc-admin-select">
                                        <option value="">-- Pilih Atribut --</option>
                                    </select>
                                </div>
                                <button type="button" id="owwc-add-attribute-btn" class="owwc-admin-btn owwc-btn-outline" style="padding: 10px 15px;">Tambah</button>
                            </div>
                        </div>

                        <!-- Tab Variasi -->
                        <div id="owwc-tab-variations" class="owwc-tab-content" style="display: none;">
                            <div id="owwc-variations-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <p style="font-size: 13px; color: #666; margin: 0;">Kelola data unik (harga, stok, SKU) untuk setiap kombinasi atribut.</p>
                                <button type="button" id="owwc-generate-variations-btn" class="owwc-admin-btn owwc-btn-secondary" style="font-size: 12px; padding: 6px 12px;">Generate Variations</button>
                            </div>

                            <div id="owwc-variations-list">
                                <!-- List variasi muncul di sini -->
                                <p style="text-align: center; padding: 20px; color: #999; border: 1px dashed #ddd; border-radius: 4px;">Tambahkan atribut terlebih dahulu, lalu klik "Generate" untuk membuat variasi.</p>
                            </div>
                        </div>

                        <!-- Tab Linked Products -->
                        <div id="owwc-tab-linked" class="owwc-tab-content" style="display: none;">
                            <div class="form-field" style="margin-bottom: 20px;">
                                <label for="prod-upsells">Upsells</label>
                                <input type="text" id="prod-upsells" class="owwc-admin-input" placeholder="ID produk dipisahkan koma (misal: 12, 15)">
                                <p style="font-size: 12px; color: #666;">Produk yang Anda rekomendasikan sebagai pengganti produk saat ini (biasanya lebih mahal atau berkualitas lebih baik).</p>
                            </div>
                            <div class="form-field">
                                <label for="prod-cross-sells">Cross-sells</label>
                                <input type="text" id="prod-cross-sells" class="owwc-admin-input" placeholder="ID produk dipisahkan koma (misal: 8, 21)">
                                <p style="font-size: 12px; color: #666;">Produk yang Anda promosikan di keranjang belanja berdasarkan produk saat ini.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Sidebar (Kanan: approx 30%) -->
            <div class="owwc-admin-sidebar">
                <!-- Metabox Publikasi / Aksi -->
                <div class="owwc-admin-card">
                    <h2 style="font-size: 16px;">Publikasi Aksi</h2>
                    
                    <div class="form-field" style="margin-bottom: 20px;">
                        <label for="prod-status">Status Tayang</label>
                        <select id="prod-status" class="owwc-admin-select">
                            <option value="publish">Publish (Tayang Umum)</option>
                            <option value="draft">Draft (Mode Konsep)</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <span id="prod-form-message" style="display: none; color: var(--owwc-admin-success); font-weight: 500; font-size: 14px;">Tersimpan!</span>
                    </div>
                    <div>
                        <button type="submit" id="prod-submit-btn" class="owwc-admin-btn" style="width: 100%; font-size: 15px; padding: 12px;">Simpan Produk</button>
                    </div>
                </div>

                <!-- Metabox Gambar Produk -->
                <div class="owwc-admin-card">
                    <h2 style="font-size: 16px;">Gambar Utama</h2>
                    <div class="owwc-image-upload-wrapper" style="text-align: center;">
                        <div id="prod-image-preview" style="display: none; margin-bottom: 15px; width: 100%; border-radius: var(--owwc-admin-radius-sm); border: 1px solid var(--owwc-admin-border); overflow: hidden; background: #fafafa;">
                            <img id="prod-image-thumb" src="" alt="Preview Gambar" style="width: 100%; height: auto; display: block; object-fit: cover;">
                        </div>
                        <a href="#" id="prod-remove-image" style="display: none; color: var(--owwc-admin-danger); font-size: 13px; text-decoration: none; margin-bottom: 15px;">&times; Hapus Gambar</a>
                        
                        <input type="hidden" id="prod-image" value="">
                        
                        <button type="button" class="owwc-admin-btn owwc-btn-outline" id="prod-upload-btn" style="width: 100%; padding: 12px; border-style: dashed; border-width: 2px;">
                            <span class="dashicons dashicons-camera" style="vertical-align: middle; margin-right: 5px;"></span> Set Gambar Produk
                        </button>
                    </div>
                </div>

                <!-- Metabox Galeri Produk -->
                <div class="owwc-admin-card" style="margin-top: 24px;">
                    <h2 style="font-size: 16px;">Galeri Produk</h2>
                    <div id="owwc-gallery-container" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">
                        <!-- Gallery items will be added here -->
                    </div>
                    <button type="button" class="owwc-admin-btn owwc-btn-outline" id="owwc-add-gallery-btn" style="width: 100%; padding: 12px; border-style: dashed; border-width: 2px;">
                        <span class="dashicons dashicons-images-alt2" style="vertical-align: middle; margin-right: 5px;"></span> Tambah Galeri
                    </button>
                    <input type="hidden" id="prod-gallery" value="">
                </div>
            </div>
        </div>
    </form>
</div>
