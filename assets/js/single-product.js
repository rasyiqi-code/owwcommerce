/**
 * OwwCommerce Single Product Page Script (Vanilla JS)
 * 
 * Mengelola interaktivitas di halaman detail produk:
 * - Kuantitas produk (+ / -)
 * - Pemilihan variasi produk (pills)
 * - Rekomendasi produk (upsells)
 * - Pengiriman & pemuatan ulasan produk (reviews)
 */

document.addEventListener('DOMContentLoaded', () => {
    const settingsEl = document.getElementById('owwc-product-settings-json');
    if (!settingsEl) return;

    const owwcProductSettings = JSON.parse(settingsEl.textContent);

    const btn = document.getElementById('owwc-single-add-btn');
    const mobileTrigger = document.getElementById('owwc-mobile-buy-trigger');
    const qtyInput = document.getElementById('owwc-single-qty');
    const pillGroups = document.querySelectorAll('.owwc-variation-options');
    const priceDisplay = document.querySelectorAll('.owwc-product-price'); // Main dan mobile
    const stockDisplay = document.querySelector('.owwc-stock-status');
    
    // Logika tombol Qty plus/minus
    const minusBtn = document.querySelector('.owwc-qty-minus');
    const plusBtn = document.querySelector('.owwc-qty-plus');

    if (minusBtn && plusBtn && qtyInput) {
        minusBtn.addEventListener('click', () => {
            if (qtyInput.value > 1) {
                qtyInput.value = parseInt(qtyInput.value) - 1;
                qtyInput.dispatchEvent(new Event('input'));
            }
        });
        plusBtn.addEventListener('click', () => {
            const max = parseInt(qtyInput.max) || 999;
            if (qtyInput.value < max) {
                qtyInput.value = parseInt(qtyInput.value) + 1;
                qtyInput.dispatchEvent(new Event('input'));
            }
        });
    }

    const productType = owwcProductSettings.productType;
    const variations = owwcProductSettings.variations || [];

    // Sinkronisasi input quantity
    if (qtyInput && btn) {
        qtyInput.addEventListener('input', () => {
            btn.setAttribute('data-qty', qtyInput.value);
        });
    }

    // Scroll otomatis ke tombol beli di mobile
    if (mobileTrigger) {
        mobileTrigger.addEventListener('click', () => {
            const actionForm = document.querySelector('.owwc-add-to-cart-form');
            if (actionForm) {
                window.scrollTo({
                    top: actionForm.offsetTop - 150,
                    behavior: 'smooth'
                });
            }
        });
    }

    // Logika Pemilihan Variasi via Pills
    if (productType === 'variable' && btn && stockDisplay) {
        const selected = {};

        pillGroups.forEach(group => {
            const attrId = group.getAttribute('data-attribute-id');
            const pills = group.querySelectorAll('.owwc-variation-pill');

            pills.forEach(pill => {
                pill.addEventListener('click', () => {
                    pills.forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    
                    selected[attrId] = pill.getAttribute('data-value');
                    checkVariations();
                });
            });
        });

        const checkVariations = () => {
            const allSelected = pillGroups.length === Object.keys(selected).length;

            if (!allSelected) {
                btn.disabled = true;
                const btnText = btn.querySelector('.btn-text');
                if (btnText) btnText.textContent = 'Pilih Variasi';
                return;
            }

            // Cari variasi yang cocok
            const variation = variations.find(v => {
                return Object.entries(selected).every(([attrId, termName]) => {
                    return v.attributes[attrId] === termName;
                });
            });

            if (variation) {
                // Update Harga
                const price = variation.sale_price || variation.price;
                const priceFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price).replace('IDR', 'Rp');
                
                let newPriceHtml = '';
                if (variation.sale_price) {
                    const oldPrice = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(variation.price).replace('IDR', 'Rp');
                    newPriceHtml = `<del>${oldPrice}</del> <ins>${priceFormatted}</ins>`;
                } else {
                    newPriceHtml = `<span>${priceFormatted}</span>`;
                }
                
                priceDisplay.forEach(pd => pd.innerHTML = newPriceHtml);

                // Update Stok & Button
                btn.setAttribute('data-variation-id', variation.id);
                const btnText = btn.querySelector('.btn-text');
                if (variation.stock_qty > 0) {
                    stockDisplay.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Ready Stock (${variation.stock_qty} unit)`;
                    stockDisplay.className = 'owwc-stock-status owwc-stock-status--in-stock';
                    btn.disabled = false;
                    if (btnText) btnText.textContent = 'Beli Sekarang';
                    qtyInput.max = variation.stock_qty;
                } else {
                    stockDisplay.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> Maaf, stok habis`;
                    stockDisplay.className = 'owwc-stock-status owwc-stock-status--out-stock';
                    btn.disabled = true;
                    if (btnText) btnText.textContent = 'Stok Habis';
                }
            } else {
                priceDisplay.forEach(pd => pd.innerHTML = '<span>Habis/Tidak ada</span>');
                btn.disabled = true;
                const btnText = btn.querySelector('.btn-text');
                if (btnText) btnText.textContent = 'Tidak Tersedia';
            }
        };
        
        // Initial state
        btn.disabled = true;
        const btnText = btn.querySelector('.btn-text');
        if (btnText) btnText.textContent = 'Pilih Variasi';
    }

    // Memuat Smart Recommendations
    const loadRecommendations = async () => {
        const apiRecUrl = owwcProductSettings.apiRecUrl;
        if (!apiRecUrl) return;

        try {
            const res = await fetch(apiRecUrl);
            const data = await res.json();

            if (data.upsells && data.upsells.length > 0) {
                const upsellWrap = document.getElementById('owwc-upsells-wrap');
                const upsellList = document.getElementById('owwc-upsells-list');
                
                if (upsellWrap && upsellList) {
                    upsellWrap.style.display = 'block';
                    upsellList.innerHTML = data.upsells.map(p => `
                        <div class="owwc-product-card owwc-recommendation-card">
                            <a href="${owwcSettings.homeUrl}${owwcSettings.productBase}/${p.slug}" style="text-decoration: none; color: inherit;">
                                <img src="${p.image_url || ''}" style="width: 100%; height: auto; border-radius: 4px; margin-bottom: 10px; display: block;">
                                <h3 style="font-size: 14px; margin-bottom: 8px;">${p.title}</h3>
                                <div class="owwc-product-price" style="font-weight: 600; color: var(--owwc-primary);">
                                    ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p.price).replace('IDR', 'Rp')}
                                </div>
                            </a>
                        </div>
                    `).join('');
                }
            }
        } catch (e) {
            console.error("Gagal memuat rekomendasi:", e);
        }
    };

    loadRecommendations();

    // ================================================================
    // LOGIKA ULASAN (REVIEWS)
    // ================================================================
    const reviewList = document.getElementById('owwc-review-list');
    const reviewForm = document.getElementById('owwc-review-form');
    const avgStars = document.getElementById('owwc-avg-stars');
    const avgNum = document.getElementById('owwc-avg-num');
    const reviewTitle = document.querySelector('.owwc-reviews-title');

    const renderStars = (rating) => {
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += `<span class="owwc-star ${i > Math.round(rating) ? 'empty' : ''}">★</span>`;
        }
        return starsHtml;
    };

    const loadReviews = async () => {
        const apiReviewUrl = owwcProductSettings.apiReviewUrl;
        if (!apiReviewUrl) return;

        try {
            const res = await fetch(apiReviewUrl);
            const data = await res.json();

            if (data.review_count > 0) {
                if (reviewTitle) reviewTitle.textContent = `Ulasan Pembeli (${data.review_count})`;
                if (avgNum) avgNum.textContent = data.average_rating.toFixed(1);
                if (avgStars) avgStars.innerHTML = renderStars(data.average_rating);

                if (reviewList) {
                    reviewList.innerHTML = data.items.map(r => `
                        <div class="owwc-review-item">
                            <div class="owwc-review-avatar">
                                <img src="${r.avatar_url || ''}" alt="Avatar">
                            </div>
                            <div class="owwc-review-content">
                                <div class="owwc-review-meta">
                                    <span class="owwc-review-author">${r.author_name || 'Anonim'}</span>
                                    <div class="owwc-rating-stars">
                                        ${renderStars(r.rating)}
                                    </div>
                                </div>
                                <div class="owwc-review-date">${new Date(r.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</div>
                                <div class="owwc-review-comment">${r.comment}</div>
                            </div>
                        </div>
                    `).join('');
                }
            } else {
                if (avgStars) avgStars.innerHTML = renderStars(0);
            }
        } catch (e) {
            console.error("Gagal memuat ulasan:", e);
        }
    };

    if (reviewForm) {
        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('owwc-submit-review');
            const msgEl = document.getElementById('owwc-review-msg');
            const formData = new FormData(reviewForm);
            const data = Object.fromEntries(formData.entries());

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengirim...';
            }

            const apiReviewUrl = owwcProductSettings.apiReviewUrl;

            try {
                const res = await fetch(apiReviewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': owwcSettings.nonce
                    },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    if (msgEl) {
                        msgEl.textContent = 'Ulasan berhasil dikirim!';
                        msgEl.style.color = '#2e7d32';
                        msgEl.style.display = 'inline';
                    }
                    reviewForm.reset();
                    loadReviews();
                } else {
                    const error = await res.json();
                    if (msgEl) {
                        msgEl.textContent = error.message || 'Gagal mengirim ulasan.';
                        msgEl.style.color = '#d32f2f';
                        msgEl.style.display = 'inline';
                    }
                }
            } catch (e) {
                if (msgEl) {
                    msgEl.textContent = 'Terjadi kesalahan jaringan.';
                    msgEl.style.display = 'inline';
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Kirim Ulasan';
                }
                setTimeout(() => { 
                    if (msgEl) msgEl.style.display = 'none'; 
                }, 5000);
            }
        });
    }

    loadReviews();
});
