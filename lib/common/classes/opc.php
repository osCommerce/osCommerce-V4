<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\classes;

  class opc {
    static function find_countries( $country_id, $state, $ctrl_name='state' ){
      $result = array();
      if (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') {      
        $result['ctrl'] = tep_draw_input_field($ctrl_name, $state, CHECKOUT_CTLPARAM_COMMON);//'<input type="text" name="state" value="'.$state.'" '.CHECKOUT_CTLPARAM_COMMON.'>';
      }
      $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "'");
      $check = tep_db_fetch_array($check_query);
      $country_has_zones = ($check['total'] > 0);
      $zone_query = tep_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and (zone_name like '" . tep_db_input($state) . "%' or zone_code like '%" . tep_db_input($state) . "%')");
      if (tep_db_num_rows($zone_query) == 1) {
        $zone = tep_db_fetch_array($zone_query);
        $zone_id = $zone['zone_id'];
      } else {
        $zone_id = 0;
      }
      $result['country_id'] = (int)$country_id;
      $result['state'] = $state;
      $result['zone_id'] = $zone_id;
      if ( (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') && $country_has_zones ) {
        $zone_query = tep_db_query("select zone_name as id, zone_name as text from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' order by zone_name");
        $zones = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
        while($za=tep_db_fetch_array($zone_query)) { $zones[]=$za; }
        $result['ctrl'] = tep_draw_pull_down_menu($ctrl_name, $zones, $state, CHECKOUT_CTLPARAM_COMMON);
      }
      return $result;
    }
    
    static function jsshippings($quotes, $free_shipping ) {
      $currencies = \Yii::$container->get('currencies');

      $first = array();
      if (sizeof($quotes) > 1 && sizeof($quotes[0]) > 1) {
/*
        $first[] = array('text' => tep_draw_separator('pixel_trans.gif', '10', '1'));
        $first[] = array('text' => TEXT_CHOOSE_SHIPPING_METHOD,
                         'width' => '50%',
                         'valign' => 'top',
                         'class' => 'main');
        $first[] = array('text' => TITLE_PLEASE_SELECT . '</b><br>' . tep_image(DIR_WS_IMAGES . 'arrow_east_south.gif'),
                         'width' => '50%',
                         'valign' => 'top',
                         'align' => 'right',
                         'class' => 'main');
        $first[] = array('text' => tep_draw_separator('pixel_trans.gif', '10', '1'));
*/
      } elseif ($free_shipping == false) {
        $first[] = array('text' => tep_draw_separator('pixel_trans.gif', '10', '1'));
        $first[] = array('text' => TEXT_ENTER_SHIPPING_INFORMATION,
                         'width' => '100%',
                         'valign' => 'top',
                         'class' => 'main');
        $first[] = array('text' => tep_draw_separator('pixel_trans.gif', '10', '1'));
        $first[] = array('text' => tep_draw_separator('pixel_trans.gif', '10', '1'));
      }
      $result_array = array();
      
      if ($free_shipping == true) {
        $result_array[] = array(array('class' => 'main',
                                      'width' => '100%',
                                      'colspan' => '3',
                                      'text' => $quotes[$i]['icon'] . '<b>' . FREE_SHIPPING_TITLE)
                               );
        $result_array[] = array(
                                array('class' => 'main',
                                      'width' => '100%',
                                      'colspan' => '2',
                                      'text' => sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER))),
                                array('class' => 'main',
                                      'text' => '',
                                      'object' => array('type' => 'hidden',
                                                        'name' => 'shipping',
                                                        'value' => 'free_free'))
                               );
      } else {
        $radio_buttons = 0;
        for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
          $result_array[] = array(array('class' => 'main',
                                        'colspan' => '3',
                                        'text' => ((isset($quotes[$i]['icon']) && tep_not_null($quotes[$i]['icon']))?$quotes[$i]['icon']:'') .'<b>' . $quotes[$i]['module'])
                                 );
          if (isset($quotes[$i]['error'])) {
            $result_array[] = array(array('class' => 'main',
                                          'colspan' => '3',
                                          'text' => $quotes[$i]['error'])
                                   );
          }else{
  /*  <tr class="moduleRow" onmouseover="rowOverEffect_ship(this)" onmouseout="rowOutEffect_ship(this)" onclick="selectRowEffect_ship(this, ' . $radio_buttons . ')"> */
            for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {
              if ( ($n > 1) || ($n2 > 1) ) {
                $result_array[] = array(array('class' => 'main',
                                              'width' => '100%',
                                              'text' => $quotes[$i]['methods'][$j]['title']),
                                        array('class' => 'main',
                                              'text' => $currencies->format(\common\helpers\Tax::add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)))),
                                        array('class' => 'main',
                                              'text' => '',
                                              'id' => $radio_buttons,
                                              'object' => array('type' => 'RADIO',
                                                                'name' => 'shipping',
                                                                'value' => $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'])
                                             )
                                       );
              }else{
                $result_array[] = array(array('class' => 'main',
                                              'width' => '100%',
                                              'text' => $quotes[$i]['methods'][$j]['title']),
                                      //array('text' => tep_draw_separator('pixel_trans.gif', '10', '1')),
                                        array('class' => 'main',
                                              'text' => $currencies->format(\common\helpers\Tax::add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)))),
                                        array('class' => 'main',
                                            //'colspan' => '2',
                                              'text' => '',
                                              'object' => array('type' => 'HIDDEN',
                                              'name' => 'shipping',
                                              'value' => $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'])
                                              )                                 
                                        );
              }
              $radio_buttons++;
            }
          }                          
        }
      }
      return array(
                   'first' => $first,
                   'result_array' => $result_array
                  );
    }
    
    static function cart(){
      global $cart, $languages_id, $order;
      // get cart listing
      $currencies = \Yii::$container->get('currencies');
      
      $cart_content = '';
      ob_start();
      $info_box_contents = array();
      $info_box_contents[0][] = array('params' => 'class="productListing-heading"',
                                      'text' => TABLE_HEADING_PRODUCTS);
      $info_box_contents[0][] = array('align' => 'center',
                                      'params' => 'class="productListing-heading"',
                                      'text' => TABLE_HEADING_QUANTITY);
      $info_box_contents[0][] = array('align' => 'right',
                                      'params' => 'class="productListing-heading"',
                                      'text' => TABLE_HEADING_TOTAL);
      $any_out_of_stock = 0;
      $products = $cart->get_products();
      for ($i=0, $n=sizeof($products); $i<$n; $i++) {
  // Push all attributes information in an array
        if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
            foreach ($products[$i]['attributes'] as $option => $value) {
                echo tep_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
                $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                            from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                            where pa.products_id = '" . (int)$products[$i]['id'] . "'
                                             and pa.options_id = '" . (int)$option . "'
                                             and pa.options_id = popt.products_options_id
                                             and pa.options_values_id = '" . (int)$value . "'
                                             and pa.options_values_id = poval.products_options_values_id
                                             and popt.language_id = '" . (int)$languages_id . "'
                                             and poval.language_id = '" . (int)$languages_id . "'");
                $attributes_values = tep_db_fetch_array($attributes);

                $products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
                $products[$i][$option]['options_values_id'] = $value;
                $products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
                $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
                $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
          }
        }
      }
  
      for ($i=0, $n=sizeof($products); $i<$n; $i++) {
        if (($i/2) == floor($i/2)) {
          $info_box_contents[] = array('params' => 'class="productListing-even"');
        } else {
          $info_box_contents[] = array('params' => 'class="productListing-odd"');
        }
        $cur_row = sizeof($info_box_contents) - 1;
        $products_name = '<table border="0" cellspacing="2" cellpadding="2">' .
                         '  <tr>' .
                         '    <td class="productListing-data" align="center"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">' . tep_image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>' .
                         '    <td class="productListing-data" valign="top"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '"><b>' . $products[$i]['name'] . '</b></a>';
  
        if (STOCK_CHECK == 'true') {
          $stock_check = \common\helpers\Product::check_stock($products[$i]['id'], $products[$i]['quantity']);
          if (tep_not_null($stock_check)) {
            $any_out_of_stock = 1;
            $products_name .= $stock_check;
          }
        }
  
        if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
          foreach ($products[$i]['attributes'] as $option => $value) {
            $products_name .= '<br><small><i> - ' . $products[$i][$option]['products_options_name'] . ' ' . $products[$i][$option]['products_options_values_name'] . '</i></small>';
          }
        }
  
        $products_name .= '    </td>' .
                          '  </tr>' .
                          '</table>';
  
        $info_box_contents[$cur_row][] = array('params' => 'class="productListing-data"',
                                               'text' => $products_name);
        $info_box_contents[$cur_row][] = array('align' => 'center',
                                               'params' => 'class="productListing-data" valign="top"',
                                               'text' => $products[$i]['quantity']);
        $info_box_contents[$cur_row][] = array('align' => 'right',
                                               'params' => 'class="productListing-data" valign="top"',
                                               'text' => '<b>' . $currencies->display_price($products[$i]['final_price'], \common\helpers\Tax::get_tax_rate($products[$i]['tax_class_id'], $order->tax_address['entry_country_id'], $order->tax_address['entry_zone_id']), $products[$i]['quantity']) . '</b>');
      }
      new productListingBox($info_box_contents);
      $cart_content = ob_get_contents();    
      ob_clean();
      $cart_content = preg_replace('/(\s{2,})/',' ',$cart_content);
      return $cart_content;    
    }
    
    static function is_temp_customer( $customer_id ){
      $is_temp = false;
      $data_r = tep_db_query("select opc_temp_account from ".TABLE_CUSTOMERS." where customers_id = '" . (int)$customer_id . "'");
      if( $data = tep_db_fetch_array($data_r) ){
        $is_temp = (int)$data['opc_temp_account']==1;
      }
      return $is_temp;
    }
    static function remove_temp_customer( $customer_id, $reasign_id = 0 ){
      tep_db_query("update " . TABLE_REVIEWS . " set customers_id = null where customers_id = '" . (int)$customer_id . "'");
      tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "'");
      tep_db_query("delete from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
      tep_db_query("delete from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int)$customer_id . "'");
      tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "'");
      tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customer_id . "'");
      tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where customer_id = '" . (int)$customer_id . "'");      
      if ($reasign_id > 0) {
          tep_db_query("update " . TABLE_ORDERS . " set customers_id = '" . (int) $reasign_id . "' where customers_id = '" . (int)$customer_id . "';");
      }
    }
  }
