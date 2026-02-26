<?php
namespace OwwCommerce\Core;

/**
 * Fired during plugin deactivation.
 */
class Deactivator {

    /**
     * Jalankan saat plugin dinonaktifkan.
     */
    public static function deactivate() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        flush_rewrite_rules();
    }
}
