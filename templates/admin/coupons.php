<div class="owwc-admin-wrap">
    <div class="owwc-admin-header">
        <h1><?php esc_html_e( 'Kupon Diskon', 'owwcommerce' ); ?></h1>
    </div>

    <div class="owwc-admin-split">
        <!-- Kolom Kiri: Form Add Coupon -->
        <div class="owwc-admin-split-form">
            <div class="owwc-admin-card">
                <h3>Tambah Kupon Baru</h3>
                <form id="owwc-coupon-form" class="owwc-admin-form">
                    <div class="owwc-form-group">
                        <label for="coupon_code">Kode Kupon (Contoh: HEMAT10)</label>
                        <input type="text" id="coupon_code" name="code" class="owwc-admin-input owwc-uppercase" required>
                    </div>
                    
                    <div class="owwc-form-group">
                        <label for="coupon_type">Tipe Diskon</label>
                        <select id="coupon_type" name="type" class="owwc-admin-select">
                            <option value="percent">Persentase (%)</option>
                            <option value="fixed_cart">Potongan Harga Tetap (Rp)</option>
                        </select>
                    </div>

                    <div class="owwc-form-group">
                        <label for="coupon_amount">Jumlah Diskon</label>
                        <input type="number" id="coupon_amount" name="amount" class="owwc-admin-input" required min="0">
                    </div>

                    <div class="owwc-form-group">
                        <label for="coupon_limit">Batas Penggunaan (Opsional)</label>
                        <input type="number" id="coupon_limit" name="usage_limit" class="owwc-admin-input" min="1">
                    </div>

                    <div class="owwc-form-group">
                        <label for="coupon_expiry">Tanggal Kadaluarsa (Opsional)</label>
                        <input type="date" id="coupon_expiry" name="expiry_date" class="owwc-admin-input">
                    </div>

                    <div class="owwc-form-actions" style="background: transparent; padding: 0; border: none; margin: 0;">
                        <button type="submit" class="owwc-admin-btn owwc-btn--block">Simpan Kupon</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Kupon -->
        <div class="owwc-admin-split-table">
            <div class="owwc-admin-card">
                <table class="owwc-admin-table" id="owwc-coupons-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Batas</th>
                            <th>Digunakan</th>
                            <th>Kadaluarsa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">Memuat data kupon...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
