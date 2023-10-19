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

namespace common\classes;

use common\models\TrackingNumbers;
use yii\base\InvalidParamException;
use yii\db\Expression;

class Order extends extended\OrderAbstract implements extended\TransactionsInterface
{
    function query($order_id)
    {
        parent::query($order_id);

        $this->trackingNumberLoad();
    }

    protected function trackingNumberLoad()
    {
        $tabledNumbers = array();
        $tracking_table = OrderTrackingNumber::getTrackingFromTable($this->order_id);
        foreach ($tracking_table as $tracking_model) {
            $tabledNumbers[$tracking_model->number] = $tracking_model;
        }

        $normalizeOrderRecord = false;
        if (!is_array($this->info['tracking_number'])) $this->info['tracking_number'] = array();
        foreach ($this->info['tracking_number'] as $_idx => $tracking) {
            if (!is_object($tracking)) {
                $parsedTracking = OrderTrackingNumber::instanceFromString($tracking, $this->order_id);
                if (isset($tabledNumbers[$parsedTracking->number])) {
                    unset($this->info['tracking_number'][$_idx]);
                    continue;
                } else {
                    // missing in tracking table but exist in order - old schema
                    $parsedTracking->save(false);
                    $parsedTracking->refresh();
                    $normalizeOrderRecord = true;
                }
                $this->info['tracking_number'][$_idx] = $parsedTracking;
            }
        }

        foreach ($tabledNumbers as $tabledNumber) {
            $this->info['tracking_number'][] = $tabledNumber;
        }
        if ($normalizeOrderRecord) {
            $orderModel = \common\models\Orders::findOne($this->order_id);
            if ($orderModel) {
                $orderModel->setAttribute('tracking_number', implode(';', array_map('strval', $this->info['tracking_number'])));
                if ($orderModel->getDirtyAttributes(['tracking_number'])) {
                    $orderModel->setAttribute('last_modified', new Expression('NOW()'));
                    $orderModel->save(false);
                }
            }
        }
    }

    public function removeTrackingNumber($tracking_number_id)
    {
        foreach ($this->info['tracking_number'] as $_idx => $trackingNumber) {
            if ($trackingNumber->tracking_numbers_id == $tracking_number_id) {
                unset($this->info['tracking_number'][$_idx]);
            }
        }
        $this->saveTrackingNumbers();
        \common\models\TrackingNumbersExport::deleteAll(['tracking_numbers_id' => $tracking_number_id]);
    }

    public function addTrackingNumber($trackingNumber)
    {
        if ( !is_object($trackingNumber) || !($trackingNumber instanceOf \common\models\TrackingNumbers) ) {
            $trackingNumber = OrderTrackingNumber::instanceFromString(strval($trackingNumber), $this->order_id);
        }

        if ( empty($trackingNumber->number) ) {
            throw new InvalidParamException('Empty tracking number');
        }
        foreach ($this->info['tracking_number'] as $currentTrackingNumber){
            if ( strtolower($trackingNumber->number)==strtolower($currentTrackingNumber->number) ) {
                throw new InvalidParamException('Tracking number "'.$trackingNumber->number.'" already added to order');
            }
        }
        $this->info['tracking_number'][] = $trackingNumber;
    }

