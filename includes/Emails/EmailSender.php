<?php
namespace OwwCommerce\Emails;

use OwwCommerce\Models\Order;
use OwwCommerce\Repositories\CustomerRepository;

/**
 * Class EmailSender
 * Mengirim notifikasi email menggunakan wp_mail dengan template HTML.
 */
class EmailSender {

    /**
     * Kirim konfirmasi pesanan ke Pelanggan.
     */
    public function send_order_confirmation( Order $order ) {
        $customer_repo = new CustomerRepository();
        $customer = $customer_repo->find( $order->customer_id );

        if ( ! $customer || empty( $customer['email'] ) ) {
            return false;
        }

        $to      = $customer['email'];
        $subject = sprintf( __( 'Konfirmasi Pesanan #%d - OwwCommerce', 'owwcommerce' ), $order->id );
        
        $body = $this->get_template_content( 'order-confirmation', [
            'order'    => $order,
            'customer' => $customer
        ] );

        return $this->send( $to, $subject, $body );
    }

    /**
     * Kirim notifikasi pesanan baru ke Admin.
     */
    public function send_admin_new_order( Order $order ) {
        $to      = get_option( 'admin_email' );
        $subject = sprintf( __( '[Pesanan Baru] #%d - OwwCommerce', 'owwcommerce' ), $order->id );
        
        $body = $this->get_template_content( 'admin-new-order', [
            'order' => $order
        ] );

        return $this->send( $to, $subject, $body );
    }

    /**
     * Kirim notifikasi perubahan status ke Pelanggan.
     */
    public function send_status_update( Order $order ) {
        $customer_repo = new CustomerRepository();
        $customer = $customer_repo->find( $order->customer_id );

        if ( ! $customer || empty( $customer['email'] ) ) {
            return false;
        }

        $to      = $customer['email'];
        $subject = sprintf( __( 'Update Status Pesanan #%d - OwwCommerce', 'owwcommerce' ), $order->id );
        
        $body = $this->get_template_content( 'order-status-update', [
            'order'    => $order,
            'customer' => $customer
        ] );

        return $this->send( $to, $subject, $body );
    }

    /**
     * Wrapper wp_mail dengan header HTML.
     */
    private function send( $to, $subject, $body ) {
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        return wp_mail( $to, $subject, $body, $headers );
    }

    /**
     * Memuat konten template PHP dan mengembalikan sebagai string.
     */
    private function get_template_content( $template_name, $args = [] ) {
        extract( $args );
        
        $template_path = OWWCOMMERCE_PLUGIN_DIR . 'templates/emails/' . $template_name . '.php';
        
        if ( ! file_exists( $template_path ) ) {
            return '';
        }

        ob_start();
        include $template_path;
        return ob_get_clean();
    }
}
