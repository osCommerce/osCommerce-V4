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

namespace common\classes\modules;

use common\helpers\Html;
use \yii\helpers\ArrayHelper;
use common\helpers\OrderPayment as OrderPaymentHelper;

abstract class ModulePayment extends Module {

  protected $transactionInfo = [];
  protected $doCheckoutInitializationOnInactive = false; //express modules which requires extra HTML from checkout_initialization_method and could be disabled by zone should set this property to true.
  //access via getStatusBeforeUpdate()
  protected $_transactionDetails = false; //transaction details received from gateway to avoid extra requests.

  public function getStatusBeforeUpdate() {
      return $this->doCheckoutInitializationOnInactive;
  }

  function javascript_validation() {
    return false;
  }

  function selection() {
    return false;
  }

  function pre_confirmation_check() {
    return false;
  }

  function confirmation() {
    return false;
  }

  function confirmationCurlAllowed() {
    return false;
  }

  function confirmationAutosubmit() {
    return false;
  }

  function process_button() {
    $order = $this->manager->getOrderInstance();

    if (!self::isPartlyPaid())
      return false;

    if (isset($order->info['total_paid_inc_tax'])) {
      $order->info['total'] = $order->info['total_inc_tax'] - $order->info['total_paid_inc_tax'];
      if ($order->info['total'] < 0)
        $order->info['total'] = 0;
    }
    $this->paid = 'partlypaid';

    $this->manager->set('pay_order_id', $order->order_id);
  }

  function before_process() {
    //global $sendto, $billto, $order;
    if (self::isPartlyPaid()) {
      $order = $this->manager->getOrderInstance();
      if (!$this->manager->has('sendto') && (int) $order->delivery['address_book_id'] > 0)
        $this->manager->set('sendto', (int) $order->delivery['address_book_id']);
      if (!$this->manager->has('billto') && (int) $order->billing['address_book_id'] > 0)
        $this->manager->set('billto', (int) $order->billing['address_book_id']);
      return true;
    }
    return false;
  }

  function after_process() {
    $order = $this->manager->getOrderInstance();
    if (is_object($order)) {
      return \common\helpers\OrderPayment::createDebitFromOrder($order);
    }
    return false;
  }

  function get_error() {
    return false;
  }

  function output_error() {
    return false;
  }

  function before_subscription($id = 0) {
    return false;
  }

  function haveSubscription() {
    return false;
  }

  function get_subscription_info($id = '') {
    return '';
  }

  function get_subscription_full_info($id = '') {
    return [];
  }

  function cancel_subscription($id = '') {
    return false;
  }

  function terminate_subscription($id = '', $type = 'none') {
    return false;
  }

  function postpone_subscription($id = '', $date = '') {
    return false;
  }

  function reactivate_subscription($id = '') {
    return false;
  }

  function isOnline() {
    return false;
  }

  function isPartlyPaid() {
    if (
        ($this->manager && $this->manager->has('partly_paid_oid') && $this->manager->get('partly_paid_oid')>0)  ||
        strpos($_SERVER['REQUEST_URI'], 'order-confirmation') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'order-process') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'order-pay') !== false ||
        (isset(\Yii::$app->request->queryParams['page_name']) && in_array(\Yii::$app->request->queryParams['page_name'], ['order_pay']))
    ) {
      return true;
    }
    return false;
  }

    public function updateTitle($platformId = 0)
    {
        return true;
    }

  const PAYMENT_PAGE = 1;
  const CONFIRMATION_PAGE = 2;
  const PROCESS_PAGE = 3;

/**
 * creates checkout URLs for checkout and payer
 * @param array $params get params
 * @param int $checkoutPage   const: self::PAYMENT_PAGE = 1 CONFIRMATION_PAGE = 2 PROCESS_PAGE = 3;
 * @return string URL
 */
  function getCheckoutUrl(array $params, int $checkoutPage = 0) {
    if ($this->isPartlyPaid() && $this->manager->isInstance()) {
      if (!isset($params['order_id'])) {
        $params['order_id'] = $this->manager->getOrderInstance()->order_id;
      }
      switch ($checkoutPage) {
        case self::CONFIRMATION_PAGE :
          $url = 'payer/order-confirmation';
          break;
        case self::PROCESS_PAGE :
          $url = 'payer/order-process';
          break;
        case self::PAYMENT_PAGE :
        default:
          $url = 'payer/order-pay';
          break;
      }
      return \Yii::$app->urlManager->createAbsoluteUrl(array_merge([$url], $params), ((ENABLE_SSL == true) ? 'https' : 'http'));
    } else {
      if ( !empty($params['payment_error']) && isset($params['order_id']) && is_numeric($params['order_id']) ){
        $url = 'payer/order-pay';
      }else
      switch ($checkoutPage) {
        case self::CONFIRMATION_PAGE :
          $url = defined('FILENAME_CHECKOUT_CONFIRMATION') ? FILENAME_CHECKOUT_CONFIRMATION : '';
          break;
        case self::PROCESS_PAGE :
          $url = defined('FILENAME_CHECKOUT_PROCESS') ? FILENAME_CHECKOUT_PROCESS : '';
          break;
        case self::PAYMENT_PAGE :
        default:
          $url = defined('FILENAME_CHECKOUT_PAYMENT') ? FILENAME_CHECKOUT_PAYMENT : '';
          break;
      }
      return \Yii::$app->urlManager->createAbsoluteUrl(array_merge([$url], $params), ((ENABLE_SSL == true) ? 'https' : 'http'));
    }
  }

  function forShop() {
    return true;
  }

  function forPOS() {
    return false;
  }

  function forAdmin() {
    return false;
  }

  function forCollect() {
    return false;
  }

  /**
   * save order before redirect to payment gateway
   * @param bool|string $asType orderClass (for now TmpOrder only) or false (
   * @param bool  $updateStock default false
   * @param array $params extra order params default []
   * @return integer|null - orderId
   */
  protected function saveOrder($asType = false, $updateStock = false, $params = []) {

    $ret = null;

    switch ($asType) {
      case 'TmpOrder':
        $tmpOrder = $this->manager->getParentToInstance('\common\classes\TmpOrder');
        if (is_object($tmpOrder)) {
          if (is_array($params) && count($params)) {
            foreach ($params as $k => $v) {
              if (property_exists($tmpOrder, $k)) {
                if (is_array($v) && is_array($tmpOrder->$k)) {
                  $tmpOrder->$k = array_merge_recursive($tmpOrder->$k, $v);
                } elseif (is_scalar($v) && is_scalar($tmpOrder->$k)) {
                  $tmpOrder->$k = $v;
                }
              }
            }
          }
          $ret = $tmpOrder->save_order();
          $tmpOrder->save_details(false);
          $tmpOrder->save_products(false);
        }
        break;

      case 'Order':
      default:
        /** @var common\classes\Order $order */
        $order = $this->manager->getOrderInstance();
        $order->save_order();
        $order->save_details(!empty($params['notify']));

        $order->save_products(!empty($params['notify']));

        $ret = $order->order_id;
        break;
    }

    return $ret;
  }

