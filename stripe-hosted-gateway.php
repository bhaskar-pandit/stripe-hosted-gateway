<?php
/*
Plugin Name: WooCommerce Custom Stripe Hosted Gateway
Description: WooCommerce Custom Stripe Hosted Gateway Integration
Version: 1.0.1
Author: Codeclouds
Author URI: codeclouds.com
Requires at least: 6.4.3
Requires WooCommerce: 8.6.0
*/

// Your plugin code goes here
/*
// Add user meta data when an user created if plugin activated
function wpse_update_user_meta_data_for_cc_payment_gateway( $user_id ) {
    update_user_meta( $user_id, 'isAllowedForCCPayment', "on" );
}
// Fire late to try to ensure this is done after any other function hooked to `user_register`.
add_action( 'user_register','wpse_update_user_meta_data_for_cc_payment_gateway', PHP_INT_MAX, 1 );
*/

// Create order_gateway_data table on activation of plugin
register_activation_hook( __FILE__, 'wp_create_order_gateway_data_table' );
function wp_create_order_gateway_data_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'order_gateway_data';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id bigint UNSIGNED NOT NULL,
        payment_url varchar(500),
        store_code varchar(250),
        is_active enum('1','0') DEFAULT '1',
        created_at datetime DEFAULT CURRENT_TIMESTAMP() NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    // FOREIGN KEY (order_id) REFERENCES ".$wpdb->prefix."wc_order_stats(order_id)

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}


add_action('plugins_loaded', 'woocommerce_stripe_hosted_gateway_plugin', 0);
function woocommerce_stripe_hosted_gateway_plugin(){
    if (!class_exists('WC_Payment_Gateway'))
        return; // if the WC payment gateway class 

    include(plugin_dir_path(__FILE__) . 'class-gateway.php');
    include(plugin_dir_path(__FILE__) . 'cc-block-gateway.php');

    // setting link set up
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'stripe_hosted_gateway_action_links' );
    function stripe_hosted_gateway_action_links( $links ) {

        $links[] = '<a href="'. menu_page_url( 'wc-settings', false ) .'&tab=checkout&section=stripe_hosted_gateway">Settings</a>';
        return $links;
    }

    
}


add_filter('woocommerce_payment_gateways', 'add_stripe_hosted_gateway');

function add_stripe_hosted_gateway($gateways) {
  $gateways[] = 'Stripe_Hosted_Gateway';
  return $gateways;
}

/**
 * Custom function to declare compatibility with cart_checkout_blocks feature 
*/
function declare_cart_checkout_blocks_compatibility() {
    // Check if the required class exists
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // Declare compatibility for 'cart_checkout_blocks'
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
}
// Hook the custom function to the 'before_woocommerce_init' action
add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility');

// Hook the custom function to the 'woocommerce_blocks_loaded' action
add_action( 'woocommerce_blocks_loaded', 'stripe_hosted_register_order_approval_payment_method_type' );

/**
 * Custom function to register a payment method type

 */
function stripe_hosted_register_order_approval_payment_method_type() {
    // Check if the required class exists
    if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        return;
    }

    // Include the custom Blocks Checkout class
    require_once plugin_dir_path(__FILE__) . 'class-block.php';

    // Hook the registration function to the 'woocommerce_blocks_payment_method_type_registration' action
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            // Register an instance of Stripe_Hosted_Gateway_Blocks
            $payment_method_registry->register( new Stripe_Hosted_Gateway_Blocks );
        }
    );
}
?>