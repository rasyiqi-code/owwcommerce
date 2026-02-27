document.addEventListener('DOMContentLoaded', function () {
    const attrForm = document.getElementById('owwc-attribute-form');
    const termForm = document.getElementById('owwc-term-form');
    const termCard = document.getElementById('owwc-term-form-card');
    const tableBody = document.querySelector('#owwc-attributes-table tbody');
    const cancelTermBtn = document.getElementById('cancel-term-mode');

    function fetchAttributes() {
        fetch(`${owwcSettings.restUrl}owwc/v1/attributes`, {
            headers: { 'X-WP-Nonce': owwcSettings.nonce }
        })
            .then(res => res.json())
            .then(data => {
                renderTable(data);
            });
    }

    async function fetchTerms(attrId) {
        const res = await fetch(`${owwcSettings.restUrl}owwc/v1/attributes/${attrId}/terms`, {
            headers: { 'X-WP-Nonce': owwcSettings.nonce }
        });
        return await res.json();
    }

    function renderTable(attributes) {
        if (!attributes || attributes.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px;">Belum ada atribut.</td></tr>';
            return;
        }

        tableBody.innerHTML = attributes.map(a => `
            <tr>
                <td><strong>${a.name}</strong></td>
                <td>${a.slug}</td>
                <td id="terms-cell-${a.id}" class="owwc-terms-list">Memuat nilai...</td>
                <td>
                    <button class="owwc-admin-btn owwc-manage-terms" data-id="${a.id}" data-name="${a.name}" style="padding: 4px 8px; font-size: 11px;">
                        <span class="dashicons dashicons-edit"></span> Kelola Nilai
                    </button>
                </td>
            </tr>
        `).join('');

        // Fetch terms for each attribute to display in the list
        attributes.forEach(async (a) => {
            const terms = await fetchTerms(a.id);
            const cell = document.getElementById(`terms-cell-${a.id}`);
            if (terms && terms.length > 0) {
                cell.innerHTML = terms.map(t => `<span class="owwc-badge" style="background: #f3f4f6; color: #374151; margin-right: 5px; margin-bottom: 5px;">${t.name}</span>`).join('');
            } else {
                cell.innerText = '-';
            }
        });

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
