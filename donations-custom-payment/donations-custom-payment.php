<?php
/**
 * Plugin Name: Donations & Custom Payment
 * Plugin URI: https://github.com/mostafasadeghidev/donations-custom-payment-with-woocommerce
 * Description: Advanced custom payment and donation plugin for WooCommerce - Create flexible payment forms with custom amounts. Perfect for charities, crowdfunding, custom payments, and installment sales. Includes comprehensive reporting system, full color customization, amount-based link support, and responsive design. Use shortcode [custom_payment_form].
 * Version: 6.2.1
 * Author: Mostafa Sadeghi
 * Author URI: https://github.com/mostafasadeghidev
 * Text Domain: donations-custom-payment
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 10.1.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html

 * 
 * @package Donations_Custom_Payment
 * @version 6.2.1
 * @author Mostafa Sadeghi
 * @license GPL v2 or later
 * @copyright 2023 Mostafa Sadeghi
 * 
 * Custom Payment Form is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * Custom Payment Form is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Custom Payment Form. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action( 'admin_notices', function() {
        echo '<div class="notice notice-error"><p>' . 
             esc_html( __( 'Donations & Custom Payment requires WooCommerce to be installed and activated.', 'donations-custom-payment' ) ) . 
             '</p></div>';
    });
    return;
}

// Load main plugin file
require_once plugin_dir_path( __FILE__ ) . 'includes/class-donations-custom-payment-core.php';
