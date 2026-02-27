<?php
/**
 * Test ProductRepository
 *
 * Menguji operasi CRUD produk termasuk field image_url dan reduce_stock().
 *
 * @package Owwcommerce
 */

namespace OwwCommerce\Tests;

use WP_UnitTestCase;
use OwwCommerce\Database\Installer;
use OwwCommerce\Models\Product;
use OwwCommerce\Repositories\ProductRepository;

class Test_ProductRepository extends WP_UnitTestCase {

    private ProductRepository $repo;

    public function setUp(): void {
        parent::setUp();
        Installer::install();
        $this->repo = new ProductRepository();
    }

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

    /**
     * Test: Menyimpan dan mengambil produk sederhana.
     */
    public function test_create_and_find_product() {
        $product = new Product( [
            'title'     => 'Kaos Polos Hitam',
            'price'     => 99000,
            'stock_qty' => 50,
        ] );

        $saved = $this->repo->save( $product );

        $this->assertNotNull( $saved->id );
        $this->assertEquals( 'Kaos Polos Hitam', $saved->title );
        $this->assertEquals( 99000.0, $saved->price );
        $this->assertEquals( 50, $saved->stock_qty );

        // Verifikasi find() mengembalikan data yang sama
        $found = $this->repo->find( $saved->id );
        $this->assertNotNull( $found );
        $this->assertEquals( $saved->id, $found->id );
        $this->assertEquals( 'kaos-polos-hitam', $found->slug );
    }

    /**
     * Test: Field image_url disimpan dan dikembalikan dengan benar.
     */
    public function test_save_with_image_url() {
        $product = new Product( [
            'title'     => 'Celana Jeans',
            'price'     => 250000,
            'stock_qty' => 30,
            'image_url' => 'https://example.com/jeans.jpg',
        ] );

        $saved = $this->repo->save( $product );
        $found = $this->repo->find( $saved->id );

        $this->assertEquals( 'https://example.com/jeans.jpg', $found->image_url );
    }

    /**
     * Test: Produk tanpa image_url memiliki null.
     */
    public function test_save_without_image_url() {
        $product = new Product( [
            'title'     => 'Topi Bucket',
            'price'     => 75000,
            'stock_qty' => 20,
        ] );

        $saved = $this->repo->save( $product );

        $this->assertNull( $saved->image_url );
    }

    /**
     * Test: reduce_stock mengurangi stok dengan benar.
     */
    public function test_reduce_stock_success() {
        $product = new Product( [
            'title'     => 'Sepatu Sneakers',
            'price'     => 350000,
            'stock_qty' => 10,
        ] );

        $saved = $this->repo->save( $product );
        $result = $this->repo->reduce_stock( $saved->id, 3 );

        $this->assertTrue( $result );

        // Verifikasi stok berkurang
        $updated = $this->repo->find( $saved->id );
        $this->assertEquals( 7, $updated->stock_qty );
    }

    /**
     * Test: reduce_stock gagal jika stok tidak cukup.
     */
    public function test_reduce_stock_insufficient() {
        $product = new Product( [
            'title'     => 'Jaket Kulit',
            'price'     => 500000,
            'stock_qty' => 2,
        ] );

        $saved = $this->repo->save( $product );
        $result = $this->repo->reduce_stock( $saved->id, 5 ); // Minta 5, stok cuma 2

        $this->assertFalse( $result );

        // Verifikasi stok TIDAK berubah
        $unchanged = $this->repo->find( $saved->id );
        $this->assertEquals( 2, $unchanged->stock_qty );
    }

    /**
     * Test: find_by_slug mengembalikan produk yang tepat.
     */
    public function test_find_by_slug() {
        $product = new Product( [
            'title'     => 'Tas Ransel Premium',
            'price'     => 450000,
            'stock_qty' => 15,
        ] );

        $saved = $this->repo->save( $product );
        $found = $this->repo->find_by_slug( 'tas-ransel-premium' );

        $this->assertNotNull( $found );
        $this->assertEquals( $saved->id, $found->id );
    }

    /**
     * Test: delete menghapus produk dari database.
     */
    public function test_delete_product() {
        $product = new Product( [
            'title'     => 'Produk Hapus',
            'price'     => 10000,
            'stock_qty' => 1,
        ] );

        $saved   = $this->repo->save( $product );
        $deleted = $this->repo->delete( $saved->id );

        $this->assertTrue( $deleted );
        $this->assertNull( $this->repo->find( $saved->id ) );
    }

    /**
     * Test: get_all mengembalikan array Product.
     */
    public function test_get_all() {
        // Buat 3 produk
        for ( $i = 1; $i <= 3; $i++ ) {
            $this->repo->save( new Product( [
                'title'     => "Produk Test $i",
                'price'     => $i * 10000,
                'stock_qty' => $i,
            ] ) );
        }

        $all = $this->repo->get_all();
        $this->assertCount( 3, $all );
        $this->assertInstanceOf( Product::class, $all[0] );
    }

    /**
     * Test: to_array() mengembalikan semua field termasuk image_url.
     */
    public function test_to_array_includes_image_url() {
        $product = new Product( [
            'title'     => 'Test Array',
            'price'     => 100,
            'image_url' => 'https://img.test/abc.png',
        ] );

        $arr = $product->to_array();

        $this->assertArrayHasKey( 'image_url', $arr );
        $this->assertEquals( 'https://img.test/abc.png', $arr['image_url'] );
    }
}
