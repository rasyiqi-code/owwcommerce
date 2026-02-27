<?php
namespace OwwCommerce\Repositories;

/**
 * Class CustomerRepository
 *
 * CRUD operasi untuk tabel oww_customers.
 * Digunakan oleh CheckoutController untuk menyimpan data pelanggan saat checkout.
 */
class CustomerRepository {
    private string $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'oww_customers';
    }

    /**
     * Membuat record customer baru.
     *
     * @param array $data Data customer (first_name, last_name, email, phone, wp_user_id)
     * @return int ID customer yang baru dibuat
     */
    public function create( array $data ): int {
        global $wpdb;

        $insert_data = [
            'first_name' => sanitize_text_field( $data['first_name'] ?? '' ),
            'last_name'  => sanitize_text_field( $data['last_name'] ?? '' ),
            'email'      => sanitize_email( $data['email'] ?? '' ),
            'phone'      => sanitize_text_field( $data['phone'] ?? '' ),
            'wp_user_id' => isset( $data['wp_user_id'] ) ? (int) $data['wp_user_id'] : null,
        ];

        $wpdb->insert( $this->table_name, $insert_data, [ '%s', '%s', '%s', '%s', '%d' ] );

        return (int) $wpdb->insert_id;
    }

    /**
     * Mengambil customer berdasarkan ID.
     */
    public function find( int $id ): ?array {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id ),
            ARRAY_A
        );

        return $row ?: null;
    }

    /**
     * Mencari customer berdasarkan email.
     * Berguna untuk menghindari duplikat saat checkout berulang.
     */
    public function find_by_email( string $email ): ?array {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE email = %s", $email ),
            ARRAY_A
        );

        return $row ?: null;
    }

    /**
     * Memperbarui data customer yang sudah ada.
     */
    public function update( int $id, array $data ): bool {
        global $wpdb;

        $update_data = [];
        $format      = [];

        if ( isset( $data['first_name'] ) ) {
            $update_data['first_name'] = sanitize_text_field( $data['first_name'] );
            $format[] = '%s';
        }
        if ( isset( $data['last_name'] ) ) {
            $update_data['last_name'] = sanitize_text_field( $data['last_name'] );
            $format[] = '%s';
        }
        if ( isset( $data['phone'] ) ) {
            $update_data['phone'] = sanitize_text_field( $data['phone'] );
            $format[] = '%s';
        }

        if ( empty( $update_data ) ) {
            return false;
        }

        return (bool) $wpdb->update( $this->table_name, $update_data, [ 'id' => $id ], $format, [ '%d' ] );
    }

    /**
     * Menambahkan jumlah total_spent pada customer.
     * Dipanggil setelah checkout berhasil.
     */
    public function add_spent( int $id, float $amount ): bool {
        global $wpdb;

        return (bool) $wpdb->query( $wpdb->prepare(
            "UPDATE {$this->table_name} SET total_spent = total_spent + %f WHERE id = %d",
            $amount,
            $id
        ) );
    }

    /**
     * Mengambil daftar semua customer (untuk Admin).
     */
    public function get_all( int $limit = 50, int $offset = 0 ): array {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
    }

    /**
     * Menghitung total pelanggan.
     */
    public function count(): int {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
    }
}
