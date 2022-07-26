<?php
 /**
 * @deprecated for PayPal Rest API v1 (deprecated by PayPal
 * Transactional Midle Ware for Paypal modules
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

abstract class PaypalMidleWare extends ModulePayment implements TransactionalInterface, TransactionSearchInterface {
    
    protected $sale = null;

    protected function _getSale($transaction_id = null){
        if (is_null($this->sale)){
            $this->sale = new PayPal\Api\Sale();
        }
        if (!is_null($transaction_id)){
            $this->sale->setId($transaction_id);
        }
        return $this->sale;
    }
    
    protected function format_raw($number, $currency_code = '', $currency_value = '') {
      $currencies = \Yii::$container->get('currencies');

      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = \Yii::$app->settings->get('currency');
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }
        
    abstract protected function _isReady();
    abstract protected function _getClientId();
    abstract protected function _getClientSecret();
    abstract protected function _getIntent();
    abstract protected function _getMode();

    protected function getAuth(){
        if ($this->_isReady()){
          $auth = new PayPal\Auth\OAuthTokenCredential($this->_getClientId(), $this->_getClientSecret());
        }

        if (!is_object($auth)){
            trigger_error('Credentials are not defined');
        }

        return $auth;
    }

    protected function getApiContext(){
        $apiContext = new PayPal\Rest\ApiContext($this->getAuth());
        $apiContext->setConfig(['mode' => $this->_getMode()]);
        return $apiContext;
    }
    
    

    protected function _getPayment(){
        $payment = new PayPal\Api\Payment();

        $payment->setIntent($this->_getIntent());

        $payer = new PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        $payment->setPayer($payer);

        return $payment;
    }
    
    public function getPaymentDetails($paymentId){
        $payment = $this->_getPayment();
        return $payment->get($paymentId, $this->getApiContext());
    }


    //transactional interface
    public function getTransactionDetails($transaction_id, \common\services\PaymentTransactionManager $tManager = null){
        try{
            $sale = $this->_getSale($transaction_id)->get($transaction_id, $this->getApiContext());
          if ($tManager && $sale) {
            $tManager->updateTransactionFromPayment($transaction_id, $sale->getState(), $sale->getAmount()->getTotal());
          }
          return $sale;
        } catch (\Exception $ex) {
          $this->sendDebugEmail($ex);
        }
    }

  /**
   * not quite implemented - mix of operations in original 1 method 
   * parse getTransactionDetails $response into $this->transactionInfo
   * @param array $transactionDetails
   */
  public function parseTransactionDetails($response) {
    $this->transactionInfo = [];
    if (is_object($response)) {
      $transactionDetails = false;
      if ($response instanceof PayPal\Api\DetailedRefund) {
        $this->transactionInfo['status_code'] = \common\helpers\OrderPayment::OPYS_REFUNDED;
        $this->transactionInfo['status'] = $response->state;
        $this->transactionInfo['transaction_id'] = $response->id;
        $this->transactionInfo['amount'] = $response->getTotalRefundedAmount()->getValue();
        $this->transactionInfo['fulljson'] = json_encode($response);

        $this->transactionInfo['comments'] = "Refund State: " . $response->state . "\n" .
                    "Refund Date: " . \common\helpers\Date::datetime_short($response->create_time) . "\n" .
                    "Refund Amount: " . $response->getTotalRefundedAmount()->getValue();

      } elseif ($response instanceof PayPal\Api\Sale) {
        $this->transactionInfo['status_code'] = \common\helpers\OrderPayment::OPYS_SUCCESSFUL;
        $this->transactionInfo['status'] = $response->getState();
        $this->transactionInfo['transaction_id'] = $response->getId();
        $this->transactionInfo['amount'] = $response->getAmount()->getTotal();
        $this->transactionInfo['fulljson'] = json_encode($response);

        $this->transactionInfo['comments'] = "Sale State: " . $response->getState() . "\n" .
                    "Amount: " . $response->getAmount()->getTotal();

      }


    }

    return $this->transactionInfo;
  }

    public function canRefund($transaction_id){
        $tm = $this->manager->getTransactionManager($this);
        $response = $this->getTransactionDetails($transaction_id, $tm);
        $canRefund = true;
        if (is_object($response) && $response->getParentPayment()){
            $payment = $this->getPaymentDetails($response->getParentPayment());
            $transactions = $payment->getTransactions();
            if ($transactions){
                $transaction = $transactions[0];
                $resources = $transaction->getRelatedResources();
                $sale = null;
                $refund = null;
                if (is_array($resources)){
                    foreach($resources as $resource){
                        if ($resource->getSale()){
                            $sale = $resource->getSale();
                        }
                        if ($resource->getRefund()){
                            $refund = $resource->getRefund();
                            if (is_object($refund)){
                                $tm->updateTransactionChild($transaction_id, $refund->getId(), $refund->getState(), $refund->getAmount()->getTotal());
                            }
                        }
                    }
                }
                $canRefund = false;
                if (is_object($sale)){
                    $canRefund = true;
                    if ($sale->getState() == 'partially_refunded'){
                        $receivable = $sale->getReceivableAmount();
                        if ($receivable){
                            if (!$receivable->getValue()){
                                $canRefund = false;
                            }
                        }
                    }
                }
            }
        }

        return (is_object($response) && !in_array($response->getState(), ['refunded', 'denied']) && $canRefund);
    }

    public function refund($transaction_id, $amount = 0){
      $ret = false;
        $refundRequest = new PayPal\Api\RefundRequest;
        $order = $this->manager->getOrderInstance();
        if ($amount){
            $data = ['total' => $this->format_raw($amount), 'currency' => $order->info['currency']];
            $rAmount = new PayPal\Api\Amount($data);

            $refundRequest->setAmount($rAmount);
        }
        $refundRequest->setInvoiceNumber($order->order_id . '-' . date('Y-m-d:H:i:s')); //date to avoid Requested invoice number was already used
        try{
            $response = $this->_getSale($transaction_id)->refundSale($refundRequest, $this->getApiContext());
          if ($response){
            $currencies = \Yii::$container->get('currencies');
            /** @var \common\services\PaymentTransactionManager $tManager */
            $tManager = $this->manager->getTransactionManager($this);
            $ret = $tManager->updatePaymentTransaction($response->id,
              [
                'fulljson' => json_encode($response),
                'status_code' => \common\helpers\OrderPayment::OPYS_REFUNDED,
                'status' =>  $response->state ,
                'amount' => (float)  $response->getTotalRefundedAmount()->getValue(),
                'comments'  =>  "Refund State: " . $response->state . "\n" .
                          "Refund Date: " . \common\helpers\Date::datetime_short($response->create_time) . "\n" .
                          "Refund Amount: " . $currencies->format($response->getTotalRefundedAmount()->getValue(), true, $order->info['currency'], $order->info['currency_value']),
                'date'  => date('Y-m-d H:i:s' /*, strtotime($res->update_time)*/),

                'payment_class' => $this->code,
                'payment_method' => $this->title,
                'parent_transaction_id' => $transaction_id,
                'orders_id' => 0
              ]);

            //$tManager->addTransactionChild($transaction_id, $response->id, $response->state, $response->getTotalRefundedAmount()->getValue(), ($amount? 'Partial Refund':'Full Refund'));
            
            $order->info['comments'] = "Refund State: " . $response->state . "\n" .
                    "Refund Date: " . \common\helpers\Date::datetime_short($response->create_time) . "\n" .
                    "Refund Amount: " . $currencies->format($response->getTotalRefundedAmount()->getValue(), true, $order->info['currency'], $order->info['currency_value']);
          }
        } catch (\Exception $ex) {
          $this->sendDebugEmail($ex);
        }
        return $ret ;
    }
    
    public function canVoid($transaction_id){
        if ($this->_getIntent() == 'authorize'){//only authorize can be voided

        }
        return false;
    }

    public function void($transaction_id){
        if ($this->_getIntent() == 'authorize'){//only authorize can be voided

        }
        return false;
    }
   

    public function getFields(){
        return [
            //[['start_date', 'end_date'], 'required'],
            [['start_date'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],
            [['end_date'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],//MM/dd/yyyy HH:mm
            ['transaction_id', 'string'],
            ['email_address', 'string']
        ];
    }

    public function search($queryParams){
        try{
            $requiered = $this->getFields();

            if (!$queryParams['skipIpn']){
                $found = $this->getIpnTransactions($queryParams);
                if($found){
                    return $found;
                }
            }

            foreach ($queryParams as $key => $value){
                if (empty($value)) unset($queryParams[$key]);
            }
            
            $email = $queryParams['email_address'];
            unset($queryParams['email_address']);

            foreach($queryParams as $key => $param){
                array_map(function($item) use (&$queryParams, $key, $param) {
                    if (is_array($item[0])){
                        if (in_array($key, $item[0]) && $item[1] == 'datetime'){
                            $queryParams[$key] = date(DATE_ATOM, strtotime($param));
                        }
                    } else {
                        if ($key == $item[0] && $item[1] == 'datetime'){
                            $queryParams[$key] = date(DATE_ATOM, strtotime($param));
                        }
                    }
                }, $requiered);
            }
            
            $fromStart = true;
            if (!$queryParams['start_date']){
                $queryParams['start_date'] = date(DATE_ATOM, strtotime("-31 days"));
                $fromStart = false;
            }
            $fromEnd = true;
            if (!$queryParams['end_date']){
                $queryParams['end_date'] = date(DATE_ATOM);
                $fromEnd = false;
            }
            
            $startD = new \DateTime($queryParams['start_date']);
            $endD = new \DateTime($queryParams['end_date']);
            $diff = $startD->diff($endD);
            if ($diff->m || $diff->y){
                if ($fromStart){
                    $startD->add(new \DateInterval('P31D'));
                    $queryParams['end_date'] = $startD->format(DATE_ATOM);
                } else {
                    $endD->sub(new \DateInterval('P31D'));
                    $queryParams['start_date'] = $endD->format(DATE_ATOM);
                }
            }
            
            
            $queryParams['fields'] = 'all';
            $queryParams['page_size'] = '100';
            $queryParams['page'] = '1';
            $response = PayPal\Api\Sync::getAll($queryParams, $this->getApiContext());
            $transactions = $response->__get('transaction_details');
            $found = [];
            //if (isset($queryParams['no_modify'])) return $transactions;
            if (is_array($transactions)){
                $currencies = \Yii::$container->get('currencies');
                foreach($transactions as $tns){
                    //if (isset($tns['transaction_info']['paypal_reference_id'])) continue;//probably refunded transaction
                    $name = isset($tns['payer_info']['payer_name']['given_name']) && !empty($tns['payer_info']['payer_name']['given_name']) ? $tns['payer_info']['payer_name']['given_name'] . " " . $tns['payer_info']['payer_name']['surname'] : $tns['payer_info']['payer_name']['alternate_full_name'];
                    if (!empty($email) && strcmp($email, $tns['payer_info']['email_address'])){ continue; }
                    $found[] = [
                        'id' => $tns['transaction_info']['transaction_id'],
                        'date' => \common\helpers\Date::formatDateTimeJS($tns['transaction_info']['transaction_initiation_date']),
                        'amount' => $currencies->format( $tns['transaction_info']['transaction_amount']['value'], true, $tns['transaction_info']['transaction_amount']['currency_code']),
                        'negative' => $tns['transaction_info']['transaction_amount']['value'] < 0,
                        'name' =>  $name . ($tns['payer_info']['email_address'] ? ", " . $tns['payer_info']['email_address']: ""),
                        'status' => $this->describeStatus($tns['transaction_status']),
                    ];
                }
            }
            return $found;
        } catch (\Exception $ex) {
            $this->sendDebugEmail($ex);
        }
    }
    
    public function describeStatus($statusCode){
        switch($statusCode){
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
    
    public function getIpnTransactions($queryParams){
        
        $condition = ['and', ['is_assigned' => 0], /*['>','mc_gross', 0],*/ ['platform_id' => $this->manager->getPlatformId()]];
        if (isset($queryParams['start_date']) && !empty($queryParams['start_date'])){
            array_push($condition, ['>=', 'payment_date', $queryParams['start_date']]);
        }

        if (isset($queryParams['end_date']) && !empty($queryParams['end_date'])){
            array_push($condition, ['<=', 'payment_date', $queryParams['end_date']]);
        }

        if (isset($queryParams['transaction_id']) && !empty($queryParams['transaction_id'])){
            array_push($condition, ['=', 'txn_id', $queryParams['transaction_id']]);
        }
        
        $txn = \common\models\PaypalipnTxn::find()
                ->where($condition)->orderBy('date(payment_date) desc');
        
        if (!$txn->exists()) return [];
        $found = [];
        $currencies = \Yii::$container->get('currencies');
        foreach($txn->all() as $ipnTx){
            $found[] = [
                'id' => $ipnTx['txn_id'],
                'date' => \common\helpers\Date::formatDateTimeJS($ipnTx['payment_date']),
                'amount' => $currencies->format($ipnTx['mc_gross'], true, $ipnTx['mc_currency']),
                'negative' => $ipnTx['mc_gross'] < 0,
                'name' =>  ($ipnTx['first_name']||$ipnTx['last_name']? $ipnTx['first_name'] . " " . $ipnTx['last_name']. ", " :'') . $ipnTx['payer_email'],
                'status' => ($ipnTx['mc_gross'] < 0 ? $this->describeStatus('F') :  $ipnTx['payment_status']),
            ];
        }
        return $found;
    }
    
    public function linkTransaction($order_id, $transaction_id){
        $txnQ = \common\models\PaypalipnTxn::find()->where(['is_assigned' => 0, 'txn_id' => $transaction_id]);
        if ($txnQ->exists()){
            $txn = $txnQ->one();
            $txn->item_number = $order_id;
            $txn->is_assigned = 1;
            $txn->save(false);
        }
    }
    
    public function unLinkTransaction($transaction_id){
        $txnQ = \common\models\PaypalipnTxn::find()->where(['txn_id' => $transaction_id]);
        if ($txnQ->exists()){
            $txn = $txnQ->one();
            $txn->is_assigned = 0;
            $txn->save(false);
        }
    }

    public function canCapture($transaction_id) {
      return false;
    }

    public function capture($transaction_id, $amount = 0) {
      return false;
    }

    public function canReauthorize($transaction_id) {
      return false;
    }

    public function reauthorize($transaction_id, $amount = 0) {
      return false;
    }
}