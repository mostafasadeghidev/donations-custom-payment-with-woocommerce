<?php
/**
 * Assets management class for Custom Payment Form plugin CSS and JavaScript files
 * 
 * @package Donations_Custom_Payment
 * @version 6.2.0
 * @author Mostafa Sadeghi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Donations_Custom_Payment_Assets {
    
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
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Load frontend assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        
        // Load admin assets
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }
    
    /**
     * Load frontend assets
     */
    public function enqueue_frontend_assets() {
        // Load frontend assets
        if ( ! is_admin() ) {
            wp_enqueue_style( 
                'cpf-front-style', 
                DONATIONS_CUSTOM_PAYMENT_PLUGIN_URL . 'assets/donations-custom-payment-front-style.css', 
                array(), 
                '6.2.0' 
            );
            
            wp_enqueue_script( 
                'cpf-front-script', 
                DONATIONS_CUSTOM_PAYMENT_PLUGIN_URL . 'assets/donations-custom-payment-front-script.js', 
                array(), 
                '6.2.0',
                true 
            );
            
            // Localize script for translations
            $currency_info = Donations_Custom_Payment_Helpers::get_currency_info();
            wp_localize_script( 'cpf-front-script', 'cpfStrings', array(
                // translators: %1$s is minimum amount, %2$s is maximum amount, %3$s is currency name
                'invalidAmount' => __( 'Please enter a valid amount between %1$s and %2$s %3$s.', 'donations-custom-payment' ),
                'currencyName' => $currency_info['name']
            ) );
        }
    }
    
    /**
     * Load admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        // Only on plugin settings page
        if ( $hook !== 'settings_page_donations-custom-payment-settings' ) {
            return;
        }
        
        wp_enqueue_style( 
            'cpf-admin-style', 
            DONATIONS_CUSTOM_PAYMENT_PLUGIN_URL . 'assets/donations-custom-payment-admin-style.css', 
            array(), 
            '6.2.0' 
        );
        
        // Load media uploader
        wp_enqueue_media();
        
        wp_enqueue_script( 
            'cpf-admin-script', 
            DONATIONS_CUSTOM_PAYMENT_PLUGIN_URL . 'assets/donations-custom-payment-admin-script.js', 
            array( 'jquery', 'media-upload', 'thickbox', 'media-views' ), 
            '6.2.0', 
            true 
        );
        
        wp_enqueue_script( 
            'cpf-simple-color-fix-script', 
            DONATIONS_CUSTOM_PAYMENT_PLUGIN_URL . 'assets/donations-custom-payment-simple-color-fix.js', 
            array(), 
            '6.2.0',
            true 
        );
        
        // Localize script for color labels
        wp_localize_script( 'cpf-simple-color-fix-script', 'cpfColorStrings', array(
            'alphaLabel' => __( 'Transparency:', 'donations-custom-payment' )
        ) );
    }
}
