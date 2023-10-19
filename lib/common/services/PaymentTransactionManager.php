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

namespace common\services;

class PaymentTransactionManager {

    private $payment;
    private $manager;
    private $enabled = false;

    public function __construct(\common\services\OrderManager $manager, $payment = null) {
        $this->enabled = true;
        $this->payment = $payment;
        $this->manager = $manager;
        // I wish to get order by transaction (for transaction status update)
        try {
          $order = $this->manager->getOrderInstance();
        } catch (\Exception $e) {
          $order = false;
        }
        if ($order && !($order instanceof \common\classes\extended\TransactionsInterface)) {
            $this->enabled = false;
        }
    }
    /**
     * set Payment module to work with transactions
     * @param \common\classes\modules\ModulePayment $payment
     */
    public function usePayment(\common\classes\modules\ModulePayment $payment) {
        $this->payment = $payment;
    }

    public function isReady() {
        return $this->enabled && $this->manager->isInstance() && $this->getOrder()->order_id;
    }

    private function getOrder() {
        return $this->manager->getOrderInstance();
    }

    private function getAdminId() {
        return $_SESSION['login_id'] ?? 0;
    }

    /**(both tables) copatibility only (if no parseTransactionDetails method)
     * Update (not create) only status & amount of transaction from payment gateway
     * @param string $transaction_id
     * @param string $status
     * @param float $amount
     * @param string_date $dateCreated
     * @param array $transactionDetails
     */
    public function updateTransactionFromPayment($transaction_id, $status, $amount, $dateCreated = null, $transactionDetails=[]){
     //if ($this->isReady() && $this->isTransactional()){
        //old tables
            //first find child
        $child = $this->_getTCModelQuery()->andWhere(['transaction_id' => $transaction_id])->one();
        if ($child){
            $child->transaction_status = $status;
            $child->transaction_amount = $amount;
            if (!is_null($dateCreated)){
                $child->date_created = date("Y-m-d H:i:s", strtotime($dateCreated));
            }
            $child->save(false);
        } else {
            //may be parent transaction
            $parent = $this->_getTModelQuery()->andWhere(['transaction_id' => $transaction_id])->one();
            if ($parent){
                $parent->transaction_status = $status;
                $parent->transaction_amount = $amount;
                if (!is_null($dateCreated)){
                    $parent->date_created = date("Y-m-d H:i:s", strtotime($dateCreated));
                }
                $parent->save(false);
            }
        }
        //}

        //new table
        $pc = '';
        if (!empty($this->payment->code)) {
          $pc = $this->payment->code;
        }

        $op = \common\helpers\OrderPayment::searchRecord($pc, $transaction_id);
        if ($op && !empty($op->orders_payment_id)) {
          // exists - update if required
          $op->orders_payment_amount = $amount;
          $op->orders_payment_transaction_status = $status;
          if (!empty($transactionDetails['comments'])) {
            $op->orders_payment_transaction_commentary = $transactionDetails['comments'];
          }
          if (!empty($transactionDetails['fulljson'])) {
            $op->orders_payment_transaction_full = $transactionDetails['fulljson'];
          }
          if (!empty($op->getDirtyAttributes())) {
            $op->save(false);
            $ret = true;
          }
        }
    }

    /** @deprecated
     * (old table) Search transaction details by payment transaction id (uses Payment->code and manager->orderInstance->orders_id)
     * @param string $transaction_id
     * @return mix OrdersTransaction|null
     */
    public function getTransaction($transaction_id) {
        if ($this->isReady()) {
            $transaction = $this->_getTModelQuery()->andWhere(['payment_class' => $this->payment->code, 'transaction_id' => $transaction_id])
                    ->one();
            return $transaction;
        }
        return null;
    }

    /** @deprecated old table
     * Search transaction details by payment transaction id && Payment->code
     * @param string $transaction_id
     * @return mix OrdersTransaction|null
     */
    public function findTransaction($transaction_id) {
      $ret = null;
      $transaction = \common\models\OrdersTransactions::find()
          ->andWhere(['payment_class' => $this->payment->code, 'transaction_id' => $transaction_id]);
      if ($transaction->count()==1) {
        $ret = $transaction->one();
      }
      return $ret;
    }

