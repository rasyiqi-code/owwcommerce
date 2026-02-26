# Panduan Integrasi Tema OwwCommerce

OwwCommerce didesain dengan konsep **Zero Bloatware**. Kami tidak memuat fragment AJAX, file CSS bawaan yang kaku, atau markup kompleks yang menyulitkan pengembang tema.

Frontend dari OwwCommerce direpresentasikan oleh *REST API calls* secara native menggunakan Vanilla Javascript ringan (`assets/js/cart.js`).

## Hooking ke Fitur Keranjang Belanja

Script keranjang belanja akan secara otomatis mendengarkan *event clicks* pada DOM. Anda hanya perlu memberikan *class* dan atribut data yang tepat pada HTML elemen Anda.

### 1. Tombol Add to Cart (Tambah ke Keranjang)

Berikan class `owwc-add-to-cart` dan *data attribute* pendukung:
- `data-product-id="[ID]"` : ID referensi produk.
- `data-qty="[JUMLAH]"` : *(Opsional)* Jumlah kuantitas ditambahkan (default: 1).

**Contoh Tombol Standar:**
```html
<button class="owwc-add-to-cart" data-product-id="12">
    Beli Sekarang
</button>
```

**Teks Tombol Berubah Otomatis:**
Ketika diklik, teks tombol akan secara otomatis berubah menjadi "Adding..." sebagai UX *feedback* bawaan.

### 2. Elemen Counter & Total Belanja Dinamis

Anda tidak perlu khawatir menggunakan React atau Vue untuk *cart badge* (jumlah item) di header. Cukup gunakan class statis berikut dan script kami akan memperbaruinya secara otomatis:

- `owwc-cart-count`: Script akan mengganti `innerText` container ini dengan total `qty` keranjang.
- `owwc-cart-total`: Script akan mengganti `innerText` container ini dengan nominal formati angka keranjang.

**Contoh Header Cart Link:**
```html
<a href="/keranjang" class="cart-link">
    Tas Belanja (<span class="owwc-cart-count">0</span>) - 
    Rp. <span class="owwc-cart-total">0</span>
</a>
```

### 3. Events Kustom Javascript

Bagi pengembang tingkat lanjut, kami memancarkan (emit) custom event setiap kali status keranjang berubah, sehingga Anda dapat memicu animasi atau efek UI Anda sendiri.

**Listener Event `owwc_cart_updated`:**
```javascript
document.addEventListener('owwc_cart_updated', function(event) {
    const cartData = event.detail;
    console.log('Total Item di Keranjang: ', cartData.count);
    console.log('Tagihan Sementara: ', cartData.total);
    console.log('List Items: ', cartData.items);
    
    // Contoh memunculkan toast popup notifikasi
    // showToast('Item berhasil dimasukkan keranjang!');
});
```

## Memanggil Objek Javascript Langsung
Objek engine *Cart* OwwCommerce di-expose secara statis ke window:
```javascript
// Melakukan fetch cart terbaru ke REST API:
const currentCart = await window.owwcCart.fetchCart();

// Menambahkan ID 99 ke keranjang secara programmatic:
window.owwcCart.addToCart(99, 2); 
```

## Manipulasi Ikon Keranjang Mengambang (Floating Cart)

Secara bawaan (*default*), OwwCommerce akan merender ikon lencana keranjang belanja yang mengambang responsif di **sudut kanan bawah layar** secara otomatis pada seluruh situs, persis seperti widget *Live Chat*.

Namun, bila Anda sebagai pengembang (*theme developer*) ingin menaruh sendiri ikon keranjang mungil ini pada bagian spesifik dari desain Anda (Misalnya menyisipkannya khusus berdampingan di navigasi atas tema Anda), Anda bisa memanggil *shortcode* khusus.

**Pemanggilan PHP Kustom:**
```php
<?php echo do_shortcode('[owwcommerce_cart_icon]'); ?>
```
**Perilaku Cerdas (Smart Override):**
Manakala *shortcode* `[owwcommerce_cart_icon]` dirender minimal 1 kali saja di halaman web terkait, maka skrip algoritma kami otomatis akan mendeteksi intervensi kustom tersebut dan seketika **menonaktifkan (menyembunyikan) Mode _Floating (Mengambang)_** standar bawaan. 

Fitur Cerdas ini berguna untuk mempertahankan konsep "Zero-Bloatware", yaitu menghindari kemunculan tombol Ikon Keranjang Ganda di satu layar yang sama yang berpotensi membingungkan pembeli.