/**
 * part of checkout /process between before and after process. for modules which save usual order and call after_process in before_process method.
 */
    protected function no_process($order) {

        // process
        foreach (\common\helpers\Hooks::getList('checkout/process', '') as $filename) {
            include($filename);
        }

        $this->manager->getTotalCollection()->apply_credit(); //ICW ADDED FOR CREDIT CLASS SYSTEM

        foreach ($order->products as $i => $product) {
            $uuid = $this->before_subscription($i);
            if ($uuid != false) {
                $info = $this->get_subscription_info($uuid);
                $subscription_id = $order->save_subscription(0, $order->order_id, $i, $uuid, $info);
            }
        }
        global $cart;
        $cart->order_id = $order->order_id;

    }
/**
 * part of checkout /process after after_process. for modules which save usual order and call after_process in before_process method.
 * redirect to success page
 * @param Order $order
 * @param bool $redirect
 */
    protected function no_process_after($order, $redirect=true) {

        $this->trackCredits();
        $this->manager->clearAfterProcess();

        foreach (\common\helpers\Hooks::getList('checkout/after-process', '') as $filename) {
            include($filename);
        }
        if ($redirect) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, 'order_id=' . $order->order_id, 'SSL'));
        }
    }

    protected function getOrderClassBeforePayment($config_key) {
        if (defined($config_key) && in_array(constant($config_key), ['TmpOrder', 'Order']) ) {
            $orderClass = constant($config_key);
        } else {
            ///default
            $orderClass = 'TmpOrder';
            //$orderClass = 'Order';
        }
        return $orderClass;
    }

    /**
     *
     * @return string (<order id> or 'tmp<order id>-e<order id expected>'
     */
    public function saveOrderBySettings() {
        $orderClass = $this->orderTypeBeforePayment();
        if ($orderClass != 'TmpOrder') {
            $order = $this->manager->getOrderInstance();
            $order->info['order_status'] = $this->getDefaultOrderStatusId();
        }
        $ret = $this->saveOrder($orderClass);
        if (!empty($ret) && $orderClass == 'TmpOrder') {
            $ret = 'tmp' . $ret . '-e' . $this->estimateOrderId();
        }
        return $ret;
    }


