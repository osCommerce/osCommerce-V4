<?php
/*
  $Id: pro_hosted_ipn.php 6498 2017-07-20 14:37:07Z dbalagov $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  if (!defined('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS') || (MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS  != 'True')) {
    exit;
  }

  require('includes/modules/payment/paypal_pro_hs.php');

  $result = false;

  if ( isset($_POST['txn_id']) && !empty($_POST['txn_id']) ) {
    $paypal_pro_hs = new paypal_pro_hs();

    $result = $paypal_pro_hs->getTransactionDetails($_POST['txn_id']);
  }

  if ( is_array($result) && isset($result['ACK']) && (($result['ACK'] == 'Success') || ($result['ACK'] == 'SuccessWithWarning')) ) {
    $pphs_result = $result;

    $paypal_pro_hs->verifyTransaction(true);
  }

  require('includes/application_bottom.php');/**/
?>
