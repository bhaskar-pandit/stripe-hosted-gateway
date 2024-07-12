<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_CC_Stripe_Settings_Block_Gateway {

  
   /**
    * The constructor function sets up actions for displaying, saving user profile fields, and updating
    * user meta on the WooCommerce thank you page.
    */
    public function __construct() {
        
        
        add_action( 'show_user_profile', array( $this, 'cc_block_extra_profile_field' ), 30);    
        add_action( 'edit_user_profile', array( $this, 'cc_block_extra_profile_field' ), 30 );        


        add_action( 'personal_options_update', array( $this, 'cc_block_save_profile_fields'));
        add_action( 'edit_user_profile_update', array( $this, 'cc_block_save_profile_fields'));

        // Load user meta update function on thank you page
        add_action( 'woocommerce_thankyou', array( $this, 'user_meta_update_thank_you_page'));
		    

    }
    

   /**
    * The function `get_settings` returns an array of settings for a login pop-up in PHP.
    * 
    * @return An array of settings with a single element containing information about showing a login
    * pop-up.
    */
    public function get_settings() {

        $settings = array(
            array(
                'name' => __( 'Do you Want to show login Pop-Up?', '' ),
                'type' => 'checkbox',
                'id'   => 'wc_settings_cc_is_login_pop',
            )
        );

        return apply_filters( 'wc_settings_cc_block_settings', $settings );
    }

    /**
     * The function cc_block_extra_profile_field adds a checkbox field to the user profile for
     * determining if the customer is allowed for credit card payment.
     * 
     * @param user The `cc_block_extra_profile_field` function is used to display an extra profile
     * field for a user in WordPress. The field allows the user to indicate whether they are allowed
     * for credit card (CC) payment.
     */
    public function cc_block_extra_profile_field( $user ) {
        $isAllowedForCCPayment = esc_attr(get_the_author_meta( 'isAllowedForCCPayment', $user->ID ));
        ?>
            <br>
            <br>
            <input type="checkbox" id="isAllowedForCCPayment" name="isAllowedForCCPayment" class="regular-text" <?php if($isAllowedForCCPayment == 'on'){ echo 'checked'; }?> />
            <label for="isAllowedForCCPayment" style="font-size: 1em; font-weight: bold;"><?php _e("Is Customer Allowed For CC Payment?"); ?></label>
        <?php
    }

    
   /**
    * The function `cc_block_save_profile_fields` updates the user meta field 'isAllowedForCCPayment'
    * with the value from the POST data for a specific user ID.
    * 
    * @param user_id The `user_id` parameter in the `cc_block_save_profile_fields` function represents
    * the ID of the user whose profile fields are being saved or updated. This function is typically
    * used in WordPress to save custom profile fields for a user.
    * 
    * @return If the current user does not have the capability to edit the user with the given
    * ``, the function will return `false`. Otherwise, it will update the user meta with the
    * key 'isAllowedForCCPayment' based on the value received from the
    * `['isAllowedForCCPayment']` data.
    */
    public function cc_block_save_profile_fields( $user_id ) {

        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }
    

        update_user_meta( $user_id, 'isAllowedForCCPayment', $_POST['isAllowedForCCPayment'] );
    }

   /**
    * The function `user_meta_update_thank_you_page` updates user meta data on the thank you page if
    * the order status is 'processing' and it is the user's first order.
    * 
    * @param orderId The `orderId` parameter in the `user_meta_update_thank_you_page` function
    * represents the unique identifier of the order for which the user meta data is being updated on
    * the thank you page.
    */
    public function user_meta_update_thank_you_page($orderId) { // on thank you page update user meta data
        $orderData = wc_get_order($orderId);
        $orderStatus = $orderData->get_status();
        if($orderStatus == 'processing') {

            $orderUserId = $orderData->get_user_id();
            
            $userNumOfOrder = $this->get_orders_count_from_status($orderUserId); // get number of orders placed by the customer
            // echo $userNumOfOrder;
            if($userNumOfOrder == 1) { // if the number of order is 1 then only update user meta.
                update_user_meta( $orderUserId, 'isAllowedForCCPayment', "on" );
            }
        }
    }

   /**
    * The function `get_orders_count_from_status` returns the count of orders with a specific status
    * for a given user ID in PHP.
    * 
    * @param userId The `userId` parameter is the ID of the customer for whom you want to retrieve the
    * count of orders with a specific status.
    * @param status The `status` parameter in the `get_orders_count_from_status` function is used to
    * specify the status of the orders that you want to count. By default, the status is set to
    * 'processing', but you can provide a different status value when calling the function to count
    * orders with a different status
    * 
    * @return The function `get_orders_count_from_status` returns the count of orders with a specific
    * status ('processing' by default) for a given user ID.
    */
    public function get_orders_count_from_status($userId, $status = 'processing') {

        return count(wc_get_orders( array(
            'customer_id' => $userId,
            'status' => $status,
            'return' => 'ids',
        )));
    }
    
    

}

$WC_CC_Stripe_Settings_Block_Gateway =  new WC_CC_Stripe_Settings_Block_Gateway();