    public function addTransaction($transaction_id, $status, $amount, $suborder_id = null, $comments = '') {
        if ($this->isReady() && $this->isTransactional()) {
            $transaction = \common\models\OrdersTransactions::create($this->getOrder()->order_id, $this->payment->code, $transaction_id, $status, $amount, $suborder_id, $this->getOrder()->info['currency'], $comments, $this->getAdminId());
            if ($transaction){
                $this->linkLocalTransaction($transaction_id);
            }
            return $transaction;
        }
        return false;
    }
    /** @deprecated - old table
     * Update or add new OrdersTransaction.
     * @param string $transaction_id - payment transaction id string
     * @param string $status - payment transaction status
     * @param float $amount - transaction amount
     * @param int $suborder_id - (invoice|creditnote id)|null (orders_splinters.splinters_suborder_id)
     * @param text $comments
     * @return false|OrdersTransactions
     */
    public function updateTransaction($transaction_id, $status, $amount, $suborder_id = null, $comments = '') {
        if ($this->isReady() && $this->isTransactional()) {
            $transaction = $this->getTransaction($transaction_id);
            if ($transaction) {
                $transaction->transaction_status = $status;
                $transaction->save();
                return $transaction;
            } else {
                $transaction = $this->addTransaction($transaction_id, $status, $amount, $suborder_id, $comments);
                return $transaction;
            }
        }
        return false;
    }

    /**
     * updates status/code/+comments of payment transaction (if exists) or add new transaction record
     * $transactionDetails['orders_id'] or "$this->manager->getOrderInstance()" should be set to save new transaction
     * sets manager->orderInstance if no orders_id in the instance (often payment gateways don't have order id in transactions details)
     * fills both transaction tables (*temp*)
     * @param string $transaction_id
     * @param array $transactionDetails [status_code status amount suborder_id comments date parent_transaction_id ("external") orders_id fulljson]
     * @return \common\models\OrdersPayment|bool|null null not found false - not changed; OrdersPayment - added/changed, to save order comments (by OrderPayment only)
     */
    public function updatePaymentTransaction($transaction_id, $transactionDetails) {
      $ret = null;
      $status = $transactionDetails['status']??'';
      $amount = $transactionDetails['amount']??'';
      $suborder_id = $transactionDetails['suborder_id']??'';
      $comments = $transactionDetails['comments']??'';
      $pp = null;
      /// first table
      if ($this->manager->isInstance() ) {
        $order = $this->manager->getOrderInstance();
      } else {
        $order = false;
      }
      //find order id by transaction id
      if (!is_object($order) && empty($transactionDetails['orders_id'])) {
        $transaction = $this->findTransaction($transaction_id);
        if ($transaction) {
          $transactionDetails['orders_id'] = $transaction->orders_id;
        }
      }
      //find order id by parent transaction id (refund)
      if (!is_object($order) && !empty($transactionDetails['parent_transaction_id'])) {
        $transaction = $this->findTransaction($transactionDetails['parent_transaction_id']);
        if ($transaction) {
          $transactionDetails['orders_id'] = $transaction->orders_id;
        }
      }

      if (!is_object($order) && !empty($transactionDetails['orders_id'])) {
        $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $transactionDetails['orders_id']);
        //$this->manager->getOrderInstance();
      }

      if (is_object($order)) {
        if (!empty($transactionDetails['parent_transaction_id'])) {
          //create child (refund) transaction
          $totals = \yii\helpers\ArrayHelper::map($order->totals, 'code', 'value_inc_tax');
          $paid = (float)($totals['ot_paid'] ?? 0);
          $this->stopPropagination();
          $child = $this->addTransactionChild($transactionDetails['parent_transaction_id'], $transaction_id, $status, $amount, (abs($amount - $paid)< 0.01? 'Full Refund':'Partial Refund'));
          if ($child && !$child->splinters_suborder_id) {
            $splitter = $this->manager->getOrderSplitter();
            $cnId = $splitter->createCreditNote($child->orders_transactions_id, $amount); // shit in splinters table :( $parent->splinters_suborder_id
            if ($cnId){ //has been created new document
              $child->splinters_suborder_id = $cnId;
              $child->save(false);
            }
          }

        } else {
          $this->updateTransaction($transaction_id, $status, $amount, $suborder_id, $comments);
        }
      }

