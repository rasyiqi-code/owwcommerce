<?php
namespace OwwCommerce\Frontend;

/**
 * Handle WordPress Customizer Integration for OwwCommerce
 */
class Customizer {

    public function __construct() {
        add_action( 'customize_register', [ $this, 'register_settings' ] );
    }

    /**
     * Register Customizer Sections, Settings, and Controls
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function register_settings( $wp_customize ) {
        // 1. Add Section
        $wp_customize->add_section( 'owwc_shop_header_section', [
            'title'    => __( 'OwwCommerce Shop Header', 'owwcommerce' ),
            'priority' => 30,
        ] );

        // 2. Settings & Controls
        
        // Hero Background Color 1 (Start Gradient)
        $wp_customize->add_setting( 'owwc_hero_bg_color_1', [
            'default'           => '#14b8a6', // Default teal-500
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        ] );

        $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'owwc_hero_bg_color_1', [
            'label'    => __( 'Hero BG Color 1 (Start)', 'owwcommerce' ),
            'section'  => 'owwc_shop_header_section',
            'settings' => 'owwc_hero_bg_color_1',
        ] ) );

        // Hero Background Color 2 (End Gradient)
        $wp_customize->add_setting( 'owwc_hero_bg_color_2', [
            'default'           => '#0d9488', // Default teal-600
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        ] );

        $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'owwc_hero_bg_color_2', [
            'label'    => __( 'Hero BG Color 2 (End)', 'owwcommerce' ),
            'section'  => 'owwc_shop_header_section',
            'settings' => 'owwc_hero_bg_color_2',
        ] ) );

        // Hero Text Color
        $wp_customize->add_setting( 'owwc_hero_text_color', [
            'default'           => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        ] );

        $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'owwc_hero_text_color', [
            'label'    => __( 'Hero Title & Breadcrumb Color', 'owwcommerce' ),
            'section'  => 'owwc_shop_header_section',
            'settings' => 'owwc_hero_text_color',
        ] ) );

        // Hero Subtitle Color
        $wp_customize->add_setting( 'owwc_hero_subtitle_color', [
            'default'           => 'rgba(255, 255, 255, 0.9)',
            'sanitize_callback' => 'sanitize_text_field', // Hex color doesn't support rgba
            'transport'         => 'refresh',
        ] );

        $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'owwc_hero_subtitle_color', [
            'label'    => __( 'Hero Subtitle Color', 'owwcommerce' ),
            'section'  => 'owwc_shop_header_section',
            'settings' => 'owwc_hero_subtitle_color',
        ] ) );
    }
}
