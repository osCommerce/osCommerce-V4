<?php

/**
 * Transactional PaypalMiddleWare for Paypal modules
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\modules\orderPayment\lib;

use common\classes\modules\ModulePayment;
use common\classes\modules\TransactionalInterface;
use common\classes\modules\TransactionSearchInterface;
use \PayPal;

require ('paypal.php');

abstract class PaypalMiddleWare extends ModulePayment implements TransactionalInterface, TransactionSearchInterface {//

    const REST_SANDBOX_ENDPOINT = "https://api-m.sandbox.paypal.com";
    const REST_LIVE_ENDPOINT = "https://api.paypal.com";

  abstract protected function _isReady();

  abstract protected function _getClientId();

  abstract protected function _getClientSecret();

  abstract protected function _getIntent();

  abstract public static function getMode();

  /**
   * from v1 API - used for webhooks and search
   * @return \PayPal\Auth\OAuthTokenCredential
   */
  protected function getAuth() {
    if ($this->_isReady()) {
      $auth = new PayPal\Auth\OAuthTokenCredential($this->_getClientId(), $this->_getClientSecret());
    }

    if (!is_object($auth)) {
      trigger_error('Credentials are not defined');
    }

    return $auth;
  }

  /**
   * required for webhooks (set & verify)
   * @return \PayPal\Rest\ApiContext
   */
  protected function getApiContext() {
    $apiContext = new PayPal\Rest\ApiContext($this->getAuth());
    $apiContext->setConfig(['mode' => $this->getMode()]);
    return $apiContext;
  }

  /**
   * get payment capture details from paypal.
   * @param string $captureId
   * @return PayPalHttp\HttpResponse|false
   */
  public function getCapture($captureId) {
    return $this->makeRequest(new \PayPalCheckoutSdk\Payments\CapturesGetRequest($captureId));
  }

  /**
   * get payment authorizations details from paypal.
   * @param string $authorizationId
   * @return PayPalHttp\HttpResponse|false
   */
  public function getAuthorization($authorizationId) {
    return $this->makeRequest(new \PayPalCheckoutSdk\Payments\AuthorizationsGetRequest($authorizationId));
  }

  /**
   * get payment refund details from paypal.
   * @param string $refundId
   * @return PayPalHttp\HttpResponse|false
   */
  public function getRefund($refundId) {
    return $this->makeRequest(new \PayPalCheckoutSdk\Payments\RefundsGetRequest($refundId));
  }

  /**
   * get transaction type by transaction full info in DB
   * @param string $tInfo json
   * @return string authorization|refund|capture
   */
  public function transactionType($tInfo) {
    $type = $this->_getIntent();
    if (is_string($tInfo)) {
      $tmp = json_decode($tInfo);
    } else {
      $tmp = $tInfo;
    }
    if (!empty($tmp->result)) {
      $tDetails = $tmp->result;
    }
    if (!empty($tDetails->links) && is_array($tDetails->links)) {
      foreach ($tDetails->links as $link) {
        if ($link->rel == 'self') {
          $type = (!empty($link->href) && strpos($link->href, '/authorizations/') !== false ? 'authorize' :
              (!empty($link->href) && strpos($link->href, '/refunds/') !== false ? 'refund' : 'capture') );
          break;
        }
      }
    }
    return $type;
  }

  /**
   * get authorization|capture|refund details from paypal and update status + amount in transaction table
   * @param string $transaction_id
   * @param \common\services\PaymentTransactionManager $tManager
   * @return stdObject|false
   */
  public function getTransactionDetails($transaction_id, \common\services\PaymentTransactionManager $tManager = null) {
    static $lastTransaction = null;
    $sale = false;
    //can void/capture/reauth - one-buy-one call it
    if (!empty($lastTransaction['id']) && $lastTransaction['id'] == $transaction_id) {
      $sale = $lastTransaction['sale'];
    } else {
      $op = $this->searchRecord($transaction_id);
      if ($op && !empty($op->orders_payment_id)) {
        if (!empty($op->payment_type)) {
          $type = $op->payment_type;

        } elseif (!empty($op->orders_payment_transaction_full)) {
          $type = $this->transactionType($op->orders_payment_transaction_full);
          
        } else {
          $type = $this->_getIntent();
        }
        try {
          if ($type == 'authorize') {
            $sale = $this->getAuthorization($transaction_id);
          } elseif ($type == 'refund') {
            $sale = $this->getRefund($transaction_id);
          } else {
            $sale = $this->getCapture($transaction_id);
          }
          $status = $sale->result->status;
          $amt = $sale->result->amount->value;

          if ($tManager && $sale) {
            $tManager->updateTransactionFromPayment($transaction_id, $status, $amt);
          }
          $lastTransaction['sale'] = $sale;
          $lastTransaction['id'] = $transaction_id;
        } catch (\Exception $ex) {
          $this->sendDebugEmail($ex);
        }
      }
    }
    return $sale;
  }

