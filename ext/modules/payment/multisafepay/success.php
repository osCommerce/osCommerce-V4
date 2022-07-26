<?php

/*
  MultiSafepay Payment Module for osCommerce 
  http://www.multisafepay.com

  Copyright (C) 2008 MultiSafepay.com
 */

chdir("../../../../");
require("includes/application_top.php");

\common\helpers\Translation::init('checkout/process');

if ($multisafepay_order_id && $_GET['customer_id'] && $_GET['hash']) {
    if (md5($multisafepay_order_id . $_GET['customer_id']) == $_GET['hash']) {
        $customer_id = $_GET['customer_id'];
        $check_customer_query = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customer_id . "'");
        $check_customer = tep_db_fetch_array($check_customer_query);
        $check_country_query = tep_db_query("select entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $check_customer['customers_id'] . "' and address_book_id = '" . (int) $check_customer['customers_default_address_id'] . "'");
        $check_country = tep_db_fetch_array($check_country_query);
        $customer_id = $check_customer['customers_id'];
        $customer_default_address_id = $check_customer['customers_default_address_id'];
        $customer_first_name = $check_customer['customers_firstname'];
        $customer_country_id = $check_country['entry_country_id'];
        $customer_zone_id = $check_country['entry_zone_id'];
        tep_session_register('customer_default_address_id');
        tep_session_register('customer_first_name');
        tep_session_register('customer_country_id');
        tep_session_register('customer_zone_id');
    }
}

$cart->reset(true);

tep_session_unregister('sendto');
tep_session_unregister('billto');
tep_session_unregister('shipping');
tep_session_unregister('payment');
tep_session_unregister('comments');

if ($customer_id) {
    tep_session_register('customer_id');
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS));
} else {
    //For unregistered customer success page shows empty card,
    //so, it's better to show the index page.
    tep_redirect(tep_href_link(FILENAME_DEFAULT));
}
?>