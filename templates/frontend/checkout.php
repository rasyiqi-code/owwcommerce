<?php
/**
 * Template Utama: Checkout
 * Dapat di-override di tema: owwcommerce/checkout.php
 *
 * Layout menggunakan CSS Grid dari frontend-pages.css.
 * Di mobile, review pesanan otomatis pindah ke atas form.
 */

use OwwCommerce\Core\Plugin;

// Ambil shipping methods & payment gateways dari container
$shipping_methods = Plugin::get_instance()->get_container()->get( 'shipping_methods' );
$payment_gateways = Plugin::get_instance()->get_container()->get( 'payment_gateways' );

// URL cart dinamis
$cart_page_id = get_option( 'owwc_page_cart_id' );
$cart_url     = $cart_page_id ? get_permalink( $cart_page_id ) : site_url( '/keranjang' );
$my_account_url = get_permalink( get_option( 'owwc_page_myaccount_id' ) );

// User data logic
$user_id = get_current_user_id();
$user_data = [];
$is_profile_complete = false;

if ( $user_id ) {
    $user = get_userdata( $user_id );
    $billing_address = json_decode( get_user_meta( $user_id, 'owwc_billing_address_json', true ), true ) ?: [];
    
    $user_data = [
        'first_name' => $billing_address['first_name'] ?? $user->first_name,
        'last_name'  => $billing_address['last_name'] ?? $user->last_name,
        'email'      => $user->user_email,
        'phone'      => $billing_address['phone'] ?? '',
        'address'    => $billing_address['address'] ?? '',
        'city'       => $billing_address['city'] ?? '',
        'province'   => $billing_address['province'] ?? '',
        'postcode'   => $billing_address['postcode'] ?? '',
    ];

    // Check completeness
    if ( ! empty( $user_data['first_name'] ) && ! empty( $user_data['last_name'] ) && 
         ! empty( $user_data['email'] ) && ! empty( $user_data['phone'] ) && 
         ! empty( $user_data['address'] ) && ! empty( $user_data['city'] ) && 
         ! empty( $user_data['province'] ) && ! empty( $user_data['postcode'] ) ) {
        $is_profile_complete = true;
    }
}
?>

