<?php
/**
 * Admin panel class for Custom Payment Form plugin
 * 
 * @package Donations_Custom_Payment
 * @version 6.2.0
 * @author Mostafa Sadeghi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Donations_Custom_Payment_Admin {
    
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
        // Add settings menu
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        
        // Save settings
        add_action( 'admin_init', array( $this, 'save_settings' ) );
        
        // Add configure button on plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( dirname( dirname( __FILE__ ) ) . '/donations-custom-payment.php' ), array( $this, 'add_plugin_action_links' ) );

    }
    
    /**
     * Add settings menu to admin panel
     */
    public function admin_menu() {
        add_options_page(
            __( 'Custom Payment Form Settings', 'donations-custom-payment' ),
            __( 'Custom Payment Form', 'donations-custom-payment' ), 
            'manage_options',
            'donations-custom-payment-settings',
            array( $this, 'settings_page' )
        );
    }
    
    /**
     * Save settings
     */
    public function save_settings() {
        if ( isset( $_POST['donations_custom_payment_save_settings'] ) && isset( $_POST['donations_custom_payment_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_settings_nonce'] ) ), 'donations_custom_payment_save_settings_action' ) ) {
            $this->process_settings_save();
        }
    }
    
    /**
     * Process save settings
     */
    private function process_settings_save() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verification is done in save_settings() method
        $min_amount = isset( $_POST['donations_custom_payment_min_amount'] ) ? absint( wp_unslash( $_POST['donations_custom_payment_min_amount'] ) ) : 0;
        $max_amount = isset( $_POST['donations_custom_payment_max_amount'] ) ? absint( wp_unslash( $_POST['donations_custom_payment_max_amount'] ) ) : 0;
        $product_name = isset( $_POST['donations_custom_payment_product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_product_name'] ) ) : '';
        $preset_amounts = isset( $_POST['donations_custom_payment_preset_amounts'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_preset_amounts'] ) ) : '';
        
        // New appearance settings
        $form_title = isset( $_POST['donations_custom_payment_form_title'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_form_title'] ) ) : '';
        $show_form_title = isset( $_POST['donations_custom_payment_show_form_title'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_show_form_title'] ) ) : '';
        $button_text = isset( $_POST['donations_custom_payment_button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_button_text'] ) ) : '';
        $button_color = isset( $_POST['donations_custom_payment_button_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_button_color'] ) ) : '';
        $form_bg_color = isset( $_POST['donations_custom_payment_form_bg_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_form_bg_color'] ) ) : '';
        $show_amount_info = isset( $_POST['donations_custom_payment_show_amount_info'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_show_amount_info'] ) ) : '';
        $amount_field_label = isset( $_POST['donations_custom_payment_amount_field_label'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_amount_field_label'] ) ) : '';
        $amount_field_bg_color = isset( $_POST['donations_custom_payment_amount_field_bg_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_amount_field_bg_color'] ) ) : '';
        $description_text = isset( $_POST['donations_custom_payment_description_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['donations_custom_payment_description_text'] ) ) : '';
        $show_description = isset( $_POST['donations_custom_payment_show_description'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_show_description'] ) ) : '';
        
        // New text color controls
        $form_title_color = isset( $_POST['donations_custom_payment_form_title_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_form_title_color'] ) ) : '';
        $field_label_color = isset( $_POST['donations_custom_payment_field_label_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_field_label_color'] ) ) : '';
        $input_text_color = isset( $_POST['donations_custom_payment_input_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_input_text_color'] ) ) : '';
        $button_text_color = isset( $_POST['donations_custom_payment_button_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_button_text_color'] ) ) : '';
        $description_bg_color = isset( $_POST['donations_custom_payment_description_bg_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_description_bg_color'] ) ) : '';
        $description_text_color = isset( $_POST['donations_custom_payment_description_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_description_text_color'] ) ) : '';
        
        // Save Alpha values
        $button_color_alpha = isset($_POST['donations_custom_payment_button_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_button_color_alpha'] ) ) : 1.0;
        $form_bg_color_alpha = isset($_POST['donations_custom_payment_form_bg_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_form_bg_color_alpha'] ) ) : 1.0;
        $amount_field_bg_color_alpha = isset($_POST['donations_custom_payment_amount_field_bg_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_amount_field_bg_color_alpha'] ) ) : 1.0;
        $description_bg_color_alpha = isset($_POST['donations_custom_payment_description_bg_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_description_bg_color_alpha'] ) ) : 1.0;
        $form_title_color_alpha = isset($_POST['donations_custom_payment_form_title_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_form_title_color_alpha'] ) ) : 1.0;
        $field_label_color_alpha = isset($_POST['donations_custom_payment_field_label_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_field_label_color_alpha'] ) ) : 1.0;
        $input_text_color_alpha = isset($_POST['donations_custom_payment_input_text_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_input_text_color_alpha'] ) ) : 1.0;
        $button_text_color_alpha = isset($_POST['donations_custom_payment_button_text_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_button_text_color_alpha'] ) ) : 1.0;
        $description_text_color_alpha = isset($_POST['donations_custom_payment_description_text_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_description_text_color_alpha'] ) ) : 1.0;
        $preset_btn_bg_color_alpha = isset($_POST['donations_custom_payment_preset_btn_bg_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_preset_btn_bg_color_alpha'] ) ) : 1.0;
        $preset_btn_text_color_alpha = isset($_POST['donations_custom_payment_preset_btn_text_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_preset_btn_text_color_alpha'] ) ) : 1.0;
        $amount_info_text_color_alpha = isset($_POST['donations_custom_payment_amount_info_text_color_alpha']) ? floatval( wp_unslash( $_POST['donations_custom_payment_amount_info_text_color_alpha'] ) ) : 1.0;
        
        // New settings
        $preset_amounts_label = isset( $_POST['donations_custom_payment_preset_amounts_label'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_preset_amounts_label'] ) ) : '';
        $preset_btn_bg_color = isset( $_POST['donations_custom_payment_preset_btn_bg_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_preset_btn_bg_color'] ) ) : '';
        $preset_btn_text_color = isset( $_POST['donations_custom_payment_preset_btn_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_preset_btn_text_color'] ) ) : '';
        $amount_info_text_color = isset( $_POST['donations_custom_payment_amount_info_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['donations_custom_payment_amount_info_text_color'] ) ) : '';
        $amount_info_min_text = isset( $_POST['donations_custom_payment_amount_info_min_text'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_amount_info_min_text'] ) ) : '';
        $amount_info_max_text = isset( $_POST['donations_custom_payment_amount_info_max_text'] ) ? sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_amount_info_max_text'] ) ) : '';
        
        // New product settings
        $product_description = isset( $_POST['donations_custom_payment_product_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['donations_custom_payment_product_description'] ) ) : '';
        $product_image_id = isset( $_POST['donations_custom_payment_product_image_id'] ) ? absint( wp_unslash( $_POST['donations_custom_payment_product_image_id'] ) ) : 0;
        
        // Validation
        if ( $min_amount < 100 ) $min_amount = 1000;
        if ( $max_amount < $min_amount ) $max_amount = $min_amount * 100;
        if ( empty( $product_name ) ) $product_name = __( 'Custom Payment', 'donations-custom-payment' );
        if ( empty( $form_title ) ) $form_title = 'Payment Form';
        if ( empty( $button_text ) ) $button_text = 'Continue Payment';
        if ( empty( $button_color ) ) $button_color = '#007cba';
        if ( empty( $form_bg_color ) ) $form_bg_color = '#ffffff';
        if ( empty( $amount_field_label ) ) $amount_field_label = 'Amount (in Tomans)';
        if ( empty( $amount_field_bg_color ) ) $amount_field_bg_color = '#ffffff';
        if ( empty( $form_title_color ) ) $form_title_color = '#333333';
        if ( empty( $field_label_color ) ) $field_label_color = '#333333';
        if ( empty( $input_text_color ) ) $input_text_color = '#333333';
        if ( empty( $button_text_color ) ) $button_text_color = '#ffffff';
        if ( empty( $description_bg_color ) ) $description_bg_color = '#f8f9fa';
        if ( empty( $description_text_color ) ) $description_text_color = '#495057';
        if ( empty( $preset_amounts_label ) ) $preset_amounts_label = '⚡ Suggested Amounts:';
        if ( empty( $preset_btn_bg_color ) ) $preset_btn_bg_color = '#f9f9f9';
        if ( empty( $preset_btn_text_color ) ) $preset_btn_text_color = '#333333';
        if ( empty( $amount_info_text_color ) ) $amount_info_text_color = '#666666';
        if ( empty( $amount_info_min_text ) ) $amount_info_min_text = 'Minimum:';
        if ( empty( $amount_info_max_text ) ) $amount_info_max_text = 'Maximum:';
        
        // Save settings
        update_option( 'donations_custom_payment_min_amount', $min_amount );
        update_option( 'donations_custom_payment_max_amount', $max_amount );
        update_option( 'donations_custom_payment_product_name', $product_name );
        update_option( 'donations_custom_payment_preset_amounts', $preset_amounts );
        update_option( 'donations_custom_payment_form_title', $form_title );
        update_option( 'donations_custom_payment_show_form_title', $show_form_title );
        update_option( 'donations_custom_payment_button_text', $button_text );
        update_option( 'donations_custom_payment_button_color', $button_color );
        update_option( 'donations_custom_payment_form_bg_color', $form_bg_color );
        update_option( 'donations_custom_payment_show_amount_info', $show_amount_info );
        update_option( 'donations_custom_payment_amount_field_label', $amount_field_label );
        update_option( 'donations_custom_payment_amount_field_bg_color', $amount_field_bg_color );
        update_option( 'donations_custom_payment_description_text', $description_text );
        update_option( 'donations_custom_payment_show_description', $show_description );
        update_option( 'donations_custom_payment_form_title_color', $form_title_color );
        update_option( 'donations_custom_payment_field_label_color', $field_label_color );
        update_option( 'donations_custom_payment_input_text_color', $input_text_color );
        update_option( 'donations_custom_payment_button_text_color', $button_text_color );
        update_option( 'donations_custom_payment_description_bg_color', $description_bg_color );
        update_option( 'donations_custom_payment_description_text_color', $description_text_color );
        update_option( 'donations_custom_payment_preset_amounts_label', $preset_amounts_label );
        update_option( 'donations_custom_payment_preset_btn_bg_color', $preset_btn_bg_color );
        update_option( 'donations_custom_payment_preset_btn_text_color', $preset_btn_text_color );
        update_option( 'donations_custom_payment_amount_info_text_color', $amount_info_text_color );
        update_option( 'donations_custom_payment_amount_info_min_text', $amount_info_min_text );
        update_option( 'donations_custom_payment_amount_info_max_text', $amount_info_max_text );
        
        // Save Alpha values
        update_option( 'donations_custom_payment_button_color_alpha', $button_color_alpha );
        update_option( 'donations_custom_payment_form_bg_color_alpha', $form_bg_color_alpha );
        update_option( 'donations_custom_payment_amount_field_bg_color_alpha', $amount_field_bg_color_alpha );
        update_option( 'donations_custom_payment_description_bg_color_alpha', $description_bg_color_alpha );
        update_option( 'donations_custom_payment_form_title_color_alpha', $form_title_color_alpha );
        update_option( 'donations_custom_payment_field_label_color_alpha', $field_label_color_alpha );
        update_option( 'donations_custom_payment_input_text_color_alpha', $input_text_color_alpha );
        update_option( 'donations_custom_payment_button_text_color_alpha', $button_text_color_alpha );
        update_option( 'donations_custom_payment_description_text_color_alpha', $description_text_color_alpha );
        update_option( 'donations_custom_payment_preset_btn_bg_color_alpha', $preset_btn_bg_color_alpha );
        update_option( 'donations_custom_payment_preset_btn_text_color_alpha', $preset_btn_text_color_alpha );
        update_option( 'donations_custom_payment_amount_info_text_color_alpha', $amount_info_text_color_alpha );
        
        // Save new product settings
        update_option( 'donations_custom_payment_product_description', $product_description );
        update_option( 'donations_custom_payment_product_image_id', $product_image_id );
        
        // Update product in WooCommerce
        $this->update_product_in_woocommerce( $product_name, $product_description, $product_image_id );
        
        echo '<div class="notice notice-success"><p>' . esc_html( __( 'Settings saved successfully!', 'donations-custom-payment' ) ) . '</p></div>';
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        // Get current currency information
        $currency_info = Donations_Custom_Payment_Helpers::get_currency_info();
        
        // Get current settings
        $settings = Donations_Custom_Payment_Helpers::get_form_settings();
        ?>
        
        <div class="wrap">
            <h1><?php echo esc_html( __( '⚙️ Custom Payment Form Settings', 'donations-custom-payment' ) ); ?></h1>
            
            <div class="cpf-admin-container">
                
                <!-- General Stats -->
                <div class="cpf-stats">
                    <div class="cpf-stat-card">
                        <div class="cpf-stat-number"><?php echo number_format($settings['min_amount']); ?></div>
                        <div class="cpf-stat-label"><?php 
                        // translators: %s is the currency name
                        echo sprintf( esc_html( __( 'Minimum Amount (%s)', 'donations-custom-payment' ) ), esc_html( $currency_info['name'] ) ); ?></div>
                    </div>
                    <div class="cpf-stat-card">
                        <div class="cpf-stat-number"><?php echo number_format($settings['max_amount']); ?></div>
                        <div class="cpf-stat-label"><?php 
                        // translators: %s is the currency name
                        echo sprintf( esc_html( __( 'Maximum Amount (%s)', 'donations-custom-payment' ) ), esc_html( $currency_info['name'] ) ); ?></div>
                    </div>
                    <div class="cpf-stat-card">
                        <div class="cpf-stat-number"><?php echo count(array_filter(explode(',', $settings['preset_amounts']))); ?></div>
                        <div class="cpf-stat-label"><?php echo esc_html( __( 'Suggested Options', 'donations-custom-payment' ) ); ?></div>
                    </div>
                    <div class="cpf-stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="cpf-stat-number"><?php echo esc_html( $currency_info['symbol'] ); ?></div>
                        <div class="cpf-stat-label"><?php 
                        // translators: %s is the currency name
                        echo sprintf( esc_html( __( 'Current Currency: %s', 'donations-custom-payment' ) ), esc_html( $currency_info['name'] ) ); ?></div>
                    </div>
                </div>
                
                <!-- Usage Guide -->
                <div class="cpf-shortcode-box">
                    <h3><?php echo esc_html( __( '📝 How to Use:', 'donations-custom-payment' ) ); ?></h3>
                    <p><?php echo esc_html( __( 'To display the payment form on any page or post, use the shortcode below:', 'donations-custom-payment' ) ); ?></p>
                    <p><code>[custom_payment_form]</code></p>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #28a745;">
                        <h4 style="margin: 0 0 10px 0; color: #28a745;"><?php echo esc_html( __( '🔗 Support for links containing amounts', 'donations-custom-payment' ) ); ?></h4>
                        <p style="margin: 0 0 10px 0; font-size: 14px;">
                            <?php echo esc_html( __( 'This plugin supports links containing amounts. Example:', 'donations-custom-payment' ) ); ?>
                        </p>
                        <p style="margin: 0 0 10px 0;">
                            <code><?php echo esc_url( home_url( '/pay/?55000' ) ); ?></code>
                        </p>
                        <p style="margin: 0 0 10px 0; font-size: 14px;">
                            <?php echo esc_html( __( 'In this example, the amount 55000 is automatically placed in the amount field.', 'donations-custom-payment' ) ); ?>
                        </p>
                        <p style="margin: 0; font-size: 13px; color: #666;">
                            <strong><?php echo esc_html( __( 'Note:', 'donations-custom-payment' ) ); ?></strong> 
                            <?php echo esc_html( __( 'To use this feature, create a page or post with the name "pay" or any name you want, and place the shortcode in it.', 'donations-custom-payment' ) ); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Navigation Tabs -->
                <div class="cpf-tabs">
                    <ul class="cpf-tab-nav">
                        <li><a href="#tab-general" class="cpf-tab-link active" data-tab="general"><?php echo esc_html( __( '⚙️ General Settings', 'donations-custom-payment' ) ); ?></a></li>
                        <li><a href="#tab-content" class="cpf-tab-link" data-tab="content"><?php echo esc_html( __( '📝 Texts and Titles', 'donations-custom-payment' ) ); ?></a></li>
                        <li><a href="#tab-design" class="cpf-tab-link" data-tab="design"><?php echo esc_html( __( '🎨 Colors and Design', 'donations-custom-payment' ) ); ?></a></li>
                        <li><a href="#tab-reports" class="cpf-tab-link" data-tab="reports"><?php echo esc_html( __( '📊 Reports', 'donations-custom-payment' ) ); ?></a></li>
                    </ul>
                </div>
                
                <form id="cpf-settings-form" method="post" action="">
                    <?php wp_nonce_field( 'donations_custom_payment_save_settings_action', 'donations_custom_payment_settings_nonce' ); ?>
                    
                            <script>
            var donations_custom_payment_ajax = {
                nonce: '<?php echo esc_js( wp_create_nonce( 'donations_custom_payment_ajax_nonce' ) ); ?>'
            };
            var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
        </script>
                    
                    <!-- General Settings Tab -->
                    <div id="tab-general" class="cpf-tab-content active">
                        <?php $this->render_general_settings_tab($settings, $currency_info); ?>
                    </div>
                    
                    <!-- Texts and Titles Tab -->
                    <div id="tab-content" class="cpf-tab-content">
                        <?php $this->render_content_settings_tab($settings); ?>
                    </div>
                    
                    <!-- Colors and Design Tab -->
                    <div id="tab-design" class="cpf-tab-content">
                        <?php $this->render_design_settings_tab($settings); ?>
                    </div>
                    
                    <?php submit_button( __( 'Save Settings', 'donations-custom-payment' ), 'primary', 'donations_custom_payment_save_settings' ); ?>
                </form>
                
                <!-- Reports Tab (Outside Form) -->
                <div id="tab-reports" class="cpf-tab-content">
                    <?php 
                    // Load reports class
                    if ( class_exists( 'Donations_Custom_Payment_Reports' ) ) {
                        Donations_Custom_Payment_Reports::render_reports_tab();
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <?php
    }
    
    /**
     * Render general settings tab
     */
    private function render_general_settings_tab($settings, $currency_info) {
        ?>
        <!-- Amount Settings -->
        <div class="cpf-admin-section">
            <h3><?php echo esc_html( __( '💰 Amount Settings', 'donations-custom-payment' ) ); ?></h3>
            
            <div style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #007cba;">
                <h4 style="margin: 0 0 8px 0; color: #007cba;"><?php echo esc_html( __( '💱 Current Currency Information', 'donations-custom-payment' ) ); ?></h4>
                <p style="margin: 0; font-size: 13px; color: #555;">
                    <strong><?php echo esc_html( __( 'Currency:', 'donations-custom-payment' ) ); ?></strong> <?php echo esc_html( $currency_info['name'] ); ?> (<?php echo esc_html( $currency_info['code'] ); ?>) | 
                    <strong><?php echo esc_html( __( 'Symbol:', 'donations-custom-payment' ) ); ?></strong> <?php echo esc_html( $currency_info['symbol'] ); ?> | 
                    <strong><?php echo esc_html( __( 'Position:', 'donations-custom-payment' ) ); ?></strong> <?php echo $currency_info['position'] === 'right' ? esc_html( __( 'Right', 'donations-custom-payment' ) ) : esc_html( __( 'Left', 'donations-custom-payment' ) ); ?>
                    <br><small><?php echo esc_html( __( 'These settings are read from WooCommerce. For changes, refer to WooCommerce settings.', 'donations-custom-payment' ) ); ?></small>
                </p>
            </div>
            
            <p>
                <label for="donations_custom_payment_min_amount" class="cpf-admin-label"><?php 
                // translators: %s is the currency name
                echo sprintf( esc_html( __( 'Minimum Amount (%s):', 'donations-custom-payment' ) ), esc_html( $currency_info['name'] ) ); ?></label>
                <input type="number" id="donations_custom_payment_min_amount" name="donations_custom_payment_min_amount" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['min_amount']); ?>" 
                       min="100" required>
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Minimum payment amount by users', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_max_amount" class="cpf-admin-label"><?php 
                // translators: %s is the currency name
                echo sprintf( esc_html( __( 'Maximum Amount (%s):', 'donations-custom-payment' ) ), esc_html( $currency_info['name'] ) ); ?></label>
                <input type="number" id="donations_custom_payment_max_amount" name="donations_custom_payment_max_amount" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['max_amount']); ?>" 
                       min="1000" required>
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Maximum payment amount by users', 'donations-custom-payment' ) ); ?></div>
            </p>
        </div>
        
        <!-- Product Settings -->
        <div class="cpf-admin-section">
            <h3><?php echo esc_html( __( '🛒 Product Settings', 'donations-custom-payment' ) ); ?></h3>
            
            <?php
            // Get product information from WooCommerce
            $product_info = $this->get_product_info();
            ?>
            
            <div style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #007cba;">
                <h4 style="margin: 0 0 8px 0; color: #007cba;"><?php echo esc_html( __( '📋 Current Product Information', 'donations-custom-payment' ) ); ?></h4>
                <p style="margin: 0; font-size: 13px; color: #555;">
                    <strong><?php echo esc_html( __( 'Status:', 'donations-custom-payment' ) ); ?></strong> 
                    <?php if ($product_info['exists']): ?>
                        <span style="color: #28a745;">✅ <?php echo esc_html( __( 'Product exists', 'donations-custom-payment' ) ); ?></span>
                    <?php else: ?>
                        <span style="color: #dc3545;">❌ <?php echo esc_html( __( 'Product does not exist', 'donations-custom-payment' ) ); ?></span>
                    <?php endif; ?>
                    <br>
                    <?php if ($product_info['exists']): ?>
                        <strong><?php echo esc_html( __( 'SKU:', 'donations-custom-payment' ) ); ?></strong> <?php echo esc_html($product_info['sku']); ?> |
                        <strong><?php echo esc_html( __( 'Last Updated:', 'donations-custom-payment' ) ); ?></strong> <?php echo esc_html($product_info['last_modified']); ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <p>
                <label for="donations_custom_payment_product_name" class="cpf-admin-label"><?php echo esc_html( __( 'Product Name:', 'donations-custom-payment' ) ); ?></label>
                <input type="text" id="donations_custom_payment_product_name" name="donations_custom_payment_product_name" 
                       class="cpf-admin-input" value="<?php echo esc_attr($product_info['name']); ?>" 
                       required>
                <div class="cpf-admin-help">
                    <?php echo esc_html( __( 'Name displayed in cart and invoice', 'donations-custom-payment' ) ); ?>
                    <?php if ($product_info['exists']): ?>
                        <br><small style="color: #007cba;"><?php echo esc_html( __( 'This name is read from the existing product in WooCommerce.', 'donations-custom-payment' ) ); ?></small>
                    <?php endif; ?>
                </div>
            </p>
            
            <p>
                <label for="donations_custom_payment_product_description" class="cpf-admin-label"><?php echo esc_html( __( 'Short Description:', 'donations-custom-payment' ) ); ?></label>
                <textarea id="donations_custom_payment_product_description" name="donations_custom_payment_product_description" 
                          class="cpf-admin-textarea" rows="3"><?php echo esc_textarea($product_info['description']); ?></textarea>
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Short product description displayed in cart', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_product_image" class="cpf-admin-label"><?php echo esc_html( __( 'Product Image:', 'donations-custom-payment' ) ); ?></label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="hidden" id="donations_custom_payment_product_image_id" name="donations_custom_payment_product_image_id" value="<?php echo esc_attr($product_info['image_id']); ?>">
                    <div id="donations_custom_payment_product_image_preview" style="width: 80px; height: 80px; border: 2px dashed #ddd; display: flex; align-items: center; justify-content: center; background: #f9f9f9;">
                        <?php if ($product_info['image_url']): ?>
                            <img src="<?php echo esc_url($product_info['image_url']); ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span style="color: #999; font-size: 12px;"><?php echo esc_html( __( 'No Image', 'donations-custom-payment' ) ); ?></span>
                        <?php endif; ?>
                    </div>
                    <button type="button" id="donations_custom_payment_select_image" class="button"><?php echo esc_html( __( 'Select Image', 'donations-custom-payment' ) ); ?></button>
                    <button type="button" id="donations_custom_payment_remove_image" class="button" <?php echo $product_info['image_url'] ? '' : 'style="display:none;"'; ?>><?php echo esc_html( __( 'Remove Image', 'donations-custom-payment' ) ); ?></button>
                </div>
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Product image displayed in cart and invoice', 'donations-custom-payment' ) ); ?></div>
            </p>
            

        </div>
        
        <!-- Suggested Amounts -->
        <div class="cpf-admin-section">
            <h3><?php echo esc_html( __( '⚡ Suggested Amounts', 'donations-custom-payment' ) ); ?></h3>
            
            <p>
                <label for="donations_custom_payment_preset_amounts" class="cpf-admin-label"><?php echo esc_html( __( 'Suggested Amounts:', 'donations-custom-payment' ) ); ?></label>
                <textarea id="donations_custom_payment_preset_amounts" name="donations_custom_payment_preset_amounts" 
                          class="cpf-admin-textarea" rows="3"><?php echo esc_textarea($settings['preset_amounts']); ?></textarea>
                <div class="cpf-admin-help">
                    <?php 
                    // translators: %s is the example amounts
                    echo sprintf( esc_html( __( 'Separate suggested amounts with commas. Example: %s', 'donations-custom-payment' ) ), '50000,100000,200000,500000' ); ?><br>
                    <?php 
                    // translators: %s is the currency name
                    echo sprintf( esc_html( __( 'These amounts will be displayed in your current currency (%s).', 'donations-custom-payment' ) ), esc_html( $currency_info['name'] ) ); ?><br>
                    <?php echo esc_html( __( 'To disable this section, leave the content empty.', 'donations-custom-payment' ) ); ?>
                </div>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render text settings tab
     */
    private function render_content_settings_tab($settings) {
        ?>
        <!-- Text Settings -->
        <div class="cpf-admin-section">
            <h3><?php echo esc_html( __( '📝 Text and Title Settings', 'donations-custom-payment' ) ); ?></h3>
            
            <p>
                <label for="donations_custom_payment_form_title" class="cpf-admin-label"><?php echo esc_html( __( 'Form Title:', 'donations-custom-payment' ) ); ?></label>
                <input type="text" id="donations_custom_payment_form_title" name="donations_custom_payment_form_title" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['form_title']); ?>">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Title displayed at the top of the form (you can use emojis)', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_show_form_title" class="cpf-admin-label"><?php echo esc_html( __( 'Show Form Title:', 'donations-custom-payment' ) ); ?></label>
                <select id="donations_custom_payment_show_form_title" name="donations_custom_payment_show_form_title" class="cpf-admin-input">
                    <option value="yes" <?php selected($settings['show_form_title'], 'yes'); ?>><?php echo esc_html( __( 'Show', 'donations-custom-payment' ) ); ?></option>
                    <option value="no" <?php selected($settings['show_form_title'], 'no'); ?>><?php echo esc_html( __( 'Hide', 'donations-custom-payment' ) ); ?></option>
                </select>
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Whether to show the form title or not', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_button_text" class="cpf-admin-label"><?php echo esc_html( __( 'Payment Button Text:', 'donations-custom-payment' ) ); ?></label>
                <input type="text" id="donations_custom_payment_button_text" name="donations_custom_payment_button_text" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['button_text']); ?>">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Text displayed on the payment button (you can use emojis)', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_amount_field_label" class="cpf-admin-label"><?php echo esc_html( __( 'Amount Field Label:', 'donations-custom-payment' ) ); ?></label>
                <input type="text" id="donations_custom_payment_amount_field_label" name="donations_custom_payment_amount_field_label" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['amount_field_label']); ?>">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Label for the amount input field (you can use emojis)', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_show_amount_info" class="cpf-admin-label"><?php echo esc_html( __( 'Show Min/Max Information:', 'donations-custom-payment' ) ); ?></label>
                <select id="donations_custom_payment_show_amount_info" name="donations_custom_payment_show_amount_info" class="cpf-admin-input">
                    <option value="yes" <?php selected($settings['show_amount_info'], 'yes'); ?>><?php echo esc_html( __( 'Show', 'donations-custom-payment' ) ); ?></option>
                    <option value="no" <?php selected($settings['show_amount_info'], 'no'); ?>><?php echo esc_html( __( 'Hide', 'donations-custom-payment' ) ); ?></option>
                </select>
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Whether to show minimum and maximum amount information below the input field', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_description_text" class="cpf-admin-label"><?php echo esc_html( __( 'Description Text:', 'donations-custom-payment' ) ); ?></label>
                <textarea id="donations_custom_payment_description_text" name="donations_custom_payment_description_text" 
                          class="cpf-admin-textarea" rows="4" placeholder="<?php echo esc_attr( __( 'Additional description displayed below the amount field...', 'donations-custom-payment' ) ); ?>"><?php echo esc_textarea($settings['description_text']); ?></textarea>
                <div class="cpf-admin-help">
                    <?php echo esc_html( __( 'Description text displayed below the amount field. You can write useful information, guidance, or necessary warnings here.', 'donations-custom-payment' ) ); ?><br>
                    <?php echo esc_html( __( 'Press Enter for new line. Simple HTML is supported.', 'donations-custom-payment' ) ); ?>
                </div>
            </p>
            
            <p>
                <label for="donations_custom_payment_show_description" class="cpf-admin-label"><?php echo esc_html( __( 'Show Description:', 'donations-custom-payment' ) ); ?></label>
                <select id="donations_custom_payment_show_description" name="donations_custom_payment_show_description" class="cpf-admin-input">
                    <option value="no" <?php selected($settings['show_description'], 'no'); ?>><?php echo esc_html( __( 'Hide', 'donations-custom-payment' ) ); ?></option>
                    <option value="yes" <?php selected($settings['show_description'], 'yes'); ?>><?php echo esc_html( __( 'Show', 'donations-custom-payment' ) ); ?></option>
                </select>
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Whether to show description text in the form or not', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_preset_amounts_label" class="cpf-admin-label"><?php echo esc_html( __( 'Suggested Amounts Text:', 'donations-custom-payment' ) ); ?></label>
                <input type="text" id="donations_custom_payment_preset_amounts_label" name="donations_custom_payment_preset_amounts_label" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['preset_amounts_label']); ?>">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Text displayed above the suggested amount buttons', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_amount_info_min_text" class="cpf-admin-label"><?php echo esc_html( __( 'Minimum Text:', 'donations-custom-payment' ) ); ?></label>
                <input type="text" id="donations_custom_payment_amount_info_min_text" name="donations_custom_payment_amount_info_min_text" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['amount_info_min_text']); ?>">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Text displayed before the minimum amount', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_amount_info_max_text" class="cpf-admin-label"><?php echo esc_html( __( 'Maximum Text:', 'donations-custom-payment' ) ); ?></label>
                <input type="text" id="donations_custom_payment_amount_info_max_text" name="donations_custom_payment_amount_info_max_text" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['amount_info_max_text']); ?>">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Text displayed before the maximum amount', 'donations-custom-payment' ) ); ?></div>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render design settings tab
     */
    private function render_design_settings_tab($settings) {
        ?>
        <!-- Background Color Controls -->
        <div class="cpf-admin-section">
            <h3><?php echo esc_html( __( '🎨 Background Colors', 'donations-custom-payment' ) ); ?></h3>
            
            <p>
                <label for="donations_custom_payment_form_bg_color" class="cpf-admin-label"><?php echo esc_html( __( 'Form Background Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_form_bg_color" name="donations_custom_payment_form_bg_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['form_bg_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['form_bg_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Select the background color for the entire form', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_button_color" class="cpf-admin-label"><?php echo esc_html( __( 'Payment Button Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_button_color" name="donations_custom_payment_button_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['button_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['button_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Select the background color for the payment button', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_amount_field_bg_color" class="cpf-admin-label"><?php echo esc_html( __( 'Amount Field Background Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_amount_field_bg_color" name="donations_custom_payment_amount_field_bg_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['amount_field_bg_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['amount_field_bg_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Select the background color for the amount input field', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_description_bg_color" class="cpf-admin-label"><?php echo esc_html( __( 'Description Background Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_description_bg_color" name="donations_custom_payment_description_bg_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['description_bg_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['description_bg_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Background color of the description box', 'donations-custom-payment' ) ); ?></div>
            </p>
        </div>
        
        <!-- Text Color Controls -->
        <div class="cpf-admin-section">
            <h3><?php echo esc_html( __( '✨ Text Colors', 'donations-custom-payment' ) ); ?></h3>
            
            <p>
                <label for="donations_custom_payment_form_title_color" class="cpf-admin-label"><?php echo esc_html( __( 'Form Title Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_form_title_color" name="donations_custom_payment_form_title_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['form_title_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['form_title_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Color of the main form title text', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_field_label_color" class="cpf-admin-label"><?php echo esc_html( __( 'Amount Field Label Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_field_label_color" name="donations_custom_payment_field_label_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['field_label_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['field_label_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Color of the amount input field label text', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_input_text_color" class="cpf-admin-label"><?php echo esc_html( __( 'Amount Input Text Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_input_text_color" name="donations_custom_payment_input_text_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['input_text_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['input_text_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Color of the text that user enters in the amount field', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_button_text_color" class="cpf-admin-label"><?php echo esc_html( __( 'Payment Button Text Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_button_text_color" name="donations_custom_payment_button_text_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['button_text_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['button_text_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Color of the text on the payment button', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_description_text_color" class="cpf-admin-label"><?php echo esc_html( __( 'Description Text Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_description_text_color" name="donations_custom_payment_description_text_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['description_text_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['description_text_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Color of the text inside the description box', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_preset_btn_bg_color" class="cpf-admin-label"><?php echo esc_html( __( 'Suggested Buttons Background Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_preset_btn_bg_color" name="donations_custom_payment_preset_btn_bg_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['preset_btn_bg_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['preset_btn_bg_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Background color of the suggested amount buttons', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_preset_btn_text_color" class="cpf-admin-label"><?php echo esc_html( __( 'Suggested Buttons Text Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_preset_btn_text_color" name="donations_custom_payment_preset_btn_text_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['preset_btn_text_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['preset_btn_text_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Text color inside the suggested amount buttons', 'donations-custom-payment' ) ); ?></div>
            </p>
            
            <p>
                <label for="donations_custom_payment_amount_info_text_color" class="cpf-admin-label"><?php echo esc_html( __( 'Amount Info Text Color:', 'donations-custom-payment' ) ); ?></label>
                <input type="color" id="donations_custom_payment_amount_info_text_color" name="donations_custom_payment_amount_info_text_color" 
                       class="cpf-admin-input" value="<?php echo esc_attr($settings['amount_info_text_color']); ?>" 
                       data-saved-alpha="<?php echo esc_attr($settings['amount_info_text_color_alpha']); ?>" 
                       style="width: 100px; height: 40px;">
                <div class="cpf-admin-help"><?php echo esc_html( __( 'Color of the text showing minimum and maximum amount', 'donations-custom-payment' ) ); ?></div>
            </p>
        </div>
        <?php
    }
    
    /**
     * Get product information from WooCommerce
     */
    private function get_product_info() {
        $default_info = array(
            'exists' => false,
            'name' => get_option( 'donations_custom_payment_product_name', __( 'Custom Payment', 'donations-custom-payment' ) ),
            'description' => get_option( 'donations_custom_payment_product_description', '' ),
            'image_id' => get_option( 'donations_custom_payment_product_image_id', '' ),
            'image_url' => '',
            'sku' => 'custom-payment-fee',
            'last_modified' => ''
        );
        
        // Search for product by SKU
        $product_id = wc_get_product_id_by_sku( 'custom-payment-fee' );
        
        if ( ! $product_id ) {
            return $default_info;
        }
        
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return $default_info;
        }
        
        // Get product image
        $image_id = $product->get_image_id();
        $image_url = '';
        if ( $image_id ) {
            $image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
        }
        
        return array(
            'exists' => true,
            'name' => $product->get_name(),
            'description' => $product->get_short_description(),
            'image_id' => $image_id,
            'image_url' => $image_url,
            'sku' => $product->get_sku(),
            'last_modified' => $product->get_date_modified() ? $product->get_date_modified()->format( 'Y-m-d H:i:s' ) : ''
        );
    }
    
    /**
     * Update product in WooCommerce
     */
    private function update_product_in_woocommerce( $product_name, $description = '', $image_id = '' ) {
        // Create or get custom-payment-fee category
        $category_id = $this->get_or_create_category();
        
        // Search for product by SKU
        $product_id = wc_get_product_id_by_sku( 'custom-payment-fee' );
        
        if ( ! $product_id ) {
            // Create new product
            $product = new WC_Product_Simple();
            $product->set_name( $product_name );
            $product->set_sku( 'custom-payment-fee' );
            $product->set_virtual( true );
            $product->set_status( 'publish' );
            $product->set_catalog_visibility( 'hidden' );
            $product->set_price( 0 );
            $product->set_regular_price( 0 );
            $product->set_sale_price( '' );
            $product->set_manage_stock( false );
            $product->set_stock_status( 'instock' );
            $product->set_tax_status( 'none' );
            $product->set_tax_class( '' );
            $product->set_meta_data( '_custom_payment_form', 'yes' );
            
            // Set category
            $product->set_category_ids( array( $category_id ) );
        } else {
            // Update existing product
            $product = wc_get_product( $product_id );
            $product->set_name( $product_name );
            
            // Update category
            $product->set_category_ids( array( $category_id ) );
        }
        
        // Set description
        if ( ! empty( $description ) ) {
            $product->set_short_description( $description );
        } else {
            $product->set_short_description( '' );
        }
        
        // Set image
        if ( ! empty( $image_id ) ) {
            $product->set_image_id( $image_id );
        } else {
            // Remove image
            $product->set_image_id( '' );
        }
        
        // Save product
        $product_id = $product->save();
        
        return $product_id;
    }
    
    /**
     * Create or get custom-payment-fee category
     */
    private function get_or_create_category() {
        // Search for existing category
        $category = get_term_by( 'slug', 'custom-payment-fee', 'product_cat' );
        
        if ( $category ) {
            return $category->term_id;
        }
        
        // Create new category
        $result = wp_insert_term(
            'Custom Payment Fee',
            'product_cat',
            array(
                'slug' => 'custom-payment-fee',
                'description' => 'Products related to custom payment form'
            )
        );
        
        if ( is_wp_error( $result ) ) {
            return 0; // In case of error, without category
        }
        
        return $result['term_id'];
    }
    
    /**
     * Add configuration button on plugins page
     */
    public function add_plugin_action_links( $links ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=donations-custom-payment-settings' ) . '">' . __( 'Configure', 'donations-custom-payment' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

}
