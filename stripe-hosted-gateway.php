<?php
/*
Plugin Name: WooCommerce Custom Stripe Hosted Gateway
Description: WooCommerce Custom Stripe Hosted Gateway Integration
Version: 1.0.0
Author: Codeclouds
Author URI: codeclouds.com
*/

// Your plugin code goes here




add_action('plugins_loaded', 'woocommerce_stripe_hosted_gateway_plugin', 0);
function woocommerce_stripe_hosted_gateway_plugin(){
    if (!class_exists('WC_Payment_Gateway'))
        return; // if the WC payment gateway class 

    include(plugin_dir_path(__FILE__) . 'class-gateway.php');

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