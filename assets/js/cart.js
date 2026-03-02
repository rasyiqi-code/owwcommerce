/**
 * OwwCommerce Cart Engine (Vanilla JS)
 * 
 * Mengelola interaksi keranjang belanja: fetch, add, remove, dan render UI.
 * Menggunakan CSS class dari frontend.css & frontend-pages.css (tanpa inline style).
 */
class OwwCommerceCart {
    constructor() {
        this.apiBase = (typeof owwcSettings !== 'undefined')
            ? `${owwcSettings.restUrl}owwc/v1/cart`
            : '/wp-json/owwc/v1/cart';
        this.nonce = (typeof owwcSettings !== 'undefined') ? owwcSettings.nonce : '';
        this.init();
    }

    init() {
        this.createToastContainer();
        this.bindEvents();
        this.refreshCartUI();
        window.owwcFormatPrice = this.formatPrice.bind(this);
    }

    /**
     * Format price berdasarkan owwcSettings.
     * @param {number} price 
     */
    formatPrice(price) {
        const symbol = owwcSettings.currencySymbol || 'Rp';
        const thousandSep = owwcSettings.thousandSep || '.';
        const decimalSep = owwcSettings.decimalSep || ',';

        // Format angka dengan pemisah ribuan & desimal (tanpa sen/desimal untuk saat ini)
        let parts = Number(price).toFixed(0).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSep);

