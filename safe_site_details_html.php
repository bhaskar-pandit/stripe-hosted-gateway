 <tr valign="top">
        <th scope="row" class="titledesc">
          <label>
            <?php esc_html_e( 'Safe Site Details:', 'woocommerce' ); ?>
          </label>
        </th>
        <td class="forminp" id="accounts">
          <div class="wc_input_table_wrapper">
            <table class="widefat wc_input_table sortable" cellspacing="0">
              <thead>
                <tr>
                  <th class="sort" style="width: 3%;">&nbsp;</th>
                  <th style="width: 15%;"><?php esc_html_e( 'Code', 'woocommerce' ); ?></th>
                  <th><?php esc_html_e( 'Payment Link', 'woocommerce' ); ?></th>
                  <th style="width: 15%;"><?php esc_html_e( 'Cap Amount', 'woocommerce' ); ?>(<?=get_woocommerce_currency_symbol()?>)</th>
                  <th style="width: 20%;"><?php esc_html_e( 'Stats', 'woocommerce' ); ?></th>
                  <th style="width: 20%;"><?php esc_html_e( 'Today Stats', 'woocommerce' ); ?></th>
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
                        <td><input type="text" value="<?= esc_attr( $safe_site['safe_payment_link'] ) ?>" name="safe_payment_link[<?= esc_attr( $i ) ?>]" /></td>
                        
                        <td>
                            <input type="text" value="<?= esc_attr( $safe_site['cap_amount'] ) ?>" name="cap_amount[<?= esc_attr( $i ) ?>]" /> <br>
                            
                        </td>
                        <td>
                            <strong>Total Order Price: <?=get_woocommerce_currency_symbol()?><?=number_format(property_exists($safe_site['stat_data'], 'site_total_order_amount') ? $safe_site['stat_data']->site_total_order_amount : 0, 2, ".", "") ?></strong><br>
                            <strong>Total Order Count: <?=$safe_site['stat_data']->site_total_orders ?? 0 ?></strong>
                        </td>
                        <td>
                          <strong>Total Order Price: <?=get_woocommerce_currency_symbol()?><?=number_format(property_exists($safe_site['today_stat_data'], 'site_total_order_amount') ? $safe_site['today_stat_data']->site_total_order_amount : 0, 2, ".", "") ?></strong><br>
                          <strong>Total Order Count: <?=$safe_site['today_stat_data']->site_total_orders ?? 0 ?></strong>
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
          </div>
          <script type="text/javascript">
            jQuery(function() {
              jQuery('#accounts').on( 'click', 'a.add', function(){

                var size = jQuery('#accounts').find('tbody .account').length;

                jQuery('<tr class="account">\
                    <td class="sort"></td>\
                    <td><input type="text" name="safe_store_code[' + size + ']" /></td>\
                    <td><input type="text" name="safe_payment_link[' + size + ']" /></td>\
                    <td><input type="text" name="cap_amount[' + size + ']" /></td>\
                    <td></td>\
                  </tr>').appendTo('#accounts table tbody');

                return false;
              });
            });
          </script>
        </td>
      </tr>