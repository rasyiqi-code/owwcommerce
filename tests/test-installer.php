<?php
/**
 * Test Database Installer
 *
 * @package Owwcommerce
 */

namespace OwwCommerce\Tests;

use WP_UnitTestCase;
use OwwCommerce\Database\Installer;

class Test_Installer extends WP_UnitTestCase {

    public function tearDown(): void {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}oww_products" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}oww_customers" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}oww_orders" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}oww_order_items" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}oww_categories" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}oww_product_category_rel" );
        parent::tearDown();
    }

    public function test_tables_created() {
        global $wpdb;

        // Jalankan installer
        Installer::install();

        // Verifikasi tabel
        $this->assertEquals( $wpdb->prefix . 'oww_products', $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}oww_products'" ) );
        $this->assertEquals( $wpdb->prefix . 'oww_customers', $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}oww_customers'" ) );
        $this->assertEquals( $wpdb->prefix . 'oww_orders', $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}oww_orders'" ) );
        $this->assertEquals( $wpdb->prefix . 'oww_order_items', $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}oww_order_items'" ) );
        $this->assertEquals( $wpdb->prefix . 'oww_categories', $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}oww_categories'" ) );
        $this->assertEquals( $wpdb->prefix . 'oww_product_category_rel', $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}oww_product_category_rel'" ) );
    }

    public function test_products_table_columns() {
        global $wpdb;
        Installer::install();

        $columns = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}oww_products" );
        $column_names = array_map( function( $col ) {
            return $col->Field;
        }, $columns );

        $this->assertContains( 'id', $column_names );
        $this->assertContains( 'title', $column_names );
        $this->assertContains( 'price', $column_names );
        $this->assertContains( 'sku', $column_names );
    }
}
