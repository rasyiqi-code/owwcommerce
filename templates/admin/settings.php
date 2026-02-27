<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="margin-bottom: 20px;">
        <h1><?php esc_html_e( 'Pengaturan OwwCommerce', 'owwcommerce' ); ?></h1>
    </div>

    <!-- Gunakan sistem flash message WP native jika ada -->
    <?php settings_errors(); ?>

    <div class="owwc-admin-2col-layout" style="display: flex; gap: 24px;">
        <div class="owwc-admin-main" style="flex: 2;">
            <div class="owwc-admin-card">
                <form action="options.php" method="post">
                    <?php 
                        // Output nonce, action, dsb untuk form API pengaturan
                        settings_fields( 'owwc_settings_group' ); 
                        // Output bagian pengaturan opsional jika ada
                        do_settings_sections( 'owwc_settings_group' );
                    ?>

                    <h2 style="font-size: 16px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--owwc-admin-border);">Tampilan Keranjang Belanja (Floating Cart)</h2>
                    <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Sesuaikan bagaimana tombol keranjang mengambang muncul di antarmuka pengunjung Anda.</p>
                    
                    <div class="form-field" style="margin-bottom: 20px;">
                        <label for="owwc_floating_cart_style" style="display: block; margin-bottom: 8px; font-weight: 500;">Gaya Visual (Style)</label>
                        <select name="owwc_floating_cart_style" id="owwc_floating_cart_style" class="owwc-admin-select">
                            <option value="style-1" <?php selected( get_option('owwc_floating_cart_style', 'style-1'), 'style-1' ); ?>>Gaya 1 (Minimalis Gelap/Emas)</option>
                            <option value="style-2" <?php selected( get_option('owwc_floating_cart_style'), 'style-2' ); ?>>Gaya 2 (Elegan dengan Bayangan Besar)</option>
                            <option value="style-3" <?php selected( get_option('owwc_floating_cart_style'), 'style-3' ); ?>>Gaya 3 (Beranimasi Denyut Jantung)</option>
                        </select>
                    </div>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label for="owwc_floating_cart_position" style="display: block; margin-bottom: 8px; font-weight: 500;">Posisi Tombol</label>
                        <select name="owwc_floating_cart_position" id="owwc_floating_cart_position" class="owwc-admin-select">
                            <option value="bottom-right" <?php selected( get_option('owwc_floating_cart_position', 'bottom-right'), 'bottom-right' ); ?>>Kanan Bawah</option>
                            <option value="bottom-left" <?php selected( get_option('owwc_floating_cart_position'), 'bottom-left' ); ?>>Kiri Bawah</option>
                            <option value="top-right" <?php selected( get_option('owwc_floating_cart_position'), 'top-right' ); ?>>Kanan Atas</option>
                            <option value="top-left" <?php selected( get_option('owwc_floating_cart_position'), 'top-left' ); ?>>Kiri Atas</option>
                        </select>
                    </div>

                    <div class="form-field" style="margin-bottom: 24px;">
                        <label for="owwc_floating_cart_icon" style="display: block; margin-bottom: 8px; font-weight: 500;">Ikon Keranjang</label>
                        <select name="owwc_floating_cart_icon" id="owwc_floating_cart_icon" class="owwc-admin-select">
                            <option value="cart" <?php selected( get_option('owwc_floating_cart_icon', 'cart'), 'cart' ); ?>>Ikon Keranjang (Cart)</option>
                            <option value="bag" <?php selected( get_option('owwc_floating_cart_icon'), 'bag' ); ?>>Ikon Tas Belanja (Bag)</option>
                            <option value="basket" <?php selected( get_option('owwc_floating_cart_icon'), 'basket' ); ?>>Ikon Keranjang Anyaman (Basket)</option>
                        </select>
                    </div>

                    <h2 style="font-size: 16px; margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--owwc-admin-border);">Pembayaran & Pengiriman</h2>
                    <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Atur opsi pembayaran, biaya pengiriman, dan ambang batas gratis ongkir.</p>
                    
                    <div class="form-field" style="margin-bottom: 20px;">
                        <label for="owwc_bacs_account" style="display: block; margin-bottom: 8px; font-weight: 500;">Detail Rekening Bank (BACS)</label>
                        <textarea name="owwc_bacs_account" id="owwc_bacs_account" class="owwc-admin-input" rows="4" style="width: 100%; max-width: 500px; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"><?php echo esc_textarea( get_option('owwc_bacs_account', "Bank BCA\nNo. Rek: 1234567890\nA.n: PT OwwCommerce Indonesia") ); ?></textarea>
                        <p style="font-size: 12px; color: #888; margin-top: 5px;">Masukkan nama bank, nomor rekening, dan nama pemilik untuk instruksi transfer.</p>
                    </div>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label for="owwc_bacs_instructions" style="display: block; margin-bottom: 8px; font-weight: 500;">Instruksi Pembayaran (BACS)</label>
                        <textarea name="owwc_bacs_instructions" id="owwc_bacs_instructions" class="owwc-admin-input" rows="3" style="width: 100%; max-width: 500px; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"><?php echo esc_textarea( get_option('owwc_bacs_instructions', 'Silakan transfer sejumlah total tagihan ke rekening berikut:') ); ?></textarea>
                    </div>

                    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 24px;">
                        <div class="form-field" style="flex: 1; min-width: 200px;">
                            <label for="owwc_flat_rate_cost" style="display: block; margin-bottom: 8px; font-weight: 500;">Biaya Pengiriman Flat Rate</label>
                            <input type="number" name="owwc_flat_rate_cost" id="owwc_flat_rate_cost" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_flat_rate_cost', 15000) ); ?>" min="0" step="500" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div class="form-field" style="flex: 1; min-width: 200px;">
                            <label for="owwc_free_shipping_threshold" style="display: block; margin-bottom: 8px; font-weight: 500;">Ambang Batas Gratis Ongkir</label>
                            <input type="number" name="owwc_free_shipping_threshold" id="owwc_free_shipping_threshold" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_free_shipping_threshold', 0) ); ?>" min="0" step="1000" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <p style="font-size: 12px; color: #888; margin-top: 5px;">Isi 0 untuk menonaktifkan gratis ongkir.</p>
                        </div>
                    </div>

                    <div class="form-field" style="margin-bottom: 24px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="owwc_enable_cod" value="1" <?php checked( get_option('owwc_enable_cod'), 1 ); ?>>
                            <span style="font-weight: 500;">Aktifkan Pembayaran Bayar di Tempat (COD)</span>
                        </label>
                    </div>

                    <h2 style="font-size: 16px; margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--owwc-admin-border);">Fitur WhatsApp</h2>
                    <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Atur nomor WhatsApp untuk memudahkan pelanggan konfirmasi pembayaran.</p>
                    
                    <div class="form-field" style="margin-bottom: 20px;">
                        <label for="owwc_whatsapp_number" style="display: block; margin-bottom: 8px; font-weight: 500;">Nomor WhatsApp Admin</label>
                        <input type="text" name="owwc_whatsapp_number" id="owwc_whatsapp_number" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_whatsapp_number') ); ?>" placeholder="Contoh: 628123456789" style="width: 100%; max-width: 400px; padding: 8px;">
                        <p style="font-size: 12px; color: #888; margin-top: 5px;">Gunakan format internasional tanpa tanda + (contoh: 62812xxx).</p>
                    </div>

                    <h2 style="font-size: 16px; margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--owwc-admin-border);">Lokalisasi & Informasi Toko</h2>
                    <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Atur mata uang dan informasi identitas toko Anda.</p>

                    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
                        <div class="form-field" style="flex: 1; min-width: 120px;">
                            <label for="owwc_currency_symbol" style="display: block; margin-bottom: 8px; font-weight: 500;">Simbol Mata Uang</label>
                            <input type="text" name="owwc_currency_symbol" id="owwc_currency_symbol" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_currency_symbol', 'Rp') ); ?>" style="width: 100%; padding: 8px;">
                        </div>
                        <div class="form-field" style="flex: 1; min-width: 120px;">
                            <label for="owwc_thousand_sep" style="display: block; margin-bottom: 8px; font-weight: 500;">Pemisah Ribuan</label>
                            <input type="text" name="owwc_thousand_sep" id="owwc_thousand_sep" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_thousand_sep', '.') ); ?>" style="width: 100%; padding: 8px;">
                        </div>
                        <div class="form-field" style="flex: 1; min-width: 120px;">
                            <label for="owwc_decimal_sep" style="display: block; margin-bottom: 8px; font-weight: 500;">Pemisah Desimal</label>
                            <input type="text" name="owwc_decimal_sep" id="owwc_decimal_sep" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_decimal_sep', ',') ); ?>" style="width: 100%; padding: 8px;">
                        </div>
                    </div>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label for="owwc_store_name" style="display: block; margin-bottom: 8px; font-weight: 500;">Nama Toko</label>
                        <input type="text" name="owwc_store_name" id="owwc_store_name" class="owwc-admin-input" value="<?php echo esc_attr( get_option('owwc_store_name', get_bloginfo('name')) ); ?>" style="width: 100%; padding: 8px;">
                    </div>

                    <div class="form-field" style="margin-bottom: 24px;">
                        <label for="owwc_store_address" style="display: block; margin-bottom: 8px; font-weight: 500;">Alamat Toko</label>
                        <textarea name="owwc_store_address" id="owwc_store_address" class="owwc-admin-input" rows="3" style="width: 100%; padding: 10px;"><?php echo esc_textarea( get_option('owwc_store_address') ); ?></textarea>
                    </div>

                    <h2 style="font-size: 16px; margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--owwc-admin-border);">Permalink & Halaman Utama</h2>
                    <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Atur struktur URL dan tentukan halaman mana yang berfungsi sebagai Toko (Shop).</p>

                    <div class="form-field" style="margin-bottom: 20px;">
                        <label for="owwc_page_shop_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Halaman Toko (Shop)</label>
                        <select name="owwc_page_shop_id" id="owwc_page_shop_id" class="owwc-admin-select" style="width: 100%; max-width: 400px;">
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
                        <label for="owwc_page_myaccount_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Halaman Akun Saya (My Account)</label>
                        <select name="owwc_page_myaccount_id" id="owwc_page_myaccount_id" class="owwc-admin-select" style="width: 100%; max-width: 400px;">
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

                    <div class="form-field" style="margin-bottom: 24px;">
                        <label for="owwc_product_base" style="font-weight: 500; display: block; margin-bottom: 8px;">Struktur URL Produk (Permalink)</label>
                        <div style="background: #f0f0f1; border-left: 4px solid var(--owwc-admin-primary); padding: 15px; border-radius: 4px;">
                            <p style="margin: 0 0 10px 0; font-size: 13px; line-height: 1.5;">Struktur URL produk sekarang dikelola secara terpusat melalui halaman pengaturan Permalink WordPress agar lebih rapi and standar.</p>
                            <a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>" class="button button-secondary">Atur Permalink Produk &rarr;</a>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--owwc-admin-border); padding-top: 20px;">
                        <button type="submit" name="submit" id="submit" class="owwc-admin-btn" style="padding: 10px 24px; font-size: 14px;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="owwc-admin-sidebar" style="flex: 1;">
            <div class="owwc-admin-card" style="background: #fafafa;">
                <h3 style="font-size: 15px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-editor-help" style="color: var(--owwc-admin-primary);"></span> Bantuan Pengaturan
                </h3>
                <p style="font-size: 13px; color: #555; line-height: 1.6; margin-bottom: 0;">
                    Ubah gaya dan letak tombol keranjang Anda agar sesuai dengan identitas merek di website utama. Tombol *Floating Cart* akan terus menempel di layar setiap kali pengunjung menelusuri halaman kategori atau produk (*zero bloat* pada halaman non e-commerce).
                </p>
            </div>
        </div>
    </div>
</div>
