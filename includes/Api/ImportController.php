<?php
namespace OwwCommerce\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use OwwCommerce\Repositories\ProductRepository;
use OwwCommerce\Models\Product;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends WP_REST_Controller {

    private ProductRepository $repository;

    public function __construct() {
        $this->namespace  = 'owwc/v1';
        $this->rest_base  = 'import';
        $this->repository = new ProductRepository();
        
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/excel', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'import_excel' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/export', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'export_excel' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/test-lib', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'test_lib' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ] );
    }

    public function permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    public function import_excel( $request ) {
        set_time_limit( 0 );
        ini_set( 'memory_limit', '512M' );

        $files = $request->get_file_params();
        if ( empty( $files['excel_file'] ) ) {
            return new WP_Error( 'rest_no_file', 'No Excel file uploaded.', [ 'status' => 400 ] );
        }

        $file_path = $files['excel_file']['tmp_name'];
        
        try {
            // Bersihkan SEMUA buffer untuk mencegah output tak terduga (Warning/Notice)
            while ( ob_get_level() ) {
                ob_end_clean();
            }
            ob_start();

            if ( ! class_exists( 'PhpOffice\PhpSpreadsheet\IOFactory' ) ) {
                throw new \Exception( 'Library PhpSpreadsheet tidak ditemukan. Pastikan vendor/ dipasang dengan benar.' );
            }

            $spreadsheet = IOFactory::load( $file_path );
            $worksheet   = $spreadsheet->getActiveSheet();
            $rows        = $worksheet->toArray();
            
            if ( count( $rows ) < 2 ) {
                return new WP_Error( 'rest_empty_file', 'File Excel kosong atau tidak memiliki data.', [ 'status' => 400 ] );
            }

            // Header mapping
            $header = array_shift( $rows );
            $header = array_map( 'trim', $header );

            $imported = 0;
            foreach ( $rows as $row ) {
                // Skip empty rows
                if ( empty( array_filter( $row ) ) ) continue;

                if ( count( $header ) !== count( $row ) ) {
                    continue;
                }

                $data = array_combine( $header, $row );
                if ( ! $data ) continue;

                // Skip jika title kosong
                if ( empty( trim( $data['title'] ?? '' ) ) ) {
                    continue;
                }

                $product = new Product( [
                    'title'       => $data['title'] ?? '',
                    'slug'        => sanitize_title( $data['title'] ?? '' ),
                    'description' => $data['description'] ?? '',
                    'price'       => floatval( $data['price'] ?? 0 ),
                    'sale_price'  => !empty($data['sale_price']) ? floatval($data['sale_price']) : null,
                    'sku'         => $data['sku'] ?? '',
                    'stock_qty'   => intval( $data['stock'] ?? 0 ),
                    'image_url'   => $data['image_url'] ?? null,
                    'status'      => 'publish',
                    'type'        => 'simple'
                ] );

                $this->repository->save( $product );
                $imported++;
            }

            return rest_ensure_response( [
                'success'  => true,
                'imported' => $imported
            ] );

        } catch ( \Exception $e ) {
            error_log( "OwwCommerce Excel Import Error: " . $e->getMessage() );
            return new WP_Error( 'rest_import_failed', 'Gagal membaca file Excel: ' . $e->getMessage(), [ 'status' => 500 ] );
        }
    }

    public function export_excel() {
        set_time_limit( 0 );
        ini_set( 'memory_limit', '512M' );

        try {
            $products = $this->repository->get_all( 9999, 0 );
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Bersihkan buffer
            while ( ob_get_level() ) {
                ob_end_clean();
            }
            ob_start();
            $headers = [ 'title', 'description', 'price', 'sale_price', 'sku', 'stock', 'image_url' ];
            $column = 'A';
            foreach ( $headers as $h ) {
                $sheet->setCellValue( $column . '1', $h );
                $sheet->getStyle( $column . '1' )->getFont()->setBold( true );
                $column++;
            }

            // Data
            $row_index = 2;
            foreach ( $products as $p ) {
                if ( empty( trim( $p->title ) ) ) continue;

                $sheet->setCellValue( 'A' . $row_index, $p->title );
                $sheet->setCellValue( 'B' . $row_index, $p->description );
                $sheet->setCellValue( 'C' . $row_index, $p->price );
                $sheet->setCellValue( 'D' . $row_index, $p->sale_price );
                $sheet->setCellValue( 'E' . $row_index, $p->sku );
                $sheet->setCellValue( 'F' . $row_index, $p->stock_qty );
                $sheet->setCellValue( 'G' . $row_index, $p->image_url );
                $row_index++;
            }

            $filename = 'owwcommerce-products-' . date('Y-m-d') . '.xlsx';
            
            // Bersihkan buffer sebelum kirim file
            if ( ob_get_length() ) ob_clean();

            header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
            header( 'Cache-Control: max-age=0' );

            $writer = new Xlsx( $spreadsheet );
            $writer->save( 'php://output' );
            exit;

        } catch ( \Exception $e ) {
            if ( ob_get_length() ) ob_clean();
            wp_die( "Gagal membuat file Excel: " . $e->getMessage() );
        }
    }

    public function test_lib() {
        ob_start();
        $exists = class_exists( 'PhpOffice\PhpSpreadsheet\IOFactory' );
        $version = \Composer\InstalledVersions::getVersion('phpoffice/phpspreadsheet') ?? 'unknown';
        return rest_ensure_response( [
            'class_exists' => $exists,
            'version' => $version,
            'php_version' => PHP_VERSION,
            'extensions' => [
                'gd' => extension_loaded('gd'),
                'zip' => extension_loaded('zip'),
                'xml' => extension_loaded('xml'),
            ]
        ] );
    }
}