/**
 * update transaction details from PayPal, check it's type (only authorization allowed), and status ('CREATED', 'AUTHORIZED', 'PARTIALLY_CAPTURED')
 * @param string $transaction_id
 * @return boolean
 */
  public function canCapture($transaction_id) {
    $canCapture = false;
    $tm = $this->manager->getTransactionManager($this);
    $op = $this->searchRecord($transaction_id);
    $type = $this->_getIntent();
    if ($op && !empty($op->orders_payment_id)) {
      if (!empty($op->payment_type)) {
        $type = $op->payment_type;
      } elseif (!empty($op->orders_payment_transaction_full)) {
        $type = $this->transactionType($op->orders_payment_transaction_full);
      }
      if (in_array($type, ['authorization', 'authorize'])) {
        if (!empty($op->orders_payment_transaction_full)) {
          $authorization = json_decode($op->orders_payment_transaction_full);
        } else {
          $authorization = $this->getTransactionDetails($transaction_id, $tm);
        }
        if (is_object($authorization) && in_array(strtoupper($authorization->result->status), ['CREATED', 'AUTHORIZED', 'PARTIALLY_CAPTURED'])) {
          $canCapture = true;
        }
      }
    }
    return $canCapture;
  }

/**
 * update transaction details from PayPal, check it's type (only authorization allowed), and status ('CREATED', 'AUTHORIZED')
 * @param string $transaction_id
 * @return boolean
 */
  public function canReauthorize($transaction_id) {
    $canCapture = false;
    $tm = $this->manager->getTransactionManager($this);
    $op = $this->searchRecord($transaction_id);
    $type = $this->_getIntent();
    if ($op && !empty($op->orders_payment_id)) {
      if (!empty($op->payment_type)) {
        $type = $op->payment_type;
      } elseif (!empty($op->orders_payment_transaction_full)) {
        $type = $this->transactionType($op->orders_payment_transaction_full);
      }
      if (in_array($type, ['authorization', 'authorize'])) {
        if (!empty($op->orders_payment_transaction_full)) {
          $authorization = json_decode($op->orders_payment_transaction_full);
        } else {
          $authorization = $this->getTransactionDetails($transaction_id, $tm);
        }
        if (is_object($authorization) && in_array(strtoupper($authorization->result->status), ['CREATED', 'AUTHORIZED'])) {
          $canCapture = true;
        }
      }
    }
    return $canCapture;
  }

/**
 * update transaction details from PayPal, check it's type (only capture allowed), and status (not refunded)
 * @param string $transaction_id
 * @return boolean
 */
  public function canRefund($transaction_id) {
    $canRefund = false;
    $tm = $this->manager->getTransactionManager($this);
    $op = $this->searchRecord($transaction_id);
    $type = $this->_getIntent();
    if ($op && !empty($op->orders_payment_id)) {
      if (!empty($op->payment_type)) {
        $type = $op->payment_type;
      } elseif (!empty($op->orders_payment_transaction_full)) {
        $type = $this->transactionType($op->orders_payment_transaction_full);
      }
      if (!in_array($type, ['authorization', 'refund', 'authorize'])) {
        if (!empty($op->orders_payment_transaction_full)) {
          $capture = json_decode($op->orders_payment_transaction_full);
        } else {
          $capture = $this->getTransactionDetails($transaction_id, $tm);
        }
        if (is_object($capture) && strtoupper($capture->result->status) != 'REFUNDED') {
          $canRefund = true;
        }
      }
    }
    return $canRefund;
  }

