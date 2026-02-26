/**
 * OwwCommerce Admin Products App (Vanilla JS)
 */
document.addEventListener('DOMContentLoaded', function () {
    const appContainer = document.getElementById('owwc-products-app');
    const btnAdd = document.getElementById('owwc-btn-add-product');

    if (!appContainer) return;

    // Load URL REST API WP
    const apiBase = `${owwcSettings.restUrl}owwc/v1/products`;
    const nonce = owwcSettings.nonce;

    // Fetch and display products
    async function fetchProducts() {
        appContainer.innerHTML = '<p>Loading products...</p>';
        try {
            const res = await fetch(apiBase, {
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
            appContainer.innerHTML = '<div class="notice notice-error"><p>Failed to load products. Check console for details.</p></div>';
        }
    }

    // Render HTML Table
    function renderTable(products) {
        if (!products.length) {
            appContainer.innerHTML = '<p>No products found. Start by adding one!</p>';
            return;
        }

        let html = `
            <table class="owwc-admin-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Product Name</th>
                        <th style="width: 20%;">Price (Rp)</th>
                        <th style="width: 15%;">Stock</th>
                        <th style="width: 15%; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        products.forEach(p => {
            html += `
                <tr>
                    <td><strong>${p.title}</strong></td>
                    <td>${Number(p.price).toLocaleString()}</td>
                    <td>
                        <span class="owwc-badge ${p.stock_qty > 0 ? 'completed' : 'failed'}">
                            ${p.stock_qty > 0 ? p.stock_qty + ' In Stock' : 'Out of Stock'}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <button class="owwc-admin-btn owwc-admin-btn-danger owwc-btn-delete" data-id="${p.id}" style="padding: 6px 12px; font-size: 12px;">Delete</button>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        appContainer.innerHTML = html;

        // Attach delete events
        document.querySelectorAll('.owwc-btn-delete').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this product?')) {
                    await deleteProduct(id);
                }
            });
        });
    }

    // Delete Product API call
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

    // Add Product Form Submit
    const addForm = document.getElementById('owwc-add-product-form');
    if (addForm) {
        addForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const title = document.getElementById('prod-name').value;
            const price = document.getElementById('prod-price').value || 0;
            const stock = document.getElementById('prod-stock').value || 10;
            const msgEl = document.getElementById('prod-form-message');
            const submitBtn = addForm.querySelector('button[type="submit"]');

            if (!title) return;

            submitBtn.disabled = true;
            submitBtn.innerText = 'Menyimpan...';

            try {
                const res = await fetch(apiBase, {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': nonce,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        price: price,
                        stock_qty: stock
                    })
                });

                if (res.ok) {
                    fetchProducts();
                    addForm.reset();
                    if (msgEl) {
                        msgEl.style.display = 'inline';
                        setTimeout(() => msgEl.style.display = 'none', 3000);
                    }
                } else {
                    alert('Error menambah produk.');
                }
            } catch (error) {
                alert('Terjadi kesalahan jaringan rute.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Simpan Produk';
            }
        });
    }

    // Initial load
    fetchProducts();
});