/**
 * get order id from order payment table (by transactions id)
 * order is created by web hoook/ipn - it could take some time to save it - during this time the orders payment record has only transaction id and nothing else.
 * @return int|null|false null - no transaction, false - no order
 */
    protected function getOrderByTransactionId($id) {
        $ret = null;
        try {
            for ($i = 0; $i < 12; $i++) {
                $transaction = \common\models\OrdersPayment::findOne(['orders_payment_module' => $this->code, 'orders_payment_transaction_id' => $id]);

                if (!empty($transaction) ) {
                    if (!empty($transaction->orders_payment_order_id)) {
                        $ret = $transaction->orders_payment_order_id;
                        break;
                    } else {
                        $ret = false;
                    }
                }
                sleep(10); // not processed yet
            }
        } catch (\Exception $e) {
            \Yii::warning(" #### " . print_r($e, true), $this->code . 'TLDEBUG');
        }

        if (!$ret) {
            \Yii::warning(" transactionId " . print_r($id, true), $this->code . 'TLDEBUG-prbl-duplicate-order');
        }

        return $ret;
    }

  /**
   * submit request using Curl and returns header/data
   * @param string $url
   * @param array $params post | ['post'=>'', 'header' =>'', 'headerOut' => 1]
   * @return array ['code'=> int, response =>'', headers =>[], header =>'' ]
   */
  protected function sendRequest($url, $params) {

    if (isset($params['post'])) {
      $post = $params['post'];
    } else {
      $post = $params;
    }

    $curl = curl_init($url);
    if ($post) {
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    } else {
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
      curl_setopt($curl, CURLOPT_POST, 0);
    }

    curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    if (!empty($params['header'])) {
      curl_setopt($curl, CURLOPT_HEADER, 1);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $params['header']);
    }

    $response = curl_exec($curl);

    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    $headers = array_map('trim', explode(PHP_EOL, $header));

    curl_close($curl);

    if (!empty($params['headerOut'])) {
      $ret = [
        'code' => $http_code,
        'response' => $body,
        'headers' => $headers,
        'header' => $header
      ];
    } else {
      $ret = [
        'code' => $http_code,
        'response' => $body
      ];
    }

    return $ret;
  }

  /**
   * it should be overridden & called in payment module to populate $this->transactionInfo
   * @param bool $notify customer default false
   * @return nothing | ['checkout/success']
   */
  protected function processPaymentNotification($notify = false) {

    if ($this->transactionInfo && !empty($this->transactionInfo['order_id'])) {

      $order_id = $this->transactionInfo['order_id'];
      $transaction_id = $this->transactionInfo['transaction_id'];
      $transaction_details = $this->transactionInfo['transaction_details'];
      $silent = $this->transactionInfo['silent'];
      if (isset($this->transactionInfo['status'])) {
        $status = $this->transactionInfo['status'];
      } elseif ($this->paid_status > 0) {
        $status = $this->paid_status;
      } else {
        $status = false;
      }

      /* @var $order \common\classes\Order */
      $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $order_id);
      /* @var $oModel \common\models\Orders */
      $oModel = $order->getARModel()->where(['orders_id' => $order_id])->one();

      $otl = \common\models\OrdersTransactions::findOne(['orders_id' => $order_id, 'transaction_id' => $transaction_id]);
      if ($otl) {
        $tList[] = $transaction_id;
      } else {
        $tList = [];
      }

      /* 2do not fully paid && additional paid amount
        if (abs($order->info['total_inc_tax'] - $order->info['total_paid_inc_tax'] - floatval($response->getAmountSettlement())) > 0.01) {
        $order->info['total_paid_inc_tax'] = $order->info['total_inc_tax'] - floatval($response->getAmountSettlement());
        }
       */

      if (empty($tList) || !in_array(trim($transaction_id), $tList)) {
        $order->info['comments'] = str_replace(["\n\n", "\r"], ["\n", ''], $transaction_details);
        if ($status) {
          $oModel->orders_status = $order->info['order_status'] = $status;
          $oModel->update(false);
        }

        //{{ transactions
        /** @var \common\services\PaymentTransactionManager $tManager */
        $tManager = $this->manager->getTransactionManager($this);
        $invoice_id = $this->manager->getOrderSplitter()->getInvoiceId();
        $tManager->addTransaction($transaction_id, 'Success', $this->transactionInfo['amountPaid'], $invoice_id, $transaction_details);
        //{{

        $orderPayment = $this->searchRecord($transaction_id);
        $orderPayment->orders_payment_order_id = $order_id;
        $orderPayment->orders_payment_snapshot = json_encode(OrderPaymentHelper::getOrderPaymentSnapshot($order));
        $orderPayment->orders_payment_status = OrderPaymentHelper::OPYS_SUCCESSFUL;
        $orderPayment->orders_payment_amount = (float) $this->transactionInfo['amountPaid'];
        $orderPayment->orders_payment_currency = trim($order->info['currency']);
        $orderPayment->orders_payment_currency_rate = (float) $order->info['currency_value'];
        $orderPayment->orders_payment_transaction_date = new \yii\db\Expression('now()');
        $orderPayment->orders_payment_transaction_id = $transaction_id;
        $orderPayment->save(false);
        //}} transactions

        /**  2do (to replace when special method exists in order class */
        if (isset($order->products) && is_array($order->products)) {
          foreach ($order->products as $p) {
            if (!empty($p['orders_products_id'])) {
              \common\helpers\OrderProduct::doAllocateAutomatic($p['orders_products_id'], true);
            } else {
              \Yii::warning('Product stock allocation failed - no orders_products_id Order# ' . $order_id, 'stock allocation');
            }
          }
        }
        /** 2do eof */
        $order->update_piad_information(true);
        $order->save_details($notify);

        if ($notify) {
          $order->notify_customer($order->getProductsHtmlForEmail(), []);
        }

        foreach (\common\helpers\Hooks::getList('module-payment/process-notification') as $filename) {
            include($filename);
        }

        try {
          $provider = (new \common\components\GoogleTools())->getModulesProvider();
          $installed_modules = $provider->getInstalledModules($order->info['platform_id']);
          if (isset($installed_modules['ecommerce'])) {
            $installed_modules['ecommerce']->forceServerSide($order);
          }
        } catch (\Exception $e) {
          \Yii::warning($e->getMessage(), 'CHECKOUT_GOOGLE_ECOMMERCE');
        }
      }

      // it shouldn't be here
      // $this->after_process();
      //if (empty($silent)) {
      //  return ['checkout/success'];
      //}
    }
  }

  /**
   * it should be overridden in payment module if the module creates order (to delete it)
   * @param bool $notify customer default false
   */
  protected function processPaymentCancellation($notify = false) {
    if ($this->transactionInfo && !empty($this->transactionInfo['order_id'])) {

      $order_id = $this->transactionInfo['order_id'];
      if (isset($this->transactionInfo['status'])) {
        $status = $this->transactionInfo['status'];
      } else {
        $status = false;
      }
      $transaction_id = $this->transactionInfo['transaction_id'];
      $transaction_details = $this->transactionInfo['transaction_details'];

      /* @var $order \common\classes\Order */
      $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $order_id);
      /* @var $oModel \common\models\Orders */
      $oModel = $order->getARModel()->where(['orders_id' => $order_id])->one();

      //2do - transactions table instead of feild.
      if (!empty($oModel->transaction_id)) {
        $tList = preg_split('/\|/', $oModel->transaction_id, -1, PREG_SPLIT_NO_EMPTY);
      } else {
        $tList = [];
      }

      if (empty($tList) || !in_array(trim($transaction_id), $tList)) {
        $order->info['comments'] = str_replace(["\n\n", "\r"], ["\n", ''], $transaction_details);
        if ($status) {
          $oModel->orders_status = $order->info['order_status'] = $status;
        }

        $oModel->transaction_id = implode('|', array_merge([trim($transaction_id)], $tList));
        $oModel->update(false);

        /**  2do (to replace when special method exists in order class */
        if (isset($order->products) && is_array($order->products)) {
          foreach ($order->products as $p) {
            if (!empty($p['orders_products_id'])) {
              \common\helpers\OrderProduct::doAllocateAutomatic($p['orders_products_id'], true);
            } else {
              \Yii::warning('Product stock allocation failed - no orders_products_id Order# ' . $order_id, 'stock allocation');
            }
          }
        }
        /** 2do eof */
        $order->save_details($notify);

        if ($notify) {
          $order->notify_customer($order->getProductsHtmlForEmail(), []);
        }
      }
    }
  }

  /**
   *
   * @param array $data (associative) of passed data
   * @param string  $api_key use in hash function
   * @param bool $incEmpty default true
   * @param bool $sort default true
   * @param string $algo default sha256
   * @return string
   */
  protected function generateSignature($data, $api_key, $incEmpty = true, $sort = true, $algo = 'sha256') {
    $ret = '';

    $algos = hash_algos();

    if (is_array($data) && in_array($algo, $algos)) {

      $clear_text = '';
      if ($sort) {
        ksort($data);
      }
      foreach ($data as $key => $value) {
        if ($incEmpty || !empty(value)) {
          $clear_text .= $key . $value;
        }
      }

      $ret = hash_hmac($algo, $clear_text, $api_key);
    }

    return $ret;
  }

  public function getDefaultOrderStatusId(): int {
    static $status_id = null;
    if ($status_id === null) {
      $status_id = 0;
      if ($this->isOnline()) {
        $defaultPaymentOS = \Yii::$app->get('db')->createCommand("SELECT configuration_value FROM configuration WHERE configuration_key = 'DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS_ID'")->queryOne();
      } else {
        $defaultPaymentOS = false;
      }
      if ($defaultPaymentOS) {
        $status_id = (int) $defaultPaymentOS['configuration_value'];
      } else {
        $defaultOS = \Yii::$app->get('db')->createCommand("SELECT configuration_value FROM configuration WHERE configuration_key = 'DEFAULT_ORDERS_STATUS_ID'")->queryOne();
        if ($defaultOS) {
          $status_id = (int) $defaultOS['configuration_value'];
        }
      }
    }
    return $status_id;
  }

  public function searchRecord($orderPaymentTransactionId = '') {
    $orderPaymentRecord = \common\helpers\OrderPayment::searchRecord($this->code, $orderPaymentTransactionId);
    if ($orderPaymentRecord instanceof \common\models\OrdersPayment) {
      if ($orderPaymentRecord->orders_payment_module_name == '') {
        $orderPaymentRecord->orders_payment_module_name = $this->title;
      }
      return $orderPaymentRecord;
    }
    return false;
  }

