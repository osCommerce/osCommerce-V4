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

namespace frontend\controllers;

use Yii;
use common\classes\order_total;
use common\classes\payment;
use common\helpers\Translation;

class MultisafeController extends Sceleton
{

    public function actionNotifyCheckout()
    { 
      $languages_id = \Yii::$app->settings->get('languages_id');
      global $order;
      global $order_totals;
      global $payment;

      Translation::init('checkout/process');

      $initial_request = ($_GET['type'] == 'initial');

      if (empty($_GET['transactionid'])) {
          $message = "No transaction ID supplied";
          $url = tep_href_link(
                  FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $payment_module->code . '&error=' . urlencode($message), 'NONSSL', true, false
          );
      } else {
          $payment = 'multisafepay';

          // load selected payment module
          $payment_modules = new payment("multisafepay");

          $payment_module = $GLOBALS[$payment_modules->selected_module];

          $order = new \common\classes\Order($_GET['transactionid']);
          if ($_GET['type'] != 'shipping') {
              //print_r($order);exit;
          }
          $order_status_query = tep_db_query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = '" . tep_db_input($order->info['orders_status']) . "' AND language_id = '" . $languages_id . "'");
          $order_status = tep_db_fetch_array($order_status_query);
          $order->info['order_status'] = $order_status['orders_status_id'];

          $order_total_modules = new order_total();

          // set some globals (expected by osCommerce)
          $customer_id = $order->customer['id'];
          $order_totals = $order->totals;

          // update order status
          $payment_module->order_id = $_GET['transactionid'];
          $transdata = $payment_module->check_transaction();


          if ($payment_module->msp->details['ewallet']['fastcheckout'] == "NO") {
              $status = $payment_module->checkout_notify();
          } else {
              $payment_modules = new payment("multisafepay_fastcheckout");
              $payment_module = $GLOBALS[$payment_modules->selected_module];
              if (method_exists($payment_module, 'checkout_notify')){
                $status = $payment_module->checkout_notify();
              }
          }
          

          if ($payment_module->_customer_id) {
              $hash = $payment_module->get_hash($payment_module->order_id, $payment_module->_customer_id);
              $parameters = 'customer_id=' . $payment_module->_customer_id . '&hash=' . $hash;
          }

          switch ($status) {
              case "initialized":
              case "completed":
                  $message = "OK";
                  $url = tep_href_link("multisafe/success", $parameters, 'NONSSL');
                  break;
              default:
                  $message = "OK"; //"Error #" . $status;
                  $url = tep_href_link(
                          FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $payment_module->code . '&error=' . urlencode($status), 'NONSSL', true, false
                  );
          }
      }

      if ($initial_request) {
          echo "<p><a href=\"" . $url . "\">" . sprintf(MODULE_PAYMENT_MULTISAFEPAY_TEXT_RETURN_TO_SHOP, htmlspecialchars(STORE_NAME)) . "</a></p>";
      } else {
          header("Content-type: text/plain");
          echo $message;
          //tep_redirect($url);
      }
    }
    
    
    public function actionSuccess(){
      global $cart;
      Translation::init('checkout/process');
      
      if (Yii::$app->user->isGuest){
          if ($_GET['multisafepay_order_id'] && $_GET['customer_id'] && $_GET['hash']) {
            if (md5($_GET['multisafepay_order_id'] . $_GET['customer_id']) == $_GET['hash']) {
                $customer_id = $_GET['customer_id'];
                $customer = new \common\components\Customer();
                $customer->loadCustomer($customer_id);
                if ($customer->customers_id){
                    Yii::$app->user->login($customer);
                    $customer_id = $customer->customers_id;
                }
            }
        }
      } else {
          $customer_id =  Yii::$app->user->getId();
      }

      $cart->reset(true);

      tep_session_unregister('sendto');
      tep_session_unregister('billto');
      tep_session_unregister('shipping');
      tep_session_unregister('payment');
      tep_session_unregister('comments');

      if ($customer_id) {
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS));
      } else {
          //For unregistered customer success page shows empty card,
          //so, it's better to show the index page.
          tep_redirect(tep_href_link(FILENAME_DEFAULT));
      }
      
    }
    
  public function actionCancel()
  {
    $languages_id = \Yii::$app->settings->get('languages_id');
    global $order;
    global $order_totals;
    global $payment;

    $payment = 'multisafepay';

    $payment_modules = new payment("multisafepay");
    $payment_module = $GLOBALS[$payment_modules->selected_module];

    $order = new \common\classes\Order($_GET['transactionid']);


    $order_status_query = tep_db_query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = '" . tep_db_input($order->info['orders_status']) . "' AND language_id = '" . $languages_id . "'");
    $order_status = tep_db_fetch_array($order_status_query);
    $order->info['order_status'] = $order_status['orders_status_id'];

    $order_total_modules = new order_total();

    // set some globals (expected by osCommerce)
    $customer_id = $order->customer['id'];
    $order_totals = $order->totals;

    // update order status
    $payment_module->order_id = $_GET['transactionid'];
    $transdata = $payment_module->checkout_notify();


    tep_redirect($payment_module->_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL', false, false));
    
  }
}
