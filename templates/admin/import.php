<div class="owwc-admin-wrap">
    <div class="owwc-admin-header" style="margin-bottom: 20px;">
        <h1><?php esc_html_e( 'Export / Import Produk', 'owwcommerce' ); ?></h1>
    </div>

    <div class="owwc-admin-2col-layout">
        <div class="owwc-admin-main">
            <!-- Import Section -->
            <div class="owwc-admin-card" style="margin-bottom: 24px;">
                <h2 style="font-size: 16px;">Import Produk (Excel)</h2>
                <p style="color: #666; font-size: 13px;">Unggah file Excel (.xlsx) untuk mengimpor produk secara massal ke OwwCommerce.</p>
                
                <form id="owwc-import-form" enctype="multipart/form-data" style="margin-top: 20px;">
                    <div class="form-field">
                        <label for="excel-file">Pilih File Excel</label>
                        <input type="file" id="excel-file" name="excel_file" accept=".xlsx" required class="owwc-admin-input" style="padding: 10px;">
                    </div>
                    
                    <div id="import-progress" style="display: none; margin-top: 20px;">
                        <div style="background: #f0f0f1; border-radius: 4px; height: 10px; overflow: hidden;">
                            <div id="import-progress-bar" style="background: var(--owwc-admin-primary); width: 0%; height: 100%; transition: width 0.3s;"></div>
                        </div>
                        <p id="import-status" style="font-size: 12px; margin-top: 5px; color: #666;">Memproses...</p>
                    </div>

                    <button type="submit" id="import-submit-btn" class="owwc-admin-btn" style="margin-top: 20px;">Mulai Import</button>
                </form>
            </div>

            <!-- Export Section -->
            <div class="owwc-admin-card">
                <h2 style="font-size: 16px;">Export Produk</h2>
                <p style="color: #666; font-size: 13px;">Unduh semua data produk Anda dalam format Excel.</p>
                <button type="button" id="owwc-export-btn" class="owwc-admin-btn owwc-btn-secondary" style="margin-top: 20px;">Unduh Excel Produk</button>
            </div>
        </div>

        <div class="owwc-admin-sidebar">
            <div class="owwc-admin-card">
                <h2 style="font-size: 16px;">Petunjuk Excel</h2>
                <p style="font-size: 13px; color: #666;">Gunakan header berikut pada baris pertama file Excel Anda:</p>
                <code style="display: block; background: #f9f9f9; padding: 10px; border-radius: 4px; font-size: 11px;">
                    title, description, price, sale_price, sku, stock, image_url
                </code>
                <p style="font-size: 12px; color: #666; margin-top: 10px;">* Pastikan pemisah kolom adalah koma (,)</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const importForm = document.getElementById('owwc-import-form');
    const importBtn = document.getElementById('import-submit-btn');
    const progressBar = document.getElementById('import-progress-bar');
    const progressWrap = document.getElementById('import-progress');
    const statusText = document.getElementById('import-status');

    if (importForm) {
        importForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const fileInput = document.getElementById('excel-file');
            if (!fileInput.files.length) return;

            const formData = new FormData();
            formData.append('excel_file', fileInput.files[0]);

            importBtn.disabled = true;
            importBtn.textContent = 'Mengimpor...';
            progressWrap.style.display = 'block';
            progressBar.style.width = '50%';
            statusText.textContent = 'Membaca file Excel dan memproses data...';

            try {
                const res = await fetch(`${owwcSettings.restUrl}owwc/v1/import/excel`, {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': owwcSettings.nonce
                    },
                    body: formData
                });

                const contentType = res.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const data = await res.json();
                    if (res.ok) {
                        progressBar.style.width = '100%';
                        statusText.textContent = `Berhasil mengimpor ${data.imported} produk dari Excel!`;
                        statusText.style.color = 'green';
                        setTimeout(() => {
                            window.location.href = '?page=owwc-products';
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Gagal mengimpor.');
                    }
                } else {
                    // Jika bukan JSON, kemungkinan ada error PHP yang tercetak sebagai HTML
                    const text = await res.text();
                    console.error('Server Response:', text);
                    throw new Error('Server mengembalikan respon tidak valid (bukan JSON). Cek konsol browser untuk detail.');
                }
            } catch (error) {
                alert(error.message);
                importBtn.disabled = false;
                importBtn.textContent = 'Mulai Import';
                progressWrap.style.display = 'none';
            }
        });
    }

    // Export Logic
    document.getElementById('owwc-export-btn')?.addEventListener('click', () => {
        const exportUrl = `${owwcSettings.restUrl}owwc/v1/import/export?_wpnonce=${owwcSettings.nonce}`;
        window.location.href = exportUrl;
    });
});
</script>
