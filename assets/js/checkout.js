/**
 * OwwCommerce Checkout App (Vanilla JS)
 */
class OwwCommerceCheckout {
    constructor() {
        this.apiBase = (typeof owwcSettings !== 'undefined') ? `${owwcSettings.restUrl}owwc/v1/checkout` : '/wp-json/owwc/v1/checkout';
        this.nonce = (typeof owwcSettings !== 'undefined') ? owwcSettings.nonce : '';
        this.form = document.getElementById('owwc-checkout-form-main');
        this.submitBtn = document.getElementById('owwc-place-order');
        this.errorBox = document.getElementById('owwc-checkout-error');
        this.cartReviewBox = document.getElementById('owwc-checkout-cart-review');

        if (this.form) {
            this.init();
        }
    }

    init() {
        this.bindEvents();
        this.loadCartReview();
    }

    async loadCartReview() {
        if (!this.cartReviewBox) return;

        try {
            const apiCart = (typeof owwcSettings !== 'undefined') ? `${owwcSettings.restUrl}owwc/v1/cart` : '/wp-json/owwc/v1/cart';
            const res = await fetch(apiCart);
            const cartData = await res.json();

            if (cartData && cartData.count > 0) {
                let html = '<div>';
                Object.values(cartData.items).forEach(item => {
                    html += `
                        <div class="owwc-summary-row">
                            <span>${item.title} x ${item.qty}</span>
                            <span>Rp${Number(item.price * item.qty).toLocaleString()}</span>
                        </div>
                    `;
                });
                html += '</div>';

                // Nanti ongkir bisa ditambahkan secara dinamis di sini
                html += `
                    <div class="owwc-summary-row">
                        <span>Subtotal</span>
                        <span>Rp${Number(cartData.total).toLocaleString()}</span>
                    </div>
                    <div class="owwc-summary-total">
                        <span>Total Tagihan</span>
                        <span>Rp${Number(cartData.total).toLocaleString()}</span>
                    </div>
                `;
                this.cartReviewBox.innerHTML = html;
            } else {
                this.cartReviewBox.innerHTML = '<p>Keranjang Anda kosong. <a href="/">Kembali belanja</a>.</p>';
                if (this.submitBtn) this.submitBtn.disabled = true;
            }

        } catch (e) {
            console.error('Failed to load cart review', e);
            this.cartReviewBox.innerHTML = '<p>Gagal memuat ringkasan keranjang.</p>';
        }
    }

    bindEvents() {
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            this.clearError();

            const formData = new FormData(this.form);
            const payload = Object.fromEntries(formData.entries());

            // Anti-spam check
            if (payload.owwc_anti_spam !== '') {
                console.log('Spam bot detected');
                return;
            }

            this.setLoading(true);

            try {
                const res = await fetch(this.apiBase, {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': this.nonce,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    if (window.owwcCart && typeof window.owwcCart.showToast === 'function') {
                        window.owwcCart.showToast('Pesanan berhasil dibuat!', 'success');
                    }

                    this.submitBtn.innerText = 'Mengalihkan...';

                    // Redirect handle dari server
                    setTimeout(() => {
                        if (data.order && data.order.redirect_url) {
                            window.location.href = data.order.redirect_url;
                        } else {
                            window.location.href = '/?order-received=' + data.order_id;
                        }
                    }, 1500);

                } else {
                    this.showError((data.message || 'Gagal memproses checkout.'));
                    this.setLoading(false);
                }

            } catch (error) {
                console.error('Checkout error:', error);
                this.showError('Terjadi kesalahan jaringan rute.');
                this.setLoading(false);
            }
        });
    }

    setLoading(isLoading) {
        if (isLoading) {
            this.submitBtn.disabled = true;
            this.submitBtn.innerText = 'Memproses Pesanan...';
        } else {
            this.submitBtn.disabled = false;
            this.submitBtn.innerText = 'Buat Pesanan';
        }
    }

    showError(msg) {
        if (this.errorBox) {
            this.errorBox.innerHTML = `<strong>Error:</strong> ${msg}`;
            this.errorBox.style.display = 'block';
        }
        if (window.owwcCart && typeof window.owwcCart.showToast === 'function') {
            window.owwcCart.showToast(msg, 'error');
        }
    }

    clearError() {
        if (this.errorBox) {
            this.errorBox.innerHTML = '';
            this.errorBox.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.owwcCheckout = new OwwCommerceCheckout();
});
