/**
 * OwwCommerce Admin Import/Export v2
 */
document.addEventListener('DOMContentLoaded', function () {
    const importBtn = document.getElementById('import-submit-btn');
    const progressBar = document.getElementById('import-progress-bar');
    const progressWrap = document.getElementById('import-progress');
    const statusText = document.getElementById('import-status');
    const fileInput = document.getElementById('excel-file');

    if (importBtn && fileInput) {
        importBtn.addEventListener('click', async (e) => {
            if (!fileInput.files.length) {
                alert('Silakan pilih file Excel terlebih dahulu.');
                return;
            }

            const formData = new FormData();
            formData.append('excel_file', fileInput.files[0]);

            importBtn.disabled = true;
            importBtn.textContent = 'Mengimpor...';
            progressWrap.style.display = 'block';
            progressBar.style.width = '50%';
            statusText.textContent = 'Membaca file Excel dan memproses data...';
            statusText.style.color = '#666';

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
