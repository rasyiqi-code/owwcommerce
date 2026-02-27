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
        this.shippingCost = 0;
        this.appliedCoupon = null;
        this.lastCartSubtotal = 0;

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
                let subtotal = cartData.total;
                this.lastCartSubtotal = subtotal; // Simpan untuk shipping check
                let html = '<div>';
                Object.values(cartData.items).forEach(item => {
                    html += `
                        <div class="owwc-summary-row">
                            <span>${item.title} x ${item.qty}</span>
                            <span>${window.owwcFormatPrice(item.price * item.qty)}</span>
                        </div>
                    `;
                });
                html += '</div>';

                let discountAmount = 0;
                if (this.appliedCoupon) {
                    if (this.appliedCoupon.type === 'percent') {
                        discountAmount = (subtotal * this.appliedCoupon.amount) / 100;
                    } else {
                        discountAmount = Math.min(this.appliedCoupon.amount, subtotal);
                    }
                }

                const total = Math.max(0, Number(subtotal) + Number(this.shippingCost) - Number(discountAmount));

                html += `
                    <div class="owwc-summary-row">
                        <span>Subtotal</span>
                        <span>${window.owwcFormatPrice(subtotal)}</span>
                    </div>
                    <div class="owwc-summary-row">
                        <span>Biaya Pengiriman</span>
                        <span>${this.shippingCost > 0 ? window.owwcFormatPrice(this.shippingCost) : 'Gratis'}</span>
                    </div>
                `;

                if (discountAmount > 0) {
                    html += `
                        <div class="owwc-summary-row owwc-text-success">
                            <span>Diskon (${this.appliedCoupon.code})</span>
                            <span>- ${window.owwcFormatPrice(discountAmount)}</span>
                        </div>
                    `;
                }

                html += `
                    <div class="owwc-summary-total">
                        <span>Total Tagihan</span>
                        <span>${window.owwcFormatPrice(total)}</span>
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
        // Event listener untuk pilihan pengiriman agar update ongkir di review
        const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
        shippingRadios.forEach(radio => {
            radio.addEventListener('change', async (e) => {
                const subtotal = this.lastCartSubtotal || 0;
                const flatRate = owwcSettings.flatRateCost || 15000;
                const threshold = owwcSettings.freeShippingThreshold || 0;

                if (e.target.value === 'flat_rate') {
                    if (threshold > 0 && subtotal >= threshold) {
                        this.shippingCost = 0;
                    } else {
                        this.shippingCost = flatRate;
                    }
                } else {
                    this.shippingCost = 0;
                }
                this.loadCartReview();
            });
            // Trigger awal
            if (radio.checked) {
                const subtotal = this.lastCartSubtotal || 0;
                const flatRate = owwcSettings.flatRateCost || 15000;
                const threshold = owwcSettings.freeShippingThreshold || 0;

                if (radio.value === 'flat_rate') {
                    if (threshold > 0 && subtotal >= threshold) {
                        this.shippingCost = 0;
                    } else {
                        this.shippingCost = flatRate;
                    }
                } else {
                    this.shippingCost = 0;
                }
                this.loadCartReview();
            }
        });

        // Event listener untuk Kupon
        const applyBtn = document.getElementById('owwc-apply-coupon');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => this.applyCoupon());
        }

        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            this.clearError();

            const formData = new FormData(this.form);
            const payload = Object.fromEntries(formData.entries());

            // Tambahkan kupon yang sedang aktif ke payload
            if (this.appliedCoupon) {
                payload.coupon_code = this.appliedCoupon.code;
            }

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
                            window.location.href = '/checkout/order-received/' + data.order_id;
                        }
                    }, 1500);

                } else {
                    this.showError((data.message || 'Gagal memproses checkout.'));
                    this.setLoading(false);
                }

            } catch (error) {
                console.error('Checkout error:', error);
                this.showError('Terjadi kesalahan jaringan.');
                this.setLoading(false);
            }
        });
    }

    async applyCoupon() {
        const input = document.getElementById('owwc-coupon-code');
        const msgBox = document.getElementById('owwc-coupon-message');
        const code = input.value.trim();

        if (!code) return;

        try {
            const apiCoupon = (typeof owwcSettings !== 'undefined') ? `${owwcSettings.restUrl}owwc/v1/coupons/validate?code=${encodeURIComponent(code)}` : `/wp-json/owwc/v1/coupons/validate?code=${encodeURIComponent(code)}`;
            const res = await fetch(apiCoupon);
            const data = await res.json();

            msgBox.style.display = 'block';
            if (res.ok && data.success) {
                this.appliedCoupon = data.coupon;
                msgBox.innerText = data.message;
                msgBox.style.color = 'green';
                this.loadCartReview();
            } else {
                msgBox.innerText = data.message || 'Kupon tidak valid.';
                msgBox.style.color = 'red';
            }
        } catch (e) {
            console.error('Coupon error', e);
        }
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
