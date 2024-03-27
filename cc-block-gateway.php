<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Settings_Block_Gateway {

  
    public function __construct() {
        
        
        add_action( 'show_user_profile', array( $this, 'cc_block_extra_profile_field' ), 30);    
        add_action( 'edit_user_profile', array( $this, 'cc_block_extra_profile_field' ), 30 );        


        add_action( 'personal_options_update', array( $this, 'cc_block_save_profile_fields'));
        add_action( 'edit_user_profile_update', array( $this, 'cc_block_save_profile_fields'));
		    

    }
    

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

    public function cc_block_extra_profile_field( $user ) {
        $isAllowedForCCPayment = esc_attr(get_the_author_meta( 'isAllowedForCCPayment', $user->ID ));
        ?>
            <br>
            <br>
            <input type="checkbox" id="isAllowedForCCPayment" name="isAllowedForCCPayment" class="regular-text" <?php if($isAllowedForCCPayment == 'on'){ echo 'checked'; }?> />
            <label for="isAllowedForCCPayment" style="font-size: 1em; font-weight: bold;"><?php _e("Is Customer Allowed For CC Payment?"); ?></label>
        <?php
    }

    
    public function cc_block_save_profile_fields( $user_id ) {

        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }
    

        update_user_meta( $user_id, 'isAllowedForCCPayment', $_POST['isAllowedForCCPayment'] );
    }
    
    

}

$WC_Settings_Block_Gateway =  new WC_Settings_Block_Gateway();