      /// new table
      $op = \common\helpers\OrderPayment::searchRecord($this->payment->code, $transaction_id);

      if ($op && !empty($op->orders_payment_id)) {
        // exists - update if required
        $ret = false;
        if ($op->orders_payment_transaction_status != $status
            || (!empty($transactionDetails['status_code']) && $op->orders_payment_status!=$transactionDetails['status_code'])
            || (!empty($transactionDetails['last_updated']) && $op->orders_payment_date_update!=$transactionDetails['last_updated'])
            ) {
            $op->orders_payment_transaction_status = $status;
            if (!empty($transactionDetails['status_code'])) {
              $op->orders_payment_status = $transactionDetails['status_code'];
            }
            if (!empty($transactionDetails['amount'])) {
              $op->orders_payment_amount = $transactionDetails['amount'];
            }
            if (!empty($transactionDetails['comments'])) {
              $op->orders_payment_transaction_commentary = $transactionDetails['comments'];
            }
            if (!empty($transactionDetails['last_updated'])) {
              $op->orders_payment_date_update = $transactionDetails['last_updated'];
            }
            if (!empty($transactionDetails['deferred'])) {
              $op->deferred = $transactionDetails['deferred'];
            }
            $currencies = \Yii::$container->get('currencies');
            if (!empty($transactionDetails['currency_value'])) {
              $op->orders_payment_currency_rate = $transactionDetails['currency_value'];
            }
            if (!empty($transactionDetails['currency']) ) {
              $op->orders_payment_currency = $transactionDetails['currency'];
              if (empty($transactionDetails['currency_value']) && $currencies->is_set($transactionDetails['currency'])) {
                  $op->orders_payment_currency_rate = $currencies->get_value($transactionDetails['currency']);
              }
            }
            if (!empty($transactionDetails['transaction_date'])) {
              $op->orders_payment_transaction_date = $transactionDetails['transaction_date'];
            }
            if (empty($op->orders_payment_order_id) && !empty($transactionDetails['orders_id'])) {
                $op->orders_payment_order_id = $transactionDetails['orders_id'];
            }
            if (empty($op->orders_payment_module_name) && !empty($transactionDetails['payment_method'])) {
                $op->orders_payment_module_name = $transactionDetails['payment_method'];
            }
            if (!empty($op->getDirtyAttributes())) {
              if (!empty($transactionDetails['fulljson'])) {
                $op->orders_payment_transaction_full = $transactionDetails['fulljson'];
              }
              $op->save(false);
              $ret = true;
            }
        }
        if (!$this->manager->isInstance() || empty($this->getOrder()->info['orders_id'])) {
          $this->manager->getOrderInstanceWithId('\common\classes\Order', $op->orders_payment_order_id);
        }

      } else {
        $parent_id = 0;
        $replaceOrderInstance = false;
        if (!empty($transactionDetails['parent_transaction_id'])) {
          $pp = \common\helpers\OrderPayment::searchRecord($this->payment->code, $transactionDetails['parent_transaction_id']);
          if ($pp) {
            $parent_id = $pp->orders_payment_id;
          }
        }
        // if order exists - add new
        if (!empty($transactionDetails['orders_id'])) {
          $order = new \common\classes\Order($transactionDetails['orders_id']);
          $replaceOrderInstance = true;
        } elseif ($pp) {
          $order = new \common\classes\Order($pp->orders_payment_order_id);
          $replaceOrderInstance = true;
        }

        if (!empty($order->info['orders_id'])) {
          $ret = \common\helpers\OrderPayment::createDebitFromOrder($order, $amount, $transactionDetails['status_code'], [
                'id' => $transaction_id,
                'status' => $status,
                'commentary' => $comments,
                'date' => (isset($transactionDetails['date']) ? $transactionDetails['date'] : '0000-00-00 00:00:00'),
                'parent_id' => $parent_id,
                'fulljson' => $transactionDetails['fulljson'],
                'payment_class' => $transactionDetails['payment_class']??($this->payment->code??''),
                'payment_method' => $transactionDetails['payment_method']??'',
                'payment_type' => $transactionDetails['payment_type']??'',
              ],
              $transactionDetails['deferred']??0
              );
        }
        if ($parent_id && $ret) {
          if (!$cnId) {
            $splitter = $this->manager->getOrderSplitter();
            $cnId = $splitter->createCreditNote($order->order_id, $amount); // shit in splinters table :( $parent->splinters_suborder_id
          }
          if ($cnId){ //has been created new document
            $ret->credit_note_id = $cnId;
            $ret->save(false);
          }
        }

      }
      if (!empty($order->info['orders_id'])) {
        if ($replaceOrderInstance ?? null) {
          $this->manager->getOrderInstanceWithId('\common\classes\Order', $order->info['orders_id']);
        }
      }

