Product Requirements Document (PRD): OwwCommerce

Versi: 1.0 (Complete Roadmap)
Produk: OwwCommerce - The Zero-Bloatware E-Commerce Engine for WordPress

Saya ingin membuat plugin e-commerce untuk wordpress yang menjadi alternatif dari woocommerce. Plugin ini akan dibangun dari nol menggunakan standar PHP modern (8.1+), arsitektur Custom Database Tables murni (tanpa ketergantungan pada wp_posts dan wp_postmeta), dan filosofi Zero Bloatware. Tujuannya adalah memberikan performa secepat kilat, kode yang bersih, dan kemudahan migrasi 1-klik bagi jutaan pengguna WordPress yang frustrasi dengan lambatnya situs e-commerce mereka. Saya sudah install localwp untuk kebutuhan testing hasil akhir plugin ini. Siapkan symlink /home/rasyiqi/Local Sites/crediblemark/app/public/wp-content/plugins

1. Executive Summary

OwwCommerce adalah revolusi e-commerce untuk ekosistem WordPress. Dirancang sebagai alternatif langsung dari WooCommerce, OwwCommerce dibangun dari nol menggunakan standar PHP modern (8.1+), arsitektur Custom Database Tables murni (tanpa ketergantungan pada wp_posts dan wp_postmeta), dan filosofi Zero Bloatware. Tujuannya adalah memberikan performa secepat kilat, kode yang bersih, dan kemudahan migrasi 1-klik bagi jutaan pengguna WordPress yang frustrasi dengan lambatnya situs e-commerce mereka.

2. Problem Statement (Masalah yang Diselesaikan)

Database Bottleneck: WooCommerce pada dasarnya menggunakan tabel blog WordPress (wp_posts dan wp_postmeta) untuk menyimpan produk dan pesanan. Ini menghasilkan ratusan query lambat untuk memuat satu halaman produk atau menghitung laporan.

Resource Hog (Bloatware): WooCommerce memuat aset CSS, JS, dan fragment cart (AJAX) di seluruh halaman website, bahkan di halaman artikel yang tidak berjualan.

Spaghetti Code & Legacy Baggage: Mempertahankan kompatibilitas mundur hingga PHP 5.6 dan versi WP lama membuat core WooCommerce berat dan sulit dikustomisasi secara headless.

3. Product Vision & Value Proposition

Zero Bloatware: Aset (CSS/JS) dan logika PHP hanya dieksekusi di halaman yang membutuhkan (Cart, Checkout, Shop, My Account).

Native Custom Tables: Produk, Pesanan, Pelanggan, dan Analitik memiliki tabel khusus yang diindeks dengan sempurna sejak hari pertama instalasi.

Micro-Modular Architecture: Fitur seperti Kupon, Downloadable Products, atau Subscription adalah modul internal. Jika dimatikan = 0 KB memory footprint.

Frictionless Migration: "Tinggalkan WooCommerce dalam 3 Menit." Migrasi data di latar belakang tanpa downtime.

4. Product Roadmap (Fase Pengembangan)

Fase 1: MVP & Core Engine (Bulan 1 - 3)

Fokus: Fondasi arsitektur database, operasional toko dasar, dan alat migrasi.

Katalog: Simple Products, Kategori, Tag, Manajemen Stok Dasar, Galeri Gambar.

Transaksi: Keranjang Belanja (berbasis sesi PHP/Redis, bukan database), One-Page Checkout (AJAX).

Pembayaran & Pengiriman: Bank Transfer (BACS), Cash on Delivery (COD), Flat Rate, Free Shipping.

Database & Performa: Implementasi oww_products, oww_orders, oww_customers.

Migrasi: OwwCommerce Importer Engine (memindahkan data dari WooCommerce legacy ke OwwCommerce custom tables via batch processing).

Fase 2: Growth & Ecosystem (Bulan 4 - 6)

Fokus: Memenuhi standar industri e-commerce modern agar bisa digunakan toko menengah.

Katalog Lanjutan: Variable Products (Atribut & Variasi), Digital/Downloadable Products.

Promosi: Modul Kupon Diskon (Persentase, Nominal, Minimum Belanja).

Pembayaran (Add-ons): Modul Payment Gateway populer (Stripe, PayPal, Midtrans, Xendit).

Pengiriman (Add-ons): Modul Live Rates (RajaOngkir, Shippo).

Admin Dashboard: Laporan analitik dasar (Penjualan per hari/bulan) menggunakan React/Vue di backend untuk UX yang responsif.

Developer Tools: REST API V1 untuk ekosistem (Products, Orders, Customers).

Fase 3: Scale & Headless (Bulan 7 - 9)

Fokus: Kemampuan skala besar, multi-negara, dan arsitektur Headless/Decoupled.

Internasionalisasi: Multi-currency support, Advanced Tax Engine (berbasis region/negara).

