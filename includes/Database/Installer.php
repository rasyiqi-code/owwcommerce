<?php
namespace OwwCommerce\Database;

/**
 * Installer class to create custom database tables.
 */
class Installer {

    public static function install() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $collate = $wpdb->get_charset_collate();

        // Tabel: wp_oww_products
        $table_products = $wpdb->prefix . 'oww_products';
        $sql_products = "CREATE TABLE $table_products (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(200) NOT NULL,
            slug varchar(200) NOT NULL,
            description longtext NOT NULL,
            type varchar(50) NOT NULL DEFAULT 'simple',
            status varchar(20) DEFAULT 'publish',
            price decimal(10,2) NOT NULL DEFAULT '0.00',
            sale_price decimal(10,2) DEFAULT NULL,
            sku varchar(100) DEFAULT NULL,
            stock_qty int(11) NOT NULL DEFAULT '0',
            image_url varchar(500) DEFAULT NULL,
            gallery_ids text DEFAULT NULL,
            upsell_ids text DEFAULT NULL,
            cross_sell_ids text DEFAULT NULL,
            checkout_url varchar(500) DEFAULT NULL,
            whatsapp_url varchar(500) DEFAULT NULL,
            sales_count int(11) NOT NULL DEFAULT '0',
            created_by bigint(20) unsigned DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug),
            KEY type (type),
            KEY sku (sku),
            KEY created_by (created_by)
        ) $collate;";

        // Tabel: wp_oww_customers
        $table_customers = $wpdb->prefix . 'oww_customers';
        $sql_customers = "CREATE TABLE $table_customers (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wp_user_id bigint(20) unsigned DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(50) DEFAULT NULL,
            total_spent decimal(10,2) NOT NULL DEFAULT '0.00',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY wp_user_id (wp_user_id),
            UNIQUE KEY email (email)
        ) $collate;";

        // Tabel: wp_oww_orders
        $table_orders = $wpdb->prefix . 'oww_orders';
        $sql_orders = "CREATE TABLE $table_orders (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            customer_id bigint(20) unsigned NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            total_amount decimal(10,2) NOT NULL DEFAULT '0.00',
            payment_method varchar(100) DEFAULT NULL,
            shipping_method varchar(100) DEFAULT NULL,
            coupon_code varchar(50) DEFAULT NULL,
            discount_total decimal(10,2) NOT NULL DEFAULT '0.00',
            billing_address text DEFAULT NULL,
            shipping_address text DEFAULT NULL,
            payment_proof varchar(500) DEFAULT NULL,
            payment_note text DEFAULT NULL,
            order_key varchar(100) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY customer_id (customer_id),
            KEY status (status)
        ) $collate;";

        // Tabel: wp_oww_order_items
        $table_order_items = $wpdb->prefix . 'oww_order_items';
        $sql_order_items = "CREATE TABLE $table_order_items (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned NOT NULL,
            product_id bigint(20) unsigned NOT NULL,
            qty int(11) NOT NULL DEFAULT '1',
            unit_price decimal(10,2) NOT NULL DEFAULT '0.00',
            total_price decimal(10,2) NOT NULL DEFAULT '0.00',
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY product_id (product_id)
        ) $collate;";

        // Tabel: wp_oww_categories
        $table_categories = $wpdb->prefix . 'oww_categories';
        $sql_categories = "CREATE TABLE $table_categories (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            slug varchar(200) NOT NULL,
            parent_id bigint(20) unsigned DEFAULT '0',
            description longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug),
            KEY parent_id (parent_id)
        ) $collate;";

        // Tabel: wp_oww_product_category_rel
        $table_prod_cat_rel = $wpdb->prefix . 'oww_product_category_rel';
        $sql_prod_cat_rel = "CREATE TABLE $table_prod_cat_rel (
            product_id bigint(20) unsigned NOT NULL,
            category_id bigint(20) unsigned NOT NULL,
            PRIMARY KEY  (product_id, category_id),
            KEY category_id (category_id)
        ) $collate;";

        // Tabel: wp_oww_coupons
        $table_coupons = $wpdb->prefix . 'oww_coupons';
        $sql_coupons = "CREATE TABLE $table_coupons (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            code varchar(50) NOT NULL,
            type varchar(20) NOT NULL DEFAULT 'percent',
            amount decimal(10,2) NOT NULL DEFAULT '0.00',
            description text,
            usage_limit int(11) DEFAULT NULL,
            usage_count int(11) NOT NULL DEFAULT '0',
            expiry_date datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code)
        ) $collate;";

        // Tabel: wp_oww_attributes
        $table_attributes = $wpdb->prefix . 'oww_attributes';
        $sql_attributes = "CREATE TABLE $table_attributes (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            slug varchar(200) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug)
        ) $collate;";

        // Tabel: wp_oww_attribute_terms
        $table_attribute_terms = $wpdb->prefix . 'oww_attribute_terms';
        $sql_attribute_terms = "CREATE TABLE $table_attribute_terms (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            attribute_id bigint(20) unsigned NOT NULL,
            name varchar(200) NOT NULL,
            slug varchar(200) NOT NULL,
            PRIMARY KEY  (id),
            KEY attribute_id (attribute_id),
            UNIQUE KEY slug (slug)
        ) $collate;";

        // Tabel: wp_oww_product_variations
        $table_variations = $wpdb->prefix . 'oww_product_variations';
        $sql_variations = "CREATE TABLE $table_variations (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            product_id bigint(20) unsigned NOT NULL,
            sku varchar(100) DEFAULT NULL,
            price decimal(10,2) NOT NULL DEFAULT '0.00',
            sale_price decimal(10,2) DEFAULT NULL,
            stock_qty int(11) NOT NULL DEFAULT '0',
            attributes text NOT NULL, /* JSON serialized attributes mapping */
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY sku (sku)
        ) $collate;";

        // Tabel: wp_oww_reviews
        $table_reviews = $wpdb->prefix . 'oww_reviews';
        $sql_reviews = "CREATE TABLE $table_reviews (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            product_id bigint(20) unsigned NOT NULL,
            customer_id bigint(20) unsigned DEFAULT NULL,
            rating tinyint(1) NOT NULL DEFAULT '5',
            comment text NOT NULL,
            author_name varchar(100) DEFAULT NULL,
            author_email varchar(100) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'approved',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY customer_id (customer_id),
            KEY status (status)
        ) $collate;";

        dbDelta( $sql_products );
        dbDelta( $sql_customers );
        dbDelta( $sql_orders );
        dbDelta( $sql_order_items );
        dbDelta( $sql_categories );
        dbDelta( $sql_prod_cat_rel );
        dbDelta( $sql_coupons );
        dbDelta( $sql_attributes );
        dbDelta( $sql_attribute_terms );
        dbDelta( $sql_variations );
        dbDelta( $sql_reviews );

        self::insert_default_data();

        update_option( 'owwcommerce_db_version', OWWCOMMERCE_VERSION );
        flush_rewrite_rules();
    }

    /**
     * Memasukkan data produk, ulasan, kupon, kategori, dan variasi contoh
     * untuk kebutuhan demo instan bagi pengguna.
     */
    private static function insert_default_data() {
        global $wpdb;

        $table_products        = $wpdb->prefix . 'oww_products';
        $table_categories      = $wpdb->prefix . 'oww_categories';
        $table_prod_cat_rel    = $wpdb->prefix . 'oww_product_category_rel';
        $table_coupons         = $wpdb->prefix . 'oww_coupons';
        $table_attributes      = $wpdb->prefix . 'oww_attributes';
        $table_attribute_terms = $wpdb->prefix . 'oww_attribute_terms';
        $table_variations      = $wpdb->prefix . 'oww_product_variations';
        $table_reviews         = $wpdb->prefix . 'oww_reviews';

        // 1. Cek apakah produk sudah ada di database
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_products" );
        if ( $count > 0 ) {
            return; // Data contoh sudah ada atau pengguna sudah memiliki produk
        }

        // 2. Tambah Kategori Contoh
        $categories = [
            [ 'name' => 'Buku', 'slug' => 'buku', 'description' => 'Koleksi buku bisnis, panduan, dan literatur berkualitas.' ],
            [ 'name' => 'Elektronik', 'slug' => 'elektronik', 'description' => 'Perangkat audio, aksesoris, dan gawai pendukung kerja.' ],
            [ 'name' => 'Pakaian', 'slug' => 'pakaian', 'description' => 'Busana kaos premium, jaket, dan pakaian harian adem.' ],
        ];
        $cat_ids = [];
        foreach ( $categories as $cat ) {
            $wpdb->insert( $table_categories, $cat );
            $cat_ids[ $cat['slug'] ] = $wpdb->insert_id;
        }

        // 3. Tambah Atribut & Terms (untuk variasi Pakaian)
        $wpdb->insert( $table_attributes, [ 'name' => 'Ukuran', 'slug' => 'ukuran' ] );
        $attr_id = $wpdb->insert_id;

        $terms = [
            [ 'attribute_id' => $attr_id, 'name' => 'M', 'slug' => 'm' ],
            [ 'attribute_id' => $attr_id, 'name' => 'L', 'slug' => 'l' ],
        ];
        foreach ( $terms as $term ) {
            $wpdb->insert( $table_attribute_terms, $term );
        }

        // 4. Tambah Produk 1: Buku (Simple Product)
        $wpdb->insert( $table_products, [
            'title'        => 'Panduan Sukses Bisnis Online',
            'slug'         => 'panduan-sukses-bisnis-online',
            'description'  => 'Buku panduan lengkap cara memulai dan mengembangkan bisnis online dari nol untuk pemula. Ditulis oleh praktisi bisnis berpengalaman dengan ulasan langkah demi langkah yang sangat praktis dan taktis.',
            'type'         => 'simple',
            'status'       => 'publish',
            'price'        => 150000.00,
            'sale_price'   => 99000.00,
            'sku'          => 'BUKU-001',
            'stock_qty'    => 25,
            'image_url'    => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&q=80&w=600',
            'sales_count'  => 12, // Memicu status badge "TERLARIS"
        ] );
        $prod1_id = $wpdb->insert_id;
        if ( isset( $cat_ids['buku'] ) ) {
            $wpdb->insert( $table_prod_cat_rel, [ 'product_id' => $prod1_id, 'category_id' => $cat_ids['buku'] ] );
        }

        // Tambah Ulasan Awal untuk Produk 1
        $wpdb->insert( $table_reviews, [
            'product_id'   => $prod1_id,
            'rating'       => 5,
            'comment'      => 'Sangat bermanfaat! Penjelasannya mudah dipahami bagi pemula seperti saya yang baru mau belajar jualan.',
            'author_name'  => 'Budi Santoso',
            'author_email' => 'budi@example.com',
            'status'       => 'approved',
        ] );
        $wpdb->insert( $table_reviews, [
            'product_id'   => $prod1_id,
            'rating'       => 4,
            'comment'      => 'Buku yang sangat bagus. Ilustrasinya menarik dan pengiriman bukunya cepat sekali.',
            'author_name'  => 'Siti Aminah',
            'author_email' => 'siti@example.com',
            'status'       => 'approved',
        ] );

        // 5. Tambah Produk 2: Pakaian (Variable Product)
        $wpdb->insert( $table_products, [
            'title'        => 'Kaos Premium OwwCommerce',
            'slug'         => 'kaos-premium-owwcommerce',
            'description'  => 'Kaos katun bambu super adem dengan sablon logo premium OwwCommerce. Sangat nyaman dipakai harian dan memiliki jahitan rapi kualitas distro.',
            'type'         => 'variable',
            'status'       => 'publish',
            'price'        => 125000.00,
            'sale_price'   => null,
            'sku'          => 'KAOS-001',
            'stock_qty'    => 35,
            'image_url'    => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&q=80&w=600',
        ] );
        $prod2_id = $wpdb->insert_id;
        if ( isset( $cat_ids['pakaian'] ) ) {
            $wpdb->insert( $table_prod_cat_rel, [ 'product_id' => $prod2_id, 'category_id' => $cat_ids['pakaian'] ] );
        }

        // Hubungkan variasi produk
        $wpdb->insert( $table_variations, [
            'product_id' => $prod2_id,
            'sku'        => 'KAOS-001-M',
            'price'      => 120000.00,
            'sale_price' => null,
            'stock_qty'  => 15,
            'attributes' => json_encode( [ (string)$attr_id => 'M' ] ),
        ] );
        $wpdb->insert( $table_variations, [
            'product_id' => $prod2_id,
            'sku'        => 'KAOS-001-L',
            'price'      => 125000.00,
            'sale_price' => null,
            'stock_qty'  => 20,
            'attributes' => json_encode( [ (string)$attr_id => 'L' ] ),
        ] );

        // 6. Tambah Produk 3: Elektronik (Simple Product - Checkout Eksternal/WA)
        $wpdb->insert( $table_products, [
            'title'        => 'Earphone Bass Booster',
            'slug'         => 'earphone-bass-booster',
            'description'  => 'Earphone kabel dengan teknologi super bass dan audio jernih maksimal. Sangat cocok untuk mendengarkan musik stereo, bermain gim, dan video call meeting harian.',
            'type'         => 'simple',
            'status'       => 'publish',
            'price'        => 75000.00,
            'sale_price'   => 59000.00,
            'sku'          => 'EAR-001',
            'stock_qty'    => 50,
            'image_url'    => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&q=80&w=600',
            'checkout_url' => 'https://shopee.co.id',
            'whatsapp_url' => '628123456789',
        ] );
        $prod3_id = $wpdb->insert_id;
        if ( isset( $cat_ids['elektronik'] ) ) {
            $wpdb->insert( $table_prod_cat_rel, [ 'product_id' => $prod3_id, 'category_id' => $cat_ids['elektronik'] ] );
        }

        // 7. Tambah Kupon Contoh
        $wpdb->insert( $table_coupons, [
            'code'        => 'DISKON10',
            'type'        => 'percent',
            'amount'      => 10.00,
            'description' => 'Kupon diskon 10% untuk semua jenis pesanan tanpa minimum belanja.',
        ] );
        $wpdb->insert( $table_coupons, [
            'code'        => 'DISKON50K',
            'type'        => 'fixed',
            'amount'      => 50000.00,
            'description' => 'Potongan harga langsung senilai Rp 50.000.',
        ] );

        // Set opsi flag agar data demo tidak di-impor ulang di masa mendatang
        update_option( 'owwc_demo_data_imported', 1 );
    }
}

