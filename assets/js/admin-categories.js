document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('owwc-categories-body');
    const addForm = document.getElementById('owwc-add-category-form');
    const parentSelect = document.getElementById('cat-parent');
    const messageSpan = document.getElementById('cat-form-message');

    if (!tableBody || typeof owwcSettings === 'undefined') return;

    // Load Categories
    function loadCategories() {
        tableBody.innerHTML = '<tr><td colspan="4">Memuat data...</td></tr>';

        fetch(owwcSettings.restUrl + 'owwc/v1/categories', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': owwcSettings.nonce
            }
        })
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = '';

                // Reset parent dropdown
                parentSelect.innerHTML = '<option value="0">Tidak Ada (Top Level)</option>';

                if (data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4">Belum ada kategori.</td></tr>';
                    return;
                }

                data.forEach(cat => {
                    // Tambah ke dropdown
                    const opt = document.createElement('option');
                    opt.value = cat.id;
                    opt.textContent = cat.name;
                    parentSelect.appendChild(opt);

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td><strong>${cat.name}</strong></td>
                    <td>${cat.description || '-'}</td>
                    <td><code>${cat.slug}</code></td>
                    <td style="text-align: right;">
                        <button class="owwc-admin-btn owwc-admin-btn-danger owwc-btn-delete" data-id="${cat.id}" style="padding: 6px 12px; font-size: 12px;">Hapus</button>
                    </td>
                </tr>`;
                    tableBody.appendChild(tr);
                });

                attachDeleteEvents();
            })
            .catch(error => {
                console.error('Error fetching categories:', error);
                tableBody.innerHTML = '<tr><td colspan="4" style="color:red;">Gagal memuat kategori.</td></tr>';
            });
    }

    // Add Category
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';

            const payload = {
                name: document.getElementById('cat-name').value,
                parent_id: document.getElementById('cat-parent').value,
                description: document.getElementById('cat-description')?.value || ''
            };

            fetch(owwcSettings.restUrl + 'owwc/v1/categories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': owwcSettings.nonce
                },
                body: JSON.stringify(payload)
            })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Tambah Kategori';

                    if (data.success) {
                        messageSpan.style.color = 'green';
                        messageSpan.textContent = 'Kategori tersimpan!';
                        messageSpan.style.display = 'inline';

                        document.getElementById('cat-name').value = '';
                        if (document.getElementById('cat-description')) {
                            document.getElementById('cat-description').value = '';
                        }

                        loadCategories();

                        setTimeout(() => { messageSpan.style.display = 'none'; }, 3000);
                    } else {
                        alert('Gagal menyimpan kategori: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error saving category:', error);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Tambah Kategori';
                    alert('Terjadi kesalahan jaringan.');
                });
        });
    }

    // Delete Category Event
    function attachDeleteEvents() {
        const deleteBtns = document.querySelectorAll('.owwc-btn-delete');
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                if (!confirm('Yakin ingin menghapus kategori ini?')) return;

                const catId = this.dataset.id;
                this.disabled = true;
                this.textContent = 'Menghapus...';

                fetch(owwcSettings.restUrl + 'owwc/v1/categories/' + catId, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': owwcSettings.nonce
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadCategories();
                        } else {
                            alert('Gagal menghapus: ' + (data.message || 'Unknown error'));
                            this.disabled = false;
                            this.textContent = 'Hapus';
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting category:', error);
                        alert('Terjadi kesalahan jaringan.');
                        this.disabled = false;
                        this.textContent = 'Hapus';
                    });
            });
        });
    }

    // Inisialisasi awal
    loadCategories();
});
