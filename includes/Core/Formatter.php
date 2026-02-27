<?php
namespace OwwCommerce\Core;

/**
 * Utility for formatting numbers and currencies.
 */
class Formatter {

    /**
     * Format price with currency symbol and separators from settings.
     *
     * @param float|int $price
     * @return string
     */
    public static function format_price( $price ): string {
        $currency_symbol = get_option( 'owwc_currency_symbol', 'Rp' );
        $thousand_sep    = get_option( 'owwc_thousand_sep', '.' );
        $decimal_sep     = get_option( 'owwc_decimal_sep', ',' );
        
        $formatted = number_format( (float) $price, 0, $decimal_sep, $thousand_sep );
        
        return $currency_symbol . ' ' . $formatted;
    }
}
