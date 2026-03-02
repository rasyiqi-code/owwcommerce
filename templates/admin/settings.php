<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="margin-bottom: 25px;">
        <h1><?php esc_html_e( 'Pengaturan OwwCommerce', 'owwcommerce' ); ?></h1>
    </div>

    <!-- Navigasi Tab (Premium Segmented Control) -->
    <div class="owwc-tabs">
        <a href="#umum" class="owwc-tab-item active" data-tab="umum">
            <span class="dashicons dashicons-admin-generic"></span>
            <?php esc_html_e( 'Umum', 'owwcommerce' ); ?>
        </a>
        <a href="#bayar-kirim" class="owwc-tab-item" data-tab="bayar-kirim">
            <span class="dashicons dashicons-cart"></span>
            <?php esc_html_e( 'Bayar & Kirim', 'owwcommerce' ); ?>
        </a>
        <a href="#floating-cart" class="owwc-tab-item" data-tab="floating-cart">
            <span class="dashicons dashicons-external"></span>
            <?php esc_html_e( 'Floating Cart', 'owwcommerce' ); ?>
        </a>
        <a href="#whatsapp" class="owwc-tab-item" data-tab="whatsapp">
            <span class="dashicons dashicons-whatsapp"></span>
            <?php esc_html_e( 'WhatsApp', 'owwcommerce' ); ?>
        </a>
        <a href="#halaman" class="owwc-tab-item" data-tab="halaman">
            <span class="dashicons dashicons-admin-page"></span>
            <?php esc_html_e( 'Halaman', 'owwcommerce' ); ?>
        </a>
        <a href="#migration" class="owwc-tab-item" data-tab="migration">
            <span class="dashicons dashicons-update"></span>
            <?php esc_html_e( 'Migrasi', 'owwcommerce' ); ?>
        </a>
        <a href="#import-export" class="owwc-tab-item" data-tab="import-export">
            <span class="dashicons dashicons-media-spreadsheet"></span>
            <?php esc_html_e( 'Impor / Ekspor', 'owwcommerce' ); ?>
        </a>
    </div>

    <!-- Flash message WP native -->
    <?php settings_errors(); ?>

    <form action="options.php" method="post" id="owwc-settings-form">
        <?php 
            settings_fields( 'owwc_settings_group' ); 
            do_settings_sections( 'owwc_settings_group' );
        ?>

        <div class="owwc-admin-2col-layout">
            <div class="owwc-admin-main">
                
                <!-- TAB 1: UMUM -->
                <div id="tab-umum" class="owwc-tab-content active">
                    <div class="owwc-admin-card">
                        <h3>Lokalisasi & Informasi Toko</h3>
                        <p class="owwc-admin-text-muted" style="margin-bottom: 20px;">Atur mata uang dan informasi identitas toko Anda.</p>

                        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
                            <div class="form-field" style="flex: 1; min-width: 120px;">
                                <label for="owwc_currency_symbol">Simbol Mata Uang</label>
                                <input type="text" name="owwc_currency_symbol" id="owwc_currency_symbol" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_currency_symbol', 'Rp') ); ?>">
                            </div>
                            <div class="form-field" style="flex: 1; min-width: 120px;">
                                <label for="owwc_thousand_sep">Pemisah Ribuan</label>
                                <input type="text" name="owwc_thousand_sep" id="owwc_thousand_sep" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_thousand_sep', '.') ); ?>">
                            </div>
                            <div class="form-field" style="flex: 1; min-width: 120px;">
                                <label for="owwc_decimal_sep">Pemisah Desimal</label>
                                <input type="text" name="owwc_decimal_sep" id="owwc_decimal_sep" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_decimal_sep', ',') ); ?>">
                            </div>
                        </div>

                        <div class="form-field" style="margin-bottom: 20px;">
                            <label for="owwc_store_name">Nama Toko</label>
                            <input type="text" name="owwc_store_name" id="owwc_store_name" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_store_name', get_bloginfo('name')) ); ?>">
                        </div>

                        <div class="form-field">
                            <label for="owwc_store_address">Alamat Toko</label>
                            <textarea name="owwc_store_address" id="owwc_store_address" class="owwc-admin-textarea" rows="4"><?php echo esc_textarea( get_option('owwc_store_address') ); ?></textarea>
                        </div>
                    </div>

                    <div class="owwc-admin-card" style="margin-top: 24px;">
                        <h3>Fitur Checkout</h3>
                        <p class="owwc-admin-text-muted" style="margin-bottom: 20px;">Aktifkan atau nonaktifkan fitur checkout sesuai kebutuhan toko Anda.</p>

                        <div class="form-field" style="margin-bottom: 15px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="owwc_enable_cart_checkout" value="1" <?php checked( get_option('owwc_enable_cart_checkout', 1), 1 ); ?>>
                                <span>Aktifkan Sistem Keranjang & Checkout Bawaan</span>
                            </label>
                        </div>

                        <div class="form-field" style="margin-bottom: 15px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="owwc_enable_external_checkout" value="1" <?php checked( get_option('owwc_enable_external_checkout', 1), 1 ); ?>>
                                <span>Aktifkan Checkout ke Marketplace (Shopee/Tokopedia/Lainnya)</span>
                            </label>
                            <p style="font-size: 12px; color: #888; padding-left: 25px;">Jika aktif, field "Marketplace Link" pada produk akan ditampilkan.</p>
                        </div>

                        <div class="form-field">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="owwc_enable_whatsapp_checkout" value="1" <?php checked( get_option('owwc_enable_whatsapp_checkout', 1), 1 ); ?>>
                                <span>Aktifkan Checkout via WhatsApp</span>
                            </label>
                            <p style="font-size: 12px; color: #888; padding-left: 25px;">Memberikan tombol pintasan bagi pelanggan untuk pesan via WhatsApp.</p>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: BAYAR & KIRIM -->
                <div id="tab-bayar-kirim" class="owwc-tab-content">
                    <div class="owwc-admin-card">
                        <h3>Pembayaran & Pengiriman</h3>
                        <p class="owwc-admin-text-muted" style="margin-bottom: 20px;">Atur opsi pembayaran, biaya pengiriman, dan ambang batas gratis ongkir.</p>
                        
                        <div class="form-field" style="margin-bottom: 20px;">
                            <label for="owwc_bacs_account">Detail Rekening Bank (BACS)</label>
                            <textarea name="owwc_bacs_account" id="owwc_bacs_account" class="owwc-admin-textarea" rows="4"><?php echo esc_textarea( get_option('owwc_bacs_account', "Bank BCA\nNo. Rek: 1234567890\nA.n: PT OwwCommerce Indonesia") ); ?></textarea>
                            <p style="font-size: 12px; color: #888; margin-top: 5px;">Masukkan nama bank, nomor rekening, dan nama pemilik untuk instruksi transfer.</p>
                        </div>

                        <div class="form-field" style="margin-bottom: 20px;">
                            <label for="owwc_bacs_instructions">Instruksi Pembayaran (BACS)</label>
                            <textarea name="owwc_bacs_instructions" id="owwc_bacs_instructions" class="owwc-admin-textarea" rows="3"><?php echo esc_textarea( get_option('owwc_bacs_instructions', 'Silakan transfer sejumlah total tagihan ke rekening berikut:') ); ?></textarea>
                        </div>

                        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 24px;">
                            <div class="form-field" style="flex: 1; min-width: 200px;">
                                <label for="owwc_flat_rate_cost">Biaya Pengiriman Flat Rate</label>
                                <input type="number" name="owwc_flat_rate_cost" id="owwc_flat_rate_cost" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_flat_rate_cost', 15000) ); ?>" min="0" step="500">
                            </div>
                            <div class="form-field" style="flex: 1; min-width: 200px;">
                                <label for="owwc_free_shipping_threshold">Ambang Batas Gratis Ongkir</label>
                                <input type="number" name="owwc_free_shipping_threshold" id="owwc_free_shipping_threshold" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_free_shipping_threshold', 0) ); ?>" min="0" step="1000">
                                <p style="font-size: 12px; color: #888; margin-top: 5px;">Isi 0 untuk menonaktifkan gratis ongkir.</p>
                            </div>
                        </div>

                        <div class="form-field">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="owwc_enable_cod" value="1" <?php checked( get_option('owwc_enable_cod'), 1 ); ?>>
                                <span>Aktifkan Pembayaran Bayar di Tempat (COD)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: FLOATING CART -->
                <div id="tab-floating-cart" class="owwc-tab-content">
                    <div class="owwc-admin-card">
                        <h3>Tampilan Keranjang Belanja (Floating Cart)</h3>
                        <p class="owwc-admin-text-muted" style="margin-bottom: 20px;">Sesuaikan bagaimana tombol keranjang mengambang muncul di antarmuka pengunjung Anda.</p>
                        
                        <div class="form-field" style="margin-bottom: 20px;">
                            <label for="owwc_floating_cart_style">Gaya Visual (Style)</label>
                            <select name="owwc_floating_cart_style" id="owwc_floating_cart_style" class="owwc-admin-select">
                                <option value="style-1" <?php selected( get_option('owwc_floating_cart_style', 'style-1'), 'style-1' ); ?>>Gaya 1 (Minimalis Gelap/Emas)</option>
                                <option value="style-2" <?php selected( get_option('owwc_floating_cart_style'), 'style-2' ); ?>>Gaya 2 (Elegan dengan Bayangan Besar)</option>
                                <option value="style-3" <?php selected( get_option('owwc_floating_cart_style'), 'style-3' ); ?>>Gaya 3 (Beranimasi Denyut Jantung)</option>
                            </select>
                        </div>

                        <div class="form-field" style="margin-bottom: 20px;">
                            <label for="owwc_floating_cart_position">Posisi Tombol</label>
                            <select name="owwc_floating_cart_position" id="owwc_floating_cart_position" class="owwc-admin-select">
                                <option value="bottom-right" <?php selected( get_option('owwc_floating_cart_position', 'bottom-right'), 'bottom-right' ); ?>>Kanan Bawah</option>
                                <option value="bottom-left" <?php selected( get_option('owwc_floating_cart_position'), 'bottom-left' ); ?>>Kiri Bawah</option>
                                <option value="top-right" <?php selected( get_option('owwc_floating_cart_position'), 'top-right' ); ?>>Kanan Atas</option>
                                <option value="top-left" <?php selected( get_option('owwc_floating_cart_position'), 'top-left' ); ?>>Kiri Atas</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="owwc_floating_cart_icon">Ikon Keranjang</label>
                            <select name="owwc_floating_cart_icon" id="owwc_floating_cart_icon" class="owwc-admin-select">
                                <option value="cart" <?php selected( get_option('owwc_floating_cart_icon', 'cart'), 'cart' ); ?>>Ikon Keranjang (Cart)</option>
                                <option value="bag" <?php selected( get_option('owwc_floating_cart_icon'), 'bag' ); ?>>Ikon Tas Belanja (Bag)</option>
                                <option value="basket" <?php selected( get_option('owwc_floating_cart_icon'), 'basket' ); ?>>Ikon Keranjang Anyaman (Basket)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: WHATSAPP -->
                <div id="tab-whatsapp" class="owwc-tab-content">
                    <div class="owwc-admin-card">
                        <h3>Fitur WhatsApp</h3>
                        <p class="owwc-admin-text-muted" style="margin-bottom: 20px;">Atur nomor WhatsApp untuk memudahkan pelanggan konfirmasi pembayaran.</p>
                        
                        <div class="form-field">
                            <label for="owwc_whatsapp_number">Nomor WhatsApp Admin</label>
                            <input type="text" name="owwc_whatsapp_number" id="owwc_whatsapp_number" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_whatsapp_number') ); ?>" placeholder="Contoh: 628123456789">
                            <p style="font-size: 12px; color: #888; margin-top: 5px;">Gunakan format internasional tanpa tanda + (contoh: 62812xxx).</p>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: HALAMAN -->
                <div id="tab-halaman" class="owwc-tab-content">
                    <div class="owwc-admin-card">
                        <h3>Permalink & Halaman Utama</h3>
                        <p class="owwc-admin-text-muted" style="margin-bottom: 20px;">Atur struktur URL dan tentukan halaman mana yang berfungsi sebagai Toko (Shop).</p>

                        <div class="form-field" style="margin-bottom: 20px;">
                            <label for="owwc_page_shop_id">Halaman Toko (Shop)</label>
                            <select name="owwc_page_shop_id" id="owwc_page_shop_id" class="owwc-admin-select">
                                <option value="0"><?php esc_html_e( '— Pilih Halaman —', 'owwcommerce' ); ?></option>
                                <?php 
                                    $pages = get_pages();
                                    $current_shop_id = (int) get_option('owwc_page_shop_id');
                                    foreach ( $pages as $page ) : 
                                ?>
                                    <option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $current_shop_id, $page->ID ); ?>>
                                        <?php echo esc_html( $page->post_title ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p style="font-size: 12px; color: #888; margin-top: 5px;">Halaman ini harus berisi shortcode [owwcommerce_shop].</p>
                        </div>

                        <div class="form-field" style="margin-bottom: 20px;">
                            <label for="owwc_page_myaccount_id">Halaman Akun Saya (My Account)</label>
                            <select name="owwc_page_myaccount_id" id="owwc_page_myaccount_id" class="owwc-admin-select">
                                <option value="0"><?php esc_html_e( '— Pilih Halaman —', 'owwcommerce' ); ?></option>
                                <?php 
                                    $current_myaccount_id = (int) get_option('owwc_page_myaccount_id');
                                    foreach ( $pages as $page ) : 
                                ?>
                                    <option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $current_myaccount_id, $page->ID ); ?>>
                                        <?php echo esc_html( $page->post_title ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p style="font-size: 12px; color: #888; margin-top: 5px;">Halaman ini harus berisi shortcode [owwcommerce_my_account].</p>
                        </div>

                        <div class="form-field">
                            <label style="margin-bottom: 8px;">Struktur URL Produk (Permalink)</label>
                            <div style="background: var(--owwc-admin-bg); border-left: 4px solid var(--owwc-admin-primary); padding: 15px; border-radius: var(--owwc-admin-radius-sm);">
                                <p style="margin: 0 0 10px 0; font-size: 13px; line-height: 1.5;">Struktur URL produk dikelola di halaman pengaturan Permalink WordPress agar lebih standar.</p>
                                <a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>" class="button button-secondary">Atur Permalink Produk &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 6: MIGRATION -->
                <div id="tab-migration" class="owwc-tab-content">
                    <div class="owwc-admin-card">
                        <h3>Migrasi WooCommerce</h3>
                        <p class="owwc-admin-text-muted" style="margin-bottom: 20px;">Pindahkan data Anda dari WooCommerce ke OwwCommerce dalam hitungan menit.</p>
                        
                        <div id="owwc-migration-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 15px; margin-bottom: 20px;">
                            <div style="background: var(--owwc-admin-bg); padding: 15px; border-radius: 8px; text-align: center; border: 1px solid var(--owwc-admin-border);">
                                <p style="margin: 0; color: var(--owwc-admin-text-muted); font-size: 11px; text-transform: uppercase;">Produk</p>
                                <h2 id="stats-products" style="margin: 5px 0 0;">0</h2>
                            </div>
                            <div style="background: var(--owwc-admin-bg); padding: 15px; border-radius: 8px; text-align: center; border: 1px solid var(--owwc-admin-border);">
                                <p style="margin: 0; color: var(--owwc-admin-text-muted); font-size: 11px; text-transform: uppercase;">Kategori</p>
                                <h2 id="stats-categories" style="margin: 5px 0 0;">0</h2>
                            </div>
                            <div style="background: var(--owwc-admin-bg); padding: 15px; border-radius: 8px; text-align: center; border: 1px solid var(--owwc-admin-border);">
                                <p style="margin: 0; color: var(--owwc-admin-text-muted); font-size: 11px; text-transform: uppercase;">Pesanan</p>
                                <h2 id="stats-orders" style="margin: 5px 0 0;">0</h2>
                            </div>
                            <div style="background: var(--owwc-admin-bg); padding: 15px; border-radius: 8px; text-align: center; border: 1px solid var(--owwc-admin-border);">
                                <p style="margin: 0; color: var(--owwc-admin-text-muted); font-size: 11px; text-transform: uppercase;">Pelanggan</p>
                                <h2 id="stats-customers" style="margin: 5px 0 0;">0</h2>
                            </div>
                        </div>

                        <div id="migration-progress-wrap" style="display: none; margin-bottom: 20px;">
                            <p id="migration-status-text" style="font-weight: 600; margin-bottom: 10px;">Sedang memigrasi Produk...</p>
                            <div style="width: 100%; height: 12px; background: #eee; border-radius: 6px; overflow: hidden;">
                                <div id="migration-bar" style="width: 0%; height: 100%; background: var(--owwc-admin-primary); transition: width 0.3s;"></div>
                            </div>
                            <p id="migration-percentage" style="text-align: right; font-size: 12px; color: #888; margin-top: 5px;">0%</p>
                        </div>

                        <div id="migration-actions">
                            <button id="owwc-start-migration" type="button" class="owwc-admin-btn">
                                <span class="dashicons dashicons-migrate" style="margin-right: 8px;"></span>
                                Mulai Migrasi Sekarang
                            </button>
                        </div>

                        <div id="owwc-migration-log-card" style="display: none; margin-top: 20px;">
                            <h4 style="margin-bottom: 10px;">Log Aktivitas</h4>
                            <div id="migration-log" style="max-height: 150px; overflow-y: auto; background: #111; color: #00ff00; padding: 12px; font-family: 'Courier New', Courier, monospace; font-size: 11px; border-radius: 4px;">
                                <div>> Menunggu perintah...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 7: IMPORT / EXPORT -->
                <div id="tab-import-export" class="owwc-tab-content">
                    <div class="owwc-admin-card">
                        <h3>Import Produk (Excel)</h3>
                        <p class="owwc-admin-text-muted" style="margin-bottom: 20px;">Unggah file Excel (.xlsx) untuk mengimpor produk secara massal.</p>
                        
                        <div id="owwc-import-form-container">
                            <div class="form-field">
                                <label for="excel-file">Pilih File Excel</label>
                                <input type="file" id="excel-file" name="excel_file" accept=".xlsx" class="owwc-admin-input" style="padding: 10px;">
                            </div>
                            
                            <div id="import-progress" style="display: none; margin-top: 20px;">
                                <div style="background: #f0f0f1; border-radius: 4px; height: 10px; overflow: hidden;">
                                    <div id="import-progress-bar" style="background: var(--owwc-admin-primary); width: 0%; height: 100%; transition: width 0.3s;"></div>
                                </div>
                                <p id="import-status" style="font-size: 12px; margin-top: 5px; color: #666;">Memproses...</p>
                            </div>

                            <button type="button" id="import-submit-btn" class="owwc-admin-btn" style="margin-top: 20px;">
                                <span class="dashicons dashicons-upload" style="margin-right: 8px;"></span>
                                Mulai Import
                            </button>
                        </div>

                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--owwc-admin-border);">
                            <h3>Export Produk</h3>
                            <p class="owwc-admin-text-muted" style="margin-bottom: 20px;">Unduh semua data produk Anda dalam format Excel.</p>
                            <button type="button" id="owwc-export-btn" class="owwc-admin-btn owwc-btn-secondary">
                                <span class="dashicons dashicons-download" style="margin-right: 8px;"></span>
                                Unduh Excel Produk
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tombol Simpan (Tetap muncul di bawah konten tab) -->
                <div style="margin-top: 20px; padding: 20px; background: #fff; border-radius: var(--owwc-admin-radius); border: 1px solid var(--owwc-admin-border); display: flex; justify-content: space-between; align-items: center;">
                    <div class="owwc-admin-text-muted" style="font-size: 13px;">
                        <span class="dashicons dashicons-info" style="font-size: 16px; margin-right: 5px;"></span>
                        <?php esc_html_e( 'Perubahan akan diterapkan secara instan setelah disimpan.', 'owwcommerce' ); ?>
                    </div>
                    <button type="submit" name="submit" id="submit" class="owwc-admin-btn" style="padding: 12px 35px;">
                        <span class="dashicons dashicons-saved" style="margin-right: 8px;"></span>
                        Simpan Perubahan
                    </button>
                </div>
            </div>

            <div class="owwc-admin-sidebar">
                <div class="owwc-admin-card" style="background: #fafafa; position: sticky; top: 50px;">
                    <h3>Bantuan</h3>
                    <p class="owwc-admin-text-muted" style="font-size: 13px; line-height: 1.6;">
                        Gunakan tab di atas untuk menelusuri berbagai kategori pengaturan. Pastikan untuk mengeklik <strong>Simpan Perubahan</strong> setelah melakukan pembaruan di tab mana pun.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