<style>
    .owwc-checkout-login-prompt {
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        animation: slideDown 0.5s ease-out;
    }
    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .owwc-clp-content { display: flex; align-items: center; gap: 20px; }
    .owwc-clp-icon {
        width: 50px;
        height: 50px;
        background: #fafafa;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #D4A843;
    }
    .owwc-clp-text h4 { margin: 0 0 5px; font-size: 16px; font-weight: 800; color: #111; }
    .owwc-clp-text p { margin: 0; font-size: 14px; color: #666; }
    .owwc-clp-actions { display: flex; gap: 12px; }
    .owwc-clp-btn {
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        border: none;
    }
    .owwc-clp-btn--login { background: #111; color: #fff; }
    .owwc-clp-btn--login:hover { background: #D4A843; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(212,168,67,0.3); }
    .owwc-clp-btn--guest { background: #f3f4f6; color: #444; }
    .owwc-clp-btn--guest:hover { background: #e5e7eb; }

    @media (max-width: 768px) {
        .owwc-checkout-login-prompt { flex-direction: column; text-align: center; }
        .owwc-clp-content { flex-direction: column; }
        .owwc-clp-actions { width: 100%; flex-direction: column; }
        .owwc-clp-btn { width: 100%; box-sizing: border-box; }
    }

    /* Profile Info Card & Warning */
    .owwc-profile-info-card {
        background: #fafafa;
        border: 1px solid #eee;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 25px;
        position: relative;
    }
    .owwc-profile-info-card p { margin: 0; line-height: 1.6; color: #444; font-size: 14px; }
    .owwc-profile-info-card strong { color: #111; display: block; margin-bottom: 5px; font-size: 15px; }
    .owwc-profile-info-card .edit-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 12px;
        font-weight: 700;
        color: var(--owwc-primary);
        text-decoration: none;
        border-bottom: 1px dashed;
    }

    .owwc-checkout-warning {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        color: #92400e;
        padding: 15px 20px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    .owwc-checkout-warning svg { color: #d97706; }
</style>

<div class="owwc-checkout-container">

    <!-- Breadcrumb navigasi -->
    <nav class="owwc-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Beranda</a>
        <span class="owwc-breadcrumb-sep">›</span>
        <a href="<?php echo esc_url( $cart_url ); ?>">Keranjang</a>
        <span class="owwc-breadcrumb-sep">›</span>
        <span class="owwc-breadcrumb-current">Checkout</span>
    </nav>

    <?php if ( ! is_user_logged_in() ) : ?>
        <div class="owwc-checkout-login-prompt" id="owwc-login-prompt">
            <div class="owwc-clp-content">
                <div class="owwc-clp-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <div class="owwc-clp-text">
                    <h4>Sudah punya akun?</h4>
                    <p>Login untuk mengakses riwayat pesanan dan checkout lebih cepat.</p>
                </div>
            </div>
            <div class="owwc-clp-actions">
                <a href="<?php echo esc_url( $my_account_url ); ?>" class="owwc-clp-btn owwc-clp-btn--login">Masuk Sekarang</a>
                <button type="button" class="owwc-clp-btn owwc-clp-btn--guest" onclick="document.getElementById('owwc-login-prompt').style.display='none'; document.getElementById('owwc-checkout-form-main').scrollIntoView({behavior:'smooth'});">Lanjut sebagai Tamu</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Header halaman -->
    <h1 class="owwc-section-title">Checkout</h1>
    <p class="owwc-section-subtitle">Lengkapi data di bawah untuk menyelesaikan pesanan Anda.</p>

    <!-- Grid layout: form kiri, review kanan -->
    <div class="owwc-checkout-grid">

        <!-- Kolom Form -->
        <div class="owwc-checkout-form">
            <form id="owwc-checkout-form-main" method="POST" action="">

                <!-- === Alamat Pengiriman === -->
                <h3 class="owwc-checkout-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    Alamat Pengiriman
                </h3>

                <?php if ( $is_profile_complete ) : ?>
                    <!-- Profile Info Card for Complete Profiles -->
                    <div class="owwc-profile-info-card">
                        <a href="<?php echo esc_url( $my_account_url . '#address' ); ?>" class="edit-btn">Ubah</a>
                        <p>
                            <strong><?php echo esc_html( $user_data['first_name'] . ' ' . $user_data['last_name'] ); ?></strong>
                            <?php echo esc_html( $user_data['email'] ); ?><br>
                            <?php echo esc_html( $user_data['phone'] ); ?><br>
                            <?php echo esc_html( $user_data['address'] ); ?><br>
                            <?php echo esc_html( $user_data['city'] . ', ' . $user_data['province'] . ' ' . $user_data['postcode'] ); ?>
                        </p>
                    </div>
                    
                    <!-- Hidden inputs to maintain form functionality -->
                    <input type="hidden" name="first_name" value="<?php echo esc_attr( $user_data['first_name'] ); ?>">
                    <input type="hidden" name="last_name" value="<?php echo esc_attr( $user_data['last_name'] ); ?>">
                    <input type="hidden" name="email" value="<?php echo esc_attr( $user_data['email'] ); ?>">
                    <input type="hidden" name="phone" value="<?php echo esc_attr( $user_data['phone'] ); ?>">
                    <input type="hidden" name="address" value="<?php echo esc_attr( $user_data['address'] ); ?>">
                    <input type="hidden" name="city" value="<?php echo esc_attr( $user_data['city'] ); ?>">
                    <input type="hidden" name="province" value="<?php echo esc_attr( $user_data['province'] ); ?>">
                    <input type="hidden" name="postcode" value="<?php echo esc_attr( $user_data['postcode'] ); ?>">

                <?php else : ?>
                    
                    <?php if ( is_user_logged_in() ) : ?>
                        <div class="owwc-checkout-warning">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <span>Informasi pengiriman belum lengkap. Harap lengkapi data Anda.</span>
                        </div>
                    <?php endif; ?>

                    <div class="owwc-form-row">
                        <div class="owwc-form-group">
                            <label for="owwc-first-name">Nama Depan *</label>
                            <input type="text" id="owwc-first-name" name="first_name" class="owwc-input" value="<?php echo esc_attr( $user_data['first_name'] ?? '' ); ?>" required>
                        </div>
                        <div class="owwc-form-group">
                            <label for="owwc-last-name">Nama Belakang *</label>
                            <input type="text" id="owwc-last-name" name="last_name" class="owwc-input" value="<?php echo esc_attr( $user_data['last_name'] ?? '' ); ?>" required>
                        </div>
                    </div>

                    <div class="owwc-form-row">
                        <div class="owwc-form-group">
                            <label for="owwc-email">Email *</label>
                            <input type="email" id="owwc-email" name="email" class="owwc-input" value="<?php echo esc_attr( $user_data['email'] ?? '' ); ?>" required>
                        </div>
                        <div class="owwc-form-group">
                            <label for="owwc-phone">Telepon</label>
                            <input type="text" id="owwc-phone" name="phone" class="owwc-input" value="<?php echo esc_attr( $user_data['phone'] ?? '' ); ?>">
                        </div>
                    </div>

                    <div class="owwc-form-group">
                        <label for="owwc-address">Alamat Lengkap *</label>
                        <textarea id="owwc-address" name="address" class="owwc-input" rows="3" required placeholder="Nama jalan, nomor rumah, RT/RW"><?php echo esc_textarea( $user_data['address'] ?? '' ); ?></textarea>
                    </div>

                    <div class="owwc-form-row">
                        <div class="owwc-form-group">
                            <label for="owwc-city">Kota *</label>
                            <input type="text" id="owwc-city" name="city" class="owwc-input" value="<?php echo esc_attr( $user_data['city'] ?? '' ); ?>" required>
                        </div>
                        <div class="owwc-form-group">
                            <label for="owwc-province">Provinsi *</label>
                            <input type="text" id="owwc-province" name="province" class="owwc-input" value="<?php echo esc_attr( $user_data['province'] ?? '' ); ?>" required>
                        </div>
                    </div>

                    <div class="owwc-form-row">
                        <div class="owwc-form-group">
                            <label for="owwc-postcode">Kode Pos *</label>
                            <input type="text" id="owwc-postcode" name="postcode" class="owwc-input" value="<?php echo esc_attr( $user_data['postcode'] ?? '' ); ?>" required maxlength="10">
                        </div>
                        <div class="owwc-form-group"></div>
                    </div>
                <?php endif; ?>

                <!-- === Metode Pengiriman === -->
                <h3 class="owwc-checkout-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    Metode Pengiriman
                </h3>

                <div class="owwc-radio-group">
                    <?php if ( ! empty( $shipping_methods ) ) : ?>
                        <?php foreach ( $shipping_methods as $id => $method ) : ?>
                            <div class="owwc-radio-option">
                                <label>
                                    <input type="radio" name="shipping_method" value="<?php echo esc_attr( $id ); ?>" required>
                                    <?php echo esc_html( $method->get_title() ); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="owwc-shop-empty">Tidak ada metode pengiriman tersedia.</p>
                    <?php endif; ?>
                </div>

                <!-- === Metode Pembayaran === -->
                <h3 class="owwc-checkout-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    Metode Pembayaran
                </h3>

                <div class="owwc-radio-group">
                    <?php 
                    $enabled_cod = get_option( 'owwc_enable_cod' );
                    if ( ! empty( $payment_gateways ) ) : ?>
                        <?php foreach ( $payment_gateways as $id => $gateway ) : 
                            if ( $id === 'cod' && ! $enabled_cod ) continue;
                        ?>
                            <div class="owwc-radio-option">
                                <label>
                                    <input type="radio" name="payment_method" value="<?php echo esc_attr( $id ); ?>" required>
                                    <?php echo esc_html( $gateway->get_title() ); ?>
                                </label>
                                <div class="owwc-payment-box payment_method_<?php echo esc_attr( $id ); ?>">
                                    <?php echo wp_kses_post( $gateway->get_description() ); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="owwc-shop-empty">Tidak ada metode pembayaran tersedia.</p>
                    <?php endif; ?>
                </div>

                <!-- Honeypot anti-spam -->
                <input type="hidden" name="owwc_anti_spam" value="">

                <button type="submit" id="owwc-place-order" class="owwc-btn owwc-btn--block">
                    Buat Pesanan
                </button>

                <div id="owwc-checkout-error" class="owwc-checkout-error" style="display: none;"></div>
            </form>
        </div>

        <!-- Kolom Review Pesanan (sidebar) -->
        <div class="owwc-checkout-review">
            <h3>Pesanan Anda</h3>
            
            <!-- Coupon Section -->
            <div class="owwc-coupon-wrapper" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dotted #ccc;">
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="owwc-coupon-code" class="owwc-input" placeholder="Kode Kupon" style="margin-bottom: 0;">
                    <button type="button" id="owwc-apply-coupon" class="owwc-btn" style="padding: 10px 15px; font-size: 13px;">Gunakan</button>
                </div>
                <div id="owwc-coupon-message" style="font-size: 12px; margin-top: 5px; display: none;"></div>
            </div>

            <div id="owwc-checkout-cart-review">
                <!-- Loading skeleton -->
                <div class="owwc-skeleton owwc-skeleton--text"></div>
                <div class="owwc-skeleton owwc-skeleton--text-sm"></div>
                <div class="owwc-skeleton owwc-skeleton--text"></div>
            </div>
        </div>

    </div>
</div>

<!-- Script untuk toggle deskripsi pembayaran -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Sembunyikan semua payment box
            document.querySelectorAll('.owwc-payment-box').forEach(box => box.style.display = 'none');
            // Tampilkan yang sesuai pilihan
            const targetBox = document.querySelector('.payment_method_' + this.value);
            if (targetBox) targetBox.style.display = 'block';
        });
    });
    // Auto-pilih opsi pertama
    if (paymentRadios.length > 0) {
        paymentRadios[0].checked = true;
        paymentRadios[0].dispatchEvent(new Event('change'));
    }
});
</script>
