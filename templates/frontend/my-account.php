<?php
/**
 * Template Frontend: My Account (Advanced Client Dashboard)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Basic settings and styles for all users (Guest & Logged-in)
?>
<style>
    :root {
        --owwc-primary: #D4A843;
        --owwc-primary-hover: #B8912E;
        --card-shadow: 0 4px 15px rgba(0,0,0,0.02);
        --header-bg: #fafafa;
    }

    /* PREMIUM LOGIN UI STYLES */
    .owwc-login-page-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 60vh;
        padding: 40px 20px;
        background: #fdfdfd;
    }
    .owwc-login-card {
        width: 100%;
        max-width: 450px;
        background: #fff;
        border-radius: 24px;
        border: 1px solid #f0f0f0;
        box-shadow: 0 10px 40px rgba(0,0,0,0.03);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .owwc-login-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.06);
    }
    .owwc-login-header {
        padding: 40px 40px 20px;
        text-align: center;
    }
    .owwc-login-icon {
        width: 64px;
        height: 64px;
        background: #fafafa;
        border: 1px solid #f0f0f0;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        color: var(--owwc-primary);
    }
    .owwc-login-icon svg { width: 28px; height: 28px; }
    .owwc-login-header h2 {
        font-size: 26px;
        font-weight: 800;
        color: #111;
        margin: 0 0 12px;
        letter-spacing: -0.5px;
    }
    .owwc-login-header p {
        font-size: 15px;
        color: #666;
        line-height: 1.6;
        margin: 0;
        padding: 0 10px;
    }
    .owwc-login-body {
        padding: 0 40px 40px;
    }
    /* WordPress Login Form Overrides */
    #owwc-login-form-element p { margin-bottom: 20px; }
    #owwc-login-form-element label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #111;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    #owwc-login-form-element input[type="text"],
    #owwc-login-form-element input[type="password"] {
        width: 100%;
        padding: 14px 18px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        font-size: 15px;
        background: #fdfdfd;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }
    #owwc-login-form-element input:focus {
        border-color: var(--owwc-primary);
        background: #fff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(212, 168, 67, 0.1);
    }
    #owwc-login-form-element .login-remember {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: -10px;
    }
    #owwc-login-form-element .login-remember label {
        text-transform: none;
        font-weight: 600;
        color: #666;
        margin: 0;
        font-size: 14px;
        cursor: pointer;
    }
    #owwc-login-form-element .login-submit {
        margin-top: 30px;
        margin-bottom: 0 !important;
    }
    #owwc-login-form-element input[type="submit"] {
        width: 100%;
        padding: 16px;
        background: #111;
        color: #fff;
        border: none;
        border-radius: 14px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #owwc-login-form-element input[type="submit"]:hover {
        background: var(--owwc-primary);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(212, 168, 67, 0.25);
    }
    #owwc-login-form-element input[type="submit"]:active {
        transform: translateY(0);
    }

    /* Dashboard Styles */
    .owwc-dashboard-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        overflow: visible;
        padding-bottom: 80px;
    }
    .floating-whatsapp__btn { display: none !important; }
    h1.entry-title, .page-title, .post-title { display: none !important; }
    .owwc-dashboard-main { min-width: 0; }
    .owwc-profile-card-mini {
        background: #fff;
        border: 1px solid #eaeaea;
        border-radius: 20px;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: var(--card-shadow);
    }
    .owwc-profile-card-mini .avatar-border {
        border: 2px solid var(--owwc-primary);
        padding: 2px;
        border-radius: 50%;
    }
    .owwc-profile-card-mini .avatar-border img { width: 45px; height: 45px; }
    .owwc-profile-card-mini .name { font-size: 16px; font-weight: 700; margin: 0; color: #111; }
    .owwc-profile-card-mini .email { font-size: 11px; color: #888; margin: 2px 0 0; }

    .owwc-dashboard-nav {
        background: rgba(255, 255, 255, 0.95);
        border-top: 1px solid #eaeaea;
        overflow: hidden;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 999;
        backdrop-filter: blur(10px);
        padding: 5px 0;
    }
    .owwc-dashboard-nav ul {
        display: flex;
        justify-content: space-around;
        list-style: none;
        margin: 0;
        padding: 0;
        overflow: visible;
    }
    .owwc-dashboard-nav ul li { flex: 1; }
    .owwc-dashboard-nav ul li a {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 8px 5px;
        color: #444;
        text-decoration: none;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
        transition: all 0.2s;
        text-align: center;
    }
    .owwc-dashboard-nav ul li a svg { 
        width: 22px; height: 22px; opacity: 0.5; margin-bottom: 2px; 
        transition: all 0.2s;
        stroke: currentColor;
        fill: none;
    }
    .owwc-dashboard-nav ul li a.active { color: var(--owwc-primary); }
    .owwc-dashboard-nav ul li a.active svg { opacity: 1; stroke-width: 2.5; }

    .tab-header { margin-bottom: 20px; }
    .tab-header h2 { font-size: 20px; font-weight: 800; color: #111; margin: 0; }
    .tab-header p { font-size: 13px; color: #666; margin-top: 5px; }

    .owwc-stats-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 25px;
    }
    .owwc-stat-card {
        background: #fff;
        padding: 20px;
        border-radius: 18px;
        border: 1px solid #eaeaea;
        box-shadow: var(--card-shadow);
    }
    .owwc-stat-card .label { font-size: 11px; color: #888; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .owwc-stat-card .value { display: block; font-size: 22px; color: #111; font-weight: 800; margin-top: 5px; }
    .owwc-stat-card .value.primary { color: var(--owwc-primary); }

    .owwc-card {
        background: #fff;
        border: 1px solid #eaeaea;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        margin-bottom: 25px;
        padding: 20px;
    }
    .owwc-card.no-padding { padding: 0; }
    .card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--header-bg);
    }
    .card-header h3 { font-size: 14px; font-weight: 700; color: #111; margin: 0; }
    .card-header a, .card-header .edit-link { font-size: 12px; color: var(--owwc-primary); font-weight: 700; text-decoration: none; border: none; background: none; cursor: pointer; }

    .order-table-wrap.horizontal-scroll {
        overflow-x: auto;
        position: relative;
        -webkit-overflow-scrolling: touch;
        width: 100%;
        display: block;
        border-radius: 10px;
        border: 1px solid #f0f0f0;
    }
    .owwc-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px; 
    }
    .owwc-table thead {
        background: var(--header-bg);
        border-bottom: 2px solid #f0f0f0;
    }
    .owwc-table th {
        padding: 12px 15px; 
        font-size: 11px;
        color: #888;
        font-weight: 800;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .owwc-table tr {
        border-bottom: 1px solid #f9f9f9;
    }
    .owwc-table td {
        padding: 12px 15px;
        font-size: 13px;
        color: #444;
        vertical-align: middle;
        white-space: nowrap;
    }
    .owwc-table .id-col { width: 50px; font-weight: 800; color: #000; }
    .owwc-table th:last-child, .owwc-table td:last-child { padding-right: 30px; text-align: right; }
    .action-btns { justify-content: flex-end; display: flex; gap: 8px; }

    .owwc-badge { border-radius: 6px; font-weight: 800; font-size: 9px; padding: 4px 8px; text-transform: uppercase; display: inline-block; white-space: nowrap; letter-spacing: 0.3px; }
    .owwc-badge.warning { background: #fef3c7; color: #92400e; }
    .owwc-badge.processing { background: #ede9fe; color: #5b21b6; }
    .owwc-badge.completed { background: #dcfce7; color: #166534; }
    
    .owwc-btn-link { font-size: 11px; font-weight: 700; color: #000; text-decoration: none; padding: 5px 10px; background: #f3f4f6; border-radius: 6px; transition: 0.2s; }
    .owwc-btn-small { border: none; background: #000; color: #fff; padding: 5px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; cursor: pointer; transition: 0.2s; }
    .owwc-btn-small.wa { background: #25D366; text-decoration: none; }

    .owwc-form.stacked input, .owwc-form.stacked textarea { 
        width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; margin-bottom: 15px; transition: 0.2s;
        box-sizing: border-box; background: #fff;
    }
    .owwc-form.stacked input:focus, .owwc-form.stacked textarea:focus {
        border-color: var(--owwc-primary);
        outline: none;
        box-shadow: 0 0 0 4px rgba(0,0,0,0.05);
    }
    .form-group label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 8px; color: #111; }
    .owwc-btn-primary { width: 100%; border: none; background: #000; color: #fff; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; }
    .owwc-btn-secondary { width: 100%; border: none; background: #f3f4f6; color: #111; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; margin-top: 8px; }
    .address-display { padding: 5px 20px 20px 20px; }
    .address-display p { font-size: 14px; line-height: 1.7; color: #444; margin: 0; }

    @media (min-width: 850px) {
        .owwc-dashboard-grid { grid-template-columns: 260px 1fr; gap: 30px; padding-bottom: 0; }
        .owwc-dashboard-nav {
            position: relative;
            top: 0;
            bottom: auto;
            left: auto;
            right: auto;
            background: #fff;
            border: 1px solid #eaeaea;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            padding: 0;
            backdrop-filter: none;
        }
        .owwc-dashboard-nav ul { flex-direction: column; padding: 10px; }
        .owwc-dashboard-nav ul li a { flex-direction: row; gap: 12px; padding: 15px 20px; font-size: 14px; border-radius: 12px; text-align: left; }
        .owwc-dashboard-nav ul li a.active { position: relative; background: #f9f9f9; }
        .owwc-dashboard-nav ul li a.active::after { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--owwc-primary); border-radius: 4px 0 0 4px; }
        
        .owwc-profile-card-mini { flex-direction: column; text-align: center; padding: 25px; margin-bottom: 20px; }
        .owwc-profile-card-mini .avatar-border img { width: 80px; height: 80px; }
        .owwc-stats-grid { grid-template-columns: repeat(3, 1fr); gap: 15px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .owwc-address-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    }

    @media (max-width: 480px) {
        .owwc-login-card { border-radius: 0; border: none; box-shadow: none; }
        .owwc-login-header { padding: 40px 20px 20px; }
        .owwc-login-body { padding: 0 20px 40px; }
        .owwc-login-page-container { padding: 0; background: #fff; display: block; }
    }
</style>

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
