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

        update_option( 'owwcommerce_db_version', OWWCOMMERCE_VERSION );
        flush_rewrite_rules();
    }
}
