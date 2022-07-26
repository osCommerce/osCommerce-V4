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

namespace backend\controllers;

use Yii;

/**
 * WhosOnline controller
 */
class WhosOnlineController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_HEADING_WHOS_ONLINE'];

    public function actionIndex() {
        
        $this->selectedMenu = array('settings', 'tools', 'whos-online');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('whos-online/'), 'title' => BOX_HEADING_WHOS_ONLINE);
        $this->view->headingTitle = BOX_HEADING_WHOS_ONLINE;

        $this->view->usersTable = array(
            array(
                'title' => TABLE_HEADING_PLATFORM,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_ONLINE,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_CUSTOMER_ID,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_FULL_NAME,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_IP_ADDRESS,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_ENTRY_TIME,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_LAST_CLICK,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_LAST_PAGE_URL,
                'not_important' => 0
            ),
        );

        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int)Yii::$app->request->get('row', 0);

        return $this->render('index', ['usersPath' => '', 'forget' => '']);
    }

    public function actionList() {
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $responseList = [];
        if ($length == -1)
            $length = 10000;
        $query_numrows = 0;

        //---
        // customer_id 	full_name 	session_id 	ip_address 	time_entry 	time_last_click 	last_page_url 
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " where full_name like '%" . $keywords . "%' or last_page_url like '%" . $keywords . "%' ";
        } else {
            $search_condition = " where 1 ";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "time_entry " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 1:
                    $orderBy = "customer_id " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 2:
                    $orderBy = "full_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "time_entry";
                    break;
            }
        } else {
            $orderBy = " time_entry ";
        }

        $platforms = [];
        $platforms[0] = '';
        $platforms_query = tep_db_query("select * from " . TABLE_PLATFORMS . " where 1");
        while ($record = tep_db_fetch_array($platforms_query)) {
            $platforms[$record['platform_id']] = $record['platform_name'];
        }
        
        $whos_online_query = "select customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id, platform_id from " . TABLE_WHOS_ONLINE . " $search_condition order by $orderBy";

        $current_page_number = ( $start / $length ) + 1;
        $_split = new \splitPageResults($current_page_number, $length, $whos_online_query, $query_numrows);
        $whos_query = tep_db_query($whos_online_query);

        while ($whos_online = tep_db_fetch_array($whos_query)) {

            $time_online = (time() - $whos_online['time_entry']);
            
            $url = '';
            if (preg_match('/^(.*)' . tep_session_name() . '=[a-f,0-9]+[&]*(.*)/i', $whos_online['last_page_url'], $array)) { 
                $url =  $array[1] . $array[2]; 
            } else {
                $url = $whos_online['last_page_url']; 
            }
            
            $responseList[] = array(
                $platforms[$whos_online['platform_id']] . '<input class="cell_identify" type="hidden" value="' . $whos_online['session_id'] . '">',
                $time_online,
                $whos_online['customer_id'],
                $whos_online['full_name'],
                $whos_online['ip_address'],
                date('H:i:s', $whos_online['time_entry']),
                date('H:i:s', $whos_online['time_last_click']),
                $url,
                
            );
        }

        //---

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $query_numrows,
            'recordsFiltered' => $query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    private function unserializesession($data) {
        $vars=preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff^|]*)\|/',
                  $data,-1,PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        for($i=0; @$vars[$i]; $i++) $result[$vars[$i++]]=unserialize($vars[$i]);
        return $result;
    }
    
    function actionItempreedit() {
        $this->layout = false;
        
        \common\helpers\Translation::init('admin/whos-online');
        
        $info = Yii::$app->request->post( 'item_id' );
        
        if (true || STORE_SESSIONS == 'mysql') {
            $session_data = tep_db_query("select value from " . TABLE_SESSIONS . " WHERE sesskey = '" . $info . "'");
            $session_data = tep_db_fetch_array($session_data);
            $session_data = trim($session_data['value']);
        } else {
            if ((file_exists(tep_session_save_path() . '/sess_' . $info)) && (filesize(tep_session_save_path() . '/sess_' . $info) > 0)) {
                $session_data = file(tep_session_save_path() . '/sess_' . $info);
                $session_data = trim(implode('', $session_data));
            }
        }
        
        if ($length = strlen($session_data)) {
                $start_id = strpos($session_data, 'customer_id|s');
                $start_cart = strpos($session_data, 'cart|O');
                $start_currency = strpos($session_data, 'currency|s');
                $start_country = strpos($session_data, 'customer_country_id|s');
                $start_currency_id = strpos($session_data, 'currency_id|s');
                $start_zone = strpos($session_data, 'customer_zone_id|s');
            
            for ($i = $start_cart; $i < $length; $i++) {
                if ($session_data[$i] == '{') {
                    if (isset($tag)) {
                        $tag++;
                    } else {
                        $tag = 1;
                    }
                } elseif ($session_data[$i] == '}') {
                    $tag--;
                } elseif ((isset($tag)) && ($tag < 1)) {
                    break;
                }
            }

            $session_data_id = substr($session_data, $start_id, (strpos($session_data, ';', $start_id) - $start_id + 1));
            $session_data_cart = substr($session_data, $start_cart, $i);
            $session_data_currency = substr($session_data, $start_currency, (strpos($session_data, ';', $start_currency) - $start_currency + 1));
            $session_data_currency_id = substr($session_data, $start_currency_id, (strpos($session_data, ';', $start_currency_id) - $start_currency_id + 1));
            $session_data_country = substr($session_data, $start_country, (strpos($session_data, ';', $start_country) - $start_country + 1));
            $session_data_zone = substr($session_data, $start_zone, (strpos($session_data, ';', $start_zone) - $start_zone + 1));

            //session_decode($session_data_id);//customer_id
            //session_decode($session_data_currency);//currency
            //session_decode($session_data_country);//customer_country_id
            //session_decode($session_data_currency_id);//currency_id
            //session_decode($session_data_zone);//customer_zone_id
            //session_decode($session_data_cart);
            $data = $this->unserializesession($session_data_cart);
            $cart = @$data['cart'];
            $data = $this->unserializesession($session_data_currency);
            $currency = @$data['currency'];

            if (is_object($cart)) {
                $products = $cart->get_products();

                for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
                    $text = $products[$i]['quantity'] . ' x ' . $products[$i]['name'];
                    if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
                        if (is_array($products[$i]['attributes'])) foreach ($products[$i]['attributes'] as $option => $value) {

                            $attributes = tep_db_query("select pa.products_attributes_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                      from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                      where pa.products_id = '" . (int) $products[$i]['id'] . "'
                                       and pa.options_id = '" . $option . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . $value . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . $languages_id . "'
                                       and poval.language_id = '" . $languages_id . "'");
                            $attributes_data = tep_db_fetch_array($attributes);
                            $text .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;<small><i> - ' . $attributes_data['products_options_name'] . ' ' . $attributes_data['products_options_values_name'] . '</i></small>';
                        }
                    }

                    echo $text . '<br>';
                }

                if (sizeof($products) > 0) {
                    $currencies = \Yii::$container->get('currencies');
                    echo '<br>' . TEXT_SHOPPING_CART_SUBTOTAL . ' ' . $currencies->format($cart->total, true, $currency);
                }
            }
        }
    }

}
