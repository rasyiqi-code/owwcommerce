<?php
/**
 * Test CustomerRepository
 *
 * Menguji operasi CRUD customer dan method pendukung.
 *
 * @package Owwcommerce
 */

namespace OwwCommerce\Tests;

use WP_UnitTestCase;
use OwwCommerce\Database\Installer;
use OwwCommerce\Repositories\CustomerRepository;

class Test_CustomerRepository extends WP_UnitTestCase {

    private CustomerRepository $repo;

    public function setUp(): void {
        parent::setUp();
        Installer::install();
        $this->repo = new CustomerRepository();
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
     * Test: Membuat customer baru dan mengambilnya.
     */
    public function test_create_and_find_customer() {
        $id = $this->repo->create( [
            'first_name' => 'Budi',
            'last_name'  => 'Santoso',
            'email'      => 'budi@example.com',
            'phone'      => '08123456789',
        ] );

        $this->assertGreaterThan( 0, $id );

        $customer = $this->repo->find( $id );
        $this->assertNotNull( $customer );
        $this->assertEquals( 'Budi', $customer['first_name'] );
        $this->assertEquals( 'budi@example.com', $customer['email'] );
    }

    /**
     * Test: find_by_email menemukan customer yang ada.
     */
    public function test_find_by_email() {
        $this->repo->create( [
            'first_name' => 'Siti',
            'last_name'  => 'Nurhaliza',
            'email'      => 'siti@example.com',
            'phone'      => '08987654321',
        ] );

        $found = $this->repo->find_by_email( 'siti@example.com' );

        $this->assertNotNull( $found );
        $this->assertEquals( 'Siti', $found['first_name'] );
    }

    /**
     * Test: find_by_email mengembalikan null jika email tidak ada.
     */
    public function test_find_by_email_not_found() {
        $result = $this->repo->find_by_email( 'tidak-ada@example.com' );
        $this->assertNull( $result );
    }

    /**
     * Test: update berhasil mengubah data customer.
     */
    public function test_update_customer() {
        $id = $this->repo->create( [
            'first_name' => 'Test',
            'last_name'  => 'User',
            'email'      => 'test@example.com',
        ] );

        $updated = $this->repo->update( $id, [
            'first_name' => 'Test Updated',
            'phone'      => '08111222333',
        ] );

        $this->assertTrue( $updated );

        $customer = $this->repo->find( $id );
        $this->assertEquals( 'Test Updated', $customer['first_name'] );
        $this->assertEquals( '08111222333', $customer['phone'] );
    }

    /**
     * Test: add_spent menambah jumlah total_spent.
     */
    public function test_add_spent() {
        $id = $this->repo->create( [
            'first_name' => 'Shopper',
            'last_name'  => 'One',
            'email'      => 'shopper@example.com',
        ] );

        $this->repo->add_spent( $id, 150000.00 );
        $this->repo->add_spent( $id, 50000.00 );

        $customer = $this->repo->find( $id );
        $this->assertEquals( 200000.00, (float) $customer['total_spent'] );
    }

    /**
     * Test: get_all mengembalikan array customer.
     */
    public function test_get_all() {
        $this->repo->create( [ 'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@test.com' ] );
        $this->repo->create( [ 'first_name' => 'C', 'last_name' => 'D', 'email' => 'c@test.com' ] );

        $all = $this->repo->get_all();
        $this->assertCount( 2, $all );
    }
}
