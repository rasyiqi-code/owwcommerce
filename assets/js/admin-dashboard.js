document.addEventListener('DOMContentLoaded', function () {
    const revenueEl = document.getElementById('stat-revenue');
    const ordersEl = document.getElementById('stat-orders');
    const productsEl = document.getElementById('stat-products');
    const topProductsEl = document.getElementById('top-products-list');
    const ctx = document.getElementById('owwc-sales-chart');

    if (!ctx) return;

    /**
     * Format price berdasarkan owwcSettings.
     */
    function formatPrice(price) {
        const symbol = owwcSettings.currencySymbol || 'Rp';
        const thousandSep = owwcSettings.thousandSep || '.';
        const decimalSep = owwcSettings.decimalSep || ',';

        let parts = Number(price).toFixed(0).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSep);

        return `${symbol} ${parts.join(decimalSep)}`;
    }

    // 1. Fetch Summary Data
    fetch(`${owwcSettings.restUrl}owwc/v1/analytics/summary`, {
        headers: { 'X-WP-Nonce': owwcSettings.nonce }
    })
        .then(res => res.json())
        .then(data => {
            revenueEl.innerText = formatPrice(data.total_revenue);
            ordersEl.innerText = data.total_orders;
            productsEl.innerText = data.total_products;

            if (data.top_products && data.top_products.length > 0) {
                topProductsEl.innerHTML = data.top_products.map(p => `
                <div class="owwc-top-product-item">
                    <div>
                        <div style="font-weight: 500;">${p.title}</div>
                        <div class="owwc-top-product-meta">${p.total_qty} terjual</div>
                    </div>
                    <div style="font-weight: bold;">${formatPrice(p.total_sales)}</div>
                </div>
            `).join('');
            } else {
                topProductsEl.innerHTML = '<p style="color: #999;">Belum ada data penjualan.</p>';
            }
        });

    // 2. Fetch Sales Chart Data
    fetch(`${owwcSettings.restUrl}owwc/v1/analytics/sales?days=7`, {
        headers: { 'X-WP-Nonce': owwcSettings.nonce }
    })
        .then(res => res.json())
        .then(data => {
            const labels = data.map(d => {
                const date = new Date(d.date);
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            });
            const values = data.map(d => d.total);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pendapatan',
                        data: values,
                        borderColor: '#dfb768',
                        backgroundColor: 'rgba(223, 183, 104, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#dfb768',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                callback: function (value) {
                                    const symbol = owwcSettings.currencySymbol || 'Rp';
                                    return symbol + ' ' + (value / 1000) + 'k';
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        });
});