/**
 * sends refund request to PayPal and save refund transaction details inf DB
 * @param string $transaction_id
 * @param float $amount
 * @return true|false|string (true - success false - no fail details else error message
 */
  public function refund($transaction_id, $amount = 0) {
    $ret = false;
    $orderPayment = $this->searchRecord($transaction_id);

    if (empty($orderPayment->orders_payment_transaction_full)) {
      $orderPayment = $this->getTransactionDetails($transaction_id);
    } else {
      $orderPayment = json_decode($orderPayment->orders_payment_transaction_full);
    }

    if ($orderPayment) {
      $refundRequest = new \PayPalCheckoutSdk\Payments\CapturesRefundRequest($transaction_id);
      $refundRequest->prefer('return=representation');
      $order = $this->manager->getOrderInstance();
      if ($amount) {
        $data = ['value' => $this->formatRaw($amount), 'currency_code' => $order->info['currency']];
        $rAmount = (object) $data;
        $refundRequest->body['amount'] = $rAmount;
      }
      if (!$this->hasOwnKeys()) {
        $platformId = $this->getPlatformId();
        $seller = $this->getSeller($platformId);
        $refundRequest->headers['PayPal-Auth-Assertion'] = base64_encode('{"alg":"none"}') . '.' . base64_encode('{"iss": "' . $this->_getClientId() . '",
      "payer_id": "' . $seller->payer_id . '"}') . '.';
      }


      $refundRequest->body['invoice_id'] = $order->order_id . '-' . date('Y-m-d:H:i:s'); //date to avoid Requested invoice number was already used
      //$refundRequest->setInvoiceNumber($order->order_id . '-' . date('Y-m-d:H:i:s')); //date to avoid Requested invoice number was already used
      try {

        $response = $this->makeRequest($refundRequest, false);

        if ($response) {
          $this->whCancelled($response, $transaction_id);
          $ret = true;
        }
      } catch (\Exception $ex) {
        if (get_class($ex) == 'PayPalHttp\HttpException') {
          $e = json_decode($ex->getMessage());
          $ret = $e->message . "\n";
          if (is_array($e->details)) {
            foreach ($e->details as $d) {
              $ret .= $d->description . "\n";
            }
          }
        } else {
          $ret = $ex->getMessage();
        }
        \Yii::warning(" #### " .print_r($e, true), $this->code);

      }
    }
    return $ret;
  }

  /**
   * update transaction details from PayPal, check it's type (only authorization allowed), and status (not refunded)
   * @param type $transaction_id
   * @return boolean
   */
  public function canVoid($transaction_id) {
    $canVoid = false;
    $tm = $this->manager->getTransactionManager($this);
    $op = $this->searchRecord($transaction_id);
    $type = $this->_getIntent();
    if ($op && !empty($op->orders_payment_id)) {
      if (!empty($op->payment_type)) {
        $type = $op->payment_type;
      } elseif (!empty($op->orders_payment_transaction_full)) {
        $type = $this->transactionType($op->orders_payment_transaction_full);
      }
      if (in_array($type, ['authorization', 'authorize'])) {
        if (!empty($op->orders_payment_transaction_full)) {
          $authorization = json_decode($op->orders_payment_transaction_full);
        } else {
          $authorization = $this->getTransactionDetails($transaction_id, $tm);
        }
        if (is_object($authorization) && in_array(strtoupper($authorization->result->status), ['CREATED', 'AUTHORIZED'])) {
          $canVoid = true;
        }
      }
    }
    return $canVoid;
  }

  /**
   * @category not_used
   * @return type
   */
  public function getFields() {
    return [
      //[['start_date', 'end_date'], 'required'],
      [['start_date'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],
      [['end_date'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'], //MM/dd/yyyy HH:mm
      ['transaction_id', 'string'],
      ['email_address', 'string']
    ];
  }

  /**
   * seems impossible
   * @param array $queryParams
   * @return array
   */
  public function search($queryParams) {
    try {
      $requiered = $this->getFields();
/*
      if (!$queryParams['skipIpn']) {
        $found = $this->getIpnTransactions($queryParams);
        if ($found) {
          return $found;
        }
      }
 */

      foreach ($queryParams as $key => $value) {
        if (empty($value))
          unset($queryParams[$key]);
      }

      $email = $queryParams['email_address'];
      unset($queryParams['email_address']);

      foreach ($queryParams as $key => $param) {
        array_map(function($item) use (&$queryParams, $key, $param) {
          if (is_array($item[0])) {
            if (in_array($key, $item[0]) && $item[1] == 'datetime') {
              $queryParams[$key] = date(DATE_ATOM, strtotime($param));
            }
          } else {
            if ($key == $item[0] && $item[1] == 'datetime') {
              $queryParams[$key] = date(DATE_ATOM, strtotime($param));
            }
          }
        }, $requiered);
      }

      $fromStart = true;
      if (!$queryParams['start_date']) {
        $queryParams['start_date'] = date(DATE_ATOM, strtotime("-31 days"));
        $fromStart = false;
      }
      $fromEnd = true;
      if (!$queryParams['end_date']) {
        $queryParams['end_date'] = date(DATE_ATOM);
        $fromEnd = false;
      }

      $startD = new \DateTime($queryParams['start_date']);
      $endD = new \DateTime($queryParams['end_date']);
      $diff = $startD->diff($endD);
      if ($diff->m || $diff->y) {
        if ($fromStart) {
          $startD->add(new \DateInterval('P31D'));
          $queryParams['end_date'] = $startD->format(DATE_ATOM);
        } else {
          $endD->sub(new \DateInterval('P31D'));
          $queryParams['start_date'] = $endD->format(DATE_ATOM);
        }
      }

      $platformId = $this->getPlatformId();
      $seller = $this->getSeller($platformId);
      //$queryParams['account_number'] = $seller->payer_id;
      $queryParams['fields'] = 'all';
      $queryParams['page_size'] = '100';
      $queryParams['page'] = '1';

      $apiContext = $this->getApiContext();
      $headers = null;
      if (!$this->hasOwnKeys()) {
        //  $apiContext->addRequestHeader('PayPal-Auth-Assertion', base64_encode('{"alg":"none"}') . '.' . base64_encode('{"iss": "' . $this->_getClientId() . '","payer_id": "' . $seller->payer_id . '"}') . '.'); // ??try?? HTTP 403
        $headers = ['PayPal-Auth-Assertion' => base64_encode('{"alg":"none"}') . '.' . base64_encode('{"iss":"' . $this->_getClientId() . '","payer_id":"' . $seller->payer_id . '"}') . '.'];
      } 

      $response = PayPal\Api\Sync::getAll($queryParams, $apiContext, null, $headers);

      $transactions = $response->__get('transaction_details');

      $found = [];
      //if (isset($queryParams['no_modify'])) return $transactions;
      if (is_array($transactions)) {
        $currencies = \Yii::$container->get('currencies');
        foreach ($transactions as $tns) {
          //if (isset($tns['transaction_info']['paypal_reference_id'])) continue;//probably refunded transaction
          $name = isset($tns['payer_info']['payer_name']['given_name']) && !empty($tns['payer_info']['payer_name']['given_name']) ? $tns['payer_info']['payer_name']['given_name'] . " " . $tns['payer_info']['payer_name']['surname'] : $tns['payer_info']['payer_name']['alternate_full_name'];
          if (!empty($email) && strcmp($email, $tns['payer_info']['email_address'])) {
            continue;
          }
          $found[] = [
            'id' => $tns['transaction_info']['transaction_id'],
            'date' => \common\helpers\Date::formatDateTimeJS($tns['transaction_info']['transaction_initiation_date']),
            'amount' => $currencies->format($tns['transaction_info']['transaction_amount']['value'], true, $tns['transaction_info']['transaction_amount']['currency_code']),
            'negative' => $tns['transaction_info']['transaction_amount']['value'] < 0,
            'name' => $name . ($tns['payer_info']['email_address'] ? ", " . $tns['payer_info']['email_address'] : ""),

            'status' => $this->describeStatus($tns['transaction_info']['transaction_status'])
              . '  ' . $this->describeEvent($tns['transaction_info']['transaction_event_code']),
            'type' => $this->typeByEvent($tns['transaction_info']['transaction_event_code']),
            'fulljson' => json_encode($tns)
          ];
        }
      }
      
    } catch (\Exception $ex) {
      if (method_exists($ex, 'getData') && !empty($ex->getData())) {
        $err = json_decode($ex->getData());
        $found = $ex->getMessage(). "\n" . $err->localizedMessage; #### <PRE>" . __FILE__ .':' . __LINE__ . ' ' . print_r($err, 1) ."</PRE>";
      }
      $this->sendDebugEmail($ex);
    }
    return $found;
  }


  public function describeStatus($statusCode) {
    switch ($statusCode) {
      case 'D' :
        return 'Denied';
        break;
      case 'F' :
        return 'Refunded';
        break;
      case 'P' :
        return 'Pending';
        break;
      case 'S' :
        return 'Completed';
        break;
      case 'V' :
        return 'Reversed';
        break;
      default :
        return '';
        break;
    }
  }
  
  public function typeByEvent($code) {
    $ret = '';
    $group = substr($code, 0, 3);
    /*
      'T00' => 'PayPal account-to-PayPal account payment',

        'T01' => 'Non-payment-related fees',

        'T02' => 'Currency conversion',

        'T03' => 'Bank deposit into PayPal account',

        'T04' => 'Bank withdrawal from PayPal account',

        'T05' => 'Debit card',

        'T06' => 'Credit card withdrawal',

        'T07' => 'Credit card deposit',

        'T08' => 'Bonus',


        'T10' => 'Bill pay',

        'T09' => 'Incentive',
          'T11' => 'Reversal',
          'T15' => 'Hold for dispute or other investigation',
          'T21' => 'Reserves and releases',

        'T12' => 'Adjustment',

        'T13' => 'Authorization',

        'T14' => 'Dividend',



        'T16' => 'Buyer credit deposit',

        'T17' => 'Non-bank withdrawal',

        'T18' => 'Buyer credit withdrawal',

        'T19' => 'Account correction',

        'T20' => 'Funds transfer from PayPal account to another',



        'T22' => 'Transfers',
        'T30' => 'Generic instrument and Open Wallet',
        'T50' => 'Collections and disbursements',
        'T97' => 'Payables and receivables',
        'T98' => 'Display only transaction',
        'T99' => 'Other',

    */
    if (in_array(strtoupper($group), ['T13' ] )) {
      $ret = 'authorization';
    } elseif (in_array(strtoupper($group), ['T11', 'T09', 'T15', 'T21'] )) {
      $ret = 'refund';
    } elseif (!empty($group)) {
      $ret = 'capture';
    }

    return $ret;

  }

  public function describeEvent($code) {
    $description = [
        'T00' => 'PayPal account-to-PayPal account payment',
        'T0000' => 'General: received payment of a type not belonging to the other T00nn categories.',
        'T0001' => 'MassPay payment.',
        'T0002' => 'Subscription payment. Either payment sent or payment received.',
        'T0003' => 'Pre-approved payment (BillUser API). Either sent or received.',
        'T0004' => 'eBay auction payment.',
        'T0005' => 'Direct payment API.',
        'T0006' => 'PayPal Checkout APIs.',
        'T0007' => 'Website payments standard payment.',
        'T0008' => 'Postage payment to carrier.',
        'T0009' => 'Gift certificate payment. Purchase of gift certificate.',
        'T0010' => 'Third-party auction payment.',
        'T0011' => 'Mobile payment, made through a mobile phone.',
        'T0012' => 'Virtual terminal payment.',
        'T0013' => 'Donation payment.',
        'T0014' => 'Rebate payments.',
        'T0015' => 'Third-party payout.',
        'T0016' => 'Third-party recoupment.',
        'T0017' => 'Store-to-store transfers.',
        'T0018' => 'PayPal Here payment.',
        'T0019' => 'Generic instrument-funded payment.',

        'T01' => 'Non-payment-related fees',
        'T0100' => 'General non-payment fee of a type not belonging to the other T01nn categories.',
        'T0101' => 'Website payments. Pro account monthly fee.',
        'T0102' => 'Foreign bank withdrawal fee.',
        'T0103' => 'WorldLink check withdrawal fee.',
        'T0104' => 'Mass payment batch fee.',
        'T0105' => 'Check withdrawal.',
        'T0106' => 'Chargeback processing fee.',
        'T0107' => 'Payment fee.',
        'T0108' => 'ATM withdrawal.',
        'T0109' => 'Auto-sweep from account.',
        'T0110' => 'International credit card withdrawal.',
        'T0111' => 'Warranty fee for warranty purchase.',
        'T0112' => 'Gift certificate expiration fee.',
        'T0113' => 'Partner fee.',
      
        'T02' => 'Currency conversion',
        'T0200' => 'General currency conversion.',
        'T0201' => 'User-initiated currency conversion.',
        'T0202' => 'Currency conversion required to cover negative balance. PayPal-system generated.',
      
        'T03' => 'Bank deposit into PayPal account',
        'T0300' => 'General funding of PayPal account.',
        'T0301' => 'PayPal balance manager funding of PayPal account.',
        'T0302' => 'ACH funding for funds recovery from account balance.',
        'T0303' => 'Electronic funds transfer (EFT) (German banking).',

        'T04' => 'Bank withdrawal from PayPal account',
        'T0400' => 'General withdrawal from PayPal account.',
        'T0401' => 'AutoSweep.',

        'T05' => 'Debit card',
        'T0500' => 'General PayPal debit card transaction.',
        'T0501' => 'Virtual PayPal debit card transaction.',
        'T0502' => 'PayPal debit card withdrawal to ATM.',
        'T0503' => 'Hidden virtual PayPal debit card transaction.',
        'T0504' => 'PayPal debit card cash advance.',
        'T0505' => 'PayPal debit authorization.',

        'T06' => 'Credit card withdrawal',
        'T0600' => 'General credit card withdrawal.',

        'T07' => 'Credit card deposit',
        'T0700' => 'General credit card deposit.',
        'T0701' => 'Credit card deposit for negative PayPal account balance.',

        'T08' => 'Bonus',
        'T0800' => 'General bonus of a type not belonging to the other T08nn categories.',
        'T0801' => 'Debit card cash back bonus.',
        'T0802' => 'Merchant referral account bonus.',
        'T0803' => 'Balance manager account bonus.',
        'T0804' => 'PayPal buyer warranty bonus.',
        'T0805' => 'PayPal protection bonus, payout for PayPal buyer protection, payout for full protection with PayPal buyer credit.',
        'T0806' => 'Bonus for first ACH use.',
        'T0807' => 'Credit card security charge refund.',
        'T0808' => 'Credit card cash back bonus',

        'T09' => 'Incentive',
        'T0900' => 'General incentive or certificate redemption.',
        'T0901' => 'Gift certificate redemption.',
        'T0902' => 'Points incentive redemption.',
        'T0903' => 'Coupon redemption.',
        'T0904' => 'eBay loyalty incentive.',
        'T0905' => 'Offers used as funding source.',

        'T10' => 'Bill pay',
        'T1000' => 'Bill pay transaction.',

        'T11' => 'Reversal',
        'T1100' => 'General reversal of a type not belonging to the other T11nn categories.',
        'T1101' => 'Reversal of ACH withdrawal transaction.',
        'T1102' => 'Reversal of debit card transaction.',
        'T1103' => 'Reversal of points usage.',
        'T1104' => 'Reversal of ACH deposit.',
        'T1105' => 'Reversal of general account hold.',
        'T1106' => 'Payment reversal, initiated by PayPal.',
        'T1107' => 'Payment refund, initiated by merchant.',
        'T1108' => 'Fee reversal.',
        'T1109' => 'Fee refund.',
        'T1110' => 'Hold for dispute investigation (T15nn).',
        'T1111' => 'Cancellation of hold for dispute resolution.',
        'T1112' => 'MAM reversal.',
        'T1113' => 'Non-reference credit payment.',
        'T1114' => 'MassPay reversal transaction.',
        'T1115' => 'MassPay refund transaction.',
        'T1116' => 'Instant payment review (IPR) reversal.',
        'T1117' => 'Rebate or cash back reversal.',
        'T1118' => 'Generic instrument/Open Wallet reversals (seller side).',
        'T1119' => 'Generic instrument/Open Wallet reversals (buyer side).',

        'T12' => 'Adjustment',
        'T1200' => 'General account adjustment.',
        'T1201' => 'Chargeback.',
        'T1202' => 'Chargeback reversal.',
        'T1203' => 'Charge-off adjustment.',
        'T1204' => 'Incentive adjustment.',
        'T1205' => 'Reimbursement of chargeback.',
        'T1207' => 'Chargeback re-presentment rejection.',
        'T1208' => 'Chargeback cancellation.',

        'T13' => 'Authorization',
        'T1300' => 'General authorization.',
        'T1301' => 'Reauthorization.',
        'T1302' => 'Void of authorization.',

        'T14' => 'Dividend',
        'T1400' => 'General dividend.',

        'T15' => 'Hold for dispute or other investigation',
        'T1500' => 'General temporary hold of a type not belonging to the other T15nn categories.',
        'T1501' => 'Account hold for open authorization.',
        'T1502' => 'Account hold for ACH deposit.',
        'T1503' => 'Temporary hold on available balance.',

        'T16' => 'Buyer credit deposit',
        'T1600' => 'PayPal buyer credit payment funding.',
        'T1601' => 'BML credit. Transfer from BML.',
        'T1602' => 'Buyer credit payment.',
        'T1603' => 'Buyer credit payment withdrawal. Transfer to BML.',

        'T17' => 'Non-bank withdrawal',
        'T1700' => 'General withdrawal to non-bank institution.',
        'T1701' => 'WorldLink withdrawal.',

        'T18' => 'Buyer credit withdrawal',
        'T1800' => 'General buyer credit payment.',
        'T1801' => 'BML withdrawal. Transfer to BML.',

        'T19' => 'Account correction',
        'T1900' => 'General adjustment without business-related event.',

        'T20' => 'Funds transfer from PayPal account to another',
        'T2000' => 'General intra-account transfer.',
        'T2001' => 'Settlement consolidation.',
        'T2002' => 'Transfer of funds from payable.',
        'T2003' => 'Transfer to external GL entity.',

        'T21' => 'Reserves and releases',
        'T2101' => 'General hold.',
        'T2102' => 'General hold release.',
        'T2103' => 'Reserve hold.',
        'T2104' => 'Reserve release.',
        'T2105' => 'Payment review hold.',
        'T2106' => 'Payment review release.',
        'T2107' => 'Payment hold.',
        'T2108' => 'Payment hold release.',
        'T2109' => 'Gift certificate purchase.',
        'T2110' => 'Gift certificate redemption.',
        'T2111' => 'Funds not yet available.',
        'T2112' => 'Funds available.',
        'T2113' => 'Blocked payments.',

        'T22' => 'Transfers',
        'T2201' => 'Transfer to and from a credit-card-funded restricted balance.',

        'T30' => 'Generic instrument and Open Wallet',
        'T3000' => 'Generic instrument/Open Wallet transaction.',

        'T50' => 'Collections and disbursements',
        'T5000' => 'Deferred disbursement. Funds collected for disbursement.',
        'T5001' => 'Delayed disbursement. Funds disbursed.',

        'T97' => 'Payables and receivables',
        'T9700' => 'Account receivable for shipping.',
        'T9701' => 'Funds payable. PayPal-provided funds that must be paid back.',
        'T9702' => 'Funds receivable. PayPal-provided funds that are being paid back.',

        'T98' => 'Display only transaction',
        'T9800' => 'Display only transaction.',

        'T99' => 'Other',
        'T9900' => 'Other.',

    ];
   
    $ret = '';
    $group = substr($code, 0, 3);
    if (isset($description[$code])) {
      $ret = $description[$code];
    } elseif (isset($description[$group])) {
      $ret = $description[$group];
    } 

    return $ret;
  }


  public function getIpnTransactions($queryParams) {

    $condition = ['and', ['is_assigned' => 0], /* ['>','mc_gross', 0], */ ['platform_id' => $this->manager->getPlatformId()]];
    if (isset($queryParams['start_date']) && !empty($queryParams['start_date'])) {
      array_push($condition, ['>=', 'payment_date', $queryParams['start_date']]);
    }

    if (isset($queryParams['end_date']) && !empty($queryParams['end_date'])) {
      array_push($condition, ['<=', 'payment_date', $queryParams['end_date']]);
    }

    if (isset($queryParams['transaction_id']) && !empty($queryParams['transaction_id'])) {
      array_push($condition, ['=', 'txn_id', $queryParams['transaction_id']]);
    }

    $txn = \common\models\PaypalipnTxn::find()
            ->where($condition)->orderBy('date(payment_date) desc');

    if (!$txn->exists())
      return [];
    $found = [];
    $currencies = \Yii::$container->get('currencies');
    foreach ($txn->all() as $ipnTx) {
      $found[] = [
        'id' => $ipnTx['txn_id'],
        'date' => \common\helpers\Date::formatDateTimeJS($ipnTx['payment_date']),
        'amount' => $currencies->format($ipnTx['mc_gross'], true, $ipnTx['mc_currency']),
        'negative' => $ipnTx['mc_gross'] < 0,
        'name' => ($ipnTx['first_name'] || $ipnTx['last_name'] ? $ipnTx['first_name'] . " " . $ipnTx['last_name'] . ", " : '') . $ipnTx['payer_email'],
        'status' => ($ipnTx['mc_gross'] < 0 ? $this->describeStatus('F') : $ipnTx['payment_status']),
      ];
    }
    return $found;
  }

/**
 * 2do
 * @param type $order_id
 * @param type $transaction_id
 */
  public function linkTransaction($order_id, $transaction_id) {
    $txnQ = \common\models\PaypalipnTxn::find()->where(['is_assigned' => 0, 'txn_id' => $transaction_id]);
    if ($txnQ->exists()) {
      $txn = $txnQ->one();
      $txn->item_number = $order_id;
      $txn->is_assigned = 1;
      $txn->save(false);
    }
  }

/**
 * 2do
 * @param type $transaction_id
 */
  public function unLinkTransaction($transaction_id) {
    $txnQ = \common\models\PaypalipnTxn::find()->where(['txn_id' => $transaction_id]);
    if ($txnQ->exists()) {
      $txn = $txnQ->one();
      $txn->is_assigned = 0;
      $txn->save(false);
    }
  }

  /**
   * returns which version of API is used by module
   * @return string v1|v2
   */
  public function getAPIVersion() {
    if ($this->code == 'paypal_partner') {
      $ret = 'v2';
    } else {
      $ret = 'v1';
    }
    return $ret;
  }

/**
 *
 * @param obj $requestObject
 * @param bool $catch default true
 * @return obj|false
 */
  public function makeRequest($requestObject, $catch = true) {
    if ($this->_isReady()) {
      if (!$catch) {
        return $this->getHttpClient()->execute($requestObject);
      } else {
        try {
          return $this->getHttpClient()->execute($requestObject);
        } catch (\Exception $ex) {
          \Yii::error($ex->getMessage(), 'paypal_partner');
          \Yii::error(print_r($this,1), 'paypal_partner');
          \Yii::error(print_r($requestObject,1), 'paypal_partner');
          \Yii::error(print_r($this->getHttpClient(),1), 'paypal_partner');
        }
      }
    }
    return false;
  }

  /** 
   *
  */
  public function release($transaction_id, $statusId) {
      $this->capture($transaction_id);
  }
  
  public function capture($transaction_id, $amount = 0) {
    $ret = false;
    $orderPayment = $this->searchRecord($transaction_id);

    if (empty($orderPayment->orders_payment_transaction_full)) {
      $orderPayment = $this->getTransactionDetails($transaction_id);
    } else {
      $orderPayment = json_decode($orderPayment->orders_payment_transaction_full);
    }

    if ($orderPayment) {
      $captureRequest = new \PayPalCheckoutSdk\Payments\AuthorizationsCaptureRequest($transaction_id);
      $captureRequest->prefer('return=representation');
      $order = $this->manager->getOrderInstance();
      if ($amount) {
        $data = ['value' => $this->formatRaw($amount), 'currency_code' => $order->info['currency']];
        $rAmount = (object) $data;
        $captureRequest->body['amount'] = $rAmount;
      }
        if (!$this->hasOwnKeys()) {
          $platformId = $this->getPlatformId();
          $seller = $this->getSeller($platformId);

          $captureRequest->headers['PayPal-Auth-Assertion'] = base64_encode('{"alg":"none"}') . '.' . base64_encode('{"iss": "' . $this->_getClientId() . '",
        "payer_id": "' . $seller->payer_id . '"}') . '.';
        }

      try {

        $response = $this->makeRequest($captureRequest, false);
//\Yii::warning("\$captureRequest #### " .print_r($response, 1), 'TLDEBUG');
        if ($response) {
          $this->whCaptured($response, $transaction_id);
          $ret = true;
        }
        
      } catch (\Exception $ex) {
        if (get_class($ex) == 'PayPalHttp\HttpException') {
          $e = json_decode($ex->getMessage());
          $ret = $e->message . "\n";
          if (is_array($e->details)) {
            foreach ($e->details as $d) {
              $ret .= $d->description . "\n";
            }
          }
        } else {
          $ret = $ex->getMessage();
        }
        \Yii::warning(" #### " .print_r($e, true), $this->code);

      }
    }
    return $ret;
  }

  /**
   * @param type $transaction_id
   * @return boolean
   */
  public function void($transaction_id) {
    $ret = false;
    $orderPayment = $this->searchRecord($transaction_id);

    if (empty($orderPayment->orders_payment_transaction_full)) {
      $orderPayment = $this->getTransactionDetails($transaction_id);
    } else {
      $orderPayment = json_decode($orderPayment->orders_payment_transaction_full);
    }

    if ($orderPayment) {
      $voidRequest = new \PayPalCheckoutSdk\Payments\AuthorizationsVoidRequest($transaction_id);
      
      if (!$this->hasOwnKeys()) {
        $platformId = $this->getPlatformId();
        $seller = $this->getSeller($platformId);
        $voidRequest->headers['PayPal-Auth-Assertion'] = base64_encode('{"alg":"none"}') . '.' . base64_encode('{"iss": "' . $this->_getClientId() . '",
      "payer_id": "' . $seller->payer_id . '"}') . '.';
      }


      try {

        $response = $this->makeRequest($voidRequest, false);

        if ($response) {
            $this->whCancelled($response); //cancel itself, not parent
            $ret = true;            
            $this->getUpdateTransaction($transaction_id);
        }
      } catch (\Exception $ex) {
        if (get_class($ex) == 'PayPalHttp\HttpException') {
          $e = json_decode($ex->getMessage());
          $ret = $e->message . "\n";
          if (is_array($e->details)) {
            foreach ($e->details as $d) {
              $ret .= $d->description . "\n";
            }
          }
        } else {
          $ret = $ex->getMessage();
        }
        \Yii::warning(" #### $e " .print_r(substr($ex->getTraceAsString(), 0, 2048).'....', true), $this->code);

      }
    }
    return $ret;
  }

   
  /** 2do
   **/

  public function reauthorize($transaction_id, $amount = 0) {
    $ret = false;
    $orderPayment = $this->searchRecord($transaction_id);

    if (empty($orderPayment->orders_payment_transaction_full)) {
      $orderPayment = $this->getTransactionDetails($transaction_id);
    } else {
      $orderPayment = json_decode($orderPayment->orders_payment_transaction_full);
    }

    if ($orderPayment) {
      $captureRequest = new \PayPalCheckoutSdk\Payments\AuthorizationsReauthorizeRequest($transaction_id);
      $captureRequest->prefer('return=representation');
      $order = $this->manager->getOrderInstance();
      if ($amount) {
        $data = ['value' => $this->formatRaw($amount), 'currency_code' => $order->info['currency']];
        $rAmount = (object) $data;
        $captureRequest->body['amount'] = $rAmount;
      }
      if (!$this->hasOwnKeys()) {
        $platformId = $this->getPlatformId();
        $seller = $this->getSeller($platformId);
        $captureRequest->headers['PayPal-Auth-Assertion'] = base64_encode('{"alg":"none"}') . '.' . base64_encode('{"iss": "' . $this->_getClientId() . '",
      "payer_id": "' . $seller->payer_id . '"}') . '.';
      }

      try {

        $response = $this->makeRequest($captureRequest, false);
        if ($response) {
          $this->whCaptured($response, $transaction_id);
          $ret = true;
        }

      } catch (\Exception $ex) {
        if (get_class($ex) == 'PayPalHttp\HttpException') {
          $e = json_decode($ex->getMessage());
          $ret = $e->message . "\n";
          if (is_array($e->details)) {
            foreach ($e->details as $d) {
              $ret .= $d->description . "\n";
            }
          }
        } else {
          $ret = $ex->getMessage();
        }
        \Yii::warning(" #### " .print_r($e, true), $this->code);

      }
    }
    return $ret;  }

}
