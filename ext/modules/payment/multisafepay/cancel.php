<?php

/*
  MultiSafepay Payment Module for osCommerce 
  http://www.multisafepay.com

  Copyright (C) 2008 MultiSafepay.com
 */

chdir("../../../../");
require("includes/application_top.php");

//require(DIR_WS_CLASSES . "payment.php");
$payment_modules = new \common\classes\payment("multisafepay");
$payment_module = $GLOBALS[$payment_modules->selected_module];

//require(DIR_WS_CLASSES . "order.php");
$order = new \common\classes\Order($_GET['transactionid']);


$order_status_query = tep_db_query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = '" . $order->info['orders_status'] . "' AND language_id = '" . $languages_id . "'");
$order_status = tep_db_fetch_array($order_status_query);
$order->info['order_status'] = $order_status['orders_status_id'];

//require(DIR_WS_CLASSES . "order_total.php");
$order_total_modules = new \common\classes\order_total();

// set some globals (expected by osCommerce)
$customer_id = $order->customer['id'];
$order_totals = $order->totals;

// update order status
$payment_module->order_id = $_GET['transactionid'];
$transdata = $payment_module->checkout_notify();


tep_redirect($payment_module->_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL', false, false));