Headless E-commerce: Dukungan GraphQL API secara native. Memungkinkan frontend dibangun menggunakan Next.js, Nuxt, atau Gatsby dengan OwwCommerce sebagai backend.

Manajemen Pesanan Lanjutan: Cetak Invois (PDF), Refund system, Edit Pesanan (tambah/hapus item oleh admin).

Abandoned Cart: Sistem recovery keranjang yang ditinggalkan via email otomatis.

Fase 4: Enterprise & Advanced Automations (Bulan 10 - 12+)

Fokus: Fitur kompleks untuk B2B dan langganan.

Subscription Engine: Dukungan produk berlangganan (bayar per bulan/tahun).

B2B Wholesale: Harga bertingkat (tier pricing), persetujuan akun grosir, sembunyikan harga untuk tamu.

Multi-Warehouse: Manajemen inventaris di berbagai lokasi gudang.

Webhooks & Automation: Trigger event kompleks (misal: "Jika pesanan > $500, kirim data ke Zapier/Slack").

5. Kebutuhan Fungsional (Functional Requirements)

5.1. Sistem Produk & Database

FR-1.1: Data produk harus disimpan di tabel relasional murni (misal: id, sku, price, stock_quantity, type dalam satu baris oww_products).

FR-1.2: Harus mendukung operasi CRUD massal (bulk edit) dengan performa tinggi tanpa memicu fungsi hook WordPress yang tidak perlu.

5.2. Checkout & Cart Engine

FR-2.1: Cart tidak boleh menggunakan AJAX fragments yang membebani server setiap ada aktivitas di frontend. Gunakan REST API ringan atau LocalStorage sync.

FR-2.2: Checkout harus berdesain One-Page bergaya modern (seperti Shopify) secara default.

5.3. Sistem Modul Internal (Zero Bloatware Engine)

FR-3.1: Terdapat "Module Manager". Fitur non-esensial (Kupon, Variasi, Analitik) di-load menggunakan Dependency Injection Container.

FR-3.2: Jika status modul adalah false (Nonaktif), file kelas PHP modul tersebut sama sekali tidak di-require atau diinisialisasi.

5.4. Migrasi dari WooCommerce

FR-4.1: Alat migrasi harus membaca struktur HPOS (High-Performance Order Storage) maupun Legacy Postmeta dari WooCommerce.

FR-4.2: Migrasi harus berjalan asinkron di latar belakang menggunakan Action Scheduler untuk menghindari timeout pada database besar (>50.000 produk).

FR-4.3: Memiliki tombol "Sync Back" sebagai fallback jika pengguna ingin membatalkan penggunaan OwwCommerce dan kembali ke WooCommerce tanpa kehilangan data pesanan terbaru.

6. Kebutuhan Non-Fungsional (Teknis & Standar)

6.1. Tech Stack & Environment

PHP: Minimum versi 8.1. Wajib menggunakan fitur modern seperti Enums, Typed Properties, Readonly properties, dan Constructor Property Promotion.

Frontend Assets: 100% Vanilla JavaScript ES6+ (Dilarang keras menggunakan jQuery).

CSS: Menggunakan pendekatan utility-first (seperti Tailwind) atau BEM dengan CSS Variables. Ukuran maksimal file CSS frontend < 30KB (Gzipped).

WordPress Admin UI: Dibangun menggunakan React (wp-element) untuk interaksi instan tanpa memuat ulang halaman.

6.2. Performa & Metrik

Database Queries: Halaman produk tunggal (Single Product) maksimal mengeksekusi 15 query database (WooCommerce biasanya 60-100+).

Asset Loading: Skrip OwwCommerce HANYA diregistrasi pada is_owwcommerce_page().

PageSpeed: Standar skor minimal 90+ di Google Lighthouse untuk instalasi tema standar (seperti GeneratePress atau Astra).

7. Desain Arsitektur Data Inti (Draft Awal)

Alih-alih menyebar di puluhan baris wp_postmeta, data dipusatkan:

wp_oww_products: id, title, slug, description, type, price, sale_price, sku, stock_qty, created_at.

wp_oww_orders: id, customer_id, total_amount, status, payment_method, shipping_method, created_at.

wp_oww_order_items: id, order_id, product_id, qty, unit_price, total_price.

wp_oww_customers: id, wp_user_id (nullable), first_name, last_name, email, phone, total_spent.

8. Go-To-Market & Strategi Adopsi

Pemasaran "Speed & SEO": Fokus pada metrik Core Web Vitals. Targetkan pemilik toko yang gagal mendapatkan ranking SEO karena situs WooCommerce mereka lambat.

Developer-First Approach: Sediakan dokumentasi API dan struktur Hook/Filter yang luar biasa bersih agar agensi web lebih memilih OwwCommerce untuk proyek klien mereka.

The "Safe Try" Guarantee: Kampanyekan fitur migrasi 1-klik yang aman. Pengguna bisa mencoba OwwCommerce di staging atau langsung tanpa merusak database WooCommerce asli mereka.