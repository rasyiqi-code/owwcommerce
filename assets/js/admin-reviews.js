document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('owwc-reviews-tbody');

    const renderStars = (rating) => {
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += `<span class="${i > rating ? 'empty' : ''}">★</span>`;
        }
        return `<div class="rating-stars">${starsHtml}</div>`;
    };

    const loadReviews = async () => {
        try {
            const res = await fetch(`${owwcSettings.restUrl}owwc/v1/reviews`, {
                headers: { 'X-WP-Nonce': owwcSettings.nonce }
            });
            const reviews = await res.json();

            if (reviews.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px;">Belum ada ulasan yang masuk.</td></tr>';
                return;
            }

            tbody.innerHTML = reviews.map(r => `
                <tr class="${r.status === 'pending' ? 'pending-row' : ''}">
                    <td>
                        <div class="product-title">${r.product_title}</div>
                        <div style="font-size: 11px; color: #999;">ID: ${r.product_id}</div>
                    </td>
                    <td>
                        <div class="author-info">
                            <strong>${r.author_name || 'Anonim'}</strong>
                            <div class="author-email">${r.author_email || '-'}</div>
                        </div>
                    </td>
                    <td>${renderStars(r.rating)}</td>
                    <td>
                        <div class="comment-text">${r.comment}</div>
                    </td>
                    <td>
                        <span class="owwc-status-badge status-${r.status}">${r.status === 'pending' ? 'Menunggu' : 'Disetujui'}</span>
                    </td>
                    <td>
                        <div style="font-size: 13px;">${new Date(r.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</div>
                        <div style="font-size: 11px; color: #999;">${new Date(r.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</div>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 5px; justify-content: flex-end;">
                            ${r.status === 'pending' ? `
                                <button class="btn-approve-review" data-id="${r.id}" title="Setujui Ulasan">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </button>
                            ` : ''}
                            <button class="btn-delete-review" data-id="${r.id}" title="Hapus Ulasan">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            // Bind approve events
            document.querySelectorAll('.btn-approve-review').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const id = this.getAttribute('data-id');
                    try {
                        const approveRes = await fetch(`${owwcSettings.restUrl}owwc/v1/reviews/${id}/approve`, {
                            method: 'POST',
                            headers: { 'X-WP-Nonce': owwcSettings.nonce }
                        });

                        if (approveRes.ok) {
                            loadReviews();
                        } else {
                            alert('Gagal menyetujui ulasan.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Terjadi kesalahan jaringan.');
                    }
                });
            });

            // Bind delete events
            document.querySelectorAll('.btn-delete-review').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const id = this.getAttribute('data-id');
                    if (!confirm('Apakah Anda yakin ingin menghapus ulasan ini?')) return;

                    try {
                        const deleteRes = await fetch(`${owwcSettings.restUrl}owwc/v1/reviews/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-WP-Nonce': owwcSettings.nonce }
                        });

                        if (deleteRes.ok) {
                            loadReviews();
                        } else {
                            alert('Gagal menghapus ulasan.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Terjadi kesalahan jaringan.');
                    }
                });
            });

        } catch (e) {
            console.error(e);
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red; padding: 40px;">Gagal memuat data ulasan.</td></tr>';
        }
    };

    loadReviews();
});
