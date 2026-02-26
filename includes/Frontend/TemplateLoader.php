<?php
namespace OwwCommerce\Frontend;

/**
 * Handle pemuatan template frontend
 */
class TemplateLoader {

    /**
     * Memuat sebuah template (mencari di tema pengguna lebih dulu, baru fallback ke plugin).
     * 
     * @param string $template_name Nama file template (misal: 'shop.php')
     * @param array $args Data variabel yang akan di-extract ke dalam template
     * @return string Konten HTML dari template
     */
    public static function get_template( string $template_name, array $args = [] ): string {
        if ( ! empty( $args ) && is_array( $args ) ) {
            extract( $args );
        }

        // Cek di tema anak / tema utama terlebih dahulu (misal: wp-content/themes/namatema/owwcommerce/shop.php)
        $located = locate_template( [
            'owwcommerce/' . $template_name,
            $template_name
        ] );

        // Fallback ke plugin directory jika tidak ditemukan di tema
        if ( ! $located ) {
            $located = OWWCOMMERCE_PLUGIN_DIR . 'templates/frontend/' . $template_name;
        }

        if ( ! file_exists( $located ) ) {
            return '<!-- Template ' . esc_html( $template_name ) . ' tidak ditemukan. -->';
        }

        // Gunakan output buffering untuk menangkap output require
        ob_start();
        include $located;
        $output = ob_get_clean();
        
        // Debugging
        error_log("OwwCommerce TemplateLoader loaded: " . $located . " | Size: " . strlen($output));
        
        return $output;
    }
}
