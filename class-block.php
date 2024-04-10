<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Stripe_Hosted_Gateway_Blocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'stripe_hosted_gateway';// your payment gateway name

    public function initialize() {
        $this->settings = get_option( 'woocommerce_stripe_hosted_gateway_settings', [] );
        $this->gateway = new Stripe_Hosted_Gateway();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'stripe_hosted_gateway-blocks-integration',
            plugin_dir_url(__FILE__) . 'build/index.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );
        if( function_exists( 'wp_set_script_translations' ) ) {            
            wp_set_script_translations( 'stripe_hosted_gateway-blocks-integration');
            
        }
        return [ 'stripe_hosted_gateway-blocks-integration' ];
    }

    public function get_payment_method_data() {
        return [
            'title' => $this->gateway->title,
            'description' => $this->gateway->description,
            'checkoutbuttontext' => $this->gateway->checkoutbuttontext,
            'testmode' => $this->gateway->testmode,
            'icon' => plugin_dir_url( __FILE__ ) . 'images/credit-cards.png',
            'skipCard' => $this->gateway->skipCard
        ];
    }

}
?>