<?php
/**
 * Uninstall file for Donations & Custom Payment Plugin
 * 
 * This file is executed when the plugin is uninstalled.
 * It removes all plugin data from the database.
 * 
 * @package Donations_Custom_Payment
 * @version 6.2.1
 * @author Mostafa Sadeghi
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check if user has permission to uninstall
if ( ! current_user_can( 'activate_plugins' ) ) {
    return;
}

// Remove plugin options
delete_option( 'donations_custom_payment_min_amount' );
delete_option( 'donations_custom_payment_max_amount' );
delete_option( 'donations_custom_payment_product_name' );
delete_option( 'donations_custom_payment_preset_amounts' );
delete_option( 'donations_custom_payment_form_title' );
delete_option( 'donations_custom_payment_show_form_title' );
delete_option( 'donations_custom_payment_button_text' );
delete_option( 'donations_custom_payment_button_color' );
delete_option( 'donations_custom_payment_form_bg_color' );
delete_option( 'donations_custom_payment_show_amount_info' );
delete_option( 'donations_custom_payment_amount_field_label' );
delete_option( 'donations_custom_payment_amount_field_bg_color' );
delete_option( 'donations_custom_payment_description_text' );
delete_option( 'donations_custom_payment_show_description' );
delete_option( 'donations_custom_payment_form_title_color' );
delete_option( 'donations_custom_payment_field_label_color' );
delete_option( 'donations_custom_payment_input_text_color' );
delete_option( 'donations_custom_payment_button_text_color' );
delete_option( 'donations_custom_payment_description_bg_color' );
delete_option( 'donations_custom_payment_description_text_color' );
delete_option( 'donations_custom_payment_preset_btn_bg_color' );
delete_option( 'donations_custom_payment_preset_btn_text_color' );
delete_option( 'donations_custom_payment_amount_info_text_color' );
delete_option( 'donations_custom_payment_form_bg_color_alpha' );
delete_option( 'donations_custom_payment_form_title_color_alpha' );
delete_option( 'donations_custom_payment_field_label_color_alpha' );
delete_option( 'donations_custom_payment_input_text_color_alpha' );
delete_option( 'donations_custom_payment_button_color_alpha' );
delete_option( 'donations_custom_payment_button_text_color_alpha' );
delete_option( 'donations_custom_payment_description_bg_color_alpha' );
delete_option( 'donations_custom_payment_description_text_color_alpha' );
delete_option( 'donations_custom_payment_amount_field_bg_color_alpha' );
delete_option( 'donations_custom_payment_preset_btn_bg_color_alpha' );
delete_option( 'donations_custom_payment_preset_btn_text_color_alpha' );
delete_option( 'donations_custom_payment_amount_info_text_color_alpha' );
delete_option( 'donations_custom_payment_preset_amounts_label' );
delete_option( 'donations_custom_payment_amount_info_min_text' );
delete_option( 'donations_custom_payment_amount_info_max_text' );

// Clear any cached data
wp_cache_flush();

// Clear rewrite rules
flush_rewrite_rules();
