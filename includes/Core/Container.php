<?php
namespace OwwCommerce\Core;

/**
 * Service Container and Module Manager for OwwCommerce
 */
class Container {

    private array $services = [];
    private array $instances = [];

    /**
     * Daftarkan service/module.
     * Jika $enabled = false, maka tidak diregistrasi.
     */
    public function register( string $id, callable $concrete, bool $enabled = true ): void {
        if ( ! $enabled ) {
            return; // Zero Bloatware: jangan muat jika tidak aktif
        }
        $this->services[ $id ] = $concrete;
    }

    /**
     * Dapatkan instance dari service/module.
     */
    public function get( string $id ) {
        if ( ! isset( $this->services[ $id ] ) ) {
            return null; // Atau throw exception
        }

        if ( ! isset( $this->instances[ $id ] ) ) {
            $this->instances[ $id ] = $this->services[ $id ]( $this );
        }

        return $this->instances[ $id ];
    }
}