    public function saveTrackingNumbers($sendEmail = true, $addStatusHistory = true)
    {
        $emailTrackingNumber = [];
        $processedIds = [];
        foreach ($this->info['tracking_number'] as $trackingNumber) {
            /**
             * @var $trackingNumber OrderTrackingNumber
             */
            $sendTrackingEmail = $trackingNumber->getDirtyAttributes(['tracking_number']);
            if ($trackingNumber->isNewRecord || $trackingNumber->getDirtyAttributes()) {
                $trackingNumber->save();
                $trackingNumber->refresh();
            }
            if ($trackingNumber->isProductsModified()) {
                $trackingNumber->saveProducts();
            }
            $processedIds[] = $trackingNumber->tracking_numbers_id;
            if ($sendTrackingEmail) {
                $emailTrackingNumber[] = $trackingNumber;
            }
        }

        foreach (
            OrderTrackingNumber::find()
                ->where(['orders_id' => $this->order_id])
                ->andFilterWhere(['NOT IN', 'tracking_numbers_id', $processedIds])
                ->all() as $removeTracking) {
            $removeTracking->delete();
        }

        $orderModel = \common\models\Orders::findOne($this->order_id);
        if ($orderModel) {
            $orderModel->setAttribute('tracking_number', implode(';', array_map('strval', $this->info['tracking_number'])));
            if ($orderModel->getDirtyAttributes(['tracking_number'])) {
                $orderModel->setAttribute('last_modified', new Expression('NOW()'));
                $orderModel->save(false);
            }
        }
        if (count($emailTrackingNumber) > 0 && ($sendEmail || $addStatusHistory)) {
            $_keep_platform_id = \Yii::$app->get('platform')->config()->getId();
            //$order = new \common\classes\Order($this->order_id);
            $order = (new \common\services\OrderManager(\Yii::$app->get('storage')))->getOrderInstanceWithId('\common\classes\Order', $this->order_id);
            $platform_config = \Yii::$app->get('platform')->config($order->info['platform_id']);

            $notify_comments = '';
            $customer_notified = 0;

            $email_params_tracking = array(
                'TRACKING_NUMBER' => '',
                'TRACKING_NUMBER_URL' => '',
            );
            $TEXT_TRACKING_NUMBER = \common\helpers\Translation::getTranslationValue('TEXT_TRACKING_NUMBER', 'admin/orders', $order->info['language_id']);
            foreach ($emailTrackingNumber as $TrackingNumber) {
                $tracking_data = \common\helpers\Order::parse_tracking_number($TrackingNumber);
                $strTrackingNumber = (empty($tracking_data['carrier'])?'':"{$tracking_data['carrier']} ").$tracking_data['number'];
                $notify_comments .= $TEXT_TRACKING_NUMBER . ': ' . $strTrackingNumber . "\n";
                $email_params_tracking['TRACKING_NUMBER'] .= (empty($email_params_tracking['TRACKING_NUMBER']) ? '' : ', ') . $strTrackingNumber;
                if ( function_exists('tep_catalog_href_link') ) {
                    $email_params_tracking['TRACKING_NUMBER_URL'] .=
                        (empty($email_params_tracking['TRACKING_NUMBER_URL']) ? '' : ', ') .
                        '<a href="' . $tracking_data['url'] . '" target="_blank">' .
                        '<img border="0" alt="' . $tracking_data['number'] . '" src="' . tep_catalog_href_link('account/order-qrcode', 'oID=' . (int)$this->order_id . '&cID=' . (int)$this->customer['customer_id'] . '&tracking=1&tracking_number=' . urlencode($tracking_data['number']), 'SSL') . '">' .
                        '</a>';
                }else{
                    $email_params_tracking['TRACKING_NUMBER_URL'] .=
                        (empty($email_params_tracking['TRACKING_NUMBER_URL']) ? '' : ', ') .
                        '<a href="' . $tracking_data['url'] . '" target="_blank">' .
                        '<img border="0" alt="' . $tracking_data['number'] . '" src="' . tep_href_link('account/order-qrcode', 'oID=' . (int)$this->order_id . '&cID=' . (int)$this->customer['customer_id'] . '&tracking=1&tracking_number=' . urlencode($tracking_data['number']), 'SSL') . '">' .
                        '</a>';
                }
            }
            $notify_comments = rtrim($notify_comments);

            if ($sendEmail) {
                $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
                $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

                $email_params = \common\helpers\Mail::emailParamsFromOrder($order);
                $email_params['TRACKING_NUMBER'] = $email_params_tracking['TRACKING_NUMBER'];
                $email_params['TRACKING_NUMBER_URL'] = $email_params_tracking['TRACKING_NUMBER_URL'];

                list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Add Tracking Number', $email_params, $order->info['language_id'], $order->info['platform_id']);
                \common\helpers\Mail::send(
                    $order->customer['name'], $order->customer['email_address'],
                    $email_subject, $email_text,
                    $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS
                );
                $customer_notified = 1;
            }

            if ($addStatusHistory) {
                tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, array(
                    'orders_id' => $order->order_id,
                    'orders_status_id' => $order->info['order_status'],
                    'date_added' => 'now()',
                    'customer_notified' => $customer_notified,
                    'comments' => $notify_comments,
                    'admin_id' => (isset($_SESSION['login_id'])?(int)$_SESSION['login_id']:0),
                ));
            }

            \Yii::$app->get('platform')->config($_keep_platform_id);
        }
    }
    
    public static function getARModel($new = false){
        if($new){
            return parent::getARModelNew(new \common\models\Orders());
        } else {
            return \common\models\Orders::find();
        }
    }
    
    public function getProductsARModel(){
        return \common\models\OrdersProducts::find();
    }
    
    public function getStatusHistoryARModel(){
        return \common\models\OrdersStatusHistory::find()->orderBy('date_added, orders_status_history_id');
    }
    
    public function getHistoryARModel(){
        return \common\models\OrdersHistory::find();
    }

    public function isHoldOn()
    {
        $details = $this->getDetails();
        if (!empty($details['hold_on_date']) && $details['hold_on_date']>2000 && $details['hold_on_date']>date('Y-m-d H:i:s')) {
            return true;
        }
        return false;
    }

    public function removeOrder($restock = false){
        if ($this->order_id){
            if ($restock) {
                \common\helpers\Order::restock($this->order_id);
            }
            \common\models\OrdersProductsAllocate::deleteAll(['orders_id' => (int)$this->order_id]);

            \common\models\TrackingNumbers::deleteAll(['orders_id' => (int)$this->order_id]);
            \common\models\TrackingNumbersToOrdersProducts::deleteAll(['orders_id' => (int)$this->order_id]);
            
            parent::removeOrder();
        }
    }
    
    public function getParent(){
        if ($this->order_id){
            $parent = \common\models\OrdersParent::findOne($this->order_id);
            if ($parent){
                if (class_exists($parent->owner_class)){
                    $class = new \ReflectionClass($parent->owner_class);
                    $class->model = $parent->owner_class::getARModel()->where(['child_id' => $this->order_id])->one();
                    return $class;
                }
            }
        }
        return false;
    }
    
    public function hasTransactions(){
        if ($this->order_id){
            return \common\models\OrdersTransactions::find()
                    ->where(['orders_id' => $this->order_id])->exists();
        }
        return 0;
    }
    
    public function maintainSplittering(){
        return true;
    }
    
    protected $splinters = [];
    /**
     * set spinters id in orders_splinters history 
     * @params $splinters - rows for creating splinter instance
    **/
    public function setSplinters(array $splinters){
        $this->splinters = $splinters;
    }
    
    public function getSplinters(){
        return $this->splinters;
    }
    
    public function save_order($order_id = 0) {
        parent::save_order($order_id);
        \common\helpers\System::ga_detection($this->manager);
        return $this->order_id;
    }
    
    public function notify_customer($products_ordered,$emailParams = [], $emailTemplate = ''){
        $notify_status = parent::notify_customer($products_ordered, $emailParams, $emailTemplate);
        if ($notify_status){
            $this->notifyGiftCards();
        }
        return $notify_status;
    }
    
    public function notifyGiftCards(){
        if (is_array($this->products)){
            foreach ($this->products as $product){
                if ($product['model'] == 'VIRTUAL_GIFT_CARD'){
                    if (isset($product['attributes'][0]['value_id']) && $product['attributes'][0]['value_id']){
                        \common\helpers\Gifts::activate($product['attributes'][0]['value_id'], $this);
                    }
                }
            }
        }
    }
    
}
