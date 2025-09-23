<?php
/**
 * Reports class for Custom Payment Form plugin
 * 
 * @package Donations_Custom_Payment
 * @version 6.2.0
 * @author Mostafa Sadeghi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Donations_Custom_Payment_Reports {
    
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
     * Get orders registered with this plugin
     */
    public static function get_orders($limit = 50, $offset = 0, $date_filter = 'all', $search = '') {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return array();
        }
        
        // Check HPOS usage
        if ( Donations_Custom_Payment_Helpers::is_hpos_enabled() ) {
            return self::get_orders_hpos($limit, $offset, $date_filter, $search);
        } else {
            return self::get_orders_traditional($limit, $offset, $date_filter, $search);
        }
    }
    
    /**
     * Get orders from traditional tables (wp_posts)
     */
    private static function get_orders_traditional($limit = 50, $offset = 0, $date_filter = 'all', $search = '') {
        global $wpdb;
        
        // Main query - based on product SKU
        $query = "
            SELECT DISTINCT
                orders.ID as order_id,
                orders.post_date as order_date,
                orders.post_status as order_status,
                billing_first_name.meta_value as first_name,
                billing_last_name.meta_value as last_name,
                billing_email.meta_value as email,
                order_total.meta_value as total,
                items.order_item_name as product_name
            FROM {$wpdb->posts} orders
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items items ON orders.ID = items.order_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta ON items.order_item_id = itemmeta.order_item_id
            LEFT JOIN {$wpdb->postmeta} billing_first_name ON orders.ID = billing_first_name.post_id 
                AND billing_first_name.meta_key = '_billing_first_name'
            LEFT JOIN {$wpdb->postmeta} billing_last_name ON orders.ID = billing_last_name.post_id 
                AND billing_last_name.meta_key = '_billing_last_name'
            LEFT JOIN {$wpdb->postmeta} billing_email ON orders.ID = billing_email.post_id 
                AND billing_email.meta_key = '_billing_email'
            LEFT JOIN {$wpdb->postmeta} order_total ON orders.ID = order_total.post_id 
                AND order_total.meta_key = '_order_total'
            WHERE orders.post_type = 'shop_order'
            AND itemmeta.meta_key = '_product_id'
            AND itemmeta.meta_value IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product' 
                AND ID IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_sku' 
                    AND meta_value = 'custom-payment-fee'
                )
            )
        ";
        
        $params = array();
        
        // Date filter
        if ($date_filter !== 'all') {
            switch ($date_filter) {
                case 'today':
                    $query .= " AND DATE(orders.post_date) = CURDATE()";
                    break;
                case 'week':
                    $query .= " AND orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $query .= " AND orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
                case 'year':
                    $query .= " AND orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                    break;
            }
        }
        
        // Search filter
        if (!empty($search)) {
            $query .= " AND (
                billing_first_name.meta_value LIKE %s 
                OR billing_last_name.meta_value LIKE %s 
                OR billing_email.meta_value LIKE %s
                OR orders.ID LIKE %s
            )";
            $search_param = '%' . $search . '%';
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        $query .= " ORDER BY orders.post_date DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- Custom reporting queries need direct access
        return $wpdb->get_results($wpdb->prepare($query, $params));
    }
    
    /**
     * Get orders from HPOS tables (wc_orders)
     */
    private static function get_orders_hpos($limit = 50, $offset = 0, $date_filter = 'all', $search = '') {
        global $wpdb;
        
        // Main query - based on product SKU with HPOS
        $query = "
            SELECT DISTINCT
                orders.id as order_id,
                orders.date_created_gmt as order_date,
                orders.status as order_status,
                '' as first_name,
                '' as last_name,
                orders.billing_email as email,
                orders.total_amount as total,
                items.order_item_name as product_name
            FROM {$wpdb->prefix}wc_orders orders
            INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON orders.id = items.order_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta ON items.order_item_id = itemmeta.order_item_id
            WHERE itemmeta.meta_key = '_product_id'
            AND itemmeta.meta_value IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product' 
                AND ID IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_sku' 
                    AND meta_value = 'custom-payment-fee'
                )
            )
        ";
        
        $params = array();
        
        // Date filter
        if ($date_filter !== 'all') {
            switch ($date_filter) {
                case 'today':
                    $query .= " AND DATE(orders.date_created_gmt) = CURDATE()";
                    break;
                case 'week':
                    $query .= " AND orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $query .= " AND orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
                case 'year':
                    $query .= " AND orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                    break;
            }
        }
        
        // Search filter
        if (!empty($search)) {
            $query .= " AND (
                orders.billing_email LIKE %s
                OR orders.id LIKE %s
            )";
            $search_param = '%' . $search . '%';
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        $query .= " ORDER BY orders.date_created_gmt DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- Custom reporting queries need direct access
        return $wpdb->get_results($wpdb->prepare($query, $params));
    }
    
    /**
     * Count total orders for pagination
     */
    public static function get_orders_count($date_filter = 'all', $search = '') {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return 0;
        }
        
        // Check HPOS usage
        if ( Donations_Custom_Payment_Helpers::is_hpos_enabled() ) {
            return self::get_orders_count_hpos($date_filter, $search);
        } else {
            return self::get_orders_count_traditional($date_filter, $search);
        }
    }
    
    /**
     * Count orders from traditional tables
     */
    private static function get_orders_count_traditional($date_filter = 'all', $search = '') {
        global $wpdb;
        
        $query = "
            SELECT COUNT(DISTINCT orders.ID) as total_count
            FROM {$wpdb->posts} as orders
            INNER JOIN {$wpdb->prefix}woocommerce_order_items as items ON orders.ID = items.order_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta ON items.order_item_id = itemmeta.order_item_id
            WHERE orders.post_type = 'shop_order'
            AND itemmeta.meta_key = '_product_id'
            AND itemmeta.meta_value IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product' 
                AND ID IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_sku' 
                    AND meta_value = 'custom-payment-fee'
                )
            )
        ";
        
        $params = array();
        
        // Date filter
        if ($date_filter !== 'all') {
            $date_condition = self::get_date_condition($date_filter);
            if ($date_condition) {
                $query .= " AND " . $date_condition;
            }
        }
        
        // Search filter
        if (!empty($search)) {
            $query .= " AND (
                orders.ID LIKE %s OR
                EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm1 
                    WHERE pm1.post_id = orders.ID 
                    AND pm1.meta_key = '_billing_first_name' 
                    AND pm1.meta_value LIKE %s
                ) OR
                EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm2 
                    WHERE pm2.post_id = orders.ID 
                    AND pm2.meta_key = '_billing_last_name' 
                    AND pm2.meta_value LIKE %s
                ) OR
                EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm3 
                    WHERE pm3.post_id = orders.ID 
                    AND pm3.meta_key = '_billing_email' 
                    AND pm3.meta_value LIKE %s
                )
            )";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- Custom reporting queries need direct access
        $result = $wpdb->get_var($wpdb->prepare($query, $params));
        return intval($result);
    }
    
    /**
     * Count orders from HPOS tables
     */
    private static function get_orders_count_hpos($date_filter = 'all', $search = '') {
        global $wpdb;
        
        $query = "
            SELECT COUNT(DISTINCT orders.id) as total_count
            FROM {$wpdb->prefix}wc_orders as orders
            INNER JOIN {$wpdb->prefix}woocommerce_order_items as items ON orders.id = items.order_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta ON items.order_item_id = itemmeta.order_item_id
            WHERE itemmeta.meta_key = '_product_id'
            AND itemmeta.meta_value IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product' 
                AND ID IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_sku' 
                    AND meta_value = 'custom-payment-fee'
                )
            )
        ";
        
        $params = array();
        
        // Date filter
        if ($date_filter !== 'all') {
            $date_condition = self::get_date_condition_hpos($date_filter);
            if ($date_condition) {
                $query .= " AND " . $date_condition;
            }
        }
        
        // Search filter
        if (!empty($search)) {
            $query .= " AND (
                orders.id LIKE %s OR
                orders.billing_email LIKE %s
            )";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- Custom reporting queries need direct access
        $result = $wpdb->get_var($wpdb->prepare($query, $params));
        return intval($result);
    }
    
    /**
     * Get date conditions for queries
     */
    public static function get_date_condition($date_filter) {
        switch ($date_filter) {
            case 'today':
                return "DATE(orders.post_date) = CURDATE()";
            case 'week':
                return "orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            case 'month':
                return "orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            case 'year':
                return "orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return '';
        }
    }
    
    /**
     * Get date conditions for HPOS
     */
    public static function get_date_condition_hpos($date_filter) {
        switch ($date_filter) {
            case 'today':
                return "DATE(orders.date_created_gmt) = CURDATE()";
            case 'week':
                return "orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            case 'month':
                return "orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            case 'year':
                return "orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return '';
        }
    }
    
    /**
     * Get orders statistics
     */
    public static function get_orders_stats($date_filter = 'all') {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return array(
                'total_orders' => 0,
                'total_amount' => 0,
                'avg_amount' => 0
            );
        }
        
        // Check HPOS usage
        if ( Donations_Custom_Payment_Helpers::is_hpos_enabled() ) {
            return self::get_orders_stats_hpos($date_filter);
        } else {
            return self::get_orders_stats_traditional($date_filter);
        }
    }
    
    /**
     * Get orders statistics from traditional tables
     */
    private static function get_orders_stats_traditional($date_filter = 'all') {
        global $wpdb;
        
        $query = "
            SELECT 
                COUNT(DISTINCT orders.ID) as total_orders,
                SUM(order_total.meta_value) as total_amount,
                AVG(order_total.meta_value) as avg_amount
            FROM {$wpdb->posts} orders
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items items ON orders.ID = items.order_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta ON items.order_item_id = itemmeta.order_item_id
            LEFT JOIN {$wpdb->postmeta} order_total ON orders.ID = order_total.post_id 
                AND order_total.meta_key = '_order_total'
            WHERE orders.post_type = 'shop_order'
            AND itemmeta.meta_key = '_product_id'
            AND itemmeta.meta_value IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product' 
                AND ID IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_sku' 
                    AND meta_value = 'custom-payment-fee'
                )
            )
        ";
        
        $params = array();
        
        // Date filter
        if ($date_filter !== 'all') {
            switch ($date_filter) {
                case 'today':
                    $query .= " AND DATE(orders.post_date) = CURDATE()";
                    break;
                case 'week':
                    $query .= " AND orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $query .= " AND orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
                case 'year':
                    $query .= " AND orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                    break;
            }
        }
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- Custom reporting queries need direct access
        $result = $wpdb->get_row($wpdb->prepare($query, $params));
        
        return array(
            'total_orders' => $result ? (int)$result->total_orders : 0,
            'total_amount' => $result ? (float)$result->total_amount : 0,
            'avg_amount' => $result ? (float)$result->avg_amount : 0
        );
    }
    
    /**
     * Get orders statistics from HPOS tables
     */
    private static function get_orders_stats_hpos($date_filter = 'all') {
        global $wpdb;
        
        $query = "
            SELECT 
                COUNT(DISTINCT orders.id) as total_orders,
                SUM(orders.total_amount) as total_amount,
                AVG(orders.total_amount) as avg_amount
            FROM {$wpdb->prefix}wc_orders orders
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items items ON orders.id = items.order_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta ON items.order_item_id = itemmeta.order_item_id
            WHERE itemmeta.meta_key = '_product_id'
            AND itemmeta.meta_value IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product' 
                AND ID IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_sku' 
                    AND meta_value = 'custom-payment-fee'
                )
            )
        ";
        
        $params = array();
        
        // Date filter
        if ($date_filter !== 'all') {
            switch ($date_filter) {
                case 'today':
                    $query .= " AND DATE(orders.date_created_gmt) = CURDATE()";
                    break;
                case 'week':
                    $query .= " AND orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $query .= " AND orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
                case 'year':
                    $query .= " AND orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                    break;
            }
        }
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- Custom reporting queries need direct access
        $result = $wpdb->get_row($wpdb->prepare($query, $params));
        
        return array(
            'total_orders' => $result ? (int)$result->total_orders : 0,
            'total_amount' => $result ? (float)$result->total_amount : 0,
            'avg_amount' => $result ? (float)$result->avg_amount : 0
        );
    }
    
    /**
     * Get successful orders statistics (completed and processing)
     */
    public static function get_successful_orders_stats($date_filter = 'all') {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return array(
                'total_orders' => 0,
                'total_amount' => 0,
                'avg_amount' => 0
            );
        }
        
        // Check HPOS usage
        if ( Donations_Custom_Payment_Helpers::is_hpos_enabled() ) {
            return self::get_successful_orders_stats_hpos($date_filter);
        } else {
            return self::get_successful_orders_stats_traditional($date_filter);
        }
    }
    
    /**
     * Get successful orders statistics from traditional tables
     */
    private static function get_successful_orders_stats_traditional($date_filter = 'all') {
        global $wpdb;
        
        $query = "
            SELECT 
                COUNT(DISTINCT orders.ID) as total_orders,
                SUM(order_total.meta_value) as total_amount,
                AVG(order_total.meta_value) as avg_amount
            FROM {$wpdb->posts} orders
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items items ON orders.ID = items.order_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta ON items.order_item_id = itemmeta.order_item_id
            LEFT JOIN {$wpdb->postmeta} order_total ON orders.ID = order_total.post_id 
                AND order_total.meta_key = '_order_total'
            WHERE orders.post_type = 'shop_order'
            AND orders.post_status IN ('wc-completed', 'wc-processing')
            AND itemmeta.meta_key = '_product_id'
            AND itemmeta.meta_value IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product' 
                AND ID IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_sku' 
                    AND meta_value = 'custom-payment-fee'
                )
            )
        ";
        
        $params = array();
        
        // Date filter
        if ($date_filter !== 'all') {
            switch ($date_filter) {
                case 'today':
                    $query .= " AND DATE(orders.post_date) = CURDATE()";
                    break;
                case 'week':
                    $query .= " AND orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $query .= " AND orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
                case 'year':
                    $query .= " AND orders.post_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                    break;
            }
        }
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- Custom reporting queries need direct access
        $result = $wpdb->get_row($wpdb->prepare($query, $params));
        
        return array(
            'total_orders' => $result ? (int)$result->total_orders : 0,
            'total_amount' => $result ? (float)$result->total_amount : 0,
            'avg_amount' => $result ? (float)$result->avg_amount : 0
        );
    }
    
    /**
     * Get successful orders statistics from HPOS tables
     */
    private static function get_successful_orders_stats_hpos($date_filter = 'all') {
        global $wpdb;
        
        $query = "
            SELECT 
                COUNT(DISTINCT orders.id) as total_orders,
                SUM(orders.total_amount) as total_amount,
                AVG(orders.total_amount) as avg_amount
            FROM {$wpdb->prefix}wc_orders orders
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items items ON orders.id = items.order_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta itemmeta ON items.order_item_id = itemmeta.order_item_id
            WHERE orders.status IN ('wc-completed', 'wc-processing')
            AND itemmeta.meta_key = '_product_id'
            AND itemmeta.meta_value IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product' 
                AND ID IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_sku' 
                    AND meta_value = 'custom-payment-fee'
                )
            )
        ";
        
        $params = array();
        
        // Date filter
        if ($date_filter !== 'all') {
            switch ($date_filter) {
                case 'today':
                    $query .= " AND DATE(orders.date_created_gmt) = CURDATE()";
                    break;
                case 'week':
                    $query .= " AND orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $query .= " AND orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
                case 'year':
                    $query .= " AND orders.date_created_gmt >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                    break;
            }
        }
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- Custom reporting queries need direct access
        $result = $wpdb->get_row($wpdb->prepare($query, $params));
        
        return array(
            'total_orders' => $result ? (int)$result->total_orders : 0,
            'total_amount' => $result ? (float)$result->total_amount : 0,
            'avg_amount' => $result ? (float)$result->avg_amount : 0
        );
    }
    
    /**
     * Render reports tab
     */
    public static function render_reports_tab() {
        // Get filter parameters
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are filter parameters for display, not form submissions
        $date_filter = isset($_GET['date_filter']) ? sanitize_text_field( wp_unslash( $_GET['date_filter'] ) ) : 'all';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are filter parameters for display, not form submissions
        $search = isset($_GET['search']) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These are filter parameters for display, not form submissions
        $page = isset($_GET['paged']) ? absint( wp_unslash( $_GET['paged'] ) ) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        // Get statistics and pagination information
        $stats = self::get_orders_stats($date_filter); // All orders
        $successful_stats = self::get_successful_orders_stats($date_filter); // Only successful orders
        $total_orders = self::get_orders_count($date_filter, $search);
        $total_pages = ceil($total_orders / $per_page);
        $orders = self::get_orders($per_page, $offset, $date_filter, $search);
        $currency_info = Donations_Custom_Payment_Helpers::get_currency_info();
        $hpos_info = Donations_Custom_Payment_Helpers::get_hpos_debug_info();
        ?>
        
        <!-- System Status Information -->
        <div class="cpf-system-info" style="background: #f0f8ff; border: 1px solid #0073aa; border-radius: 4px; padding: 10px; margin-bottom: 20px;">
            <strong><?php esc_html_e( 'Reporting System Status:', 'donations-custom-payment' ); ?></strong>
            <?php if ( $hpos_info['hpos_enabled'] ): ?>
                <span style="color: #0073aa;"><?php esc_html_e( 'Using HPOS tables (New)', 'donations-custom-payment' ); ?></span>
            <?php else: ?>
                <span style="color: #666;"><?php esc_html_e( 'Using traditional WordPress tables', 'donations-custom-payment' ); ?></span>
            <?php endif; ?>
        </div>
        
        <!-- General Reports Statistics -->
        <div class="cpf-reports-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <!-- Card 1: Total Orders -->
            <div class="cpf-stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="cpf-stat-number"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="cpf-stat-label"><?php echo esc_html( __( 'All Orders', 'donations-custom-payment' ) ); ?></div>
            </div>
            
            <!-- Card 2: Total Successful Orders -->
            <div class="cpf-stat-card" style="background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);">
                <div class="cpf-stat-number"><?php echo number_format($successful_stats['total_orders']); ?></div>
                <div class="cpf-stat-label"><?php echo esc_html( __( 'Successful Orders', 'donations-custom-payment' ) ); ?></div>
            </div>
            
            <!-- Card 3: Total Sales (Successful Only) -->
            <div class="cpf-stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="cpf-stat-number"><?php echo number_format($successful_stats['total_amount']); ?></div>
                <div class="cpf-stat-label"><?php echo esc_html( __( 'Total Sales', 'donations-custom-payment' ) ); ?> (<?php echo esc_html( $currency_info['name'] ); ?>)</div>
            </div>
            
            <!-- Card 4: Average Sales (Successful Only) -->
            <div class="cpf-stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="cpf-stat-number"><?php echo number_format($successful_stats['avg_amount']); ?></div>
                <div class="cpf-stat-label"><?php echo esc_html( __( 'Average Sales', 'donations-custom-payment' ) ); ?> (<?php echo esc_html( $currency_info['name'] ); ?>)</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <form method="get" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                <input type="hidden" name="page" value="donations-custom-payment-settings">
                <input type="hidden" name="tab" value="reports">
                
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php echo esc_html( __( '📅 Time Filter:', 'donations-custom-payment' ) ); ?></label>
                    <select name="date_filter" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="all" <?php selected($date_filter, 'all'); ?>><?php echo esc_html( __( 'All Times', 'donations-custom-payment' ) ); ?></option>
                        <option value="today" <?php selected($date_filter, 'today'); ?>><?php echo esc_html( __( 'Today', 'donations-custom-payment' ) ); ?></option>
                        <option value="week" <?php selected($date_filter, 'week'); ?>><?php echo esc_html( __( 'Last Week', 'donations-custom-payment' ) ); ?></option>
                        <option value="month" <?php selected($date_filter, 'month'); ?>><?php echo esc_html( __( 'Last Month', 'donations-custom-payment' ) ); ?></option>
                        <option value="year" <?php selected($date_filter, 'year'); ?>><?php echo esc_html( __( 'Last Year', 'donations-custom-payment' ) ); ?></option>
                    </select>
                </div>
                
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php echo esc_html( __( '🔍 Search:', 'donations-custom-payment' ) ); ?></label>
                    <input type="text" name="search" value="<?php echo esc_attr($search); ?>" 
                           placeholder="<?php echo esc_attr( __( 'Name, email or order number...', 'donations-custom-payment' ) ); ?>" 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <button type="submit" style="padding: 8px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    <?php echo esc_html( __( 'Apply Filter', 'donations-custom-payment' ) ); ?>
                </button>
                
                <?php if ($date_filter !== 'all' || !empty($search)): ?>
                    <a href="?page=donations-custom-payment-settings&tab=reports" 
                       style="padding: 8px 20px; background: #666; color: white; text-decoration: none; border-radius: 4px;">
                        <?php echo esc_html( __( 'Clear Filter', 'donations-custom-payment' ) ); ?>
                    </a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Orders Table -->
        <?php if (!empty($orders)): ?>
            <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 15px; text-align: right; font-weight: bold;"><?php echo esc_html( __( 'Order Number', 'donations-custom-payment' ) ); ?></th>
                            <th style="padding: 15px; text-align: right; font-weight: bold;"><?php echo esc_html( __( 'Customer Name', 'donations-custom-payment' ) ); ?></th>
                            <th style="padding: 15px; text-align: right; font-weight: bold;"><?php echo esc_html( __( 'Email', 'donations-custom-payment' ) ); ?></th>
                            <th style="padding: 15px; text-align: right; font-weight: bold;"><?php echo esc_html( __( 'Amount', 'donations-custom-payment' ) ); ?></th>
                            <th style="padding: 15px; text-align: right; font-weight: bold;"><?php echo esc_html( __( 'Date', 'donations-custom-payment' ) ); ?></th>
                            <th style="padding: 15px; text-align: right; font-weight: bold;"><?php echo esc_html( __( 'Status', 'donations-custom-payment' ) ); ?></th>
                            <th style="padding: 15px; text-align: right; font-weight: bold;"><?php echo esc_html( __( 'Actions', 'donations-custom-payment' ) ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 12px 15px;">#<?php echo esc_html( $order->order_id ); ?></td>
                                <td style="padding: 12px 15px;">
                                    <?php echo esc_html($order->first_name . ' ' . $order->last_name); ?>
                                </td>
                                <td style="padding: 12px 15px;"><?php echo esc_html($order->email); ?></td>
                                <td style="padding: 12px 15px; font-weight: bold; color: #28a745;">
                                    <?php 
                                    if ($currency_info['position'] === 'right') {
                                        echo number_format($order->total) . ' ' . esc_html( $currency_info['symbol'] );
                                    } else {
                                        echo esc_html( $currency_info['symbol'] ) . ' ' . number_format($order->total);
                                    }
                                    ?>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <?php echo esc_html( date_i18n('Y/m/d H:i', strtotime($order->order_date)) ); ?>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <?php 
                                    $status_colors = array(
                                        'wc-completed' => '#28a745',
                                        'wc-processing' => '#007cba',
                                        'wc-on-hold' => '#ffc107',
                                        'wc-pending' => '#6c757d',
                                        'wc-cancelled' => '#dc3545',
                                        'wc-refunded' => '#6f42c1',
                                        'wc-failed' => '#dc3545'
                                    );
                                    $status_names = array(
                                        'wc-completed' => __( 'Completed', 'donations-custom-payment' ),
                                        'wc-processing' => __( 'Processing', 'donations-custom-payment' ),
                                        'wc-on-hold' => __( 'On Hold', 'donations-custom-payment' ),
                                        'wc-pending' => __( 'Pending Payment', 'donations-custom-payment' ),
                                        'wc-cancelled' => __( 'Cancelled', 'donations-custom-payment' ),
                                        'wc-refunded' => __( 'Refunded', 'donations-custom-payment' ),
                                        'wc-failed' => __( 'Failed', 'donations-custom-payment' )
                                    );
                                    $color = isset($status_colors[$order->order_status]) ? $status_colors[$order->order_status] : '#6c757d';
                                    $name = isset($status_names[$order->order_status]) ? $status_names[$order->order_status] : $order->order_status;
                                    ?>
                                    <span style="background: <?php echo esc_attr( $color ); ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        <?php echo esc_html( $name ); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <a href="<?php echo esc_url( admin_url('post.php?post=' . $order->order_id . '&action=edit') ); ?>" 
                                       target="_blank" 
                                       style="background: #007cba; color: white; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 12px;">
                                        <?php echo esc_html( __( 'View', 'donations-custom-payment' ) ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="background: #f8f9fa; padding: 40px; text-align: center; border-radius: 8px; border: 2px dashed #dee2e6;">
                <h3 style="color: #6c757d; margin: 0 0 10px 0;"><?php echo esc_html( __( '😔 No orders found', 'donations-custom-payment' ) ); ?></h3>
                <p style="color: #6c757d; margin: 0;"><?php echo esc_html( __( 'No orders have been registered in the selected time range or with the searched keyword.', 'donations-custom-payment' ) ); ?></p>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="cpf-pagination" style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            
            <!-- Page Information -->
            <div class="cpf-pagination-info" style="color: #6c757d; font-size: 14px;">
                Showing <?php echo esc_html( (($page - 1) * $per_page) + 1 ); ?> to <?php echo esc_html( min($page * $per_page, $total_orders) ); ?> of <?php echo esc_html( number_format($total_orders) ); ?> orders
            </div>
            
            <!-- Pagination Controls -->
            <div class="cpf-pagination-controls" style="display: flex; gap: 8px; align-items: center;">
                
                <!-- Previous Page -->
                <?php if ($page > 1): ?>
                    <a href="<?php echo esc_url(add_query_arg(array(
                        'page' => 'donations-custom-payment-settings',
                        'tab' => 'reports',
                        'date_filter' => $date_filter,
                        'search' => $search,
                        'paged' => $page - 1
                    ), admin_url('options-general.php'))); ?>" 
                       class="cpf-pagination-btn" 
                       style="padding: 8px 12px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; transition: background 0.3s;">
                        <?php echo esc_html( __( '← Previous', 'donations-custom-payment' ) ); ?>
                    </a>
                <?php else: ?>
                    <span class="cpf-pagination-btn disabled" style="padding: 8px 12px; background: #e9ecef; color: #6c757d; border-radius: 4px; font-size: 14px;"><?php echo esc_html( __( '← Previous', 'donations-custom-payment' ) ); ?></span>
                <?php endif; ?>
                
                <!-- Page Numbers -->
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1) {
                    echo '<a href="' . esc_url(add_query_arg(array(
                        'page' => 'donations-custom-payment-settings',
                        'tab' => 'reports',
                        'date_filter' => $date_filter,
                        'search' => $search,
                        'paged' => 1
                    ), admin_url('options-general.php'))) . '" class="cpf-pagination-btn" style="padding: 8px 12px; background: #f8f9fa; color: #495057; text-decoration: none; border-radius: 4px; font-size: 14px; border: 1px solid #dee2e6;">1</a>';
                    if ($start_page > 2) {
                        echo '<span style="padding: 8px 4px; color: #6c757d;">...</span>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $page) {
                        echo '<span class="cpf-pagination-btn current" style="padding: 8px 12px; background: #0073aa; color: white; border-radius: 4px; font-size: 14px; font-weight: bold;">' . esc_html( $i ) . '</span>';
                    } else {
                        echo '<a href="' . esc_url(add_query_arg(array(
                            'page' => 'donations-custom-payment-settings',
                            'tab' => 'reports',
                            'date_filter' => $date_filter,
                            'search' => $search,
                            'paged' => $i
                        ), admin_url('options-general.php'))) . '" class="cpf-pagination-btn" style="padding: 8px 12px; background: #f8f9fa; color: #495057; text-decoration: none; border-radius: 4px; font-size: 14px; border: 1px solid #dee2e6;">' . esc_html( $i ) . '</a>';
                    }
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span style="padding: 8px 4px; color: #6c757d;">...</span>';
                    }
                    echo '<a href="' . esc_url(add_query_arg(array(
                        'page' => 'donations-custom-payment-settings',
                        'tab' => 'reports',
                        'date_filter' => $date_filter,
                        'search' => $search,
                        'paged' => $total_pages
                    ), admin_url('options-general.php'))) . '" class="cpf-pagination-btn" style="padding: 8px 12px; background: #f8f9fa; color: #495057; text-decoration: none; border-radius: 4px; font-size: 14px; border: 1px solid #dee2e6;">' . esc_html( $total_pages ) . '</a>';
                }
                ?>
                
                <!-- Next Page -->
                <?php if ($page < $total_pages): ?>
                    <a href="<?php echo esc_url(add_query_arg(array(
                        'page' => 'donations-custom-payment-settings',
                        'tab' => 'reports',
                        'date_filter' => $date_filter,
                        'search' => $search,
                        'paged' => $page + 1
                    ), admin_url('options-general.php'))); ?>" 
                       class="cpf-pagination-btn" 
                       style="padding: 8px 12px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; transition: background 0.3s;">
                        <?php echo esc_html( __( 'Next →', 'donations-custom-payment' ) ); ?>
                    </a>
                <?php else: ?>
                    <span class="cpf-pagination-btn disabled" style="padding: 8px 12px; background: #e9ecef; color: #6c757d; border-radius: 4px; font-size: 14px;"><?php echo esc_html( __( 'Next →', 'donations-custom-payment' ) ); ?></span>
                <?php endif; ?>
                
            </div>
        </div>
        <?php endif; ?>
        
        <?php
    }
}
