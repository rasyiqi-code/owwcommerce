<?php
namespace OwwCommerce\Repositories;

use OwwCommerce\Models\Coupon;

/**
 * Class CouponRepository
 * CRUD untuk tabel oww_coupons.
 */
class CouponRepository {
    private string $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'oww_coupons';
    }

    public function save( Coupon $coupon ): Coupon {
        global $wpdb;

        $data = [
            'code'        => strtoupper( sanitize_text_field( $coupon->code ) ),
            'type'        => $coupon->type,
            'amount'      => $coupon->amount,
            'description' => $coupon->description,
            'usage_limit' => $coupon->usage_limit,
            'expiry_date' => (function() use ($coupon) {
                if ( ! $coupon->expiry_date ) return null;
                // Ganti / dengan - agar strtotime konsisten (menganggap d-m-Y jika ada dash)
                $date_str = str_replace('/', '-', $coupon->expiry_date);
                $ts = strtotime($date_str);
                return $ts ? date('Y-m-d 23:59:59', $ts) : $coupon->expiry_date;
            })(),
        ];

        $format = [ '%s', '%s', '%f', '%s', '%d', '%s' ];

        if ( $coupon->id ) {
            $wpdb->update( $this->table_name, $data, [ 'id' => $coupon->id ], $format, [ '%d' ] );
        } else {
            $wpdb->insert( $this->table_name, $data, $format );
            $coupon->id = $wpdb->insert_id;
        }

        return $this->find( $coupon->id );
    }

    public function find( int $id ): ?Coupon {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id ), ARRAY_A );
        return $row ? new Coupon( $row ) : null;
    }

    public function find_by_code( string $code ): ?Coupon {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE code = %s", strtoupper($code) ), ARRAY_A );
        return $row ? new Coupon( $row ) : null;
    }

    public function get_all( int $limit = 50, int $offset = 0 ): array {
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d", $limit, $offset ),
            ARRAY_A
        );
        return array_map( fn( $row ) => new Coupon( $row ), $results );
    }

    public function delete( int $id ): bool {
        global $wpdb;
        return (bool) $wpdb->delete( $this->table_name, [ 'id' => $id ], [ '%d' ] );
    }

    public function increment_usage( int $id ): bool {
        global $wpdb;
        return (bool) $wpdb->query( $wpdb->prepare(
            "UPDATE {$this->table_name} SET usage_count = usage_count + 1 WHERE id = %d",
            $id
        ) );
    }

    public function count(): int {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
    }
}
