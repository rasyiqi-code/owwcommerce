/**
 * OwwCommerce Admin Orders App (Vanilla JS)
 *
 * Menangani interaksi AJAX pada halaman detail pesanan:
 * - Update status pesanan via REST API
 */
document.addEventListener('DOMContentLoaded', function () {
    const updateBtn = document.getElementById('owwc-update-status-btn');
    const statusSelect = document.getElementById('owwc-order-status');
    const messageEl = document.getElementById('owwc-status-message');

    if (!updateBtn || !statusSelect) return;

    const apiBase = `${owwcSettings.restUrl}owwc/v1/orders`;
    const nonce = owwcSettings.nonce;

    /**
     * Handler klik tombol "Simpan Status"
     * Mengirim PATCH request ke /owwc/v1/orders/{id}/status
     */
    updateBtn.addEventListener('click', async function () {
        const orderId = updateBtn.getAttribute('data-order-id');
        const newStatus = statusSelect.value;

        if (!orderId || !newStatus) return;

        updateBtn.disabled = true;
        updateBtn.innerText = 'Menyimpan...';

        try {
            const res = await fetch(`${apiBase}/${orderId}/status`, {
                method: 'PATCH',
                headers: {
                    'X-WP-Nonce': nonce,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                // Tampilkan pesan sukses
                if (messageEl) {
                    messageEl.innerText = `Status berhasil diubah ke "${newStatus}".`;
                    messageEl.style.display = 'block';
                    messageEl.style.color = 'var(--owwc-admin-success)';
                }

                // Update badge status di header jika ada
                const badgeEl = document.querySelector('.owwc-admin-header .owwc-badge');
                if (badgeEl) {
                    badgeEl.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

                    // Mapping class badge
                    badgeEl.className = 'owwc-badge';
                    if (newStatus === 'completed') badgeEl.classList.add('completed');
                    else if (newStatus === 'processing') badgeEl.classList.add('processing');
                    else if (newStatus === 'failed' || newStatus === 'cancelled') badgeEl.classList.add('failed');
                    else badgeEl.classList.add('pending');
                }

                // Sembunyikan pesan setelah 3 detik
                setTimeout(() => {
                    if (messageEl) messageEl.style.display = 'none';
                }, 3000);

            } else {
                // Tampilkan error
                if (messageEl) {
                    messageEl.innerText = data.message || 'Gagal mengubah status.';
                    messageEl.style.display = 'block';
                    messageEl.style.color = 'var(--owwc-admin-danger)';
                }
            }

        } catch (error) {
            console.error('Update status error:', error);
            if (messageEl) {
                messageEl.innerText = 'Terjadi kesalahan jaringan.';
                messageEl.style.display = 'block';
                messageEl.style.color = 'var(--owwc-admin-danger)';
            }
        } finally {
            updateBtn.disabled = false;
            updateBtn.innerText = 'Simpan Status';
        }
    });
});
