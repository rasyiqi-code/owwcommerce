<?php
/**
 * Template Frontend: My Account (Advanced Client Dashboard)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Basic settings and styles for all users (Guest & Logged-in)
?>
<!-- style block dipindah ke frontend-pages.css -->

<script>
    window.owwcSettings = {
        restUrl: '<?php echo esc_url_raw( rest_url() ); ?>',
        nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
        homeUrl: '<?php echo esc_url( home_url('/') ); ?>'
    };
</script>

<?php
// Redirect if not logged in
if ( ! is_user_logged_in() ) {
    ?>
    <div class="owwc-login-page-container">
        <div class="owwc-login-card">
            <div class="owwc-login-header">
                <div class="owwc-login-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h2>Silakan Login</h2>
                <p>Anda harus masuk ke akun Anda untuk mengakses fitur lengkap dashboard.</p>
            </div>
            
            <div class="owwc-login-body">
                <?php wp_login_form( [
                    'redirect'       => get_permalink(),
                    'label_username' => __( 'Username atau Email', 'owwcommerce' ),
                    'label_log_in'   => __( 'Masuk Sekarang', 'owwcommerce' ),
                    'remember'       => true,
                    'form_id'        => 'owwc-login-form-element'
                ] ); ?>
            </div>
        </div>
    </div>
    <?php
    return;
}

$current_user = wp_get_current_user();
$order_repo = new \OwwCommerce\Repositories\OrderRepository();
$orders = $order_repo->find_by_user_id( $current_user->ID );

// Hitung Statistik Dasar
$total_spent = 0;
$active_orders = 0;
foreach ( $orders as $o ) {
    if ( in_array( $o->status, ['completed', 'processing'] ) ) {
        $total_spent += $o->total_amount;
    }
    if ( in_array( $o->status, ['pending', 'on-hold', 'awaiting-confirmation', 'processing'] ) ) {
        $active_orders++;
    }
}

// Get User Meta for Addresses
$billing_address = json_decode( get_user_meta( $current_user->ID, 'owwc_billing_address_json', true ), true ) ?: [];
$shipping_address = json_decode( get_user_meta( $current_user->ID, 'owwc_shipping_address_json', true ), true ) ?: [];

?>

<div class="owwc-frontend-wrap owwc-dashboard-advanced" style="max-width: 1100px; margin: 40px auto; padding: 0 20px;">
    
    <div class="owwc-dashboard-grid">
        
        <!-- Sidebar Navigation -->
        <aside class="owwc-dashboard-sidebar">
            <div class="owwc-profile-card-mini">
                <div class="avatar-wrap">
                    <div class="avatar-border">
                        <?php echo get_avatar( $current_user->ID, 80, '', '', ['style' => 'border-radius: 50%; display: block;'] ); ?>
                    </div>
                </div>
                <div class="profile-info">
                    <h3 class="name"><?php echo esc_html( $current_user->display_name ); ?></h3>
                    <p class="email"><?php echo esc_html( $current_user->user_email ); ?></p>
                </div>
            </div>

            <nav class="owwc-dashboard-nav">
                <ul>
                    <li><a href="#dashboard" class="active" onclick="owwcTabs.switchTab(event, 'dashboard')"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg> <span>Dashboard</span></a></li>
                    <li><a href="#orders" onclick="owwcTabs.switchTab(event, 'orders')"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg> <span>Pesanan</span></a></li>
                    <li><a href="#address" onclick="owwcTabs.switchTab(event, 'address')"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> <span>Alamat</span></a></li>
                    <li><a href="#account" onclick="owwcTabs.switchTab(event, 'account')"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> <span>Akun</span></a></li>
                    <li><a href="<?php echo esc_url( home_url('/') ); ?>"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg> <span>Beranda</span></a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="owwc-dashboard-main">
            <!-- Tab: Dashboard -->
            <div id="tab-dashboard" class="owwc-tab-content">
                <header class="tab-header">
                    <h2>Halo, <?php echo esc_html( $current_user->first_name ?: $current_user->display_name ); ?>!</h2>
                    <p>Selamat datang di dashboard belanja Anda.</p>
                </header>

                <div class="owwc-stats-grid">
                    <div class="owwc-stat-card">
                        <span class="label">Total Pesanan</span>
                        <strong class="value"><?php echo count($orders); ?></strong>
                    </div>
                    <div class="owwc-stat-card">
                        <span class="label">Pesanan Aktif</span>
                        <strong class="value"><?php echo $active_orders; ?></strong>
                    </div>
                    <div class="owwc-stat-card">
                        <span class="label">Total Pengeluaran</span>
                        <strong class="value primary"><?php echo \OwwCommerce\Core\Formatter::format_price($total_spent); ?></strong>
                    </div>
                </div>

                <!-- Recent Orders Preview -->
                <div class="owwc-card owwc-recent-orders">
                    <div class="card-header">
                        <h3>Pesanan Terbaru</h3>
                        <a href="#orders" onclick="owwcTabs.switchTab(event, 'orders')">Lihat Semua</a>
                    </div>
                    <?php if ( empty($orders) ) : ?>
                        <div class="empty-state">Belum ada riwayat pesanan.</div>
                    <?php else : ?>
                        <div class="order-table-wrap horizontal-scroll">
                            <table class="owwc-table">
                                <thead>
                                    <tr>
                                        <th>Pesanan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( array_slice($orders, 0, 3) as $order ) : 
                                        $label = (['pending'=>'warning','on-hold'=>'warning','awaiting-confirmation'=>'processing','processing'=>'processing','completed'=>'completed','cancelled'=>'cancelled','failed'=>'failed'])[$order->status] ?? 'default';
                                    ?>
                                        <tr>
                                            <td class="id-col">#<?php echo $order->id; ?></td>
                                            <td class="date-col"><?php echo wp_date('d M Y', strtotime($order->created_at)); ?></td>
                                            <td class="status-col"><span class="owwc-badge <?php echo $label; ?>"><?php echo ucfirst($order->status); ?></span></td>
                                            <td class="total-col text-right"><?php echo \OwwCommerce\Core\Formatter::format_price($order->total_amount); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab: Orders -->
            <div id="tab-orders" class="owwc-tab-content" style="display: none;">
                <header class="tab-header">
                    <h2>Riwayat Pesanan</h2>
                    <p>Daftar lengkap pesanan yang pernah Anda buat.</p>
                </header>
                <div class="owwc-card no-padding">
                    <div class="order-table-wrap horizontal-scroll">
                        <table class="owwc-table full-history">
                            <thead>
                                <tr>
                                    <th>Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $orders as $order ) : 
                                    $label = (['pending'=>'warning','on-hold'=>'warning','awaiting-confirmation'=>'processing','processing'=>'processing','completed'=>'completed','cancelled'=>'cancelled','failed'=>'failed'])[$order->status] ?? 'default';
                                ?>
                                    <tr>
                                        <td class="id-col">#<?php echo $order->id; ?></td>
                                        <td class="date-col"><?php echo wp_date('d M Y', strtotime($order->created_at)); ?></td>
                                        <td class="status-col"><span class="owwc-badge <?php echo $label; ?>"><?php echo ucfirst($order->status); ?></span></td>
                                        <td class="total-col text-right"><?php echo \OwwCommerce\Core\Formatter::format_price($order->total_amount); ?></td>
                                        <td class="actions-col">
                                            <div class="action-btns">
                                                <a href="<?php echo esc_url( home_url('/checkout/order-received/'.$order->id.'/') ); ?>" class="owwc-btn-link">Detail</a>
                                                <button onclick="owwcTabs.reorder(<?php echo $order->id; ?>)" class="owwc-btn-small">Beli Lagi</button>
                                                <?php 
                                                    $wa_number = preg_replace('/[^0-9]/', '', get_option('owwc_whatsapp_number', ''));
                                                    if ($wa_number) : 
                                                        $msg = urlencode("Halo Admin, saya ingin bertanya tentang Pesanan #{$order->id}");
                                                ?>
                                                    <a href="https://wa.me/<?php echo $wa_number; ?>?text=<?php echo $msg; ?>" target="_blank" class="owwc-btn-small wa">Bantuan</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab: Address -->
            <div id="tab-address" class="owwc-tab-content" style="display: none;">
                <header class="tab-header">
                    <h2>Buku Alamat</h2>
                    <p>Kelola alamat pengiriman utama Anda.</p>
                </header>
                
                <div class="owwc-address-grid">
                    <!-- Billing Address (Simplified as Primary) -->
                    <div class="owwc-card owwc-address-card">
                        <div class="card-header">
                            <h3>Alamat Utama</h3>
                            <button onclick="owwcTabs.toggleForm('billing')" class="edit-link">Ubah</button>
                        </div>
                        <div id="billing-display" class="address-display">
                            <?php if ( ! empty( $billing_address ) ) : ?>
                                <p>
                                    <strong><?php echo esc_html( $billing_address['first_name'] . ' ' . $billing_address['last_name'] ); ?></strong><br>
                                    <?php echo esc_html( $billing_address['phone'] ); ?><br>
                                    <?php echo esc_html( $billing_address['address'] ); ?><br>
                                    <?php echo esc_html( $billing_address['city'] . ', ' . $billing_address['province'] . ' ' . $billing_address['postcode'] ); ?>
                                </p>
                            <?php else : ?>
                                <p class="placeholder">Belum ada alamat yang tersimpan.</p>
                            <?php endif; ?>
                        </div>
                        <form id="billing-form" class="owwc-form stacked" style="display: none;" onsubmit="owwcTabs.saveAddress(event, 'billing')">
                            <div class="form-row">
                                <input type="text" name="first_name" placeholder="Nama Depan" value="<?php echo esc_attr($billing_address['first_name']??''); ?>" required>
                                <input type="text" name="last_name" placeholder="Nama Belakang" value="<?php echo esc_attr($billing_address['last_name']??''); ?>" required>
                            </div>
                            <input type="text" name="phone" placeholder="No. Telepon" value="<?php echo esc_attr($billing_address['phone']??''); ?>" required>
                            <textarea name="address" placeholder="Alamat Lengkap" rows="3" required><?php echo esc_textarea($billing_address['address']??''); ?></textarea>
                            <div class="form-row">
                                <input type="text" name="city" placeholder="Kota" value="<?php echo esc_attr($billing_address['city']??''); ?>" required>
                                <input type="text" name="province" placeholder="Provinsi" value="<?php echo esc_attr($billing_address['province']??''); ?>" required>
                            </div>
                            <input type="text" name="postcode" placeholder="Kode Pos" value="<?php echo esc_attr($billing_address['postcode']??''); ?>" required>
                            <div class="form-actions">
                                <button type="submit" class="owwc-btn-primary">Simpan</button>
                                <button type="button" onclick="owwcTabs.toggleForm('billing')" class="owwc-btn-secondary">Batal</button>
                            </div>
                        </form>
                    </div>

                    <!-- Shipping Address -->
                    <div class="owwc-card owwc-address-card">
                        <div class="card-header">
                            <h3>Alamat Pengiriman</h3>
                            <button onclick="owwcTabs.toggleForm('shipping')" class="edit-link">Edit</button>
                        </div>
                        <div id="shipping-display" class="address-display">
                            <?php if ( ! empty($shipping_address) ) : ?>
                                <p>
                                    <strong><?php echo esc_html($shipping_address['first_name'].' '.$shipping_address['last_name']); ?></strong><br>
                                    <?php echo esc_html($shipping_address['phone']); ?><br>
                                    <?php echo esc_html($shipping_address['address']); ?><br>
                                    <?php echo esc_html($shipping_address['city'].', '.$shipping_address['province'].' '.$shipping_address['postcode']); ?>
                                </p>
                            <?php else : ?>
                                <p class="placeholder">Belum ada alamat pengiriman.</p>
                            <?php endif; ?>
                        </div>
                        <form id="shipping-form" class="owwc-form stacked" style="display: none;" onsubmit="owwcTabs.saveAddress(event, 'shipping')">
                            <div class="form-row">
                                <input type="text" name="first_name" placeholder="Nama Depan" value="<?php echo esc_attr($shipping_address['first_name']??''); ?>" required>
                                <input type="text" name="last_name" placeholder="Nama Belakang" value="<?php echo esc_attr($shipping_address['last_name']??''); ?>" required>
                            </div>
                            <input type="text" name="phone" placeholder="No. Telepon" value="<?php echo esc_attr($shipping_address['phone']??''); ?>" required>
                            <textarea name="address" placeholder="Alamat Lengkap" rows="3" required><?php echo esc_textarea($shipping_address['address']??''); ?></textarea>
                            <div class="form-row">
                                <input type="text" name="city" placeholder="Kota" value="<?php echo esc_attr($shipping_address['city']??''); ?>" required>
                                <input type="text" name="province" placeholder="Provinsi" value="<?php echo esc_attr($shipping_address['province']??''); ?>" required>
                            </div>
                            <input type="text" name="postcode" placeholder="Kode Pos" value="<?php echo esc_attr($shipping_address['postcode']??''); ?>" required>
                            <div class="form-actions">
                                <button type="submit" class="owwc-btn-primary">Simpan</button>
                                <button type="button" onclick="owwcTabs.toggleForm('shipping')" class="owwc-btn-secondary">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab: Account Details -->
            <div id="tab-account" class="owwc-tab-content" style="display: none;">
                <header class="tab-header">
                    <h2>Detail Akun</h2>
                    <p>Perbarui informasi biodata dan email Anda.</p>
                </header>
                <div class="owwc-card no-padding">
                    <div class="card-header">
                        <h3>Biodata Akun</h3>
                        <button onclick="owwcTabs.toggleForm('profile')" class="edit-link">Edit</button>
                    </div>
                    
                    <div id="profile-display" class="address-display">
                        <p>
                            <strong>Nama Lengkap</strong><br>
                            <?php echo esc_html( ($current_user->first_name ?: '-') . ' ' . ($current_user->last_name ?: '') ); ?>
                        </p>
                        <p style="margin-top: 15px;">
                            <strong>Nama Tampilan</strong><br>
                            <?php echo esc_html( $current_user->display_name ); ?>
                        </p>
                        <p style="margin-top: 15px;">
                            <strong>Alamat Email</strong><br>
                            <?php echo esc_html( $current_user->user_email ); ?>
                        </p>
                        <div class="logout-section" style="margin-top: 30px; padding: 0 0 20px;">
                            <a href="<?php echo wp_logout_url( get_permalink() ); ?>" class="owwc-btn-secondary" style="color: var(--owwc-danger); display: flex; align-items: center; justify-content: center; gap: 10px; border: 1px solid #fee2e2; background: #fff; width: 100%; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 14px;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                <span>Keluar dari Akun</span>
                            </a>
                        </div>
                    </div>

                    <form id="profile-form" class="owwc-form stacked" style="display: none; padding: 25px;" onsubmit="owwcTabs.saveProfile(event)">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Depan</label>
                                <input type="text" name="first_name" value="<?php echo esc_attr($current_user->first_name); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Belakang</label>
                                <input type="text" name="last_name" value="<?php echo esc_attr($current_user->last_name); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nama Tampilan (Display Name)</label>
                            <input type="text" name="display_name" value="<?php echo esc_attr($current_user->display_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Alamat Email</label>
                            <input type="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="owwc-btn-primary full-width">Simpan Perubahan</button>
                            <button type="button" onclick="owwcTabs.toggleForm('profile')" class="owwc-btn-secondary full-width">Batal</button>
                        </div>
                        <div id="profile-msg" class="form-feedback"></div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        window.owwcTabs = {
            switchTab: function(e, tabId) {
                if (e) e.preventDefault();
                document.querySelectorAll('.owwc-tab-content').forEach(el => el.style.display = 'none');
                document.getElementById('tab-' + tabId).style.display = 'block';
                document.querySelectorAll('.owwc-dashboard-nav a').forEach(el => el.classList.remove('active'));
                const target = e ? e.currentTarget : document.querySelector('a[href="#'+tabId+'"]');
                if (target) target.classList.add('active');
            },

            toggleForm: function(type) {
                const display = document.getElementById(type + '-display');
                const form = document.getElementById(type + '-form');
                if (form.style.display === 'none') {
                    form.style.display = 'block';
                    display.style.display = 'none';
                } else {
                    form.style.display = 'none';
                    display.style.display = 'block';
                }
            },

            saveProfile: async function(e) {
                e.preventDefault();
                const form = e.target;
                const btn = form.querySelector('button');
                const msg = document.getElementById('profile-msg');
                const originalText = btn.innerText;

                btn.innerText = 'Menyimpan...';
                btn.disabled = true;

                try {
                    const response = await fetch(owwcSettings.restUrl + 'owwc/v1/dashboard/update-profile', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': owwcSettings.nonce },
                        body: JSON.stringify(Object.fromEntries(new FormData(form)))
                    });
                    const data = await response.json();
                    
                    msg.style.display = 'block';
                    msg.innerText = data.message;
                    msg.style.background = data.success ? '#dcfce7' : '#fee2e2';
                    msg.style.color = data.success ? '#166534' : '#991b1b';

                    if (data.success) {
                        setTimeout(() => location.reload(), 1500);
                    }
                } catch (err) {
                    console.error(err);
                } finally {
                    btn.innerText = originalText;
                    btn.disabled = false;
                    setTimeout(() => msg.style.display = 'none', 3000);
                }
            },

            saveAddress: async function(e, type) {
                e.preventDefault();
                const form = e.target;
                const data = Object.fromEntries(new FormData(form));
                data.type = type;

                try {
                    const response = await fetch(owwcSettings.restUrl + 'owwc/v1/dashboard/update-address', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': owwcSettings.nonce },
                        body: JSON.stringify(data)
                    });
                    const res = await response.json();
                    if (res.success) {
                        location.reload();
                    }
                } catch (err) {
                    console.error(err);
                }
            },

            reorder: async function(orderId) {
                try {
                    const response = await fetch(owwcSettings.restUrl + 'owwc/v1/orders/' + orderId, {
                        headers: { 'X-WP-Nonce': owwcSettings.nonce }
                    });
                    const order = await response.json();
                    
                    if (order && order.items) {
                        let cart = JSON.parse(localStorage.getItem('owwc_cart') || '[]');
                        order.items.forEach(item => {
                            const existing = cart.find(c => c.id === item.product_id);
                            if (existing) {
                                existing.qty += parseInt(item.qty);
                            } else {
                                cart.push({ id: item.product_id, qty: parseInt(item.qty) });
                            }
                        });
                        localStorage.setItem('owwc_cart', JSON.stringify(cart));
                        window.location.href = owwcSettings.homeUrl + 'checkout/cart/';
                    }
                } catch (err) {
                    alert('Gagal memuat pesanan untuk beli lagi.');
                }
            }
        };

        window.addEventListener('load', function() {
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById('tab-' + hash)) {
                owwcTabs.switchTab(null, hash);
            }
        });
    </script>
</div>
