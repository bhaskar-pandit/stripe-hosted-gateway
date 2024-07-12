<?php
/* The `Stripe_Hosted_Gateway` class is a WooCommerce payment gateway that allows customers to pay
using Stripe Hosted Checkout, with features such as test mode, order total limits, safe site
details, and dynamic selection of payment site based on certain criteria. */
class Stripe_Hosted_Gateway extends WC_Payment_Gateway {
  
 // Deifine variables
  public $checkoutbuttontext, $testmode, $statement_descriptor, $skipCard,$safe_site_details_raw, $payment_link, $store_code, $max_order_total,$min_order_total,$safe_site_details,$is_gateway_available_for_all;     
  public $wpdb, $safe_site_order_stat_data = array(), $stat_data_query, $today_stat_query,$db_time;
  // Constructor method
  /**
   * The function is a constructor for a WooCommerce payment gateway plugin for processing payments
   * using Stripe Hosted Checkout with various settings and database queries.
   */
  public function __construct() {
    global $wpdb;

    $this->wpdb = $wpdb;
    $this->id = 'stripe_hosted_gateway';
    $this->method_title = 'Stripe Hosted Checkout';
    $this->method_description = 'Customers pay using Stripe Hosted Checkout';
    $this->icon = plugin_dir_url( __FILE__ ) . '/assets/credit-cards.png'; // URL of the icon that will be displayed on checkout page near your gateway name

    // Other initialization code goes here
    
    $this->init_form_fields();
    $this->init_settings();



    // Load the settings.
    $this->title = $this->get_option( 'title' );
    $this->description = $this->get_option( 'description' );
    $this->enabled = $this->get_option( 'enabled' );

    $this->checkoutbuttontext = $this->get_option( 'checkoutbuttontext' );
    $this->testmode = 'yes' === $this->get_option( 'testmode' );

    $this->statement_descriptor = $this->get_option( 'statement_descriptor' );
    $this->max_order_total = $this->get_option( 'max_order_total' );
    $this->min_order_total = $this->get_option( 'min_order_total' );
    $this->payment_link = $this->get_option( 'payment_link' );
    $this->store_code = $this->get_option( 'store_code' );
    $this->is_login_pop = $this->get_option( 'is_login_pop' );
    $this->is_gateway_available_for_all = $this->get_option( 'is_gateway_available_for_all' );



    $this->safe_site_details = get_option(
			'woocommerce_stripe_hosted_gateways_settings',
			array(
				array(
					'safe_store_code'   => $this->get_option( 'safe_store_code' ),
					'safe_referrer_link' => $this->get_option( 'safe_referrer_link' ),
					'safe_payment_link' => $this->get_option( 'safe_payment_link' ),
					'cap_amount'      => $this->get_option( 'cap_amount' ),
					'cap_order_count'      => $this->get_option( 'cap_order_count' ),
				),
			)
		);

    $tablePrefix = $this->wpdb->prefix;

    
    // Overall stat data query
    $this->stat_data_query = "SELECT ".$tablePrefix."order_gateway_data.store_code, COUNT(".$tablePrefix."wc_order_stats.order_id) AS site_total_orders, SUM(".$tablePrefix."wc_order_stats.total_sales) AS site_total_order_amount FROM ".$tablePrefix."order_gateway_data INNER JOIN ".$tablePrefix."wc_order_stats ON ".$tablePrefix."order_gateway_data.order_id=".$tablePrefix."wc_order_stats.order_id WHERE ".$tablePrefix."order_gateway_data.is_active='1' AND (".$tablePrefix."wc_order_stats.status='wc-processing' OR ".$tablePrefix."wc_order_stats.status='wc-completed') GROUP BY ".$tablePrefix."order_gateway_data.store_code ORDER BY ".$tablePrefix."order_gateway_data.store_code";

    // Today's stat data query
     $this->today_stat_query = "SELECT ".$tablePrefix."order_gateway_data.store_code, COUNT(".$tablePrefix."wc_order_stats.order_id) AS site_total_orders, SUM(".$tablePrefix."wc_order_stats.total_sales) AS site_total_order_amount FROM ".$tablePrefix."order_gateway_data INNER JOIN ".$tablePrefix."wc_order_stats ON ".$tablePrefix."order_gateway_data.order_id=".$tablePrefix."wc_order_stats.order_id WHERE ".$tablePrefix."order_gateway_data.is_active='1' AND ".$tablePrefix."order_gateway_data.store_code<>'' AND DATE(".$tablePrefix."order_gateway_data.created_at)=CURDATE() AND (".$tablePrefix."wc_order_stats.status='wc-processing' OR ".$tablePrefix."wc_order_stats.status='wc-completed') GROUP BY ".$tablePrefix."order_gateway_data.store_code ORDER BY ".$tablePrefix."order_gateway_data.store_code";
     
     
    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_safe_site_details' ) );   


    if($this->is_gateway_available_for_all !== 'yes') { // Checking if the gateway is available for all
      add_action( 'woocommerce_after_checkout_form', array( $this, 'add_code_on_body_open'));  
    }

    
    add_filter( 'woocommerce_available_payment_gateways', array( $this, 'sop_payment_gateway_disable'));
  }
  
  
/**
 * The `init_form_fields` function in PHP initializes an array of form fields with various settings for
 * a payment gateway, including store code, enable/disable options, title, description, test mode,
 * checkout button text, max and min cart amounts, login pop-up option, and more.
 */
  public function init_form_fields() {
    $this->form_fields = array(

          'store_code' => array(
              'title'       => 'Store Code',
              'type'        => 'text',
              'description' => 'Dont Change This value. This Value Wiil Provide By Your Developer',
          ),
          'enabled' => array(
              'title'       => 'Enable/Disable',
              'label'       => 'Enable Stripe Hosted Gateway',
              'type'        => 'checkbox',
              'description' => '',
              'default'     => 'no'
          ),
          'title' => array(
              'title'       => 'Title',
              'type'        => 'text',
              'description' => 'This controls the title which the user sees during checkout.',
              'default'     => 'Pay With Debit/Credit Cards',
              'desc_tip'    => true,
          ),
          'description' => array(
              'title'       => 'Description',
              'type'        => 'textarea',
              'description' => 'This controls the description which the user sees during checkout.',
              'default'     => 'Pay With Debit/Credit Cards.',
          ),
          'testmode' => array(
              'title'       => 'Test mode',
              'label'       => 'Enable Test Mode',
              'type'        => 'checkbox',
              'description' => 'Place the payment gateway in test mode using test API keys.',
              'default'     => 'yes',
              'desc_tip'    => true,
          ),
           'checkoutbuttontext' => array(
              'title'       => 'Checkout Button Text',
              'type'        => 'text',
              'description' => 'This controls the Checkout Button Text which the user sees during checkout. If it left blank then it will show the default text.',
              'default'     => 'Proceed to Stripe',
              'desc_tip'    => true,
          ),
         
          'max_order_total' => array(
              'title'       => 'Enter the Max Cart Amount',
              'type'        => 'text',
              'description' => 'If user exceed this amount in cart then they cannot place order using credit card.',
              'desc_tip'    => true,
          ),
          'min_order_total' => array(
              'title'       => 'Enter the Min Cart Amount',
              'type'        => 'text',
              'description' => 'If user dont add this amount in cart then they cannot place order using credit card.',
              'desc_tip'    => true,
          ),
          // 'cap_type'        => array(
          //     'title'         => __( 'Load Balancing Cap Type', 'woocommerce' ),
          //     'type'          => 'select',
          //     'default'       => 'price',
          //     'options'       => array(
          //       'price'       => __( 'Price', 'woocommerce' ),
          //       'Percentage'  => __( 'Percentage', 'woocommerce' ),
          //     ),
          // ),
          'safe_site_details' => array(
            'type' => 'safe_site_details',
          ),
          'is_login_pop' => array(
              'title'       => 'Login Pop-Up',
              'label'       => 'Do you Want to show login Pop-Up?',
              'type'        => 'checkbox',
              'default'     => 'yes',
              'desc_tip'    => true,
          ),
          'is_gateway_available_for_all' => array(
              'title'       => 'Available for all',
              'label'       => 'Do you want to make the gateway available for all user?',
              'type'        => 'checkbox',
              'default'     => 'no',
              'desc_tip'    => true,
          ),
         
      // Add more settings fields as needed
    );
  }


