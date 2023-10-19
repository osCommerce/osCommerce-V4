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

use common\models\OrdersSplinters;
use common\classes\Splinter;
use common\classes\Order;

class SplitterManager {

    private $manager;
    private $original;
    private $source;
    private $splinter;

    CONST STATUS_PENDING = 1;
    CONST STATUS_PAYED = 2;
    CONST STATUS_RETURNING = 3;
    CONST STATUS_RETURNED = 4;
    CONST OWNER_CUSTOMER = 1; //splinters_type
    CONST OWNER_DELIVERY = 2;
    CONST OWNER_BILLING = 3;
    CONST OWNER_INFO = 4;
    CONST OWNER_PRODUCT = 5;
    CONST OWNER_TOTAL = 6;
    CONST OWNER_MIXED = 7;

    public function __construct(OrderManager $manager) {
        $this->manager = $manager;
    }

    protected function createSplinterObject() {
        $this->splinter = new Splinter();
        $this->splinter->manager = $this->manager;
        $this->splinter->setSplitter($this);
        return $this->splinter;
    }

    /**
     * Cretate splinters rows for order from ordermanager instance
     * @return new invoice id|null;
     */
    public function createInvoice() {
        $order = $this->manager->getOrderInstance();
        if ($order && $order->order_id && $order->maintainSplittering()) {
            if ($this->saveSplinter($order, self::STATUS_PAYED)) {
                if (method_exists($order, 'getSplinters')) {
                    if ($id = $this->createDocumentNumber(self::STATUS_PAYED)) {
                        $this->setDocumentNumber($id, $order->getSplinters());
                        return $id;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Create empty Credit Note with RETURNED status for returned amount
     * @param int $ownerSplinterId - parent Invoice number
     * @param float $amount - returned amount
     * @param mixed $id|null - document number
     */
    public function createEmptyCreditNote($ownerSplinterId, $amount, $payment = null) {
        $splinter = $this->createSplinterObject();
        $splinter->order_id = $this->manager->getOrderInstance()->order_id;
        $parentInvoices = $this->getInstancesFromSplinters($splinter->order_id, self::STATUS_PAYED, $ownerSplinterId);
        if ($parentInvoices){
            $parentInvoice = array_pop($parentInvoices);
            $this->copyData($parentInvoice, $splinter);
            $splinter->info = $parentInvoice->info;
            $owner = '';
            $data = 'Returning funds';
            $splinter->createMixedType($owner, $amount, $data);
            if (is_object($payment)){
                $splinter->info['payment_info'] = $payment->title;
                $splinter->info['payment_class'] = $payment->code;
                $splinter->info['payment_method'] = $payment->title;
            }
            if ($this->saveSplinter($splinter, self::STATUS_RETURNED)) {
                if (method_exists($splinter, 'getSplinters')) {
                    if ($id = $this->createDocumentNumber(self::STATUS_RETURNED)) {
                        $this->setDocumentNumber($id, $splinter->getSplinters());
                        return $id;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Create empty Credit Note if has not prepared
     * @param int $ownerSplinterId - parent Invoice number
     * @param float $amount
     * @return int credit nore document id
     */
    public function createCreditNote($ownerSplinterId, $amount, $payment = null) {
        $splinter = $this->getMadeSplinter();
        if (!$splinter) {
            //check if has returning items
            $order_id = $this->manager->getOrderInstance()->order_id;
            if ($this->hasUnclosedRma($order_id)){
                $instances = $this->getInstancesFromSplinters($order_id, self::STATUS_RETURNING);
                if ($instances){
                    $instance = array_pop($instances);
                    $this->updateStatusRows(self::STATUS_RETURNED, $instance->getSplinters());
                    if ($id = $this->createDocumentNumber(self::STATUS_RETURNED)) {
                        $this->setDocumentNumber($id, $instance->getSplinters());
                        return $id;
                    }
                }
            } else {
                return $this->createEmptyCreditNote($ownerSplinterId, $amount); //return credit note id
            }
        } else {
            //already prepared roows for credit note
            if (method_exists($splinter, 'getSplinters')) {
                $this->updateStatusRows(self::STATUS_RETURNED, $splinter->getSplinters()); //change to returned status
                if ($id = $this->createDocumentNumber(self::STATUS_RETURNED)) {
                    $this->setDocumentNumber($id, $splinter->getSplinters());
                    return $id;
                }
            }
        }
        return null;
    }

    /**
     * On Splnter (spinters rows with PENDINT STATUS) set RETURNING status
     */
    public function prepareSplinterToCN() {
        $splinter = $this->getMadeSplinter();
        if ($splinter) {
            $this->updateStatusRows(self::STATUS_RETURNING, $splinter->getSplinters());
        }
    }

    /**
     * create invoice, usually use after payment
     * @return int $invoice_id or null if fail
     */
    public function getInvoiceId() {
        $splinter = $this->getInvoiceInstance();
        /* check if invoice instance */
        if ($splinter) {
            $this->updateStatusRows(self::STATUS_PAYED, $splinter->getSplinters());
            if ($invoice_id = $this->createDocumentNumber(self::STATUS_PAYED)) {
                $this->setDocumentNumber($invoice_id, $splinter->getSplinters());
            }
            if ($pm = $this->manager->getPaymentCollection()->getSelectedPayment()) {
                $this->updateFields('info', $splinter->getSplinters(), ['payment_class' => $pm->code, 'payment_method' => $pm->title]);
            }
        } else {
            $invoice_id = $this->createInvoice();
        }
        return $invoice_id;
    }

    /**
     * Save blocks of Instance to db
     * @param instance of \common\classes\Order|Splinter
     * @param int $status
     * @return boolean true if has saved rows
     */
    private function saveSplinter($order, $status) {
        $rows = [];
        if (is_array($order->products)) {
            foreach ($order->products as $product) {
                $row = OrdersSplinters::create($order->order_id, $status, self::OWNER_PRODUCT, $product['qty'], $product['final_price'], \common\helpers\Tax::add_tax($product['final_price'], $product['tax']), $product['template_uprid'], $this->_getEncoded($product), $this->getAdmin());
                if ($row->splinters_id) {
                    $rows[] = $row->splinters_id;
                }
            }
        }

        if (is_array($order->totals) && $order->totals) {
            foreach ($order->totals as $totals) {
                $row = OrdersSplinters::create($order->order_id, $status, self::OWNER_TOTAL, 1, $totals['value_exc_vat'], $totals['value_inc_tax'], $totals['code'] . ':' . $totals['sort_order'], $totals['title'], $this->getAdmin());
                if ($row->splinters_id) {
                    $rows[] = $row->splinters_id;
                }
            }
        }

        if (is_array($order->mixed ?? null) && $order->mixed) {
            foreach ($order->mixed as $mixed) {
                $row = OrdersSplinters::create($order->order_id, $status, self::OWNER_MIXED, $mixed['qty'], $mixed['value_exc_vat'], $mixed['value_inc_tax'], $mixed['owner'], $mixed['data'], $this->getAdmin());
                if ($row->splinters_id) {
                    $rows[] = $row->splinters_id;
                }
            }
        }

        foreach ([self::OWNER_INFO => ['info', $order->info],
            self::OWNER_CUSTOMER => ['customer', $order->customer],
            self::OWNER_DELIVERY => ['delivery', $order->delivery],
            self::OWNER_BILLING => ['billing', $order->billing]] as $key => $array) {

            if (is_array($array) && $array) {
                $create = true;
                if ($status == self::STATUS_PENDING) {
                    $row = OrdersSplinters::find()->where(['splinters_status' => $status, 'orders_id' => $order->order_id, 'splinters_type' => $key])->one();
                    if ($row) {
                        $row->splinters_order = $this->_getEncoded($array[1]);
                        $row->admin_id = $this->getAdmin();
                        $row->save();
                        $create = false;
                    }
                }
                if ($create) {
                    $row = OrdersSplinters::create($order->order_id, $status, $key, 1, 0, 0, $array[0], $this->_getEncoded($array[1]), $this->getAdmin());
                }
                if ($row->splinters_id) {
                    $rows[] = $row->splinters_id;
                }
            }
        }

        if ($order->order_id) {
            $prods = (new \yii\db\Query)->select(['GROUP_CONCAT(splinters_id) as splinters_id', '(sum(qty)=0) as _qty', '(sum(value_inc_tax) = value_inc_tax*2) as _inc', '(sum(value_exc_vat) = value_exc_vat*2) as _exc'])
                            ->from(OrdersSplinters::tableName())
                            ->where(['orders_id' => $order->order_id, 'splinters_type' => self::OWNER_PRODUCT, 'splinters_status' => self::STATUS_PENDING])
                            ->groupBy('splinters_owner')->all();
            if ($prods) {
                foreach ($prods as $prod) {
                    if ($prod['_qty'] && $prod['_inc'] && $prod['_exc']) {
                        \common\models\OrdersSplinters::deleteAll(['in', 'splinters_id', explode(",", $prod['splinters_id'])]);
                    }
                }
                //$rows = array_diff($rows, \yii\helpers\ArrayHelper::getColumn($prods, 'splinters_id'));
            }
        }

        if ($rows) {
            if (method_exists($order, 'setSplinters')) {
                $order->setSplinters($rows);
            }
            return true;
        }
        return false;
    }

    /* update subordernumber: invoice id or credit note id for splinters rows
     * @params document $id, splinters rows
     */

    public function setDocumentNumber($id, $rows) {
        if ($rows && $id) {
            return OrdersSplinters::updateAll(['splinters_suborder_id' => $id, 'date_status_changed' => new \yii\db\Expression('now()')], ['in', 'splinters_id', $rows]);
        }
        return false;
    }

    public function createDocumentNumber($status) {
        $max = OrdersSplinters::find()->where(['splinters_status' => $status])->max('splinters_suborder_id');
        return ($max ? ($max + 1) : 1);
    }

    private function _getEncoded($data) {
        return base64_encode(gzcompress(serialize($data)));
    }

    private function _getDecoded($data) {
        return unserialize(gzuncompress(base64_decode($data)));
    }

    public function hasInvoice($fromOrderId) {
        return \common\models\OrdersSplinters::find()->where(['orders_id' => $fromOrderId, 'splinters_status' => self::STATUS_PAYED])->exists();
    }

    public function hasUnclosedRma($fromOrderId) {
        return \common\models\OrdersSplinters::find()->where(['orders_id' => $fromOrderId, 'splinters_status' => self::STATUS_RETURNING])->exists();
    }

    public function hasClosedRma($fromOrderId) {
        return \common\models\OrdersSplinters::find()->where(['orders_id' => $fromOrderId, 'splinters_status' => self::STATUS_RETURNED])->exists();
    }

    private function clearBeforeImprint($fromOrderId) {
        \common\models\OrdersSplinters::deleteAll(['orders_id' => $fromOrderId]);
    }

    private function createImprint($fromOrderId = null) {
        if ($this->splinter) {
            if (!is_null($fromOrderId)) {
                $this->clearBeforeImprint($fromOrderId);
                $this->splinter->order_id = $fromOrderId;
            }
            $this->copyData($this->original, $this->splinter);
            $this->splinter->modified = true;
            $this->splinter->info = $this->original->info;
            $this->splinter->products = $this->original->products;
            $this->splinter->totals = $this->manager->getTotalOutput(false);//$this->original->totals;
        }
    }
    /**
     * create history rows as base for Invoice|creditNote
     * @param int $fromOrderId - order id
     * @return boolean
     */
    public function makeSplinters($fromOrderId = null) {
        $this->createSplinterObject();
        $this->original = $this->manager->getOrderInstance();
        if (!is_null($fromOrderId)) {
            if ($this->hasInvoice($fromOrderId)) {
                $this->source = new \common\classes\Order($fromOrderId);
                $this->buidSplinter();
            } else {
                $this->createImprint($fromOrderId);
            }
        } else {
            $this->createImprint();
        }

        if (is_object($this->splinter) && $this->splinter->modified) {
            $status = self::STATUS_PENDING;
            $due = $this->getDue($this->splinter->totals);
            $paid = $this->getOtValue($this->splinter->totals, 'ot_paid', 'value_inc_tax');
            if (!is_null($due) && !is_null($paid)){
                $total = $this->getOtValue($this->splinter->totals, 'ot_total', 'value_inc_tax');
                if ( (round($total, 2) - round($paid, 2)) < 0.02  && $due < 0.02){
                    $status = self::STATUS_PAYED;
                }
            }
            if ($this->saveSplinter($this->splinter, $status)){
                if ($status == self::STATUS_PAYED){
                    if ($id = $this->createDocumentNumber($status)) {
                        $this->setDocumentNumber($id, $this->splinter->getSplinters());
                    }
                }
            }
        }
        return false;
    }

    public function getMadeSplinter() {
        return $this->splinter;
    }

    private function copyData(Order $from, Order $to) {
        $to->customer = $from->customer;
        $to->delivery = $from->delivery;
        $to->billing = $from->billing;
        //$this->splinter->tax_address = $this->original->tax_address;
        //$this->original->totals = $this->manager->getTotalOutput(false);
    }

    /**
     * create Splinter that has difference between original (current edited order and saved before version)
     * */
    private function buidSplinter() {


        $oClone = clone $this->original;
        $oClone->prepareOrderInfo();
        $this->splinter->order_id = $this->source->order_id;
        $this->splinter->info = $oClone->info;
        $this->splinter->info['shipping_cost_inc_tax'] = $this->original->info['shipping_cost_inc_tax'] - $this->getOtValue($this->source->totals, 'ot_shipping', 'value_inc_tax');
        $this->splinter->info['shipping_cost_exc_tax'] = $this->original->info['shipping_cost_exc_tax'] - $this->getOtValue($this->source->totals, 'ot_shipping', 'value_exc_vat');
        $this->splinter->info['shipping_cost'] = $this->splinter->info['shipping_cost_exc_tax'];
        if ($this->original->info['shipping_cost_exc_tax'] != $this->getOtValue($this->source->totals, 'ot_shipping', 'value_exc_vat')) {
            $this->splinter->modified = true;
        }
        $this->copyData($this->original, $this->splinter);

        $this->_buidSplinterTotals();
    }

    private function _buidSplinterTotals() {

        $sourceProducts = \yii\helpers\ArrayHelper::index($this->source->products, 'template_uprid');
        $originalProducts = \yii\helpers\ArrayHelper::index($this->original->products, 'template_uprid');
        foreach ($originalProducts as $uprid => $oProduct) {
            if (isset($sourceProducts[$uprid])) {
                if (
                        (intval($sourceProducts[$uprid]['qty']) != intval($oProduct['qty'])) ||
                        (strval($sourceProducts[$uprid]['final_price']) != strval($oProduct['final_price'])) ||
                        (strval($sourceProducts[$uprid]['tax']) != strval($oProduct['tax']))) {

                    $this->splinter->modified = true;

                    /* if qty 1=1 will be 0
                     * $oProduct['qty'] = intval($oProduct['qty']) - intval($sourceProducts[$uprid]['qty']);
                     * if price 2.5=2.5 will be 0
                      $oProduct['final_price'] = floatval(number_format($oProduct['final_price'],4)) - floatval(number_format($sourceProducts[$uprid]['final_price'], 4));
                      $oProduct['tax'] = floatval(number_format($oProduct['tax'],2)) - floatval(number_format($sourceProducts[$uprid]['tax'],2)); */

                    if (intval($sourceProducts[$uprid]['qty']) != intval($oProduct['qty'])) {
                        $oProduct['qty'] = intval($oProduct['qty']) - intval($sourceProducts[$uprid]['qty']);
                    }

                    if ((strval($sourceProducts[$uprid]['final_price']) != strval($oProduct['final_price']))) {
                        $oProduct['final_price'] = floatval(number_format($oProduct['final_price'], 4)) - floatval(number_format($sourceProducts[$uprid]['final_price'], 4));
                    }

                    if (strval($sourceProducts[$uprid]['tax']) != strval($oProduct['tax'])) {
                        $oProduct['tax'] = floatval(number_format($oProduct['tax'], 2)) - floatval(number_format($sourceProducts[$uprid]['tax'], 2));
                    }
                    $this->splinter->products[] = $oProduct;
                }
                unset($sourceProducts[$uprid]);
            } else {
                $this->splinter->products[] = $oProduct;
            }
        }

        if ($sourceProducts) {//removed from order
            foreach ($sourceProducts as $uprid => $sProduct) {
                $sProduct['qty'] = -$sProduct['qty'];
                $this->splinter->products[] = $sProduct;
            }
            $this->splinter->modified = true;
        }

        if (is_array($this->splinter->products) && count($this->splinter->products)) {
            $this->splinter->prepareOrderInfoTotals();
        }

        $originalTotals = $this->manager->getTotalOutput(false);
        $totals = [];
        foreach ($originalTotals as $key => $oTotal) {
            if (isset($this->source->totals[$key])) {
                if ($this->source->totals[$key]['code'] == $oTotal['code']) {
                    if ($this->source->totals[$key]['value_exc_vat'] != $oTotal['value_exc_vat'])
                        $this->splinter->modified = true;
                    $oTotal['value_exc_vat'] -= $this->source->totals[$key]['value_exc_vat'];
                    $oTotal['value_inc_tax'] -= $this->source->totals[$key]['value_inc_tax'];
                    $totals[$key] = $oTotal;
                } else {
                    /* $tmp = $this->source->totals[$key];
                      $tmp['value_exc_vat'] = -$tmp['value_exc_vat'];
                      $tmp['value_inc_tax'] = -$tmp['value_inc_tax'];
                      $totals[$key] = $tmp; */
                    $totals[$key] = $oTotal;
                }
                unset($this->source->totals[$key]);
            } else {
                $this->splinter->modified = true;
                $totals[$key] = $oTotal;
            }
        }

        if ($this->source->totals) {
            foreach ($this->source->totals as $sTotal) {
                $totals[$sTotal['sort_order']] = $sTotal;
            }
        }

        $this->splinter->totals = $totals;
    }

    /*
     * do not use for ot class with identical code|class
     */

    private function getOtValue($totals, $class, $field) {
        $totals = \yii\helpers\ArrayHelper::map($totals, 'code', $field);
        return $totals[$class] ?? null;
    }

    public function getDue($totals = []) {
        $due = $this->getOtValue($totals, 'ot_due', 'value_inc_tax');
        if (is_null($due)) {
            $due = $this->getOtValue($totals, 'ot_total', 'value_inc_tax');
        }
        return round($due, 2);
    }

    /**
     * set order id for created slpitters rows 
     * @param int $order_id 
     */
    public function updateSplinterOrderId($order_id) {
        $splObj = $this->getMadeSplinter();
        if ($splObj && $splObj->getSplinters() && $order_id) {
            \common\models\OrdersSplinters::updateAll(['orders_id' => $order_id], ['in', 'splinters_id', $splObj->getSplinters()]);
        }
    }

    /**
     * update logical part of order structure (info, delivery, billing, customer or product)
     * @param string $owner - need to be changed, 
     * @param array $rows - in range of splitters rows
     * @param array $params
     */
    public function updateFields($owner, array $rows, $params = []) {
        if ($params) {
            $splRow = \common\models\OrdersSplinters::find()
                            ->where(['splinters_owner' => $owner])
                            ->andWhere(['in', 'splinters_id', $rows])->one();
            if ($splRow) {
                $data = $this->_getDecoded($splRow->splinters_order);
                if (is_array($data)) {
                    $data = array_merge($data, $params);
                    $splRow->splinters_order = $this->_getEncoded($data);
                }
                $splRow->save();
            }
        }
    }

    private $instances = [];

    /**
     * create Splitter instances for specified order with status
     * @param int $order_id
     * @param int $status
     * @param mix $onlyDocuments array|int of partical documents
     * @return array of instances
     */
    public function getInstancesFromSplinters($order_id, $status, $onlyDocuments = null) {
        if ($order_id) {
            $this->instances = [];
            $order = \common\models\Orders::find()
                            ->select(['currency', 'currency_value'])
                            ->where(['orders_id' => $order_id])->one();
            if ($order) {
                $splintersQuery = \common\models\OrdersSplinters::getOrdersSplinters($order_id);
                $splintersQuery->status($status, in_array($status, [self::STATUS_PAYED, self::STATUS_RETURNED]));
                if (!is_null($onlyDocuments)){
                    if (is_array($onlyDocuments)){
                        $splintersQuery->andWhere(['in', 'splinters_suborder_id', $onlyDocuments]);
                    }elseif (is_int($onlyDocuments)){
                        $splintersQuery->andWhere(['splinters_suborder_id' => $onlyDocuments]);
                    }
                }
                $splintersRows = $splintersQuery->orderBy('date_status_changed, date_added')->all();
                if ($splintersRows) {
                    $currencies = \Yii::$container->get('currencies');
                    $downInstances = \yii\helpers\ArrayHelper::index($splintersRows, 'splinters_id', function ($element) {
                                return $element['splinters_suborder_id'];
                            });

                    foreach ($downInstances as $keyIns => $downInstance) { //may be some invoices or credit notes
                        $_instance = $this->createSplinterObject();
                        $_instance->manager = $this->manager;
                        $_instance->setSplinters(array_keys($downInstance));
                        $totals = [];
                        foreach ($downInstance as $splinter) {
                            $_instance->order_id = $splinter->splinters_suborder_id;
                            $_instance->parent_id = $splinter->orders_id;
                            $_instance->status = $splinter->splinters_status;
                            if ($splinter->splinters_type == self::OWNER_PRODUCT) {
                                $_instance->create_product($order_id, $splinter->splinters_owner, $splinter->qty, $splinter->value_exc_vat, $splinter->value_inc_tax, $this->_getDecoded($splinter->splinters_order));
                            }
                            if (in_array($splinter->splinters_type, [self::OWNER_CUSTOMER, self::OWNER_INFO, self::OWNER_BILLING, self::OWNER_DELIVERY])) {
                                $_instance->{$splinter->splinters_owner} = $this->_getDecoded($splinter->splinters_order);
                            }
                            if (in_array($splinter->splinters_type, [self::OWNER_MIXED])) {
                                $_instance->createMixedType($splinter->splinters_owner, $splinter->value_inc_tax, $splinter->splinters_order);
                            }
                            if ($splinter->splinters_type == self::OWNER_TOTAL) {
                                list($code, $sort_order) = explode(":", $splinter->splinters_owner);
                                $totals[] = [
                                    'title' => $splinter->splinters_order,
                                    'code' => $code,
                                    'sort_order' => intval($sort_order),
                                    'value_exc_vat' => $splinter->value_exc_vat,
                                    'value_inc_tax' => $splinter->value_inc_tax,
                                ];
                            }
                            $_instance->info['date_purchased'] = $splinter->date_status_changed;
                        }
                        $new = [];
                        foreach ($totals as $total) {
                            if (!isset($new[$total['code'] . '_' . $total['sort_order']])) {
                                $new[$total['code'] . '_' . $total['sort_order']] = [
                                    'title' => $total['title'],
                                    'code' => $total['code'],
                                    'sort_order' => intval($total['sort_order']),
                                    'value_exc_vat' => $total['value_exc_vat'],
                                    'value_inc_tax' => $total['value_inc_tax'],
                                ];
                            } else {
                                $new[$total['code'] . '_' . $total['sort_order']]['value_exc_vat'] += $total['value_exc_vat'];
                                $new[$total['code'] . '_' . $total['sort_order']]['value_inc_tax'] += $total['value_inc_tax'];
                            }
                        }
                        foreach ($new as &$total) {
                            $value = (DISPLAY_PRICE_WITH_TAX ? $total['value_inc_tax'] : $total['value_exc_vat']);
                            $total['text'] = $currencies->format($value, true, $order->currency, $order->currency_value);
                            $total['value'] = $value;
                            $total['text_exc_tax'] = $currencies->format($total['value_exc_vat'], true, $order->currency, $order->currency_value);
                            $total['text_inc_tax'] = $currencies->format($total['value_inc_tax'], true, $order->currency, $order->currency_value);
                            if ($total['code'] == 'ot_shipping') {
                                $_instance->info['shipping_cost_exc_tax'] = $total['value_exc_vat'];
                                $_instance->info['shipping_cost_inc_tax'] = $total['value_inc_tax'];
                                $_instance->info['shipping_cost'] = $total['value_exc_vat'];
                                $total['title'] = $_instance->info['shipping_method'];
                            }
                            if ($total['code'] == 'ot_subtotal') {
                                $_instance->info['subtotal_exc_tax'] = $total['value_exc_vat'];
                                $_instance->info['subtotal_inc_tax'] = $total['value_inc_tax'];
                                $_instance->info['subtotal'] = $total['value_exc_vat'];
                            }
                            if ($total['code'] == 'ot_total') {
                                $_instance->info['total_exc_tax'] = $total['value_exc_vat'];
                                $_instance->info['total_inc_tax'] = $total['value_inc_tax'];
                                $_instance->info['total'] = $total['value_exc_vat'];
                            }
                            $_total['tax_class_id'] = 0;
                        }
                        $sorted = \yii\helpers\ArrayHelper::index($new, 'sort_order');
                        ksort($sorted);
                        $_instance->totals = [];
                        foreach($sorted as $_sort){
                            $_instance->totals[$_sort['sort_order']] = $_sort;
                        }
                        $this->instances[] = $_instance;
                    }
                }
            }
        }
        return $this->instances;
    }

    private $iInstance;

    public function setInvoiceInstance(Splinter $splinter) {
        $this->iInstance = $splinter;
        $this->iInstance->setSplitter($this);
    }

    public function getInvoiceInstance() {
        return $this->iInstance;
    }

    private function getAdmin() {
        return $_SESSION['login_id'] ?? null;
    }

    /**
     * update status 
     * @param int $status
     * @param array $rows
     */
    private function updateStatusRows($status, array $rows) {
        \common\models\OrdersSplinters::updateAll(['splinters_status' => $status, 'date_status_changed' => new \yii\db\Expression('now()')], ['in', 'splinters_id', $rows]);
    }
    
    public function getCreditNoteRow($creditNoteId = null){
        $splintersQuery = \common\models\OrdersSplinters::find()->where(['>', 'orders_id', 0]);
        if ($creditNoteId){
            $splintersQuery->andWhere(['splinters_suborder_id' => $creditNoteId]);
        }
        $splintersQuery->status(self::STATUS_RETURNED);
        return $splintersQuery->one();
    }

}
