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
?>

<div class="owwc-checkout-container">

    <!-- Breadcrumb navigasi -->
    <nav class="owwc-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Beranda</a>
        <span class="owwc-breadcrumb-sep">›</span>
        <a href="<?php echo esc_url( $cart_url ); ?>">Keranjang</a>
        <span class="owwc-breadcrumb-sep">›</span>
        <span class="owwc-breadcrumb-current">Checkout</span>
    </nav>

    <!-- Header halaman -->
    <h1 class="owwc-section-title">Checkout</h1>
    <p class="owwc-section-subtitle">Lengkapi data di bawah untuk menyelesaikan pesanan Anda.</p>

    <!-- Grid layout: form kiri, review kanan -->
    <div class="owwc-checkout-grid">

        <!-- Kolom Form -->
        <div class="owwc-checkout-form">
            <form id="owwc-checkout-form-main" method="POST" action="">

                <!-- === Detail Penagihan === -->
                <h3 class="owwc-checkout-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Detail Penagihan
                </h3>

                <div class="owwc-form-row">
                    <div class="owwc-form-group">
                        <label for="owwc-first-name">Nama Depan *</label>
                        <input type="text" id="owwc-first-name" name="first_name" class="owwc-input" required>
                    </div>
                    <div class="owwc-form-group">
                        <label for="owwc-last-name">Nama Belakang *</label>
                        <input type="text" id="owwc-last-name" name="last_name" class="owwc-input" required>
                    </div>
                </div>

                <div class="owwc-form-row">
                    <div class="owwc-form-group">
                        <label for="owwc-email">Email *</label>
                        <input type="email" id="owwc-email" name="email" class="owwc-input" required>
                    </div>
                    <div class="owwc-form-group">
                        <label for="owwc-phone">Telepon</label>
                        <input type="text" id="owwc-phone" name="phone" class="owwc-input">
                    </div>
                </div>

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
                    <?php if ( ! empty( $payment_gateways ) ) : ?>
                        <?php foreach ( $payment_gateways as $id => $gateway ) : ?>
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
