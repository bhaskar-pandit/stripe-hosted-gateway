<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Stripe_Hosted_Gateway_Blocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'stripe_hosted_gateway';// your payment gateway name

   /**
    * The initialize function retrieves settings for the WooCommerce Stripe Hosted Gateway and creates
    * a new instance of the gateway.
    */

    public function initialize() {
        $this->settings = get_option( 'woocommerce_stripe_hosted_gateway_settings', [] );
        $this->gateway = new Stripe_Hosted_Gateway();
    }

 /**
  * The is_active function checks if the gateway is available.
  * 
  * @return The `is_active` function is returning the result of the `is_available` method of the
  * `gateway` object.
  */
    public function is_active() {
        return $this->gateway->is_available();
    }

  /**
   * The function `get_payment_method_script_handles` registers a script for a Stripe hosted gateway
   * blocks integration in WordPress.
   * 
   * @return The function `get_payment_method_script_handles()` is returning an array containing the
   * script handle `'stripe_hosted_gateway-blocks-integration'`.
   */
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

   /**
    * The function `get_payment_method_data` returns an array of payment method data including title,
    * description, checkout button text, test mode, icon URL, and skip card option.
    * 
    * @return An array of payment method data is being returned. The array includes the title,
    * description, checkout button text, test mode status, an icon URL, and a flag for skipping the
    * card option.
    */
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