  	/**
     * The function generates HTML content for safe site details by fetching data from database queries
     * and populating an array with the results.
     * 
     * @return The function `generate_safe_site_details_html()` is returning the HTML content generated
     * by including the "safe_site_details_html.php" file after populating the
     * `->safe_site_order_stat_data` array with data from the `` and
     * `` results.
     */
    public function generate_safe_site_details_html() {
      ob_start();
      
      $siteStatData = $this->wpdb->get_results($this->stat_data_query);
      $todaySiteStatData = $this->wpdb->get_results($this->today_stat_query);
      $timeNow = $this->wpdb->get_results('SELECT NOW() AS now, DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 0 HOUR AS tomorrow, TIMEDIFF(DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 0 HOUR, NOW()) AS time_remaining;');
      $this->db_time = $timeNow[0];


      foreach($this->safe_site_details as $data) {
        $statDataKey = array_search($data['safe_store_code'], array_column($siteStatData, 'store_code'));
        $todayStatDataKey = array_search($data['safe_store_code'], array_column($todaySiteStatData, 'store_code'));

        if($statDataKey !== false) {
          $statData = $siteStatData[$statDataKey];
        }
        else {
          $statData = (object)array();
        }
        
        if($todayStatDataKey !== false) {
          $todayStatData = $todaySiteStatData[$todayStatDataKey];
        }
        else {
          $todayStatData = (object)array();
        }
        $data['stat_data'] = $statData;
        $data['today_stat_data'] = $todayStatData;
        array_push($this->safe_site_order_stat_data, $data);
      }

      require_once "safe_site_details_html.php";
      return ob_get_clean();
    }