/**
 * search order payment record by transaction_id and if found then request transaction details from gateway update payment and order details (totals, status)
 * for transactional payment modules only.
 * @param string $transaction_id
 */
    protected function getUpdateTransaction($transaction_id) {
        $op = $this->searchRecord($transaction_id);
        if ($op && $this instanceof \common\classes\modules\TransactionalInterface) {
            if (!empty($op->orders_payment_id)) {
                $this-> _transactionDetails = false;
                $res = \common\helpers\OrderPayment::updateTransactionDetails(
                        $op,
                        $this,
                        $this->manager,
                        false
                );

            } else {
                \Yii::warning("empty payment_id", 'TLDEBUG_' . $this->code);
            }
        } else {
            \Yii::warning("payment not found \$transaction_id $transaction_id", 'TLDEBUG_' . $this->code);
        }
    }

  public function tokenAllowed(): bool {
    return defined('USE_TOKENS_IN_PAYMENT_METHODS') ? USE_TOKENS_IN_PAYMENT_METHODS == 'True' : false;
  }

  /**
   * true if module supports tokens
   * @return bool
   */
  public function hasToken(): bool {
    /* override in your module if it's support tokens
      return true && parent::tokenAllowed();
     */
    return false;
  }

  /**
   * true if tokens is allowed in the module settings
   * @return bool
   */
  public function useToken(): bool {
    /* override in your module if tokens is enabled in the method settings
      return $this->hasToken() && defined('MODULE_PAYMENT_SAGE_PAY_SERVER_USE_TOKENS') && MODULE_PAYMENT_SAGE_PAY_SERVER_USE_TOKENS  == 'True';
     */
    return false;
  }

  /**
   * returns [all] customer token(s)
   * @param int $customersId
   * @param int $tokenId
   * @return array|null
   */
  public function getTokens($customersId, $tokenId = false) {
    $ret = null;
    if ($this->useToken()) {
      $q = \common\models\PaymentTokens::find()->andWhere([
        'customers_id' => (int) $customersId,
        'payment_class' => $this->code,
      ]);
      if ($tokenId) {
        $q->andWhere(['payment_tokens_id' => $tokenId]);
      }
      $ret = ArrayHelper::toArray($q->all()); //do not use asArray() as there are afterFind method
    }
    return $ret;
  }

  /**
   * check customers token
   * @param int $customersId
   * @param string $token
   * @return bool
   */
  public function checkToken($customersId, $token) {
    $ret = false;
    if ($this->useToken()) {

      $tokens = $this->getTokens($customersId);
      if (is_array($tokens)) {
        $arr = ArrayHelper::getColumn($tokens, 'token');
        $ret = in_array($token, $arr);
      }
    }
    return $ret;
  }

  /**
   * checks customer/token and try to delete it from DB. Override this method in your module to delete the token at gateway.
   * @param int $customersId
   * @param string $token
   * @return bool (deleted - true, not found - false)
   */
  public function deleteToken($customersId, $token) {
    $res = false;
    if ($this->checkToken($customersId, $token)) {
      $res = \common\models\PaymentTokens::deleteToken($customersId, $this->code, $token);
    }
    return $res;
  }

  /**
   * save token in DB, update only default flag, set old_payment_token to update existing token
   * @param int $customersId
   * @param array $tokenData [old_payment_token=>'', token => '', cardType => '', lastDigits =>'', fistDigits =>'', maskedCC =>'', expDate =>]
   * @return type
   */
  public function saveToken($customersId, $tokenData) {
    if ($this->useToken() && (int) $customersId > 0 && !empty($tokenData['token'])) {
      try {
        $m = false;

        if (!empty($tokenData['old_payment_token'])) {
          $tokens = $this->getTokens($customersId);

          if (is_array($tokens)) {
            $tokens = array_values(array_filter($tokens, function ($el) use($tokenData) {
                  return $el['token'] == $tokenData['old_payment_token'];
                }));
          }
          if (!empty($tokens[0]['payment_tokens_id'])) {
            $m = \common\models\PaymentTokens::findOne($tokens[0]['payment_tokens_id']);
          }
        } else {

          $m = new \common\models\PaymentTokens();
          $m->customers_id = $customersId;
          $m->payment_class = $this->code;

          $m->token = $tokenData['token'];

          if (!empty($tokenData['cardType'])) {
            $m->card_type = $tokenData['cardType'];
          }

          if (!empty($tokenData['expDate'])) {
            $m->exp_date = $tokenData['expDate'];
          } else {
            $m->exp_date = date('Y-m-01', strtotime("+20 years")); //FUI Visa
          }

          if (!empty($tokenData['maskedCC'])) {
            $m->last_digits = $tokenData['maskedCC'];
          } elseif (!empty($tokenData['lastDigits']) && !empty($tokenData['lastDigits'])) {
            $m->last_digits = $tokenData['fistDigits'] . str_repeat('x', 20 - strlen($tokenData['fistDigits'] . $tokenData['lastDigits'])) . $tokenData['lastDigits'];
          } elseif (!empty($tokenData['lastDigits'])) {
            $m->last_digits = str_repeat('x', 16 - strlen($tokenData['lastDigits'])) . $tokenData['lastDigits'];
          } elseif (!empty($tokenData['fistDigits'])) {
            $m->last_digits = $tokenData['fistDigits'] . str_repeat('x', 16 - strlen($tokenData['fistDigits']));
          }
        }

        if ($m) {
          $m->is_default = empty($this->manager->get('update_default_token')) ? 0 : 1;
          $m->save(false);
        }
      } catch (\Exception $e) {
        \Yii::warning($e->getMessage() . $e->getTraceAsString(), 'TOKEN_SAVE');
      }
    }
  }

  /**
   * checkout - module's selection method
   * @param int $customersId
   * @return array
   */
  public function renderTokenSelection($customersId = false) {
    $ret = null;
    if ($this->useToken()) {
      $ret = [];
      if (!empty($customersId)) {
        $tokens = $this->getTokens($customersId);
      }
      \Yii::$app->getView()->registerJs($this->getJS());
      $this->getCCCss();

      $ret[] = [
        'title' => '<label for="data_' . $this->code . '_use_token" class="use-token-label">' . sprintf(PAYMENT_USE_TOKEN_TEXT, ($this->public_title ? $this->public_title : $this->title)) . '</label>',
        'field' => Html::checkbox($this->code . 'use_token', !empty($tokens), ['id' => 'data_' . $this->code . '_use_token', 'class' => "use-token $this->code"]) /*. $this->getJS()*/
      ];
      if (!empty($tokens) && is_array($tokens)) {
        $ret[] = [
          'title' => '<label for="data_' . $this->code . '_use_token_0" class="token-label">' . PAYMENT_USE_DIFFERENT_CARD . '</label>',
          'field' => Html::radio($this->code . 'ptoken', empty($tokens), ['id' => 'data_' . $this->code . '_use_token_0', 'class' => "ptoken $this->code", 'value' => 0])
        ];
        foreach ($tokens as $token) {
          $ret[] = [
            'title' => '<i class="cc-icon cc-' . (!empty($token['card_type']) ? strtolower($token['card_type']) : 'unknown') . '"></i><label for="data_' . $this->code . '_use_token_' . $token['payment_tokens_id'] . '" class="token-label">' . (!empty($token['card_name']) ? $token['card_name'] : $token['last_digits']) . '</label>',
            'field' => Html::radio($this->code . 'ptoken', !empty($token['is_default']), ['value' => $token['token'], 'id' => 'data_' . $this->code . '_use_token_' . $token['payment_tokens_id'], 'class' => "ptoken $this->code"])
          ];
        }
      }
    }
    return $ret;
  }

  public function getJS() {
    return <<<EOD

function toggleSubFields_{$this->code}(){
    if ($('input[name=payment][value="{$this->code}"]').is(':checked')){
        $('.payment_class_{$this->code} .sub-item').show();
    } else {
        $('.payment_class_{$this->code} .sub-item').hide();
    }
    $('.payment_class_{$this->code} .sub-item label, .payment_class_{$this->code} .sub-item input').css('display', 'inline-block');

}
if (typeof tl == 'function'){
    tl(function(){ toggleSubFields_{$this->code}();
        $('input[name="payment"]').change(function(){toggleSubFields_{$this->code}(); })
        var target = document.getElementsByClassName('w-checkout-payment-method')[0];
        if ( !target || target.length==0 ) target = document.getElementsByClassName('w-update-and-pay-pay-form')[0];
        var config = {
            attributes: true,
            childList: true,
            subtree: true
        };
        const observer{$this->code} = new MutationObserver(function(){
            $('input[name="payment"]').off('change', toggleSubFields_{$this->code}).on('change', toggleSubFields_{$this->code});
        });
        try {
            observer{$this->code}.observe(target, config);
        } catch (e) { }
    })
}

EOD;
        //</script>
  }

  /**
   *  format prices without currency formatting Replace format_raw with formatRaw in most modules and delete it
   * @param decimal $number
   * @param string $currency_code
   * @param float $currency_value
   * @return decimal
   */
  function formatRaw($number, $currency_code = '', $currency_value = '') {
    $currencies = \Yii::$container->get('currencies');

    if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = \Yii::$app->settings->get('currency');
    }

    if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
    }

    return number_format(round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
  }

  /**
   * @return bool
   */
  public function isWithoutConfirmation(): bool {
    return defined('SKIP_CHECKOUT') && SKIP_CHECKOUT === 'True';
  }

  public function popUpMode() {
    return false;
  }

  public function directPayment() {
    return false;
  }

  /**
   * @return string
   */
  protected function tlPopupJS(): string {
    if (!$this->isWithoutConfirmation()) {
      $url = $this->getCheckoutUrl([], self::PROCESS_PAGE);
    } else {
      $url = $this->getCheckoutUrl(['order_id' => \Yii::$app->request->get('order_id')], self::CONFIRMATION_PAGE);
    }
    return $this->openPopupJS($url);
  }

  /**
   * @param string $url
   * @return JS string
   */
  public function openPopupJS(string $url, string $whScript = ''): string {
      if (!empty($whScript)) {
          $whScript = '
          var w = 300;
          var h = 300;' . $whScript;
      } else {
          $whScript = '
          var w = Math.max(300, Math.round(screen.width/2));
          var h = Math.max(300, Math.round(screen.height*0.65));
          ';
      }

    $ret = <<<EOD
        function popUpIframe{$this->code}() {
          var divId = 'tl-payment-popup-checkout';
          var frameId = 'tl-payment-popup-checkout-frm';
          var paymentPopup = $('#'+divId);
          if (paymentPopup.length>0) {
            paymentPopup.remove();
          }
          $('body').append('<div class="tl-payment-popup" id="' + divId + '" style = "display: none;"></div>');
          paymentPopup = $('#' + divId);

          //useless not aligned paymentPopup.html('<div style="width:' + Math.round(screen.width/2) +'px;height:' + Math.round(screen.height*0.65) +'px"></div>');
          paymentPopup.popUp({ 'event': 'show' });

          {$whScript}

          $(".popup-box").css("width", w +'px').css("height", h +'px');
          var d = ($(window).height() - $('.popup-box').height()) / 2;
          if (d < 0) d = 0;
          $('.popup-box-wrap').css('top', $(window).scrollTop() + d);
          //paymentPopup.position($('.popup-box:last'));
          $(".pop-up-content").html('<iframe src="{$url}" frameborder="0" style="width:100%;height:' + (h-15) +'px" class="payment-iframe payment-iframe-{$this->code}"></iframe>');
        }
EOD;

    return $ret;
  }

  /**
   * MOTO order - most payment gateways require to mark transaction as Moto (card not present)
   */
  public function onBehalf() {
    $ret = false;
    if (!empty(\Yii::$app->settings->get('from_admin'))) {
      $ret = true;
    }
    return $ret;
  }

  /**
   * Register payment jsCallback
   * @param type $callback
   */
  public function registerCallback($callback) {
    $colection = $this->manager->getPaymentCollection();
    if (!$colection->hasCallback($this->code)) {
      $colection->registerCallback($this->code, $callback);
    }
  }

  public function refundOrderStatus() {
    static $status_id = false;
    if ($status_id === false) {
      if ($this->refund_status) {
        $status_id = $this->refund_status;
      } else {
        $status_id = \common\models\OrdersStatus::getDefaultByOrderEvaluationState(\common\helpers\Order::OES_CANCELLED);
        if ($status_id) {
          $status_id = $status_id->orders_status_id;
        }
      }
    }
    return $status_id;
  }

  public function partialRefundOrderStatus() {
    static $status_id = false;
    if ($status_id === false) {
      if ($this->partial_refund_status) {
        $status_id = $this->partial_refund_status;
      } else {
        $status_id = \common\models\OrdersStatus::getDefaultByOrderEvaluationState(\common\helpers\Order::OES_PARTIAL_CANCELLED);
        if ($status_id) {
          $status_id = $status_id->orders_status_id;
        }
      }
    }
    return $status_id;
  }

  public function paidOrderStatus() {
    $detectedStatus = null;
    if ($this->paid_status ?? null) {
      $detectedStatus = $this->paid_status;
    } elseif (defined('ORDER_STATUS_FULL_AMOUNT') && (int)ORDER_STATUS_FULL_AMOUNT >0){
      $detectedStatus = constant('ORDER_STATUS_FULL_AMOUNT');
    }
    if (!empty($detectedStatus) && !\common\helpers\Order::isStatusExist($detectedStatus) ){
      $detectedStatus = null;
    }
    if (empty($detectedStatus) ) {
        if (defined('ORDER_STATUS_FULL_AMOUNT') && (int)ORDER_STATUS_FULL_AMOUNT >0) {
          $detectedStatusBE = constant('ORDER_STATUS_FULL_AMOUNT');
          if ($detectedStatusBE && !\common\helpers\Order::isStatusExist($detectedStatusBE) ){
            $detectedStatusBE = null;
          }
        }
        if (defined('DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS_ID') && (int)DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS_ID >0) {
          $detectedStatusFE = constant('DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS_ID');
          if ($detectedStatusFE && !\common\helpers\Order::isStatusExist($detectedStatusFE) ){
            $detectedStatusFE = null;
          }
        }
        if (!empty($detectedStatusFE) && !\frontend\design\Info::isTotallyAdmin() ) {
            $detectedStatus = $detectedStatusFE;
        /*} elseif (!empty($detectedStatusBE) && \frontend\design\Info::isTotallyAdmin() ) {
            $detectedStatus = $detectedStatusBE;*/
        } elseif (!empty($detectedStatusBE)) {
            $detectedStatus = $detectedStatusBE;
        } elseif (!empty($detectedStatusFE)) {
            $detectedStatus = $detectedStatusFE;
        }
    }


    return $detectedStatus;
  }

  public function partlyPaidOrderStatus() {
    $detectedStatus = null;
    if ($this->partlypaid_status) {
      $detectedStatus = $this->partlypaid_status;
    } elseif (defined('ORDER_STATUS_PART_AMOUNT') && (int)ORDER_STATUS_PART_AMOUNT >0){
      $detectedStatus = constant('ORDER_STATUS_PART_AMOUNT');
    }
    if ($detectedStatus && !\common\helpers\Order::isStatusExist($detectedStatus) ){
      $detectedStatus = null;
    }
    return $detectedStatus;
  }

  public function updatePaidTotalsAndNotify($commentary = '', $notify = false) {
    $updateOrder = true;
    /** @var \common\services\PaymentTransactionManager $tm */
    $tm = $this->manager->getTransactionManager($this);
    if ($this->manager->isInstance() ) {
      $order = $this->manager->getOrderInstance();
    }
    if ($updateOrder && $order && $order->info['orders_id']) {
      $updated = $order->updatePaidTotals();
      if ($updated) { //update order status and notify customer if required
        $status = '';
        if (isset($updated['paid']) ) {
          if ($updated['details']['status']>0) {// has due
            $status = $this->paidOrderStatus();
          } else {
            $status = $this->partlyPaidOrderStatus();
          }
        } elseif (isset($updated['refund']) && ($updated['details']['credit']>0 || $updated['details']['due']>0)) {
            $tmp = (($updated['details']['credit']??0)>0 ? $updated['details']['credit'] : $updated['details']['due']);
          if (abs(
              round($updated['details']['total'], 2) -
              round($tmp, 2)
              //round($updated['details']['credit'], 2)
              ) < 0.01) {
            $status = $this->refundOrderStatus();
          } else {
            $status = $this->partialRefundOrderStatus();
          }
        }

        if (1 && !empty($status) && $status != $order->info['order_status']) {
          $order->update_status_and_notify($status, false, $commentary, [], [], $notify);
        }
      }
    }
  }

