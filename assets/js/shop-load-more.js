/**
 * OwwCommerce - Shop Load More AJAX logic
 */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('owwc-load-more');
        const grid = document.getElementById('owwc-products-grid');

        if (!btn || !grid) return;

        btn.addEventListener('click', function () {
            const page = parseInt(this.dataset.page) + 1;
            const q = this.dataset.q || '';
            const cat = this.dataset.category || '';
            const order = this.dataset.orderby || 'newest';
            const loader = this.querySelector('.owwc-loader');
            const text = this.querySelector('span');

            // Pastikan owwcSettings tersedia
            if (typeof owwcSettings === 'undefined') {
                console.error('owwcSettings is not defined');
                return;
            }

            btn.disabled = true;
            if (loader) loader.style.display = 'inline-block';
            if (text) text.style.opacity = '0.5';

            // Gunakan URL constructor yang lebih aman
            const apiUrl = owwcSettings.restUrl.replace(/\/$/, '') + '/owwc/v1/products/public';
            const url = new URL(apiUrl);
            url.searchParams.append('page', page);
            if (q) url.searchParams.append('q', q);
            if (cat) url.searchParams.append('category', cat);
            if (order) url.searchParams.append('orderby', order);

            fetch(url)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.html) {
                            grid.insertAdjacentHTML('beforeend', data.html);
                            btn.dataset.page = page;

                            // Re-init Cart Event Listeners
                            if (window.owwcCart) {
                                window.owwcCart.init();
                            }
                        }
                        if (!data.has_more) {
                            btn.parentElement.remove();
                        }
                    } catch (e) {
                        console.error('JSON Parse Error:', e, text);
                    }
                })
                .catch(err => console.error('Load more error:', err))
                .finally(() => {
                    btn.disabled = false;
                    if (loader) loader.style.display = 'none';
                    if (text) text.style.opacity = '1';
                });
        });
    });
})();
