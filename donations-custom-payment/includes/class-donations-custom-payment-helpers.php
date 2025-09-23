<?php
/**
 * Helper functions class for Custom Payment Form plugin
 * 
 * @package Donations_Custom_Payment
 * @version 6.2.0
 * @author Mostafa Sadeghi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Donations_Custom_Payment_Helpers {
    
    /**
     * Class instance
     */
    private static $instance = null;
    
    /**
     * Get class instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Class constructor
     */
    private function __construct() {
        // Static class - no constructor needed
    }
    
    /**
     * Convert HEX to RGBA function
     */
    public static function hex_to_rgba($hex, $alpha = 1.0) {
        // Validate values
        if (empty($hex)) {
            $hex = '#000000';
        }
        if ($alpha === null || $alpha === '') {
            $alpha = 1.0;
        }
        
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        } else if (strlen($hex) == 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            // In case of error, return black color
            $r = $g = $b = 0;
        }
        
        // Ensure alpha is a number
        $alpha = floatval($alpha);
        
        return "rgba($r, $g, $b, $alpha)";
    }
    
    /**
     * Get currency information from WooCommerce
     */
    public static function get_currency_info() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return array(
                'code' => 'USD', // Global default currency
                'symbol' => '$',
                'name' => 'USD',
                'position' => 'left'
            );
        }
        
        $currency_code = get_woocommerce_currency();
        $currency_symbol = get_woocommerce_currency_symbol();
        $currency_position = get_option( 'woocommerce_currency_pos', 'left' );
        
        return array(
            'code' => $currency_code,
            'symbol' => $currency_symbol,
            'name' => $currency_code, // Use currency code instead of custom name
            'position' => $currency_position
        );
    }
    
    /**
     * Format price with currency
     */
    public static function format_price($amount, $show_symbol = true) {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return number_format($amount) . ($show_symbol ? ' Toman' : '');
        }
        
        return wc_price($amount, array(
            'currency' => get_woocommerce_currency()
        ));
    }
    
    /**
     * Get product name from settings
     */
    public static function get_product_name() {
        return get_option( 'donations_custom_payment_product_name', __( 'Custom Payment', 'donations-custom-payment' ) );
    }
    
    /**
     * Check HPOS (High-Performance Order Storage) usage
     */
    public static function is_hpos_enabled() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return false;
        }
        
        // Check HPOS class existence
        if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }
        
        return false;
    }
    
    /**
     * Get HPOS status information for debugging
     */
    public static function get_hpos_debug_info() {
        $info = array(
            'woocommerce_active' => class_exists( 'WooCommerce' ),
            'hpos_class_exists' => class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ),
            'hpos_enabled' => false,
            'current_method' => 'traditional'
        );
        
        if ( $info['woocommerce_active'] && $info['hpos_class_exists'] ) {
            $info['hpos_enabled'] = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
            $info['current_method'] = $info['hpos_enabled'] ? 'hpos' : 'traditional';
        }
        
        return $info;
    }
    
    /**
     * Validate amount
     */
    public static function validate_amount($amount, $min_amount = null, $max_amount = null) {
        if ($min_amount === null) {
            $min_amount = get_option( 'donations_custom_payment_min_amount', 1000 );
        }
        if ($max_amount === null) {
            $max_amount = get_option( 'donations_custom_payment_max_amount', 10000000 );
        }
        
        // Remove commas and spaces
        $amount = str_replace( array( ',', ' ' ), '', $amount );
        $amount = absint( $amount );
        
        if ( empty( $amount ) ) {
            return array(
                'valid' => false,
                'message' => __( 'Please enter an amount.', 'donations-custom-payment' )
            );
        }
        
        if ( $amount < $min_amount ) {
            $currency_info = self::get_currency_info();
            // translators: %s is the minimum amount with currency
            $error_msg = sprintf( __( 'Minimum payment amount is %s.', 'donations-custom-payment' ), number_format($min_amount) . ' ' . $currency_info['name'] );
            return array(
                'valid' => false,
                'message' => $error_msg
            );
        }
        
        if ( $amount > $max_amount ) {
            $currency_info = self::get_currency_info();
            // translators: %s is the maximum amount with currency
            $error_msg = sprintf( __( 'Maximum payment amount is %s.', 'donations-custom-payment' ), number_format($max_amount) . ' ' . $currency_info['name'] );
            return array(
                'valid' => false,
                'message' => $error_msg
            );
        }
        
        return array(
            'valid' => true,
            'amount' => $amount
        );
    }
    
    /**
     * Get form settings
     */
    public static function get_form_settings() {
        $currency_info = self::get_currency_info();
        
        return array(
            'min_amount' => get_option( 'donations_custom_payment_min_amount', 1000 ),
            'max_amount' => get_option( 'donations_custom_payment_max_amount', 10000000 ),
            'product_name' => get_option( 'donations_custom_payment_product_name', __( 'Custom Payment', 'donations-custom-payment' ) ),
            'preset_amounts' => get_option( 'donations_custom_payment_preset_amounts', '50000,100000,200000,500000' ),
            'form_title' => get_option( 'donations_custom_payment_form_title', __( '💳 Payment Form', 'donations-custom-payment' ) ),
            'show_form_title' => get_option( 'donations_custom_payment_show_form_title', 'yes' ),
            'button_text' => get_option( 'donations_custom_payment_button_text', __( '🚀 Continue Payment', 'donations-custom-payment' ) ),
            'button_color' => get_option( 'donations_custom_payment_button_color', '#007cba' ),
            'form_bg_color' => get_option( 'donations_custom_payment_form_bg_color', '#ffffff' ),
            'show_amount_info' => get_option( 'donations_custom_payment_show_amount_info', 'yes' ),
            'amount_field_label' => get_option( 'donations_custom_payment_amount_field_label', __( '💰 Amount (in Tomans)', 'donations-custom-payment' ) ),
            'amount_field_bg_color' => get_option( 'donations_custom_payment_amount_field_bg_color', '#ffffff' ),
            'description_text' => get_option( 'donations_custom_payment_description_text', '' ),
            'show_description' => get_option( 'donations_custom_payment_show_description', 'no' ),
            'form_title_color' => get_option( 'donations_custom_payment_form_title_color', '#333333' ),
            'field_label_color' => get_option( 'donations_custom_payment_field_label_color', '#333333' ),
            'input_text_color' => get_option( 'donations_custom_payment_input_text_color', '#333333' ),
            'button_text_color' => get_option( 'donations_custom_payment_button_text_color', '#ffffff' ),
            'description_bg_color' => get_option( 'donations_custom_payment_description_bg_color', '#f8f9fa' ),
            'description_text_color' => get_option( 'donations_custom_payment_description_text_color', '#495057' ),
            'preset_amounts_label' => get_option( 'donations_custom_payment_preset_amounts_label', __( '⚡ Suggested Amounts:', 'donations-custom-payment' ) ),
            'preset_btn_bg_color' => get_option( 'donations_custom_payment_preset_btn_bg_color', '#f9f9f9' ),
            'preset_btn_text_color' => get_option( 'donations_custom_payment_preset_btn_text_color', '#333333' ),
            'amount_info_text_color' => get_option( 'donations_custom_payment_amount_info_text_color', '#666666' ),
            'amount_info_min_text' => get_option( 'donations_custom_payment_amount_info_min_text', __( 'Minimum:', 'donations-custom-payment' ) ),
            'amount_info_max_text' => get_option( 'donations_custom_payment_amount_info_max_text', __( 'Maximum:', 'donations-custom-payment' ) ),
            'currency_info' => $currency_info,
            // Alpha values
            'button_color_alpha' => get_option( 'donations_custom_payment_button_color_alpha', 1.0 ),
            'form_bg_color_alpha' => get_option( 'donations_custom_payment_form_bg_color_alpha', 1.0 ),
            'amount_field_bg_color_alpha' => get_option( 'donations_custom_payment_amount_field_bg_color_alpha', 1.0 ),
            'description_bg_color_alpha' => get_option( 'donations_custom_payment_description_bg_color_alpha', 1.0 ),
            'form_title_color_alpha' => get_option( 'donations_custom_payment_form_title_color_alpha', 1.0 ),
            'field_label_color_alpha' => get_option( 'donations_custom_payment_field_label_color_alpha', 1.0 ),
            'input_text_color_alpha' => get_option( 'donations_custom_payment_input_text_color_alpha', 1.0 ),
            'button_text_color_alpha' => get_option( 'donations_custom_payment_button_text_color_alpha', 1.0 ),
            'description_text_color_alpha' => get_option( 'donations_custom_payment_description_text_color_alpha', 1.0 ),
            'preset_btn_bg_color_alpha' => get_option( 'donations_custom_payment_preset_btn_bg_color_alpha', 1.0 ),
            'preset_btn_text_color_alpha' => get_option( 'donations_custom_payment_preset_btn_text_color_alpha', 1.0 ),
            'amount_info_text_color_alpha' => get_option( 'donations_custom_payment_amount_info_text_color_alpha', 1.0 )
        );
    }
    
}