      return $ret;
    }

    /**
     * Update child transaction or add new, child transaction makes for refund|void
     * @param string $transaction_id - payment transaction id
     * @param string $child_transaction_id - payment child transaction id, if payment does not return child use transaction id
     * @param string $status - payment status
     * @param float $amount - payment transaction value
     * @param text $comments
     * @return boolean result
     */
    public function updateTransactionChild($transaction_id, $child_transaction_id, $status, $amount, $comments = '') {
        if ($this->isReady() && $this->isTransactional()) {
            $parent = $this->getTransaction($transaction_id);
            if ($parent) {
                $child = $parent->updateTransactionChild($child_transaction_id, $status, $amount, $comments, $this->getAdminId());//instead updateTransactionChild
                /*if ($child){
                    if ($this->propagination){
                        if (!$child->splinters_suborder_id){
                            $splitter = $this->manager->getOrderSplitter();
                            $cnId = $splitter->createCreditNote($parent->splinters_suborder_id, $amount, $this->payment);//if splitter has prepared
                            if ($cnId){ //has been created new document
                                $child->splinters_suborder_id = $cnId;
                                $child->save(false);
                            }
                            if (!$this->manager->hasCart()){
                                $this->manager->loadCart(new \common\classes\shopping_cart);
                            }
                            $this->manager->getOrderInstance()->return_paid($amount, $amount);
                            $this->manager->getOrderInstance()->save_details();
                        }
                    }
                }*/
            }
        }
        return false;
    }

    /**
     * 2deprecate
     * @param string $transaction_id
     * @param string $child_transaction_id
     * @param string $status
     * @param type $amount
     * @param string $comments
     * @return false|child
     */
    public function addTransactionChild($transaction_id, $child_transaction_id, $status, $amount, $comments = '') {
        if ($this->isReady() && $this->isTransactional()) {
          /** @var \common\models\OrdersTransactions $parent */
          $parent = $this->getTransaction($transaction_id);
            if ($parent) {
                $child = $parent->addTransactionChild($child_transaction_id, $status, $amount, $comments, $this->getAdminId());
                if ($child){
                    if ($this->propagination){
                        if (!$child->splinters_suborder_id){ //
                            $splitter = $this->manager->getOrderSplitter();
                            $cnId = $splitter->createCreditNote($parent->splinters_suborder_id, $amount, $this->payment);//if splitter has prepared
                            if ($cnId){ //has been created new document
                                $child->splinters_suborder_id = $cnId;
                                $child->save(false);
                            }
                            if (!$this->manager->hasCart()){
                                $this->manager->loadCart(new \common\classes\shopping_cart);
                            }
                            $currencies = \Yii::$container->get('currencies');
                            if (defined('DEFAULT_CURRENCY') && !empty($this->manager->getOrderInstance()->info['currency'])){
                                $amount *= $currencies->get_market_price_rate($this->manager->getOrderInstance()->info['currency'], DEFAULT_CURRENCY);
                            }
                            $this->manager->getOrderInstance()->return_paid($amount, $amount);
                            $this->manager->getOrderInstance()->save_details();
                        }
                    }
                    return $child;
                }
            }
        }
        return false;
    }

    /**
     * Use for creating documents after got transaction child data from payment
     * @var boolean, by default true
     */
    private $propagination = true;

    public function stopPropagination(){
        $this->propagination = false;
    }

    public function continuePropagination(){
        $this->propagination = true;
    }

    public function finalizeRefunding($ownerSplinterId, array $childTransactions, $amount){
        if ($childTransactions){
            $splitter = $this->manager->getOrderSplitter();
            $docId = $splitter->createCreditNote($ownerSplinterId, $amount);
            \Yii::info('creditnote form several transactions '.$docId);
            if ($docId){
                \Yii::info('creditnote transactions '.print_r($childTransactions,1));
                foreach($childTransactions as $child){
                    $trChild = $this->_getTCModelQuery()->andWhere(['orders_transactions_child_id' => $child])->one();
                    \Yii::info('creditnote transactions child before '. print_r($trChild, 1));
                    if ($trChild){
                        $trChild->splinters_suborder_id = $docId;
                        $trChild->save();
                        \Yii::info('creditnote transactions child after '. print_r($trChild, 1));
                    }
                }
            }
        }
        $this->continuePropagination();
        return $docId;
    }

    private function _getTModelQuery() {
        return \common\models\OrdersTransactions::find()
                        ->where(['orders_id' => $this->getOrder()->order_id]);
    }

    private function _getTCModelQuery() {
        return \common\models\OrdersTransactionsChildren::find()
                ->where(['orders_id' => $this->getOrder()->order_id]);
    }

    public function getTransactionById($id) {
        if ($this->isReady()) {
            return $this->_getTModelQuery()
                            ->andWhere(['orders_transactions_id' => $id])
                            ->one();
        }
        return false;
    }

    public function getTransactionsCount() {
        if ($this->isReady()) {
            return $this->_getTModelQuery()->count();
        }
        return false;
    }

    public function getTransactions($withChildren = false) {
        if ($this->isReady()) {
            $tmQ = $this->_getTModelQuery();
            if ($withChildren){
                $tmQ->with('transactionChildren');
            }
            return $tmQ->all();
        }
        return false;
    }

    public function isTransactional() {
        if ($this->payment && $this->payment instanceof \common\classes\modules\TransactionalInterface) {
            return true;
        }
        return false;
    }

    public function isTransactionSearch() {
        if ($this->payment && $this->payment instanceof \common\classes\modules\TransactionSearchInterface) {
            return true;
        }
        return false;
    }

    /**
     * get current transactions status and check possibility to do refund|void
     * @param array $orders_transactions ids
     * @return array $response
     */
    public function getTransactionsStatus(array $orders_transactions){
        $response = [];
        if ($this->isReady()){
            foreach($this->_getTModelQuery()->where(['in', 'orders_transactions_id', $orders_transactions])->orderBy('date_created asc')->all() as $transaction){
                $data = ['parent' => $transaction->orders_transactions_id];
                $payment = $this->manager->getPaymentCollection()->get($transaction->payment_class, true);
                if ($payment){
                    $this->usePayment($payment);
                    $data['can_refund'] = $this->canPaymentRefund($transaction->transaction_id);
                    $data['can_void'] = !$data['can_refund'] ? $this->canPaymentVoid($transaction->transaction_id) : false;
                }
                $children = $transaction->getTransactionChildren()->asArray()->all();
                if ($children){
                    $currencies = \Yii::$container->get('currencies');
                    foreach($children as &$child){
                        $child['date_created'] = \common\helpers\Date::formatDateTime($child['date_created']);
                        $child['transaction_amount'] = "-".$currencies->format($child['transaction_amount'], false, $child['transaction_currency']);
                        $child['transaction_amount_clear'] = $currencies->format_clear($child['transaction_amount'], false, $child['transaction_currency']);
                        $child['transaction_status'] = \yii\helpers\Inflector::humanize($child['transaction_status']);
                    }
                }
                $data['children'] = $children;
                $data['status'] = \yii\helpers\Inflector::humanize($transaction->transaction_status);
                $data['date_created'] = \common\helpers\Date::formatDateTime($transaction->date_created);
                $data['amount'] = $transaction->transaction_amount;
                $response[] = $data;
            }
        }

        return $response;
    }

    public function checkPaymentTransaction($transaction_id) {
        if ($this->isReady() && $this->isTransactional()) {
            try {
                $this->payment->getTransactionDetails($transaction_id, $this);
            } catch (\Exception $ex) {
                \Yii::info($ex->getMessage(), 'PAYMENTTRANSACTION');
            }
        }
        return false;
    }

    public function canPaymentRefund($transaction_id) {
        if ($this->isReady() && $this->isTransactional()) {
            $result = $this->payment->canRefund($transaction_id, $this);
            return $result;
        }
        return false;
    }

    public function paymentRefund($transaction_id, $amount = 0) {
        if ($this->isReady() && $this->isTransactional()) {
            $result = $this->payment->refund($transaction_id, $amount, $this);
            return $result;
        }
        return false;
    }

    public function canPaymentVoid($transaction_id) {
        if ($this->isReady() && $this->isTransactional()) {
            $result = $this->payment->canVoid($transaction_id, $this);
            return $result;
        }
        return false;
    }

    public function paymentVoid($transaction_id) {
        if ($this->isReady() && $this->isTransactional()) {
            $result = $this->payment->void($transaction_id);
            return $result;
        }
        return false;
    }

    public function getRequeredFields(){
        return [];
    }

    public function getFields(){
        if ($this->isReady() && $this->isTransactionSearch()) {
            return $this->payment->getFields();
        }
        return [];
    }

    private $_requered = [];
    private $_errors = [];
    public function prepareQuery(array $searchData){//fieldname => value
        if ($this->isReady() && $this->isTransactionSearch()) {
            $_fields = $this->payment->getFields();
            if (is_array($_fields)){
                $model = \Yii::createObject('common\components\PaymentModel');
                \Yii::configure($model, ['rules' => $_fields]);
                if ($model->load($searchData, '') && $model->validate()){
                    $this->_requered = $model->getAttributes();
                    return true;
                } else {
                    $this->_errors = $model->getErrors();
                }
            }
        }
        return false;
    }

    public function getErrors(){
        return $this->_errors;
    }

    /**
     * @return array of transactions [id => transaction description]
     */
    public function executeQuery(){
        $found = [];
        if ($this->isReady() && $this->isTransactionSearch()) {
            try{
                if (!$this->_errors){
                    $found = $this->payment->search($this->_requered);
                }
            } catch (\Exception $ex) {
                $found = [$ex->getMessage()];
                \Yii::info($ex->getMessage(), 'PAYMENTTRANSACTION');
            }
        }
        return $found;
    }

    public function linkLocalTransaction($transaction_id){
        if ($this->isReady()){
            if (method_exists($this->payment, 'linkTransaction')){
                $this->payment->linkTransaction($this->getOrder()->order_id, $transaction_id);
            }
        }
    }

    public function unLinkLocalTransaction($transaction_id, $payment_class = null){
        if ($this->isReady()){
            if (!is_object($this->payment) && !is_null($payment_class)){
                $payment = $this->manager->getPaymentCollection()->get($payment_class, true);
                if ($payment){
                    $this->usePayment($payment);
                }
            }
            if (method_exists($this->payment, 'unLinkTransaction')){
                $this->payment->unLinkTransaction($transaction_id);
            }
        }
    }

    /**
     * Unlink transaction from order
     * @param type $transaction_orders_id
     */
    public function unlinkTransactionById($transaction_orders_id){
        $transaction = $this->getTransactionById($transaction_orders_id);
        if ($transaction){
            $transaction->orders_id = 0;
            //$transaction->splinters_suborder_id = 0;//??need to be unlinked
            if ($transaction->save(false)){
                if ($transaction->getTransactionChildren()->exists()){

                }
                $this->unLinkLocalTransaction($transaction->transaction_id, $transaction->payment_class);
                return true;
            }
        }
        return false;
    }

    public function isLinkedTransaction($transaction_id){
        if ($this->isReady() && $this->isTransactionSearch()){
            return \common\models\OrdersTransactions::hasLinked($this->payment->code, $transaction_id);
        }
        return false;
    }

}
