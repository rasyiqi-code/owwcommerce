document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('#owwc-coupons-table tbody');
    const form = document.getElementById('owwc-coupon-form');

    function fetchCoupons() {
        fetch(`${owwcSettings.restUrl}owwc/v1/coupons`, {
            headers: { 'X-WP-Nonce': owwcSettings.nonce }
        })
            .then(res => res.json())
            .then(data => {
                renderTable(data);
            });
    }

    function renderTable(coupons) {
        if (!coupons || coupons.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px;">Belum ada kupon yang dibuat.</td></tr>';
            return;
        }

        tableBody.innerHTML = coupons.map(c => `
            <tr>
                <td><strong>${c.code}</strong></td>
                <td>${c.type === 'percent' ? 'Persentase' : 'Potongan Tetap'}</td>
                <td>${c.type === 'percent' ? c.amount + '%' : 'Rp ' + parseInt(c.amount).toLocaleString()}</td>
                <td>${c.usage_limit || '∞'}</td>
                <td>${c.usage_count}</td>
                <td>${c.expiry_date ? new Date(c.expiry_date).toLocaleDateString() : '-'}</td>
                <td>
                    <button class="owwc-delete-coupon owwc-admin-btn" style="background: #ef4444; padding: 4px 8px;" data-id="${c.id}">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </td>
            </tr>
        `).join('');

        // Attach delete events
        document.querySelectorAll('.owwc-delete-coupon').forEach(btn => {
            btn.addEventListener('click', function () {
                if (confirm('Hapus kupon ini?')) {
                    deleteCoupon(this.dataset.id);
                }
            });
        });
    }

    function deleteCoupon(id) {
        fetch(`${owwcSettings.restUrl}owwc/v1/coupons/${id}`, {
            method: 'DELETE',
            headers: { 'X-WP-Nonce': owwcSettings.nonce }
        })
            .then(res => res.json())
            .then(() => fetchCoupons());
    }

    form.onsubmit = function (e) {
        e.preventDefault();
        const formData = new FormData(form);
        const data = Object.fromEntries ? Object.fromEntries(formData.entries()) : {};

        if (Object.keys(data).length === 0) {
            formData.forEach((value, key) => data[key] = value);
        }

        fetch(`${owwcSettings.restUrl}owwc/v1/coupons`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': owwcSettings.nonce
            },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(res => {
                if (res.code && res.message) {
                    alert(res.message);
                } else {
                    form.reset();
                    fetchCoupons();
                }
            });
    };

    fetchCoupons();
});
