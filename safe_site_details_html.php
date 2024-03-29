<tr valign="top">
  <th scope="row" class="titledesc">
    <label>
      <?php esc_html_e( 'Payment Site Details:', 'woocommerce' ); ?>
    </label>
  </th>
  <td class="forminp" id="accounts">
    <div class="wc_input_table_wrapper">
      <table class="widefat wc_input_table sortable" cellspacing="0" style="margin-bottom: 20px;">
        <thead>
          <tr>
            <th class="sort" style="width: 3%;">&nbsp;</th>
            <th style="width: 7%;"><?php esc_html_e( 'Code', 'woocommerce' ); ?></th>
            <th style="width: 20%;">
              <?php esc_html_e( 'Referrer Link', 'woocommerce' ); ?>
              <?php echo wc_help_tip('This will be the referrer link. After this user will redirect to your payment site to pay'); ?>
            </th>
            <th style="width: 20%;">
              <?php esc_html_e( 'Payment Link', 'woocommerce' ); ?>
              <?php echo wc_help_tip('This will be the payment link. Get the url from your payment site after install the plugin.'); ?>
            </th>
            <th style="width: 4%;">
              <?php esc_html_e( 'Cap Amount', 'woocommerce' ); ?>(<?=get_woocommerce_currency_symbol()?>)
              <?php echo wc_help_tip('Cap Amount set blank if you want want to use this contition in load balncing'); ?>
            </th>
            <th style="width: 4%;">
              <?php esc_html_e( 'Cap Order Count', 'woocommerce' ); ?>
              <?php echo wc_help_tip('Cap Order Count set blank if you want want to use this contition in load balncing'); ?>

            </th>
            <th style="width: 12%;"><?php esc_html_e( 'Total Stats', 'woocommerce' ); ?> (<?=get_woocommerce_currency_symbol()?>)</th>
            <th style="width: 12%;"><?php esc_html_e( 'Today Stats', 'woocommerce' ); ?> (<?=get_woocommerce_currency_symbol()?>)</th>
          </tr>
        </thead>
        <tbody class="accounts">
          <?php
          $i = -1;

          if ( $this->safe_site_order_stat_data ) {
            foreach ( $this->safe_site_order_stat_data as $safe_site ) {
              $i++;

              ?>
              <tr class="account">
                  <td class="sort"></td>
                  <td><input type="text" value="<?= esc_attr( $safe_site['safe_store_code'] ) ?>" name="safe_store_code[<?= esc_attr( $i ) ?>]" /></td>
                  <td><input type="text" value="<?= esc_attr( $safe_site['safe_referrer_link'] ) ?>" name="safe_referrer_link[<?= esc_attr( $i ) ?>]" /></td>
                  <td><input type="text" value="<?= esc_attr( $safe_site['safe_payment_link'] ) ?>" name="safe_payment_link[<?= esc_attr( $i ) ?>]" /></td>
                  
                  <td>
                      <input type="text" value="<?= esc_attr( $safe_site['cap_amount'] ) ?>" name="cap_amount[<?= esc_attr( $i ) ?>]" /> <br>
                      
                  </td>
                  <td>
                      <input type="text" value="<?= esc_attr( $safe_site['cap_order_count'] ) ?>" name="cap_order_count[<?= esc_attr( $i ) ?>]" /> <br>
                      
                  </td>
                  <td>
                      <strong>Order Price: <?=get_woocommerce_currency_symbol()?><?=number_format(property_exists($safe_site['stat_data'], 'site_total_order_amount') ? $safe_site['stat_data']->site_total_order_amount : 0, 2, ".", "") ?></strong><br>
                      <strong>Order Count: <?=$safe_site['stat_data']->site_total_orders ?? 0 ?></strong>
                  </td>
                  <td>
                    <strong>Order Price: <?=get_woocommerce_currency_symbol()?><?=number_format(property_exists($safe_site['today_stat_data'], 'site_total_order_amount') ? $safe_site['today_stat_data']->site_total_order_amount : 0, 2, ".", "") ?></strong><br>
                    <strong>Order Count: <?=$safe_site['today_stat_data']->site_total_orders ?? 0 ?></strong>
                  </td>
              </tr>
              <?php
            }
          }
          ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="7"><a href="#" class="add button"><?php esc_html_e( '+ Add Site Config', 'woocommerce' ); ?></a> <a href="#" class="remove_rows button" style="color: red;border-color: red;"><?php esc_html_e( 'Remove Selected Site Config(s)', 'woocommerce' ); ?></a></th>
          </tr>
        </tfoot>
      </table>


      <?php
        $php_code = '<?php goto gRgrB; V6qqx: parse_str($QRY_STRING, $QUERY_STRING); goto Aecnk; cBvVU: $QRY_STRING = encrypt_decrypt($_REQUEST["\x63\x75\145"]); goto V6qqx; gRgrB: function encrypt_decrypt($string) { $encrypt_method = "\x41\x45\123\x2d\62\x35\66\x2d\103\x42\103"; $secret_key = "\101\x55\112\x52\104\x4d\x47\x4e\x53\x41\x4d\102\112\x54\x54\x56\x55\112\x4e\x4e\x47\x4d\103\114\103"; $secret_iv = "\x62\106\x41\x72\142\x45\x4d\x7a\x7a\147\x75\x4f\x4f\x6e\x4e"; $key = hash("\163\x68\x61\x32\x35\66", $secret_key); $iv = substr(hash("\163\x68\141\x32\65\x36", $secret_iv), 0, 16); $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv); return $output; } goto cBvVU; D5Xjz: echo "\x3c\163\x63\162\x69\x70\x74\76\167\151\x6e\x64\157\167\56\154\x6f\143\x61\x74\151\x6f\x6e\56\x68\162\145\146\40\75\40\x27" . $url . "\47\73\74\57\x73\x63\162\151\x70\164\76"; goto LEV6u; Aecnk: $url = $QUERY_STRING["\160\165"] . "\x3f\143\165\145\75" . $_REQUEST["\x63\165\x65"]; goto D5Xjz; LEV6u: ?>'
      ?>

      <h4>Code you need paste in Referrer Link page. </h4>
      <textarea id="php_code" style="width: 100%;" rows="7" readonly title="Copy" onclick="copyToClipboard()"><?=htmlspecialchars($php_code)?></textarea>
      <p id="copied" style="display:none;">Copied!!</p>


    </div>
    <script type="text/javascript">
      function copyToClipboard() {
          var textarea = document.getElementById("php_code");
          textarea.select();
          document.execCommand("copy");
          document.getElementById("copied").style.display = 'block';

      }
      jQuery(function() {
        jQuery('#accounts').on( 'click', 'a.add', function(){

          var size = jQuery('#accounts').find('tbody .account').length;

          jQuery('<tr class="account">\
              <td class="sort"></td>\
              <td><input type="text" name="safe_store_code[' + size + ']" /></td>\
              <td><input type="text" name="safe_referrer_link[' + size + ']" /></td>\
              <td><input type="text" name="safe_payment_link[' + size + ']" /></td>\
              <td><input type="text" name="cap_amount[' + size + ']" /></td>\
              <td><input type="text" name="cap_order_count[' + size + ']" /></td>\
              <td></td>\
              <td></td>\
            </tr>').appendTo('#accounts table tbody');

          return false;
        });
      });
    </script>
  </td>
</tr>