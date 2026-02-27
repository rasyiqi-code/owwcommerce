<?php
namespace OwwCommerce\Models;

/**
 * Class Coupon
 * Merepresentasikan data kupon diskon.
 */
class Coupon {
    public ?int $id;
    public string $code;
    public string $type; // 'percent' atau 'fixed_cart'
    public float $amount;
    public ?string $description;
    public ?int $usage_limit;
    public int $usage_count;
    public ?string $expiry_date;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct( array $data = [] ) {
        $this->id          = isset( $data['id'] ) ? (int) $data['id'] : null;
        $this->code        = $data['code'] ?? '';
        $this->type        = $data['type'] ?? 'percent';
        $this->amount      = isset( $data['amount'] ) ? (float) $data['amount'] : 0.00;
        $this->description = $data['description'] ?? null;
        $this->usage_limit = ( isset( $data['usage_limit'] ) && $data['usage_limit'] !== '' ) ? (int) $data['usage_limit'] : null;
        $this->usage_count = isset( $data['usage_count'] ) ? (int) $data['usage_count'] : 0;
        $this->expiry_date = $data['expiry_date'] ?? null;
        $this->created_at  = $data['created_at'] ?? null;
        $this->updated_at  = $data['updated_at'] ?? null;
    }

    /**
     * Mengecek apakah kupon masih berlaku.
     */
    public function is_valid(): bool {
        // Cek expiry date
        if ( ! empty( $this->expiry_date ) ) {
            // Normalisasi pemisah tanggal (Ubah / ke - agar strtotime mengenali format d-m-Y)
            $date_str = str_replace('/', '-', $this->expiry_date);
            $expiry_ts = strtotime( $date_str );
            
            // Jika parsing berhasil, bandingkan dengan waktu sekarang.
            // Gunakan akhir hari (23:59:59) jika formatnya hanya tanggal saja.
            if ( $expiry_ts !== false ) {
                if ( strlen( $date_str ) <= 10 ) {
                    $expiry_ts = strtotime( date( 'Y-m-d 23:59:59', $expiry_ts ) );
                }
                
                if ( $expiry_ts < time() ) {
                    return false;
                }
            }
        }

        // Cek usage limit
        // Anggap null atau 0 sebagai tanpa batas (unlimited)
        if ( ! empty( $this->usage_limit ) && $this->usage_limit > 0 ) {
            if ( $this->usage_count >= $this->usage_limit ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Menghitung nilai diskon berdasarkan total belanja.
     */
    public function calculate_discount( float $total ): float {
        if ( ! $this->is_valid() ) {
            return 0;
        }

        if ( $this->type === 'percent' ) {
            return ( $total * $this->amount ) / 100;
        }

        // fixed_cart
        return min( $this->amount, $total );
    }

    public function to_array(): array {
        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'type'        => $this->type,
            'amount'      => $this->amount,
            'description' => $this->description,
            'usage_limit' => $this->usage_limit,
            'usage_count' => $this->usage_count,
            'expiry_date' => $this->expiry_date,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
