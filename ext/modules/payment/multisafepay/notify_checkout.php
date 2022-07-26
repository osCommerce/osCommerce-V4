<?php

/*
  MultiSafepay Payment Module for osCommerce
  http://www.multisafepay.com

  Copyright (C) 2008 MultiSafepay.com

 */

chdir("../../../../");
require("includes/application_top.php");
\common\helpers\Translation::init('checkout/process');
$initial_request = ($_GET['type'] == 'initial');

if (empty($_GET['transactionid'])) {
    $message = "No transaction ID supplied";
    $url = tep_href_link(
            FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $payment_module->code . '&error=' . urlencode($message), 'NONSSL', true, false
    );
} else {
    // load selected payment module
    //require(DIR_WS_CLASSES . "payment.php");
    $payment_modules = new \common\classes\payment("multisafepay");
    //$payment_module 			= 	$GLOBALS[$payment_modules->selected_module];

    $payment_module = $GLOBALS[$payment_modules->selected_module];


    //require(DIR_WS_CLASSES . "order.php");
    $order = new \common\classes\Order($_GET['transactionid']);
    if ($_GET['type'] != 'shipping') {
        //print_r($order);exit;
    }
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
    $transdata = $payment_module->check_transaction();


    if ($payment_module->msp->details['ewallet']['fastcheckout'] == "NO") {
        $status = $payment_module->checkout_notify();
    } else {
        $payment_modules = new \common\classes\payment("multisafepay_fastcheckout");
        $payment_module = $GLOBALS[$payment_modules->selected_module];
        $status = $payment_module->checkout_notify();
    }
    

    if ($payment_module->_customer_id) {
        $hash = $payment_module->get_hash($payment_module->order_id, $payment_module->_customer_id);
        $parameters = 'customer_id=' . $payment_module->_customer_id . '&hash=' . $hash;
    }

    switch ($status) {
        case "initialized":
        case "completed":
            $message = "OK";
            $url = tep_href_link("ext/modules/payment/multisafepay/success.php", $parameters, 'NONSSL');
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
?>