    /**
     * Save account details table.
     */
    /**
     * The function `save_safe_site_details` saves safe site details entered via POST request into an
     * array and updates the WooCommerce option for hosted gateways settings.
     */
    public function save_safe_site_details() {

      $gateways = array();


      if ( isset( $_POST['safe_store_code'] ) && isset( $_POST['safe_payment_link'] ) && isset( $_POST['safe_referrer_link'] ) && isset( $_POST['cap_amount'] ) ) {

        $safe_store_codes     = wc_clean( wp_unslash( $_POST['safe_store_code'] ) );
        $safe_referrer_links  = wc_clean( wp_unslash( $_POST['safe_referrer_link'] ) );
        $safe_payment_links   = wc_clean( wp_unslash( $_POST['safe_payment_link'] ) );
        $cap_amounts          = wc_clean( wp_unslash( $_POST['cap_amount'] ) );
        $cap_order_counts     = wc_clean( wp_unslash( $_POST['cap_order_count'] ) );
       

        foreach ( $safe_store_codes as $i => $name ) {
          if ( ! isset( $safe_store_codes[ $i ] ) ) {
            continue;
          }

          $gateways[$safe_store_codes[$i]] = array(
            'safe_store_code'      => $safe_store_codes[ $i ],
            'safe_referrer_link'    => $safe_referrer_links[ $i ],
            'safe_payment_link'    => $safe_payment_links[ $i ],
            'cap_amount'        => $cap_amounts[ $i ],
            'cap_order_count'        => $cap_order_counts[ $i ],
          );
        }
      }
      // phpcs:enable
  
      do_action( 'woocommerce_update_option', array( 'id' => 'woocommerce_stripe_hosted_gateways_settings' ) );
      update_option( 'woocommerce_stripe_hosted_gateways_settings', $gateways );
    }



  

 
    
      
/**
 * The `payment_fields` function in PHP displays a description before the payment form, including
 * handling test mode instructions.
 */
	public function payment_fields() {

      // ok, let's display some description before the payment form
      if ( $this->description ) {
          // you can instructions for test mode, I mean test card numbers etc.
          if ( $this->testmode ) {
              $this->description .= '<br><br><h3>TEST MODE ENABLED.</h3>';
              $this->description  = trim( $this->description );
          }
          // display the description with <p> tags etc.
          echo wpautop( wp_kses_post( $this->description ) );
      }
  }
        
        
      

