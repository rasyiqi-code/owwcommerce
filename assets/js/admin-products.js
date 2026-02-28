/**
 * OwwCommerce Admin Products App (Vanilla JS)
 */
document.addEventListener('DOMContentLoaded', function () {
    const appContainer = document.getElementById('owwc-products-app');
    const addForm = document.getElementById('owwc-add-product-form');

    // Load URL REST API WP
    const apiBase = `${owwcSettings.restUrl}owwc/v1/products`;
    const nonce = owwcSettings.nonce;

    // =========================================================
    // LIST VIEW MODE (Jika ada container tabel)
    // =========================================================
    if (appContainer) {
        let currentPage = 1;
        const perPage = 10;

        async function fetchProducts(page = 1) {
            currentPage = page;
            appContainer.innerHTML = '<div class="owwc-admin-card" style="padding:0;"><p style="padding: 24px; text-align: center; color: #666;">Memuat produk...</p></div>';
            try {
                const res = await fetch(`${apiBase}?page=${page}&per_page=${perPage}`, {
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': nonce,
                        'Content-Type': 'application/json'
                    }
                });

                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

                const data = await res.json();
                renderTable(data);
            } catch (e) {
                console.error("Fetch error:", e);
                appContainer.innerHTML = '<div class="notice notice-error"><p>Gagal memuat produk. Silakan cek konsol.</p></div>';
            }
        }

        function renderTable(data) {
            const products = data.items || [];
            if (!products.length) {
                appContainer.innerHTML = '<div class="owwc-admin-card" style="padding:24px; text-align:center;"><p>Produk tidak ditemukan. Ayo buat satu!</p></div>';
                return;
            }

            let html = `
                <div class="owwc-admin-card" style="padding:0; overflow:hidden;">
                <table class="owwc-admin-table" style="margin:0; border:none;">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Gambar</th>
                            <th style="width: 40%;">Product Name</th>
                            <th style="width: 18%;">Price (Rp)</th>
                            <th style="width: 15%;">Stock</th>
                            <th style="width: 17%; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            products.forEach(p => {
                const imgHtml = p.image_url
                    ? `<img src="${p.image_url}" alt="${p.title}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">`
                    : `<span style="display:inline-block;width:40px;height:40px;background:#f0f0f1;border-radius:4px;border:1px solid #dcdcdc;"></span>`;

                html += `
                    <tr>
                        <td style="vertical-align: middle;">${imgHtml}</td>
                        <td style="vertical-align: middle;"><strong>${p.title}</strong></td>
                        <td style="vertical-align: middle;">${Number(p.price).toLocaleString()}</td>
                        <td style="vertical-align: middle;">
                            <span class="owwc-badge ${p.stock_qty > 0 ? 'completed' : 'failed'}">
                                ${p.stock_qty > 0 ? p.stock_qty + ' In Stock' : 'Out of Stock'}
                            </span>
                        </td>
                        <td style="text-align: right; vertical-align: middle;">
                            <a href="${owwcSettings.homeUrl}${owwcSettings.productBase}/${p.slug}" target="_blank" class="owwc-admin-btn" style="padding: 6px 12px; font-size: 12px; margin-right: 4px; text-decoration:none; background:#2271b1; color:white;">View</a>
                            <a href="?page=owwc-products&action=edit&id=${p.id}" class="owwc-admin-btn owwc-btn-secondary" style="padding: 6px 12px; font-size: 12px; margin-right: 4px; text-decoration:none;">Edit</a>
                            <button class="owwc-admin-btn owwc-admin-btn-danger owwc-btn-delete" data-id="${p.id}" style="padding: 6px 12px; font-size: 12px;">Delete</button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';

            // Pagination UI
            if (data.total_pages > 1) {
                html += `
                    <div class="owwc-admin-pagination" style="margin-top:20px; display:flex; justify-content:space-between; align-items:center; background:white; padding:15px; border-radius:8px; border:1px solid #e5e7eb;">
                        <div class="pagination-info" style="color:#6b7280; font-size:14px;">
                            Menampilkan halaman <strong>${data.current_page}</strong> dari <strong>${data.total_pages}</strong> (Total: ${data.total_items} produk)
                        </div>
                        <div class="pagination-controls">
                            <button class="owwc-admin-btn owwc-btn-secondary owwc-prev-page" ${data.current_page <= 1 ? 'disabled' : ''} style="margin-right:10px;">&laquo; Sebelumnya</button>
                            <button class="owwc-admin-btn owwc-btn-secondary owwc-next-page" ${data.current_page >= data.total_pages ? 'disabled' : ''}>Selanjutnya &raquo;</button>
                        </div>
                    </div>
                `;
            }

            appContainer.innerHTML = html;

            // Pagination Events
            const prevBtn = appContainer.querySelector('.owwc-prev-page');
            const nextBtn = appContainer.querySelector('.owwc-next-page');

            if (prevBtn) {
                prevBtn.addEventListener('click', () => fetchProducts(currentPage - 1));
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', () => fetchProducts(currentPage + 1));
            }

            document.querySelectorAll('.owwc-btn-delete').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const id = e.target.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this product?')) {
                        await deleteProduct(id);
                    }
                });
            });
        }

        async function deleteProduct(id) {
            try {
                const res = await fetch(`${apiBase}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-WP-Nonce': nonce,
                        'Content-Type': 'application/json'
                    }
                });
                if (res.ok) fetchProducts();
            } catch (e) {
                alert('Failed to delete product.');
            }
        }

        fetchProducts();

        // Bulk Stock Update Logic
        const bulkStockBtn = document.getElementById('owwc-bulk-stock-btn');
        let isBulkMode = false;

        if (bulkStockBtn) {
            bulkStockBtn.addEventListener('click', async () => {
                if (!isBulkMode) {
                    // Masuk ke mode edit
                    isBulkMode = true;
                    bulkStockBtn.textContent = 'Simpan Stok';
                    bulkStockBtn.classList.remove('owwc-btn-secondary');
                    bulkStockBtn.classList.add('owwc-admin-btn'); // Primary color

                    document.querySelectorAll('.owwc-badge').forEach(badge => {
                        const td = badge.closest('td');
                        const tr = td.closest('tr');
                        const deleteBtn = tr.querySelector('.owwc-btn-delete');
                        const editLink = tr.querySelector('a[href*="id="]');

                        const prodId = deleteBtn ? deleteBtn.getAttribute('data-id') :
                            (editLink ? editLink.href.split('id=')[1].split('&')[0] : null);

                        console.log("Bulk Edit Mode - Found Prod ID:", prodId);

                        if (!prodId) return;

                        const currentStock = badge.textContent.trim().split(' ')[0];
                        td.innerHTML = `<input type="number" class="owwc-admin-input bulk-stock-input" data-id="${prodId}" value="${currentStock === 'Out' ? 0 : currentStock}" style="width: 80px; padding: 4px 8px;">`;
                    });
                } else {
                    // Simpan data
                    const inputs = document.querySelectorAll('.bulk-stock-input');
                    const updateData = [];

                    inputs.forEach(input => {
                        const id = input.getAttribute('data-id');
                        if (id) {
                            updateData.push({
                                id: parseInt(id),
                                stock: parseInt(input.value)
                            });
                        }
                    });

                    if (updateData.length === 0) {
                        alert('Tidak ada data produk yang ditemukan untuk diperbarui.');
                        bulkStockBtn.disabled = false;
                        bulkStockBtn.textContent = 'Simpan Stok';
                        return;
                    }

                    bulkStockBtn.disabled = true;
                    bulkStockBtn.textContent = 'Menyimpan...';

                    try {
                        const res = await fetch(`${apiBase}/bulk-update-stock`, {
                            method: 'POST',
                            headers: {
                                'X-WP-Nonce': nonce,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(updateData)
                        });

                        if (res.ok) {
                            alert('Stok berhasil diperbarui secara massal!');
                            isBulkMode = false;
                            bulkStockBtn.textContent = 'Update Stok Massal';
                            bulkStockBtn.classList.add('owwc-btn-secondary');
                            bulkStockBtn.disabled = false;
                            fetchProducts(currentPage);
                        } else {
                            alert('Gagal memperbarui stok.');
                            bulkStockBtn.disabled = false;
                            bulkStockBtn.textContent = 'Simpan Stok';
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan.');
                        bulkStockBtn.disabled = false;
                        bulkStockBtn.textContent = 'Simpan Stok';
                    }
                }
            });
        }
    }


    // =========================================================
    // ADD / EDIT FORM VIEW MODE (Jika ada Form Container)
    // =========================================================
    if (addForm) {
        const prodIdInput = document.getElementById('prod-id');
        const formTitle = document.getElementById('form-title');
        const submitBtn = document.getElementById('prod-submit-btn');
        const productId = prodIdInput ? prodIdInput.value : '';

        // --- VARIABLE PRODUCT LOGIC ---
        const prodType = document.getElementById('prod-type');
        const variableSection = document.getElementById('owwc-variable-product-section');
        const standardPriceFields = document.querySelectorAll('#prod-price, #prod-sale-price, #prod-sku, #prod-stock');
        const availableAttrSelect = document.getElementById('owwc-available-attributes');
        const addAttrBtn = document.getElementById('owwc-add-attribute-btn');
        const selectedAttrContainer = document.getElementById('owwc-selected-attributes');
        const generateVariationsBtn = document.getElementById('owwc-generate-variations-btn');
        const variationsList = document.getElementById('owwc-variations-list');

        let selectedAttributes = []; // Format: { id: 1, name: 'Size', terms: ['S', 'M'] }
        let currentVariations = []; // Format: { sku: '', price: 0, sale_price: 0, stock_qty: 0, attributes: { 1: 'S' } }

        // Fetch Global Attributes
        async function fetchGlobalAttributes() {
            try {
                const res = await fetch(`${owwcSettings.restUrl}owwc/v1/attributes`, {
                    headers: { 'X-WP-Nonce': nonce }
                });
                const data = await res.json();
                if (availableAttrSelect) {
                    data.forEach(attr => {
                        const opt = document.createElement('option');
                        opt.value = attr.id;
                        opt.textContent = attr.name;
                        availableAttrSelect.appendChild(opt);
                    });
                }
            } catch (e) { console.error("Error loading attributes", e); }
        }
        fetchGlobalAttributes();

        // Handle Type Change UI
        function toggleProductTypeUI() {
            const isVariable = prodType.value === 'variable';
            if (variableSection) {
                variableSection.style.display = isVariable ? 'block' : 'none';
            }

            // Fields to disable/dim for variable product
            ['prod-price', 'prod-sale-price', 'prod-sku', 'prod-stock'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.disabled = isVariable;
                    const fieldWrap = el.closest('.form-field');
                    if (fieldWrap) {
                        fieldWrap.style.opacity = isVariable ? '0.5' : '1';
                    }
                }
            });
        }

        prodType?.addEventListener('change', toggleProductTypeUI);
        // Initial run
        toggleProductTypeUI();

        // Handle Tabs
        document.querySelectorAll('.owwc-tab-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.owwc-tab-link').forEach(l => {
                    l.classList.remove('active');
                    l.style.color = '#666';
                    l.style.borderBottom = 'none';
                });
                link.classList.add('active');
                link.style.color = 'inherit';
                link.style.borderBottom = '2px solid var(--owwc-admin-primary)';

                document.querySelectorAll('.owwc-tab-content').forEach(c => c.style.display = 'none');
                document.getElementById(link.getAttribute('data-target')).style.display = 'block';
            });
        });

        // Add Attribute
        addAttrBtn?.addEventListener('click', async () => {
            const attrId = availableAttrSelect.value;
            if (!attrId || selectedAttributes.find(a => a.id == attrId)) return;

            const attrName = availableAttrSelect.options[availableAttrSelect.selectedIndex].text;

            // Fetch Terms
            try {
                const res = await fetch(`${owwcSettings.restUrl}owwc/v1/attributes/${attrId}/terms`, {
                    headers: { 'X-WP-Nonce': nonce }
                });
                const terms = await res.json();

                selectedAttributes.push({ id: attrId, name: attrName, terms: terms });
                renderSelectedAttributes();
            } catch (e) { alert("Gagal memuat terms."); }
        });

        function renderSelectedAttributes() {
            selectedAttrContainer.innerHTML = '';
            selectedAttributes.forEach((attr, idx) => {
                const div = document.createElement('div');
                div.className = 'owwc-admin-card';
                div.style.padding = '15px';
                div.style.marginBottom = '10px';
                div.style.background = '#f9f9f9';
                div.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <strong>${attr.name}</strong>
                        <a href="#" class="owwc-remove-attr" data-idx="${idx}" style="color:var(--owwc-admin-danger); font-size:12px;">Hapus</a>
                    </div>
                    <div style="margin-top:10px;">
                        ${attr.terms.map(t => `<label style="margin-right:10px; font-size:12px;"><input type="checkbox" checked class="term-check" data-attr-id="${attr.id}" data-term-name="${t.name}"> ${t.name}</label>`).join('')}
                    </div>
                `;
                selectedAttrContainer.appendChild(div);
            });

            document.querySelectorAll('.owwc-remove-attr').forEach(a => {
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    selectedAttributes.splice(e.target.getAttribute('data-idx'), 1);
                    renderSelectedAttributes();
                });
            });
        }

        // Generate Variations Logic
        generateVariationsBtn?.addEventListener('click', () => {
            const groups = [];
            selectedAttributes.forEach(attr => {
                const selectedTerms = [];
                // Find checkboxes for this attribute
                document.querySelectorAll(`.term-check[data-attr-id="${attr.id}"]:checked`).forEach(chk => {
                    selectedTerms.push(chk.getAttribute('data-term-name'));
                });
                if (selectedTerms.length) {
                    groups.push({ id: attr.id, name: attr.name, terms: selectedTerms });
                }
            });

            if (groups.length === 0) {
                alert("Pilih setidaknya satu atribut dan satu nilai.");
                return;
            }

            // Cartesian Product Helper
            const combinations = groups.reduce((a, b) => a.flatMap(d => b.terms.map(e => ({ ...d, [b.id]: e }))), [{}]);

            currentVariations = combinations.map(combo => ({
                sku: '',
                price: document.getElementById('prod-price').value || 0,
                sale_price: '',
                stock_qty: 0,
                attributes: combo
            }));

            renderVariationsList();
        });

        function renderVariationsList() {
            if (!currentVariations.length) {
                variationsList.innerHTML = '<p style="text-align: center; padding: 20px; color: #999;">Klik "Generate" untuk membuat variasi.</p>';
                return;
            }

            let html = `
                <table class="owwc-admin-table" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th>Kombinasi</th>
                            <th>SKU</th>
                            <th>Harga (Rp)</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            currentVariations.forEach((v, idx) => {
                const comboDesc = Object.values(v.attributes).join(' / ');
                html += `
                    <tr>
                        <td><strong>${comboDesc}</strong></td>
                        <td><input type="text" class="owwc-admin-input v-sku" value="${v.sku}" data-idx="${idx}" style="padding: 4px 8px;"></td>
                        <td><input type="number" class="owwc-admin-input v-price" value="${v.price}" data-idx="${idx}" style="padding: 4px 8px;"></td>
                        <td><input type="number" class="owwc-admin-input v-stock" value="${v.stock_qty}" data-idx="${idx}" style="padding: 4px 8px;"></td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            variationsList.innerHTML = html;

            // Sync data back to currentVariations array on change
            variationsList.querySelectorAll('input').forEach(input => {
                input.addEventListener('change', (e) => {
                    const idx = e.target.getAttribute('data-idx');
                    const field = e.target.classList.contains('v-sku') ? 'sku' : (e.target.classList.contains('v-price') ? 'price' : 'stock_qty');
                    currentVariations[idx][field] = e.target.value;
                });
            });
        }

        // Jika ID ada, mode = EDIT, tarik data product dari API
        if (productId) {
            formTitle.innerText = 'Edit Produk #' + productId;
            submitBtn.innerText = 'Memuat Data...';
            submitBtn.disabled = true;

            fetch(`${apiBase}/${productId}`, {
                method: 'GET',
                headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' }
            })
                .then(res => res.json())
                .then(product => {
                    document.getElementById('prod-name').value = product.title;
                    document.getElementById('prod-description').value = product.description || '';
                    document.getElementById('prod-price').value = product.price;
                    document.getElementById('prod-sale-price').value = product.sale_price !== null ? product.sale_price : '';
                    document.getElementById('prod-sku').value = product.sku || '';
                    document.getElementById('prod-stock').value = product.stock_qty;
                    document.getElementById('prod-status').value = product.status || 'publish';
                    document.getElementById('prod-type').value = product.type || 'simple';

                    // Trigger type change UI
                    toggleProductTypeUI();

                    // Load variations if any
                    if (product.variations && product.variations.length > 0) {
                        currentVariations = product.variations;
                        renderVariationsList();

                        // Reconstruct selectedAttributes for UI
                        const attrsMap = {};
                        currentVariations.forEach(v => {
                            Object.entries(v.attributes).forEach(([attrId, termName]) => {
                                if (!attrsMap[attrId]) attrsMap[attrId] = new Set();
                                attrsMap[attrId].add(termName);
                            });
                        });

                        // For each attribute found in variations, we need its name to show in UI
                        // This uses a bit of guesswork but for global attributes we can fetch them or use placeholders
                        // Simplified: clear and let user manage, or fetch names from availableAttrSelect
                        selectedAttributes = [];
                        Object.entries(attrsMap).forEach(([attrId, termsSet]) => {
                            const option = [...availableAttrSelect.options].find(o => o.value == attrId);
                            const attrName = option ? option.text : 'Atribut #' + attrId;
                            selectedAttributes.push({
                                id: attrId,
                                name: attrName,
                                terms: Array.from(termsSet).map(t => ({ name: t }))
                            });
                        });
                        renderSelectedAttributes();
                    }

                    if (product.upsell_ids) {
                        document.getElementById('prod-upsells').value = product.upsell_ids;
                    }
                    if (product.cross_sell_ids) {
                        document.getElementById('prod-cross-sells').value = product.cross_sell_ids;
                    }

                    if (product.image_url) {
                        document.getElementById('prod-image').value = product.image_url;
                        const previewWrap = document.getElementById('prod-image-preview');
                        const previewImg = document.getElementById('prod-image-thumb');
                        const removeBtn = document.getElementById('prod-remove-image');

                        if (previewImg) previewImg.src = product.image_url;
                        if (previewWrap) previewWrap.style.display = 'block';
                        if (removeBtn) removeBtn.style.display = 'inline-block';
                    }

                    submitBtn.innerText = 'Update Produk';
                    submitBtn.disabled = false;

                    // Load Gallery if any
                    if (product.gallery_ids && product.gallery_ids.length > 0) {
                        document.getElementById('prod-gallery').value = product.gallery_ids.join(',');
                        renderGalleryContainer(product.gallery_ids);
                    }
                })
                .catch(e => {
                    alert('Gagal memuat data produk.');
                    submitBtn.innerText = 'Update Produk';
                    submitBtn.disabled = false;
                });
        }

        // Handle Submit
        addForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const pId = document.getElementById('prod-id').value;
            const title = document.getElementById('prod-name').value;
            const description = document.getElementById('prod-description').value;
            const price = document.getElementById('prod-price').value || 0;
            const salePrice = document.getElementById('prod-sale-price').value || '';
            const sku = document.getElementById('prod-sku').value || '';
            const stock = document.getElementById('prod-stock').value || 0;
            const status = document.getElementById('prod-status').value || 'publish';
            const imageUrl = document.getElementById('prod-image')?.value || '';
            const upsellIds = document.getElementById('prod-upsells')?.value || '';
            const crossSellIds = document.getElementById('prod-cross-sells')?.value || '';
            const msgEl = document.getElementById('prod-form-message');

            if (!title) return;

            submitBtn.disabled = true;
            submitBtn.innerText = pId ? 'Updating...' : 'Menyimpan...';

            const method = pId ? 'PUT' : 'POST';
            const url = pId ? `${apiBase}/${pId}` : apiBase;

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'X-WP-Nonce': nonce,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        description: description,
                        price: price,
                        sale_price: salePrice,
                        sku: sku,
                        stock_qty: stock,
                        status: status,
                        type: prodType.value,
                        image_url: imageUrl,
                        gallery_ids: document.getElementById('prod-gallery')?.value ? document.getElementById('prod-gallery').value.split(',') : [],
                        upsell_ids: upsellIds,
                        cross_sell_ids: crossSellIds,
                        variations: currentVariations
                    })
                });

                if (res.ok) {
                    if (msgEl) {
                        msgEl.innerText = pId ? 'Produk Diperbarui!' : 'Produk Tersimpan!';
                        msgEl.style.display = 'inline';
                    }
                    // Redirect back to list
                    setTimeout(() => {
                        window.location.href = '?page=owwc-products';
                    }, 1000);
                } else {
                    alert(pId ? 'Error update produk.' : 'Error menambah produk.');
                    submitBtn.disabled = false;
                    submitBtn.innerText = pId ? 'Update Produk' : 'Simpan Produk';
                }
            } catch (error) {
                alert('Terjadi kesalahan jaringan.');
                submitBtn.disabled = false;
                submitBtn.innerText = pId ? 'Update Produk' : 'Simpan Produk';
            }
        });
    }

    // ================================================================
    // WordPress Media Library Upload Integration
    // ================================================================
    const uploadBtn = document.getElementById('prod-upload-btn');
    const imageInput = document.getElementById('prod-image');
    const previewWrap = document.getElementById('prod-image-preview');
    const previewImg = document.getElementById('prod-image-thumb');
    const removeBtn = document.getElementById('prod-remove-image');

    let mediaFrame = null;

    if (uploadBtn && typeof wp !== 'undefined' && wp.media) {
        /**
         * Buka WordPress Media Library modal saat tombol "Pilih Gambar" diklik.
         * Hanya gambar (image/*) yang bisa dipilih.
         */
        uploadBtn.addEventListener('click', function (e) {
            e.preventDefault();

            // Reuse frame jika sudah ada
            if (mediaFrame) {
                mediaFrame.open();
                return;
            }

            mediaFrame = wp.media({
                title: 'Pilih Gambar Produk',
                button: { text: 'Gunakan Gambar Ini' },
                multiple: false,
                library: { type: 'image' }
            });

            // Saat gambar dipilih dari media library
            mediaFrame.on('select', function () {
                const attachment = mediaFrame.state().get('selection').first().toJSON();
                // Gunakan ukuran medium jika tersedia, fallback ke full
                const imgUrl = (attachment.sizes && attachment.sizes.medium)
                    ? attachment.sizes.medium.url
                    : attachment.url;

                imageInput.value = imgUrl;
                previewImg.src = imgUrl;
                previewWrap.style.display = 'block';
                if (removeBtn) removeBtn.style.display = 'inline-block';
                uploadBtn.textContent = '📷 Ganti Gambar';
            });

            mediaFrame.open();
        });

        // Hapus gambar yang dipilih
        if (removeBtn) {
            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                imageInput.value = '';
                previewImg.src = '';
                previewWrap.style.display = 'none';
                removeBtn.style.display = 'none';
                uploadBtn.textContent = '📷 Pilih Gambar';
            });
        }
    }

    // ================================================================
    // Gallery Management
    // ================================================================
    const galleryBtn = document.getElementById('owwc-add-gallery-btn');
    const galleryContainer = document.getElementById('owwc-gallery-container');
    const galleryInput = document.getElementById('prod-gallery');
    let galleryFrame = null;

    if (galleryBtn && typeof wp !== 'undefined' && wp.media) {
        galleryBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (galleryFrame) {
                galleryFrame.open();
                return;
            }

            galleryFrame = wp.media({
                title: 'Pilih Galeri Produk',
                button: { text: 'Tambahkan ke Galeri' },
                multiple: true,
                library: { type: 'image' }
            });

            galleryFrame.on('select', () => {
                const selections = galleryFrame.state().get('selection');
                let currentIds = galleryInput.value ? galleryInput.value.split(',') : [];

                selections.forEach(attachment => {
                    const id = attachment.id.toString();
                    if (!currentIds.includes(id)) {
                        currentIds.push(id);
                    }
                });

                galleryInput.value = currentIds.join(',');
                renderGalleryContainer(currentIds);
            });

            galleryFrame.open();
        });
    }

    async function renderGalleryContainer(ids) {
        if (!galleryContainer) return;
        galleryContainer.innerHTML = '';

        if (!ids || ids.length === 0) return;

        // Fetch image URLs if we only have IDs (e.g. from DB)
        // Or if we just selected them, we might already have them in selections? 
        // To be safe and consistent, let's fetch metadata for these IDs
        for (const id of ids) {
            try {
                // We use WP REST API to get attachment URL
                const res = await fetch(`${owwcSettings.restUrl}wp/v2/media/${id}`);
                const media = await res.json();
                const url = (media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail)
                    ? media.media_details.sizes.thumbnail.source_url
                    : media.source_url;

                const item = document.createElement('div');
                item.className = 'owwc-gallery-item';
                item.style.position = 'relative';
                item.style.aspectRatio = '1/1';
                item.style.borderRadius = '4px';
                item.style.overflow = 'hidden';
                item.style.border = '1px solid #ddd';

                item.innerHTML = `
                    <img src="${url}" style="width:100%; height:100%; object-fit:cover;">
                    <button type="button" class="owwc-remove-gallery-item" data-id="${id}" style="position:absolute; top:2px; right:2px; background:rgba(255,0,0,0.7); color:white; border:none; border-radius:50%; width:18px; height:18px; font-size:12px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
                `;
                galleryContainer.appendChild(item);

                item.querySelector('.owwc-remove-gallery-item').addEventListener('click', (e) => {
                    e.preventDefault();
                    const newIds = galleryInput.value.split(',').filter(item_id => item_id != id);
                    galleryInput.value = newIds.join(',');
                    renderGalleryContainer(newIds);
                });

            } catch (e) { console.error("Error loading gallery image", e); }
        }
    }

    /**
     * Helper: Reset preview gambar setelah form submit berhasil.
     */
    function resetImagePreview() {
        if (imageInput) imageInput.value = '';
        if (previewImg) previewImg.src = '';
        if (previewWrap) previewWrap.style.display = 'none';
        if (removeBtn) removeBtn.style.display = 'none';
        if (uploadBtn) uploadBtn.textContent = '📷 Pilih Gambar';

        if (galleryInput) galleryInput.value = '';
        if (galleryContainer) galleryContainer.innerHTML = '';
    }

    // Patch form reset agar juga mereset preview gambar
    const origFormReset = addForm ? addForm.reset.bind(addForm) : null;
    if (addForm) {
        const originalResetFn = addForm.reset;
        addForm.reset = function () {
            originalResetFn.call(this);
            resetImagePreview();
        };
    }
});
