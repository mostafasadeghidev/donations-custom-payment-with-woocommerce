<?php
/**
 * Form handling class for Donations & Custom Payment
 * 
 * @package Donations_Custom_Payment
 * @version 6.2.1
 * @author Mostafa Sadeghi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Donations_Custom_Payment_Form {
    
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
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register shortcode
        add_shortcode( 'custom_payment_form', array( $this, 'display_form' ) );
        
        // Process form
        add_action( 'template_redirect', array( $this, 'process_form' ) );
        
        // Set custom price
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_custom_price' ) );
    }
    
    /**
     * Display payment form
     */
    public function display_form() {
        // Get settings
        $settings = Donations_Custom_Payment_Helpers::get_form_settings();
        $preset_amounts_array = array_map( 'trim', explode( ',', $settings['preset_amounts'] ) );
        
        // Display messages if any (these come from redirects, so no nonce needed)
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- These are redirect messages, not form submissions
        if ( isset( $_GET['donations_custom_payment_error'] ) ) {
            $error_message = sanitize_text_field( wp_unslash( $_GET['donations_custom_payment_error'] ) );
            echo '<div class="cpf-error-message">' . esc_html( $error_message ) . '</div>';
        }

        if ( isset( $_GET['donations_custom_payment_success'] ) ) {
            $success_message = sanitize_text_field( wp_unslash( $_GET['donations_custom_payment_success'] ) );
            echo '<div class="cpf-success-message">' . esc_html( $success_message ) . '</div>';
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        // Apply dynamic styles with Alpha
        echo "<style>
            .donations-custom-payment-container {
                background: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['form_bg_color'], $settings['form_bg_color_alpha'])) . " !important;
            }
            .cpf-form-title {
                color: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['form_title_color'], $settings['form_title_color_alpha'])) . " !important;
            }
            .cpf-label {
                color: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['field_label_color'], $settings['field_label_color_alpha'])) . " !important;
            }
            .cpf-input {
                background-color: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['amount_field_bg_color'], $settings['amount_field_bg_color_alpha'])) . " !important;
                color: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['input_text_color'], $settings['input_text_color_alpha'])) . " !important;
            }
            .cpf-submit-btn {
                background: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['button_color'], $settings['button_color_alpha'])) . " !important;
                color: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['button_text_color'], $settings['button_text_color_alpha'])) . " !important;
            }
            .cpf-description {
                background: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['description_bg_color'], $settings['description_bg_color_alpha'])) . " !important;
                color: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['description_text_color'], $settings['description_text_color_alpha'])) . " !important;
            }
            .cpf-preset-btn {
                background: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['preset_btn_bg_color'], $settings['preset_btn_bg_color_alpha'])) . " !important;
                color: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['preset_btn_text_color'], $settings['preset_btn_text_color_alpha'])) . " !important;
            }
            .cpf-amount-info {
                color: " . esc_attr(Donations_Custom_Payment_Helpers::hex_to_rgba($settings['amount_info_text_color'], $settings['amount_info_text_color_alpha'])) . " !important;
            }
        </style>";

        ob_start();
        ?>
        <div class="donations-custom-payment-container">
            
            <?php if ( $settings['show_form_title'] === 'yes' ): ?>
                <h3 class="cpf-form-title"><?php echo esc_html($settings['form_title']); ?></h3>
            <?php endif; ?>
            
        <form id="donations-custom-payment" action="" method="post">
            <?php wp_nonce_field( 'donations_custom_payment_nonce_action', 'donations_custom_payment_nonce' ); ?>
            
                <?php if ( !empty($preset_amounts_array) && $preset_amounts_array[0] != '' ): ?>
                <div class="cpf-form-group">
                    <label class="cpf-label"><?php echo esc_html($settings['preset_amounts_label']); ?></label>
                    <div class="cpf-preset-amounts">
                        <?php foreach ( $preset_amounts_array as $preset ): ?>
                            <?php if ( $preset > 0 ): ?>
                                <div class="cpf-preset-btn" data-amount="<?php echo esc_attr($preset); ?>">
                                    <?php echo number_format($preset); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="cpf-form-group">
                    <label for="donations_custom_payment_amount" class="cpf-label">
                        <?php echo esc_html($settings['amount_field_label']); ?> <span style="color:red;">*</span>
                    </label>
                    <input type="text" id="donations_custom_payment_amount" name="donations_custom_payment_amount" 
                           class="cpf-input" 
                           required 
                           placeholder="<?php 
                           // translators: %s is the minimum amount example
                           echo esc_attr(sprintf( __( 'Example: %s', 'donations-custom-payment' ), number_format($settings['min_amount']))) ; ?>"
                           min="<?php echo esc_attr($settings['min_amount']); ?>"
                           max="<?php echo esc_attr($settings['max_amount']); ?>">
                    <?php if ( $settings['show_amount_info'] === 'yes' ): ?>
                        <div class="cpf-amount-info">
                            <?php
                            if ($settings['currency_info']['position'] === 'right') {
                                echo esc_html($settings['amount_info_min_text'] . ' ' . number_format($settings['min_amount']) . ' ' . $settings['currency_info']['symbol'] . ' | ');
                                echo esc_html($settings['amount_info_max_text'] . ' ' . number_format($settings['max_amount']) . ' ' . $settings['currency_info']['symbol']);
                            } else {
                                echo esc_html($settings['amount_info_min_text'] . ' ' . $settings['currency_info']['symbol'] . ' ' . number_format($settings['min_amount']) . ' | ');
                                echo esc_html($settings['amount_info_max_text'] . ' ' . $settings['currency_info']['symbol'] . ' ' . number_format($settings['max_amount']));
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $settings['show_description'] === 'yes' && !empty($settings['description_text']) ): ?>
                        <div class="cpf-description">
                            <?php echo wp_kses_post(nl2br($settings['description_text'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" name="donations_custom_payment_submit" class="cpf-submit-btn" id="cpf-submit-btn">
                    <span class="cpf-btn-text"><?php echo esc_html($settings['button_text']); ?></span>
                    <div class="cpf-loading">
                        <div class="cpf-spinner"></div>
                    </div>
                </button>
        </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Process form and redirect to payment page
     */
    public function process_form() {
        // Check if form is submitted
        if ( ! isset( $_POST['donations_custom_payment_submit'] ) ) {
            return;
        }
        
        // Verify nonce for security
        if ( ! isset( $_POST['donations_custom_payment_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_nonce'] ) ), 'donations_custom_payment_nonce_action' ) ) {
            wp_redirect( add_query_arg( 'donations_custom_payment_error', __( 'Security error. Please try again.', 'donations-custom-payment' ), wp_get_referer() ) );
            exit;
        }

        // Check if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            wp_die( esc_html( __( 'Please install and activate WooCommerce plugin first.', 'donations-custom-payment' ) ) );
        }

        // Get and validate amount
        if ( ! isset( $_POST['donations_custom_payment_amount'] ) ) {
            wp_redirect( add_query_arg( 'donations_custom_payment_error', __( 'Amount is required.', 'donations-custom-payment' ), wp_get_referer() ) );
            exit;
        }
        $amount_raw = sanitize_text_field( wp_unslash( $_POST['donations_custom_payment_amount'] ) );
        $validation = Donations_Custom_Payment_Helpers::validate_amount($amount_raw);
        
        if ( ! $validation['valid'] ) {
            wp_redirect( add_query_arg( 'donations_custom_payment_error', $validation['message'], wp_get_referer() ) );
            exit;
        }
        
        $amount = $validation['amount'];
        
        global $woocommerce;
        
        // Empty cart to prevent conflicts
        $woocommerce->cart->empty_cart();
        
        // Product name for payment
        $product_name = Donations_Custom_Payment_Helpers::get_product_name();
        $product_id = wc_get_product_id_by_sku( 'custom-payment-fee' );

        // Create product if it doesn't exist
        if ( ! $product_id ) {
            $product = new WC_Product_Simple();
            $product->set_name( $product_name );
            $product->set_slug( 'custom-payment' );
            $product->set_sku( 'custom-payment-fee' );
            $product->set_regular_price( 0 ); // Initial price is zero
            $product->set_status( 'publish' );
            $product->set_virtual( true ); // Virtual product
            $product->set_catalog_visibility( 'hidden' ); // Don't show in shop
            $product_id = $product->save();
        }
        
        // Add product to cart with custom price
        $cart_item_data = array(
            'custom_price' => $amount,
            'unique_key' => md5( microtime() . wp_rand() ) // Ensure unique item each time
        );

        try {
            $woocommerce->cart->add_to_cart( $product_id, 1, 0, array(), $cart_item_data );
        } catch ( Exception $e ) {
            // Handle error if occurs
            wp_redirect( add_query_arg( 'donations_custom_payment_error', __( 'Error adding to cart. Please try again.', 'donations-custom-payment' ), wp_get_referer() ) );
            exit;
        }

        // Check if product was added to cart
        if ( WC()->cart->get_cart_contents_count() > 0 ) {
        // Redirect to checkout
        wp_redirect( wc_get_checkout_url() );
        exit;
        } else {
            // If product wasn't added, show error
            wp_redirect( add_query_arg( 'donations_custom_payment_error', __( 'Error adding product to cart. Please try again.', 'donations-custom-payment' ), wp_get_referer() ) );
            exit;
        }
    }
    
    /**
     * Hook to set custom price before cart totals calculation
     */
    public function set_custom_price( $cart_obj ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        foreach ( $cart_obj->get_cart() as $key => $value ) {
            if ( isset( $value['custom_price'] ) ) {
                $value['data']->set_price( (float) $value['custom_price'] );
            }
        }
    }
}