  // Process the payment
  /**
   * The function `process_payment` in PHP processes a payment for a WooCommerce order, generates a
   * payment link, and handles error messages based on order total.
   * 
   * @param order_id The `order_id` parameter in the `process_payment` function represents the unique
   * identifier of the order for which the payment is being processed. It is used to retrieve order
   * details and update the order status accordingly during the payment processing flow.
   * 
   * @return An array is being returned with the keys 'result' and 'redirect'. The 'result' key has a
   * value of 'success' and the 'redirect' key has a value of the payment redirect URL.
   */
  public function process_payment($order_id) {

    global $woocommerce, $wpdb;

    $OrderDataRaw = wc_get_order($order_id);
    $OrderDataRaw->update_status( 'pending' );
    
    
    $cart_total = $OrderDataRaw->get_total();


    $skipCardMax = ($cart_total > $this->max_order_total)?true:false;
    $skipCardMin = ($cart_total < $this->min_order_total)?true:false;

    $paymentSiteData = $this->get_payment_site_data();
    

    $paymenturl = $paymentSiteData['safe_payment_link'] ?? "";
    $referrerUrl = $paymentSiteData['safe_referrer_link'] ?? "";
    if ($this->testmode) { $TestParam = '&test=yes'; }
    $params = 'AFFID='.$this->store_code.'&id='.$order_id.'&total='.$cart_total .'&currency='.$OrderDataRaw->currency.'&wc_key='.$OrderDataRaw->order_key.'&pu='.$paymenturl.$TestParam;
  
    $storeCode = $paymentSiteData['safe_store_code'] ?? "";
    $encdeParam = $this->encrypt_decrypt($params, 'encrypt');
    $PaymentRedirectUrl = $referrerUrl .'?cue='. $encdeParam;
    $OrderDataRaw->add_order_note( 'Payment Link: '.$PaymentRedirectUrl );
    $OrderDataRaw->add_order_note( 'Payment Link Code: '.$storeCode );
    $OrderDataRaw->update_meta_data( 'payment_link', $PaymentRedirectUrl );

    try {
      // Check if order ID exist and update if exists.
      $this->wpdb->update($this->wpdb->prefix."order_gateway_data",
        array(
          'is_active' => '0'
        ),
        array(
          'order_id' => $order_id
        ),
      );

      $insertedRowID = $this->wpdb->insert($this->wpdb->prefix.'order_gateway_data', array(
        'order_id' => $order_id,
        'payment_url' => $PaymentRedirectUrl,
        'store_code' => $storeCode,
      ));

    } catch (\Throwable $th) {
      
    }
   
    if($skipCardMax){ 
        $error_message = "Order Total must be less than ".$this->max_order_total;
        
    }
    if ($skipCardMin) {
       $error_message = "Order Total must be greater than ".$this->min_order_total;
    }

    if ($skipCardMax || $skipCardMin) {
      $OrderDataRaw->update_status( 'on-hold' );
      $OrderDataRaw->add_order_note( 'Process Payment using this link: '.$PaymentRedirectUrl );
      
      wc_add_notice($error_message, 'error' ); 
      return;
    }
    

 
  
    return array(
      'result'   => 'success',
      'redirect' => $PaymentRedirectUrl,
    );
  }


/**
 * The function `get_payment_site_data` retrieves and processes payment site data for safe sites based
 * on certain conditions and returns a randomly selected site's data.
 * 
 * @return The `get_payment_site_data` function returns the data of a payment site that meets certain
 * conditions based on the provided input data. The function processes the input data to determine the
 * payment site with remaining order amount and count based on specific criteria. The selected payment
 * site data is then returned as an array containing information such as today's order amount, today's
 * order count, remaining order amount, and remaining order
 */
  public function get_payment_site_data() {
    $todayAllSiteStatData = $this->wpdb->get_results($this->today_stat_query);
    $allSiteDataArray = array();
    if(!empty($todayAllSiteStatData)) {
      foreach($this->safe_site_details as $data) {
        $todayStatDataKey = array_search($data['safe_store_code'], array_column($todayAllSiteStatData, 'store_code'));
        if($todayStatDataKey !== false) {
          $todayStatData = $todayAllSiteStatData[$todayStatDataKey];
          // if($todayStatData->site_total_orders < $data['cap_order_count'] && $todayStatData->site_total_order_amount < $data['cap_amount']) {
          if(($todayStatData->site_total_orders < $data['cap_order_count'] && $todayStatData->site_total_order_amount < $data['cap_amount']) 
          || ($data['cap_order_count'] === '' && $todayStatData->site_total_order_amount < $data['cap_amount']
          || $data['cap_amount'] === '' && $todayStatData->site_total_orders < $data['cap_order_count'])) {


            $data['today_order_amount'] = $todayStatData->site_total_order_amount;
            $data['today_order_count'] = $todayStatData->site_total_orders;
            
            $data['remain_order_amount'] = ((float) $data['cap_amount'] - (float) $todayStatData->site_total_order_amount);
            $data['remain_order_count'] = ((float) $data['cap_order_count'] - (float) $todayStatData->site_total_orders);
            // $data['today_stat_data'] = $todayStatData;
            array_push($allSiteDataArray, $data);
          }
          // else {
          //   $data['today_order_amount'] = 0;
          //   $data['today_order_count'] = 0;

          //   $data['remain_order_amount'] = ((float) $data['cap_amount'] - 0);
          //   $data['remain_order_count'] = ((float) $data['cap_order_count'] - 0);
          // }
        }
        else {
          $data['today_order_amount'] = 0;
          $data['today_order_count'] = 0;

          $data['remain_order_amount'] = ((float) $data['cap_amount'] - 0);
          $data['remain_order_count'] = ((float) $data['cap_order_count'] - 0);
          array_push($allSiteDataArray, $data);
        }
      }
    }
    else {
      foreach($this->safe_site_details as $data) {
        $data['today_order_amount'] = 0;
        $data['today_order_count'] = 0;

        $data['remain_order_amount'] = ((float) $data['cap_amount'] - 0);
        $data['remain_order_count'] = ((float) $data['cap_order_count'] - 0);
        array_push($allSiteDataArray, $data);
      }
    }

    // uasort($allSiteDataArray, fn($a, $b) => $b['remain_order_amount'] <=> $a['remain_order_amount']);
    // $newStatArr = $allSiteDataArray;
    // print_r(array_shift($newStatArr));
    // exit;
    // $paymentSiteData = array_shift($newStatArr);

    $siteArrayLength = sizeof($allSiteDataArray);
    $randNum = $siteArrayLength > 0 ? rand(0 , ($siteArrayLength-1)) : 0;
    $paymentSiteData = $allSiteDataArray[$randNum];

    return $paymentSiteData;
  }


/**
 * The function `add_code_on_body_open` checks if a user is not logged in, on the checkout page, and a
 * specific setting is enabled, then it enqueues styles and scripts, and displays a custom login popup
 * with options to login or skip.
 */
  public function add_code_on_body_open() {
		if ( !is_user_logged_in() && is_checkout() && $this->is_login_pop == 'yes' ) {
			wp_enqueue_style( 'cc-styles-css', plugin_dir_url( __FILE__ ) .'assets/styles.css',false,time(),'all'); 
			wp_enqueue_script( 'cc-script-js', plugin_dir_url( __FILE__ ) .'assets/script.js',false,time(),'all'); 
			echo '<div class="custom-model-pop custom-model-login bg-overlay" >
							<div class="custom-model-inner">        
							<!-- <div class="close-btn">×</div> -->
								<div class="custom-model-wrap">
									<div class="pop-up-content-wrap">
										<!-- If you already have account with then please login with that info <a href="'.get_permalink( get_option('woocommerce_myaccount_page_id') ).'">here</a> to get the Credit Card option. -->
                                        If you already have an exising account and would like to pay by credit card please login here
										<div>
											<a class="login-btn" href="'.get_permalink( get_option('woocommerce_myaccount_page_id') ).'">Login</a> 
											<a class="nothanks-btn">No Thanks</a>
										</div>

									</div>                                
								</div>  
							</div>  
						</div>';
		}
	}


/* The above PHP code defines a function named `sop_payment_gateway_disable` that takes an array of
available payment gateways as input. The function is intended to disable certain payment gateways
based on specific conditions or criteria. However, the actual logic for disabling the gateways is
not provided in the code snippet you shared. */
  function sop_payment_gateway_disable( $available_gateways ) {
      if (is_checkout()) {
       
        $userData = wp_get_current_user();
        $isAllowedForCCPayment = esc_attr(get_the_author_meta('isAllowedForCCPayment', $userData->data->ID ));
        if(!is_user_logged_in()){$isAllowedForCCPayment =  "";}

        $paymentSiteData = $this->get_payment_site_data();

         if($this->is_gateway_available_for_all !== 'yes') { // Checking if the gateway is available for all
          if ($isAllowedForCCPayment !== 'on') {
            unset( $available_gateways['stripe_hosted_gateway'] );
          }
        }  

        if (!$paymentSiteData) {
            unset( $available_gateways['stripe_hosted_gateway'] );
        }


        // echo "<pre style='margin-left: 15%;margin-top: 4%;'>";
        // print_r($paymentSiteData);
        // echo "</pre>";

        
        return $available_gateways;
         
      }

      
    }
	





/**
 * The function `encrypt_decrypt` in PHP performs encryption and decryption using AES-256-CBC with a
 * user-defined private key and secret key.
 * 
 * @param string The `encrypt_decrypt` function you provided is used for encrypting and decrypting a
 * string using AES-256-CBC encryption algorithm.
 * @param action The `action` parameter in the `encrypt_decrypt` function determines whether the input
 * string should be encrypted or decrypted. If the `action` is set to 'encrypt', the function will
 * encrypt the input string. If the `action` is set to 'decrypt', the function will decrypt the input
 * string
 * 
 * @return The `encrypt_decrypt` function takes a string and an action (either 'encrypt' or 'decrypt')
 * as input parameters. It uses AES-256-CBC encryption method with a secret key and initialization
 * vector (IV) derived from user-defined keys.
 */
  public function encrypt_decrypt($string, $action = 'encrypt')
  {
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'AUJRDMGNSAMBJTTVUJNNGMCLC';      // user define private key
    $secret_iv = 'bFArbEMzzguOOnN';                 // user define secret key
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
    if ($action == 'encrypt') {
      $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
      $output = base64_encode($output);
    } else if ($action == 'decrypt') {
      $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
  }
  
}




?>