        return `${symbol} ${parts.join(decimalSep)}`;
    }

    /* ==============================================================
       TOAST NOTIFICATIONS
       ============================================================== */
    createToastContainer() {
        if (!document.getElementById('owwc-toast-container')) {
            const container = document.createElement('div');
            container.id = 'owwc-toast-container';
            container.className = 'owwc-toast-container';
            document.body.appendChild(container);
        }
    }

    /**
     * Tampilkan notifikasi toast.
     * @param {string} message - Teks notifikasi
     * @param {'success'|'error'} type - Jenis notifikasi
     */
    showToast(message, type = 'success') {
        const container = document.getElementById('owwc-toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `owwc-toast ${type}`;

        // Ikon SVG berdasarkan tipe
        const icon = type === 'success'
            ? '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
            : '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

        toast.innerHTML = `${icon} <span>${message}</span>`;
        container.appendChild(toast);

        // Animasi masuk
        requestAnimationFrame(() => toast.classList.add('owwc-show'));

        // Auto-hapus setelah 3 detik
        setTimeout(() => {
            toast.classList.remove('owwc-show');
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }

    /* ==============================================================
       API CALLS
       ============================================================== */
    async fetchCart() {
        try {
            const res = await fetch(this.apiBase);
            return await res.json();
        } catch (e) {
            console.error('OwwCommerce Cart Error:', e);
            return null;
        }
    }

    async addToCart(productId, qty = 1, variationId = 0) {
        try {
            const res = await fetch(this.apiBase, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': this.nonce,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, variation_id: variationId, qty })
            });
            const data = await res.json();
            this.refreshCartUI(data);

            if (data.success !== false) {
                this.showToast('Produk berhasil ditambahkan ke keranjang!', 'success');
            } else {
                this.showToast('Gagal menambahkan produk.', 'error');
            }
            return data;
        } catch (e) {
            console.error('Failed to add to cart:', e);
            this.showToast('Terjadi kesalahan jaringan.', 'error');
        }
    }

    async removeFromCart(key) {
        try {
            const res = await fetch(`${this.apiBase}/${key}`, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': this.nonce }
            });
            const data = await res.json();
            this.refreshCartUI(data);
            this.showToast('Produk dihapus dari keranjang.', 'success');
            return data;
        } catch (e) {
            console.error('Failed to remove from cart:', e);
            this.showToast('Gagal menghapus produk.', 'error');
        }
    }

    /* ==============================================================
       UI UPDATE
       ============================================================== */
    async refreshCartUI(cartData = null) {
        if (!cartData) {
            cartData = await this.fetchCart();
        }
        if (!cartData) return;

        // Render halaman cart (jika elemen ada)
        this.renderCartPage(cartData);

        // Update badge/counter di header atau floating cart
        document.querySelectorAll('.owwc-cart-count').forEach(el => {
            el.textContent = cartData.count;
        });

        // Emit event custom untuk integrase tema
        document.dispatchEvent(
            new CustomEvent('owwc_cart_updated', { detail: cartData })
        );
    }

    /* ==============================================================
       EVENT BINDING
       ============================================================== */
    bindEvents() {
        document.body.addEventListener('click', async (e) => {
            // === Tombol "Tambah ke Keranjang" ===
            const addBtn = e.target.closest('.owwc-add-to-cart-btn');
            if (addBtn) {
                // Skip if it's a marketplace or whatsapp link
                if (addBtn.classList.contains('owwc-marketplace-btn') || addBtn.classList.contains('owwc-whatsapp-btn')) {
                    return;
                }

                e.preventDefault();
                const productId = addBtn.dataset.productId;
                const variationId = parseInt(addBtn.dataset.variationId || 0, 10);
                const qty = parseInt(addBtn.dataset.qty || 1, 10);
                const origText = addBtn.querySelector('.btn-text') ? addBtn.querySelector('.btn-text').textContent : addBtn.textContent;

                const btnLabel = addBtn.querySelector('.btn-text') || addBtn;
                btnLabel.textContent = 'Menambahkan...';
                addBtn.disabled = true;

                // Tampilkan spinner jika ada
                const spinner = addBtn.querySelector('.owwc-spinner');
                if (spinner) spinner.style.display = 'inline-block';

                await this.addToCart(productId, qty, variationId);

                addBtn.textContent = '✓ Ditambahkan';
                if (spinner) spinner.style.display = 'none';

                setTimeout(() => {
                    addBtn.textContent = origText;
                    addBtn.disabled = false;
                }, 2000);
            }

            // === Tombol "Hapus dari Keranjang" ===
            const removeBtn = e.target.closest('.owwc-cart-item-remove');
            if (removeBtn) {
                e.preventDefault();
                const key = removeBtn.dataset.cartKey;
                removeBtn.style.opacity = '0.5';
                removeBtn.style.pointerEvents = 'none';
                await this.removeFromCart(key);
            }
        });
    }

    /* ==============================================================
       RENDER HALAMAN CART
       ============================================================== */
    renderCartPage(cartData) {
        const contentBox = document.getElementById('owwc-cart-content');
        const summaryBox = document.getElementById('owwc-cart-summary');
        const totalSpan = document.getElementById('owwc-cart-total');

        // Jika elemen tidak ada, user tidak sedang di halaman cart
        if (!contentBox) return;

        // --- Empty State ---
        if (cartData.count === 0) {
            contentBox.innerHTML = this.renderEmptyState();
            if (summaryBox) summaryBox.style.display = 'none';
            return;
        }

        // --- Render tabel keranjang ---
        contentBox.innerHTML = this.renderCartTable(cartData);

        // --- Tampilkan summary ---
        if (summaryBox && totalSpan) {
            summaryBox.style.display = 'block';
            totalSpan.textContent = this.formatPrice(cartData.total);
        }
    }

    /**
     * Render tabel cart dengan class CSS (tanpa inline style).
     * Di mobile, CSS akan mengubah tabel ini menjadi card-based layout.
     */
    renderCartTable(cartData) {
        let rows = '';
        Object.values(cartData.items).forEach(item => {
            const subtotal = Number(item.price * item.qty).toLocaleString('id-ID');
            const price = Number(item.price).toLocaleString('id-ID');
            rows += `
                <tr>
                    <td class="owwc-cart-col-thumb">
                        <div class="owwc-cart-thumb">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                    </td>
                    <td class="owwc-cart-col-name">${item.title}</td>
                    <td class="owwc-cart-col-price">${this.formatPrice(item.price)}</td>
                    <td class="owwc-cart-col-qty">${item.qty}x</td>
                    <td class="owwc-cart-col-subtotal">${this.formatPrice(item.price * item.qty)}</td>
                    <td class="owwc-cart-col-remove">
                        <button class="owwc-cart-item-remove"
                                data-cart-key="${item.key}"
                                aria-label="Hapus ${item.title}">
                            ×
                        </button>
                    </td>
                </tr>`;
        });

        return `
            <table class="owwc-table">
                <thead>
                    <tr>
                        <th colspan="2">Produk</th>
                        <th class="owwc-cart-col-price">Harga</th>
                        <th class="owwc-cart-col-qty">Qty</th>
                        <th class="owwc-cart-col-subtotal">Subtotal</th>
                        <th class="owwc-cart-col-remove"></th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`;
    }

    /**
     * Render empty state dengan ilustrasi SVG keranjang kosong.
     */
    renderEmptyState() {
        return `
            <div class="owwc-cart-empty">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <p>Keranjang belanja Anda masih kosong.</p>
                <a href="/" class="owwc-btn owwc-btn--outline">Mulai Belanja</a>
            </div>`;
    }
}

// Inisiasi otomatis saat DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.owwcCart = new OwwCommerceCart();
});
