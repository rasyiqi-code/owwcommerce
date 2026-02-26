<?php
/**
 * Halaman Pengaturan (Settings) OwwCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap owwc-admin-wrap">
    <h1>Pengaturan OwwCommerce</h1>
    
    <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 4px; max-width: 800px; margin-top: 20px;">
        <form method="post" action="options.php">
            <?php settings_fields( 'owwc_settings_group' ); ?>
            <?php do_settings_sections( 'owwc_settings_group' ); ?>
            
            <table class="form-table" role="presentation">
                <tr valign="top">
                    <th scope="row">
                        <label for="owwc_floating_cart_style">Gaya Ikon Keranjang Mengambang</label>
                    </th>
                    <td>
                        <select name="owwc_floating_cart_style" id="owwc_floating_cart_style" style="min-width: 250px;">
                            <option value="style-1" <?php selected( get_option('owwc_floating_cart_style', 'style-1'), 'style-1' ); ?>>Style 1: Bulat Minimalis (Default)</option>
                            <option value="style-2" <?php selected( get_option('owwc_floating_cart_style', 'style-1'), 'style-2' ); ?>>Style 2: Kotak Sudut Halus</option>
                            <option value="style-3" <?php selected( get_option('owwc_floating_cart_style', 'style-1'), 'style-3' ); ?>>Style 3: Kapsul Lebar (dengan Teks)</option>
                        </select>
                        <p class="description">
                            Pilih desain antarmuka bagi lencana keranjang belanja yang melayang di layar pengunjung.<br/>
                            <em>(Catatan: Akan otomatis nonaktif jika Anda membypass widgetnya lewat shortcode).</em>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="owwc_floating_cart_position">Posisi Layar</label>
                    </th>
                    <td>
                        <select name="owwc_floating_cart_position" id="owwc_floating_cart_position" style="min-width: 250px;">
                            <option value="bottom-right" <?php selected( get_option('owwc_floating_cart_position', 'bottom-right'), 'bottom-right' ); ?>>Kanan Bawah (Default)</option>
                            <option value="bottom-left" <?php selected( get_option('owwc_floating_cart_position', 'bottom-right'), 'bottom-left' ); ?>>Kiri Bawah</option>
                            <option value="top-right" <?php selected( get_option('owwc_floating_cart_position', 'bottom-right'), 'top-right' ); ?>>Kanan Atas</option>
                            <option value="top-left" <?php selected( get_option('owwc_floating_cart_position', 'bottom-right'), 'top-left' ); ?>>Kiri Atas</option>
                        </select>
                        <p class="description">Di sudut sebelah mana ikon melayang akan ditempatkan?</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="owwc_floating_cart_icon">Jenis Ikon/Gambar Keranjang</label>
                    </th>
                    <td>
                        <select name="owwc_floating_cart_icon" id="owwc_floating_cart_icon" style="min-width: 250px;">
                            <option value="cart" <?php selected( get_option('owwc_floating_cart_icon', 'cart'), 'cart' ); ?>>Keranjang Belanja (🛒 Cart)</option>
                            <option value="bag" <?php selected( get_option('owwc_floating_cart_icon', 'cart'), 'bag' ); ?>>Tas Belanja (🛍️ Bag)</option>
                            <option value="basket" <?php selected( get_option('owwc_floating_cart_icon', 'cart'), 'basket' ); ?>>Keranjang Bambu (🧺 Basket)</option>
                        </select>
                        <p class="description">Pilih model gambar SVG (vektor) dari keranjang.</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button( 'Simpan Perubahan', 'primary' ); ?>
        </form>
    </div>
</div>
