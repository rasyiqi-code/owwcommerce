document.addEventListener('DOMContentLoaded', function () {
    const startBtn = document.getElementById('owwc-start-migration');
    const statsContainer = document.getElementById('owwc-migration-stats');
    const progressWrap = document.getElementById('migration-progress-wrap');
    const progressBar = document.getElementById('migration-bar');
    const progressText = document.getElementById('migration-status-text');
    const progressPerc = document.getElementById('migration-percentage');
    const migrationLog = document.getElementById('migration-log');
    const logCard = document.getElementById('owwc-migration-log-card');

    let totalProducts = 0;
    let totalOrders = 0;
    let totalCategories = 0;
    let totalCustomers = 0;

    // 1. Fetch Stats on Load
    function fetchStats() {
        fetch(`${owwcSettings.restUrl}owwc/v1/migration/stats`, {
            headers: { 'X-WP-Nonce': owwcSettings.nonce }
        })
            .then(res => res.json())
            .then(data => {
                totalProducts = data.products || 0;
                totalOrders = data.orders || 0;
                totalCategories = data.categories || 0;
                totalCustomers = data.customers || 0;

                document.getElementById('stats-products').innerText = totalProducts;
                document.getElementById('stats-categories').innerText = totalCategories;
                document.getElementById('stats-orders').innerText = totalOrders;
                document.getElementById('stats-customers').innerText = totalCustomers;
            });
    }

    function addLog(message, type = 'info') {
        const div = document.createElement('div');
        div.innerText = `> [${new Date().toLocaleTimeString()}] ${message}`;
        if (type === 'error') div.style.color = '#ff4444';
        if (type === 'success') div.style.color = '#00ff00';
        migrationLog.appendChild(div);
        migrationLog.scrollTop = migrationLog.scrollHeight;
    }

    async function runMigration(type, total) {
        let offset = 0;
        const limit = 50;

        addLog(`Memulai migrasi ${type}...`);

        while (offset < total || (total === 0 && offset === 0)) {
            try {
                const response = await fetch(`${owwcSettings.restUrl}owwc/v1/migration/run`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': owwcSettings.nonce
                    },
                    body: JSON.stringify({ type, limit, offset })
                });

                const res = await response.json();

                if (!res.success) throw new Error('Gagal memproses batch.');

                const count = res.count;
                offset += count;

                // Update Progress
                const perc = total > 0 ? Math.round((offset / total) * 100) : 100;
                progressBar.style.width = `${perc}%`;
                progressPerc.innerText = `${perc}%`;
                progressText.innerText = `Memigrasi ${type}: ${offset} dari ${total} selesai...`;

                addLog(`Berhasil memigrasi ${count} ${type}.`);

                if (count === 0 && total > 0) break; // Safeguard
                if (total === 0) break;

            } catch (err) {
                addLog(`Error pada ${type}: ${err.message}`, 'error');
                break;
            }
        }

        addLog(`Migrasi ${type} selesai!`, 'success');
    }

    startBtn.onclick = async function () {
        if (!confirm('Apakah Anda yakin ingin memulai migrasi? Proses ini akan memindahkan data produk and pesanan ke OwwCommerce.')) return;

        startBtn.disabled = true;
        progressWrap.style.display = 'block';
        logCard.style.display = 'block';
        addLog('Memulai mesin migrasi...');

        // Step 1: Products & Categories
        progressBar.style.width = '0%';
        await runMigration('products', totalProducts);

        // Step 2: Orders & Customers
        progressBar.style.width = '0%';
        await runMigration('orders', totalOrders);

        addLog('SELURUH PROSES MIGRASI SELESAI!', 'success');
        progressText.innerText = 'Migrasi Selesai!';
        startBtn.innerText = 'Migrasi Berhasil';

        alert('Migrasi data WooCommerce berhasil diselesaikan!');
    };

    fetchStats();
});
