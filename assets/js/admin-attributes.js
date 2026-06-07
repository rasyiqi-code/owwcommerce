document.addEventListener('DOMContentLoaded', function () {
    const attrForm = document.getElementById('owwc-attribute-form');
    const termForm = document.getElementById('owwc-term-form');
    const termCard = document.getElementById('owwc-term-form-card');
    const tableBody = document.querySelector('#owwc-attributes-table tbody');
    const cancelTermBtn = document.getElementById('cancel-term-mode');

    // Helper untuk mencegah XSS
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }

    function fetchAttributes() {
        fetch(`${owwcSettings.restUrl}owwc/v1/attributes`, {
            headers: { 'X-WP-Nonce': owwcSettings.nonce }
        })
            .then(res => res.json())
            .then(data => {
                renderTable(data);
            });
    }

    function renderTable(attributes) {
        if (!attributes || attributes.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px;">Belum ada atribut.</td></tr>';
            return;
        }

        tableBody.innerHTML = attributes.map(a => {
            const termsHtml = (a.terms && a.terms.length > 0)
                ? a.terms.map(t => `<span class="owwc-badge" style="background: #f3f4f6; color: #374151; margin-right: 5px; margin-bottom: 5px;">${escapeHTML(t.name)}</span>`).join('')
                : '-';

            return `
                <tr>
                    <td><strong>${escapeHTML(a.name)}</strong></td>
                    <td><code>${escapeHTML(a.slug)}</code></td>
                    <td id="terms-cell-${a.id}" class="owwc-terms-list">${termsHtml}</td>
                    <td>
                        <button class="owwc-admin-btn owwc-manage-terms" data-id="${escapeHTML(a.id)}" data-name="${escapeHTML(a.name)}" style="padding: 4px 8px; font-size: 11px;">
                            <span class="dashicons dashicons-edit"></span> Kelola Nilai
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        // Attach events for manage terms
        document.querySelectorAll('.owwc-manage-terms').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                openTermMode(id, name);
            });
        });
    }

    function openTermMode(id, name) {
        attrForm.parentElement.style.display = 'none';
        termCard.style.display = 'block';
        document.getElementById('current-attr-name').innerText = name;
        document.getElementById('current-attr-id').value = id;
        document.getElementById('term-name').focus();
    }

    cancelTermBtn.onclick = () => {
        termCard.style.display = 'none';
        attrForm.parentElement.style.display = 'block';
    };

    attrForm.onsubmit = function (e) {
        e.preventDefault();
        const formData = new FormData(attrForm);
        const data = Object.fromEntries(formData.entries());

        fetch(`${owwcSettings.restUrl}owwc/v1/attributes`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': owwcSettings.nonce
            },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(() => {
                attrForm.reset();
                fetchAttributes();
            });
    };

    termForm.onsubmit = function (e) {
        e.preventDefault();
        const attrId = document.getElementById('current-attr-id').value;
        const name = document.getElementById('term-name').value;

        fetch(`${owwcSettings.restUrl}owwc/v1/attributes/${attrId}/terms`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': owwcSettings.nonce
            },
            body: JSON.stringify({ name })
        })
            .then(res => res.json())
            .then(() => {
                termForm.reset();
                fetchAttributes(); // Refresh to show new terms in table
            });
    };

    fetchAttributes();
});