/**
 * use in payment modules which create orders itself and then switch it to "paid" status (common - in after_process after save transaction details)
 * to track coupons usage
 * part of standard checkout/process
 */
    public function trackCredits() {
        try {
            $order = $this->manager->getOrderInstance();
        } catch (\Exception $ex) {
            return false;
        }
        $orderId = (int) $order->order_id;
        if ($orderId > 0) {
            if ($this->manager->has('cc_id') || $this->manager->has('cot_gv')) {
                $this->manager->getTotalCollection()->apply_credit();
            } else {
///something?? 2do from totals if this is notify without session (ex from tmp order)
                $orderTotal = $order->totals;
                if ($ccExt = \common\helpers\Acl::checkExtensionAllowed('CustomerCredit', 'allowed')) {
                    $ccExt::onOrderSave($this->manager, $orderTotal);
                }
                if (is_array($orderTotal)) {
                    foreach ($orderTotal as $total) {
                        if ($total['code'] == 'ot_coupon') {
                            $res = [];

                            preg_match('/\(([^\)]+)\)/', $total['title'], $res);

                            if (!empty($res[1])) {
                                $coupon = \common\helpers\Coupon::getCouponByCode($res[1], false);
                                if ($coupon) {
                                    $coupon->addRedeemTrack($order->customer['customer_id'], $order->order_id, $total['value_inc_tax']);
                                }
                            }
                        } elseif ($total['code'] == 'ot_gv') {
                            if ($order->customer['customer_id']) {
                                $currencies = \Yii::$container->get('currencies');
                                $gv_payment_amount = round($total['value_inc_tax'], 2);
                                try {
                                    //check amount/customer/order in history
                                    $check = \common\models\CustomersCreditHistory::find()
                                        ->andWhere(
                                          new \yii\db\Expression('abs(credit_amount - ' . (float)$gv_payment_amount . ')<0.2')
                                          )
                                        ->andWhere(['like', 'comments', $order->order_id])
                                        ->andWhere([
                                            'customers_id' => (int)$order->customer['customer_id'],
                                            'credit_prefix' => '-',
                                          ])->exists();
                                    if (!$check) {
                                        $customer = \common\components\Customer::findOne(['customers_id' => $order->customer['customer_id']]);
                                        $customer->credit_amount -= $gv_payment_amount;
                                        $customer->save();
                                        $customer->saveCreditHistory($order->customer['customer_id'], $gv_payment_amount, '-', DEFAULT_CURRENCY, $currencies->currencies[DEFAULT_CURRENCY]['value'], 'Order #' . $order->order_id);
                                    }
                                } catch (\Exception $e) {
                                    \Yii::warning(print_r($e->getMessage(), true), 'TLDEBUG');
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    function estimateOrderId() {
        $ret = '';
        try {
            $d = \common\models\Orders::find()->select(['oid' => new \yii\db\Expression('max(orders_id) +1')])->asArray()->one();
            if ($d['oid']) {
                $ret .= $d['oid'];
            }
        } catch (\Exception $e) {
            \Yii::warning(" #### " . print_r($e->getMessage(), 1), 'TLDEBUG-' . $this->code??'payment');
        }
        return $ret;
    }

    /**
     * Payment notification and return could be sent by gateway almost in the same time.
     * So the same payment details could be processed twice (simultaneously).
     * To avoid it "lock" order payment transaction record (create first and ignore all other attempts).
     * There isn't unique key for module,transaction_id (because of manual ids for offline payments)
     * So extra query :(
     * @param string $id
     * @return boolean
     */
    public function lockTransaction($id) {
        $ret = false;
        try {
            $transaction = \common\models\OrdersPayment::findOne(['orders_payment_module' => $this->code, 'orders_payment_transaction_id' => $id]);
            if (empty($transaction)) {
                $transaction = new \common\models\OrdersPayment();
                $transaction->loadDefaultValues();
                $transaction->orders_payment_module = $this->code;
                $transaction->orders_payment_transaction_id = $id;
                $transaction->save(false);
                //Check whiether the record is newest one.
                $chk = \common\models\OrdersPayment::find()
                    ->andWhere(['<', 'orders_payment_id', $transaction->orders_payment_id])
                    ->andWhere([
                      'orders_payment_module' => $this->code,
                      'orders_payment_transaction_id' => $id
                    ])
                    ->count();
                if ($chk == 0) {
                    $ret = true;
                } else {
                    //remove duplicate
                    \common\models\OrdersPayment::deleteAll(['orders_payment_id' => $transaction->orders_payment_id]);
                    //$transaction->delete();
                }
            }
        } catch (\Exception $e) {
            \Yii::warning(print_r('lockTransaction ' . $e->getMessage(), true), 'TLDEBUG-' . $this->code);
        }
        return $ret;
    }

/**
 * tmp order - convert to real one, IPN/webhook could do the same :(
 * use child_id as a lock flag (-1*ID) possible problem in case of 2^32 order number
 */
    public function lockTmpOrder($id) {
        $ret = false;
        $tmpAROrder = \common\models\TmpOrders::find()->where(['orders_id' => $id, 'child_id' => 0])->one();
        if (!empty($tmpAROrder)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $tmpAROrder->child_id = -1;
                $tmpAROrder->save(false);
                $transaction->commit();
                $ret = true;
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
        }
        return $ret;
    }

    protected function checkStatusByZone($zone_id, $which = 'billing') {
        return parent::checkStatusByZone($zone_id, $which);
    }

    public function getCCCss() {
      \Yii::$app->getView()->registerCss("
.cc-AMEX, .cc-amex {
  background-image:  url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTExIDc5LjE1ODMyNSwgMjAxNS8wOS8xMC0wMToxMDoyMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjY2MjcyQUIzRDBCRDExRTk4RDgwQzVFODIzNUVBNjNEIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjY2MjcyQUI0RDBCRDExRTk4RDgwQzVFODIzNUVBNjNEIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NjYyNzJBQjFEMEJEMTFFOThEODBDNUU4MjM1RUE2M0QiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NjYyNzJBQjJEMEJEMTFFOThEODBDNUU4MjM1RUE2M0QiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4j9FbEAAAJbElEQVRo3u1a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');
  background-position: 30%;
}
.cc-DC, .cc-dc {
  background-image:  url('data:image/jpeg;base64,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');
  background-size: contain;
}
.cc-DELTA, .cc-VISA, .cc-delta, .cc-visa {
  background-image:  url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACIAAAAMCAYAAAAH4W+EAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAANuSURBVHgBzVRPaxRZEK963TPmlvkGtmEGvCzpXfaQw0paFmRvyQcQZrwsiwjJ7mFBAs5EL0IEFQ+Kf3AGvTt68SA4PQgS8GDnoAgzo51PYDx4MOlXZdXr7mQ0fgBfd9OvX9X7varfr6oBfpKBc43TKwi4zAYDQAP6GDS98du7ndKpEa6GBOYRIAKLrVo1XUv+gMATd+yNN9c7QdSu+dnMKqLXFB/B8tQ3BfA6k/hcr8Sqn7rXQTRNNGIH0x89Pf2vrpv3o4fXJ6MHJ8lyCxlqshYQ4IUgbNXKzcSwAsCB2gzDYG+vEhLzUV2T90f1qexWXwtymxmOyuMudHvsftbH/7ofyPoFd4b4yTsqbaacpOMHQzbQBUFxz+dKUNqYaRFy7HT8emOImM3LIqofESeNhYstdofKN9vrxtJvGfNJcThjgYYljrV7bUTZJ34IpO8wiO67hP1vhGLYKiboVXleJsncL2dbAnjM6cjcKfwiQdIb0leXhnMLnci4uIRsgzB6+X9SIA4P2LgZ2IxaukuC7svSkgblz/ihzGMzHQfNQN+hqSthmB+OzYKlHQv8OJeKlBG5bZzTykP91iNkrNb/uDwIFq4G09h2lyInmbLBdl0P0C8ph/kcY2qkSXdHHLcL0DA4/o+AUQTuAOqnybUdLVyx1TRgYcRlLsUaE1An99MAIfIr2Yf6iY3VA7aprUyLzPHo2d+J7E1lTSM4HEgx+i5b4tDzWIqPHLL9srfu2JBC095xoEBxuen9ZmfdZ/+YxNd1qeohiO0gulqrRzckGQg0eAm/6zYgxk5bqaUfBiK0DSGnZFaoXwZXChSn726lLjGARaeezETz7em97zbPp5OXa2fEltcIcw137SyCbeadxCpjt/7nbVHXNjVY7TItWP/7QOyuH5sjeWUrUE636e4HChC6zAB20mQjqf++FonaTflfJMbDLTB+JPawkOnDkaqP0kEtQPkpIOapYImvxJAULISHGElTVydJUbSKlk7e3OwdaM2RyoJMW7lUdlGEbxoD18T7udjb+7WC2X8Z0QoUDBLBcsaVwJKn8v7KRWMgm/lDjOS0cI89XnJg1rWaG43wbJgRD6RDpT5sL5dSWECIBVNaHGvouov68s/tjV+sJY0TV5aEsYEE82kyPPd46pTt+qk7T4TtWeGn9hWzcuysQhuPrwAAAABJRU5ErkJggg==');
}
.cc-JCB, .cc-jcb {
  background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAnCAYAAABNJBuZAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTExIDc5LjE1ODMyNSwgMjAxNS8wOS8xMC0wMToxMDoyMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgV2luZG93cyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo0RTA4NjQ4M0QwQkQxMUU5QkVDQUM4QzFBMkI4RjRBMiIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo0RTA4NjQ4NEQwQkQxMUU5QkVDQUM4QzFBMkI4RjRBMiI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjRFMDg2NDgxRDBCRDExRTlCRUNBQzhDMUEyQjhGNEEyIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjRFMDg2NDgyRDBCRDExRTlCRUNBQzhDMUEyQjhGNEEyIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+gHDIWAAACnVJREFUWMPtmXmM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');
  background-size: 50%;
}
.cc-MAESTRO, .cc-maestro {
  background-image:  url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACUAAAAXCAMAAAC78naXAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAC6UExURQAAAACf388gEACa49IgEACX388gENMgEACY4tIgEACZ39IgEACZ4dIgEACZ3wCZ4dIgENMgEgCY3wCY4ACa39IgENQgEtIgEQCa4Glhj9MgEACZ3wCa39MgENMgEQCZ39IgEdQgEQCZ39MgEQCZ3w2T2xuN1i+F0DaBzTaCzkN8yUR8yUp5yFF2xVF3xlh0xF5xwWxrvXlhp39dnYVYkotUiJNPfJ9FZ59GZ6ZBXbkzPMYpJ8YqJ9MgEfmo0uMAAAAkdFJOUwAQED8/QEBAT09QUF9gj4+PkJ+fn5+foL+/v8/Pz8/f39/v79+i97AAAADuSURBVCjPjdNnE4IwDAbgOHAPVNwoFuqeda/+/78lVFCS63nmW4/nQpN7C/CubJN50hu2MupkVG2Xc7tRgHgVmYyKZcDo8Kic0sck2zJeXR6vaqSGCJ3FAjE7oVANoasQYoaYFaAyQk8R1AQxw1cMqaNSa6QcfzxNKyHGpFkLqVuopkhVyICnUK3ILxGSh1DtkHKJ2msVB0+rtkSxP+7VB/OPGeuQReqh3VcOYKTZ/YbunjS7a1qpjOF0XXw0J5tXIaT5WuJ8RTE0f2TVSnwyXf6O0EuD4Xwvjt9HvjmQ0huYaXVKWX2Xu04jFX59AenPhOS0Rm5pAAAAAElFTkSuQmCC');
}
.cc-MCDEBIT, .cc-MC, .cc-mcdebit, .cc-mc, .cc-mastercard {
  background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAASCAMAAAB7LJ7rAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAADMUExURQAAAO+fAOcQGO+fAOQLG/SfAOMMFPeiAOMMGOtYDPefAOINF+xHEPmhAOINFvWfAPeeAPehAPafAPahAPWhAPOfAPigAOILFuINFuQNFvWgAPafAPagAOMMF+MNF/egAOMMF+MOFuMOF/WfAPafAOINF+QNF+QOF/afAPagAOMNF/WWAfWfAOMNF+QSFuQYFeYyEeg8EOhCDulHDulMDepRDOpXC+taC+thCuxlCexpCe1pCO5wB+90Bu95BvKMA/OPAvSTAfWXAfafAOeIgNIAAAAtdFJOUwAQICAwMD8/QEBAT09PUFBfX29vf4CPn5+fn6+vv7+/z8/Pz8/f39/f3+/v7xqlv1MAAADFSURBVCjPfdHVDsJAFATQwd3dHQp0cRlc9v//iYeW3jYkzOvJTiZ7AQCIVidK9appAPHyVGuzmYMTf1vZGcU62o6RsTU4+6paHl/aScl669IDT3PxLABMHFUbkldh0wekRZckyad4EagJr0iSd2ED6AtvSZJnYe2DqNqRJOnikJv3PxxxD7fKL57yn2k30QEQFV6QJB/CBQBt8bW324gEPJ+6OPD0Fs78PUn+e1BnfStZl2a5eKLWVWpcCQMI1IemNhopCz79AV6NuICCnwAAAABJRU5ErkJggg==');
}
.cc-UKE, .cc-uke {
  background-image:  url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAAAzCAIAAACPExgOAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTExIDc5LjE1ODMyNSwgMjAxNS8wOS8xMC0wMToxMDoyMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjk5ODA3NjAzRDBCRDExRTk5MkNGODBBNzZBMEM5RThEIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjk5ODA3NjA0RDBCRDExRTk5MkNGODBBNzZBMEM5RThEIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6OTk4MDc2MDFEMEJEMTFFOTkyQ0Y4MEE3NkEwQzlFOEQiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6OTk4MDc2MDJEMEJEMTFFOTkyQ0Y4MEE3NkEwQzlFOEQiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7F//G9AAAKRUlEQVRo3u1Z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');
  background-size: 40%;
}

.cc-smaller,  .token-cc {
  font-weight: 500;
}
.payment-method .sub-item .ptoken {
    float:left;
    width:2em;
    min-width: 2em;
    margin: 5px 0 0 0;
}
.cc-icon {
    background-position: 50%;
    background-repeat: no-repeat;
    color: transparent;
    height:1em;
    min-width:2em;
    /*margin:-5px 7px;*/
    display:inline-block;
    background-size: contain;
}
.payment_class_{$this->code} .sub-item > label, .payment_class_{$this->code} .sub-item > input, .payment-method .payment_class_{$this->code} .sub-item span {
    display: inline-block;
}

.payment_class_{$this->code} .token-label {
line-height: 2;
padding-left: 10px;
}

");
    }

    protected function isOrderPaymentPage() {
        $ret = false;
        if (\Yii::$app->id=='app-frontend' && (
            (\Yii::$app->controller->id == 'checkout' && \Yii::$app->controller->action->id != 'login')
            || (\Yii::$app->controller->id == 'payer') ) ) {
            $ret = true;
        }
        return $ret;
    }

/**
 * format as `+` and `country code` and `phone number`
 * @param string $telephone
 * @param array $country
 * @return string
 */
    protected function formatPhone($telephone, $country) {
        $telephone = preg_replace('/0-9\+/', '', $telephone);
        // remove leading 0,
        if (substr($telephone, 0, 1) == '+') {
            $hasCountryCode = true;
        } elseif (substr($telephone, 0, 1) == '0') {
            $telephone = substr($telephone, 1);
        }

        if (!empty($country['dialling_prefix'])) {
            $_countryCode = preg_replace('/[^0-9]/', '', $country['dialling_prefix']);
        }

        if (!$hasCountryCode && !empty($_countryCode) ) {
            if (substr($telephone, 0, strlen($_countryCode))==$_countryCode && strlen($telephone)>7 ) {
                //suppose correct country code, add +
                    $telephone = '+' . $telephone;
            } else { // add country code
                $telephone = '+' . $_countryCode . $telephone;
            }
        }
        return $telephone;
    }

/*
 * Force turn on PreAuthorization transaction method if payment supports it.
 * PreAuthorize mode can be called differently in different payments:
 * - Authorize in paypal
 * - AuthOnlyTransaction in Authorize.net
 * - CaptureDelay in worldpay
 * etc
 */
    protected function forcePreAuthorizeMethod() {
        foreach (\common\helpers\Hooks::getList('module-payment/force-pre-authorize-method', '') as $filename) {
            $force_pre_authorize = include($filename);
            if ($force_pre_authorize === true) {
                return true;
            }
        }
        return false;
    }

    public function getChargeFromOrder($order) {
        $orderAmount = 0;
        if (is_array($order->totals) && $order->info['orders_id'] > 0) {
            foreach ($order->totals as $key => $total) {
                if ($total['class'] === 'ot_due' || $total['code'] === 'ot_due') {
                    $orderAmount = (float) $order->totals[$key]['value_inc_tax'];
                    break;
                }
            }
        } else {
            $orderAmount = (float) ($order->info['total_inc_tax'] ? $order->info['total_inc_tax'] : $order->info['total']);
        }
        return $orderAmount;
    }

/**
 * override with return true if your payment module allows guest checkout without any customer and address details (ex PayPal express checkout)
 * @return boolean
 */
    public function hasGuestCheckout() {
        return false;
    }

    public function customerDetailsOptional() {
        return !static::isOnline() || static::hasGuestCheckout();
    }

    public function customerDetailsRequired() {
        return static::isOnline() && !static::hasGuestCheckout();
    }

}
