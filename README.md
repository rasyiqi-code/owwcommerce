# OwwCommerce

**The Zero-Bloatware E-Commerce Engine for WordPress.**

OwwCommerce adalah alternatif ringan untuk WooCommerce, dibangun dengan arsitektur modern (PHP 8.1+), tabel database kustom untuk performa kilat, dan filosofi tanpa beban (*zero bloatware*).

## Panduan Shortcode

Gunakan shortcode berikut untuk menampilkan fitur OwwCommerce di halaman WordPress Anda:

### 1. Katalog Toko
`[owwcommerce_shop]`
Menampilkan daftar produk (shop page).

### 2. Keranjang Belanja
`[owwcommerce_cart]`
Menampilkan halaman keranjang belanja (cart page).

### 3. Halaman Checkout
`[owwcommerce_checkout]`
Menampilkan formulir pembayaran dan pengiriman (checkout page).

### 4. Akun Pelanggan
`[owwcommerce_my_account]`
Menampilkan dasbor akun pelanggan dan riwayat pesanan.

### 5. Ikon Keranjang (Badge)
`[owwcommerce_cart_icon]`
Menampilkan ikon belanja dengan indikator jumlah item (cocok untuk header atau widget).

---

## Fitur Utama
- **Zero Bloatware**: Script hanya dimuat di halaman yang menggunakan shortcode di atas.
- **Custom Tables**: Data produk dan order tidak menumpuk di `wp_posts`, menjamin performa database tetap optimal.
- **Modern Tech Stack**: Menggunakan Vanilla JS (tanpa jQuery) dan PHP modern.
