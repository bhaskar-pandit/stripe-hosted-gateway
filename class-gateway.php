<?php
class Stripe_Hosted_Gateway extends WC_Payment_Gateway {
  
 // Deifine variables
  public $checkoutbuttontext, $testmode, $statement_descriptor, $skipCard,$safe_site_details_raw, $payment_link, $store_code, $max_order_total,$min_order_total,$safe_site_details;     
  public $wpdb, $safe_site_order_stat_data = array(), $stat_data_query, $today_stat_query;
  // Constructor method
  public function __construct() {
    global $wpdb;

    $this->wpdb = $wpdb;
    $this->id = 'stripe_hosted_gateway';
    $this->method_title = 'Stripe Hosted Checkout';
    $this->method_description = 'Customers pay using Stripe Hosted Checkout';
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



    $this->safe_site_details = get_option(
			'woocommerce_stripe_hosted_gateways_settings',
			array(
				array(
					'safe_store_code'   => $this->get_option( 'safe_store_code' ),
					'safe_payment_link' => $this->get_option( 'safe_payment_link' ),
					'cap_amount'      => $this->get_option( 'cap_amount' ),
					'cap_order_count'      => $this->get_option( 'cap_order_count' ),
				),
			)
		);

    $tablePrefix = $this->wpdb->prefix;

    
    // Overall stat data query
    $this->stat_data_query = "SELECT ".$tablePrefix."order_gateway_data.store_code, COUNT(".$tablePrefix."wc_order_stats.order_id) AS site_total_orders, SUM(".$tablePrefix."wc_order_stats.total_amount) AS site_total_order_amount FROM ".$tablePrefix."order_gateway_data INNER JOIN ".$tablePrefix."wc_order_stats ON ".$tablePrefix."order_gateway_data.order_id=".$tablePrefix."wc_order_stats.order_id GROUP BY ".$tablePrefix."order_gateway_data.store_code ORDER BY ".$tablePrefix."order_gateway_data.store_code";

    // Today's stat data query
    $this->today_stat_query = "SELECT ".$tablePrefix."order_gateway_data.store_code, COUNT(".$tablePrefix."wc_order_stats.order_id) AS site_total_orders, SUM(".$tablePrefix."wc_order_stats.total_amount) AS site_total_order_amount FROM ".$tablePrefix."order_gateway_data INNER JOIN ".$tablePrefix."wc_order_stats ON ".$tablePrefix."order_gateway_data.order_id=".$tablePrefix."wc_order_stats.order_id WHERE ".$tablePrefix."order_gateway_data.store_code<>'' AND DATE(".$tablePrefix."order_gateway_data.created_at)=CURDATE() GROUP BY ".$tablePrefix."order_gateway_data.store_code ORDER BY ".$tablePrefix."order_gateway_data.store_code";
    
    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_safe_site_details' ) );

  }
  
  
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
              'title'       => 'Enter the Max Amount',
              'type'        => 'text',
              'description' => 'If user exceed this amount in cart then they cannot place order using credit card.',
              'desc_tip'    => true,
          ),
          'min_order_total' => array(
              'title'       => 'Enter the Min Amount',
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
         
      // Add more settings fields as needed
    );
  }


  	public function generate_safe_site_details_html() {
      ob_start();
      
      $siteStatData = $this->wpdb->get_results($this->stat_data_query);
      $todaySiteStatData = $this->wpdb->get_results($this->today_stat_query);
      
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
    public function save_safe_site_details() {

      $gateways = array();


      if ( isset( $_POST['safe_store_code'] ) && isset( $_POST['safe_payment_link'] ) && isset( $_POST['cap_amount'] ) ) {

        $safe_store_codes    = wc_clean( wp_unslash( $_POST['safe_store_code'] ) );
        $safe_payment_links   = wc_clean( wp_unslash( $_POST['safe_payment_link'] ) );
        $cap_amounts            = wc_clean( wp_unslash( $_POST['cap_amount'] ) );
        $cap_order_counts          = wc_clean( wp_unslash( $_POST['cap_order_count'] ) );
       

        foreach ( $safe_store_codes as $i => $name ) {
          if ( ! isset( $safe_store_codes[ $i ] ) ) {
            continue;
          }

          $gateways[$safe_store_codes[$i]] = array(
            'safe_store_code'      => $safe_store_codes[ $i ],
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



  

 
    
      

        
        
      

  // Process the payment
  public function process_payment($order_id) {

    global $woocommerce, $wpdb;

    $OrderDataRaw = wc_get_order($order_id);
    $OrderDataRaw->update_status( 'cancelled' );
    
    
    $cart_total = $OrderDataRaw->get_total();


    $skipCardMax = ($cart_total > $this->max_order_total)?true:false;
    $skipCardMin = ($cart_total < $this->min_order_total)?true:false;

    $paymentSiteData = $this->get_payment_site_data();
    // print_r($paymentSiteData);
    // exit;

    $paymenturl = $paymentSiteData['safe_payment_link'] ?? "";
    if ($this->testmode) { $TestParam = '&test=yes'; }
    $params = 'AFFID='.$this->store_code.'&id='.$order_id.'&total='.$cart_total .'&currency='.$OrderDataRaw->currency.'&wc_key='.$OrderDataRaw->order_key.$TestParam;

    $storeCode = $paymentSiteData['safe_store_code'] ?? "";
    $encdeParam = $this->encrypt_decrypt($params, 'encrypt');
    $PaymentRedirectUrl = $paymenturl .'?cue='. $encdeParam;
    $OrderDataRaw->add_order_note( 'Payment Link: '.$PaymentRedirectUrl );
    $OrderDataRaw->update_meta_data( 'payment_link', $PaymentRedirectUrl );

    try {
      $dbstatus = $this->wpdb->insert($this->wpdb->prefix.'order_gateway_data', array(
        'order_id' => $order_id,
        'payment_url' => $PaymentRedirectUrl,
        'store_code' => $storeCode,
      ));
      wc_add_notice($this->wpdb->last_query, 'error' ); 
      return;
    } catch (\Throwable $th) {
      wc_add_notice(print_r($th,true), 'error' ); 
      return;
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
      wp_die($error_message);
    }
    

 
  
    return array(
      'result'   => 'success',
      'redirect' => $PaymentRedirectUrl,
    );
  }


  public function get_payment_site_data() {
    $todayAllSiteStatData = $this->wpdb->get_results($this->today_stat_query);

    $allSiteDataArray = array();
    if(!empty($todayAllSiteStatData)) {
      foreach($this->safe_site_details as $data) {
        $todayStatDataKey = array_search($data['safe_store_code'], array_column($todayAllSiteStatData, 'store_code'));
        if($todayStatDataKey !== false) {
          $todayStatData = $todayAllSiteStatData[$todayStatDataKey];
          if($todayStatData->site_total_orders < $data['cap_order_count'] && $todayStatData->site_total_order_amount < $data['cap_amount']) {
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