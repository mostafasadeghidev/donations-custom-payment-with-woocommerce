<?php
/**
 * Main plugin class for Donations & Custom Payment
 * 
 * @package Donations_Custom_Payment
 * @version 6.2.1
 * @author Mostafa Sadeghi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Donations_Custom_Payment_Core {
    
    /**
     * Plugin version
     */
    const VERSION = '6.2.1';
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Define paths
        define( 'DONATIONS_CUSTOM_PAYMENT_PLUGIN_URL', plugin_dir_url( dirname( __FILE__ ) ) );
        define( 'DONATIONS_CUSTOM_PAYMENT_PLUGIN_PATH', plugin_dir_path( dirname( __FILE__ ) ) );
        
        // Load dependencies
        add_action( 'plugins_loaded', array( $this, 'load_dependencies' ) );
        
        // Load text domain
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        
        // Declare HPOS compatibility
        add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
    }
    
    /**
     * Load required files
     */
    public function load_dependencies() {
        // Load helper classes
        require_once DONATIONS_CUSTOM_PAYMENT_PLUGIN_PATH . 'includes/class-donations-custom-payment-helpers.php';
        require_once DONATIONS_CUSTOM_PAYMENT_PLUGIN_PATH . 'includes/class-donations-custom-payment-form.php';
        require_once DONATIONS_CUSTOM_PAYMENT_PLUGIN_PATH . 'includes/class-donations-custom-payment-admin.php';
        require_once DONATIONS_CUSTOM_PAYMENT_PLUGIN_PATH . 'includes/class-donations-custom-payment-assets.php';
        require_once DONATIONS_CUSTOM_PAYMENT_PLUGIN_PATH . 'includes/class-donations-custom-payment-reports.php';
        
        // Initialize classes
        Donations_Custom_Payment_Form::get_instance();
        Donations_Custom_Payment_Admin::get_instance();
        Donations_Custom_Payment_Assets::get_instance();
        Donations_Custom_Payment_Reports::get_instance();
    }
    
    /**
     * Load text domain
     * Note: WordPress.org automatically loads translations since version 4.6
     */
    public function load_textdomain() {
        // WordPress.org automatically loads translations for plugins
        // No manual loading needed since WordPress 4.6
    }
    
    /**
     * Declare HPOS compatibility
     */
    public function declare_hpos_compatibility() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', dirname( dirname( __FILE__ ) ), true );
        }
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        // Default settings
        if ( ! get_option( 'donations_custom_payment_min_amount' ) ) {
            update_option( 'donations_custom_payment_min_amount', 1000 );
        }
        if ( ! get_option( 'donations_custom_payment_max_amount' ) ) {
            update_option( 'donations_custom_payment_max_amount', 10000000 );
        }
        if ( ! get_option( 'donations_custom_payment_product_name' ) ) {
            update_option( 'donations_custom_payment_product_name', __( 'Custom Payment', 'donations-custom-payment' ) );
        }
        if ( ! get_option( 'donations_custom_payment_preset_amounts' ) ) {
            update_option( 'donations_custom_payment_preset_amounts', '50000,100000,200000,500000' );
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize main class
Donations_Custom_Payment_Core::get_instance();

// Activation and deactivation hooks
register_activation_hook( dirname( dirname( __FILE__ ) ) . '/donations-custom-payment.php', array( 'Donations_Custom_Payment_Core', 'activate' ) );
register_deactivation_hook( dirname( dirname( __FILE__ ) ) . '/donations-custom-payment.php', array( 'Donations_Custom_Payment_Core', 'deactivate' ) );
