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

namespace backend\models\EP\Provider\Magento;

use Yii;
use backend\models\EP\Exception;
use backend\models\EP\Messages;
use backend\models\EP\Provider\DatasourceInterface;
use backend\models\EP\Tools;
use common\classes\language;
use backend\models\EP\Provider\Magento\helpers\SoapClient;
use backend\models\EP\Directory;
use backend\models\EP\Provider\Magento\maps\OrderCustomerMap;

class ImportOrders implements DatasourceInterface {

    protected $total_count = 0;
    protected $row_count = 0;
    protected $orders_list;
    protected $config = [];
    protected $afterProcessFilename = '';
    protected $afterProcessFile = false;
    protected $client;

    function __construct($config) {
        if (substr($config['client']['location'], -1) == '/') {
            $config['client']['location'] = substr($config['client']['location'], 0, -1);
        }
        $this->config = $config;
        $this->initDB();
        $pltformConfig = new \common\classes\platform_config(\common\classes\platform::defaultId());
        $pltformConfig->constant_up();
        $p_address = $pltformConfig->getPlatformAddress();
        $this->config['default_platform'] = \common\helpers\Country::get_countries($p_address['country_id'], true);
        $manager = \common\services\OrderManager::loadManager();
        $order_total_modules = $manager->getTotalCollection();
        $payment_modules = $manager->getPaymentCollection();
        if (is_array($this->config['predefined_status'])){
            $this->config['predefined_status'] = array_flip($this->config['predefined_status']);
            foreach($this->config['predefined_status'] as $key => $status){
                $ex = explode("_", $status);
                if (isset($ex[1])){
                    $this->config['predefined_status'][$key] = $ex[1];
                }
            }
        }
    }

    public function allowRunInPopup() {
        return true;
    }

    public function initDB() {
        tep_db_query("CREATE TABLE IF NOT EXISTS ep_holbi_soap_link_orders(
   ep_directory_id INT(11) NOT NULL,
   remote_order_id INT(11) NOT NULL,
   local_order_id INT(11) NOT NULL,
   KEY(ep_directory_id, remote_order_id),
   UNIQUE KEY(local_order_id)
);");
    }

    public function getProgress() {
        if ($this->total_count > 0) {
            $percentDone = min(100, ($this->row_count / $this->total_count) * 100);
        } else {
            $percentDone = 100;
        }
        return number_format($percentDone, 1, '.', '');
    }

    public function prepareProcess(Messages $message) {

        $mg = new SoapClient($this->config['client']);
        $this->client = $mg->getClient();
        $this->session = $mg->loginClient();

        $this->config['assign_platform'] = \common\classes\platform::defaultId();

        $this->getOrdersList();

        $this->total_count = count($this->orders_list);

        $this->afterProcessFilename = tempnam($this->config['workingDirectory'], 'after_process');
        $this->afterProcessFile = fopen($this->afterProcessFilename, 'w+');
    }

    public function getOrdersList() {
        try {
            $this->orders_list = $this->client->call($this->session, 'sales_order.list');
            if (is_array($this->orders_list)) {
                if (isset($this->config['trunkate_orders'])) {
                    \common\helpers\Order::trunk_orders();
                    tep_db_query("delete from ep_holbi_soap_link_orders where ep_directory_id = '" . (int) $this->config['directoryId'] . "'");
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception('Fetch customers list error');
        }
    }

    public function getOrderInfo($id) {
        try {
            $result = $this->client->call($this->session, 'sales_order.info', $id);
        } catch (\Exception $ex) {
            throw new \Exception('Fetch order ' . $id . ' info error');
        }
        return $result;
    }

    public function getCustomerAddressInfo($id) {
        try {
            $result = $this->client->call($this->session, 'customer_address.info', $id);
        } catch (\Exception $ex) {
            throw new \Exception('Fetch customer address info error');
        }
        return $result;
    }

    public function getCustomerAddresses($id) {
        try {
            $result = $this->client->call($this->session, 'customer_address.list', $id);
            if ($result) {
                if (is_array($result)) {
                    foreach ($result as $key => $address) {
                        $result[$key] = array_merge($result[$key], $this->getCustomerAddressInfo($address['customer_address_id']));
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception('Fetch customer ' . $id . ' info error');
        }
        return $result;
    }

    public function processRow(Messages $message) {
        $remoteOrder = current($this->orders_list);
        if (!$remoteOrder)
            return false;
        try {
            $this->processRemoteOrder($remoteOrder['increment_id']);
        } catch (\Exception $ex) {
            throw new \Exception('Processing order error (increment ID: ' . $remoteOrder['increment_id'] . ')');
        }


        $this->row_count++;
        next($this->orders_list);
        return true;
    }

    public function postProcess(Messages $message) {
        return;
    }

    protected function processRemoteOrder($remoteOrderId) {
        global $cart, $sendto, $billto;
        $currencies = \Yii::$container->get('currencies');
        static $timing = [
            'soap' => 0,
            'local' => 0,
        ];
        $t1 = microtime(true);

        $localId = $this->lookupLocalId($remoteOrderId);
        if (!$localId) {
            
            $remoteOrder = $this->getOrderInfo($remoteOrderId);
            
            $t2 = microtime(true);
            $timing['soap'] += $t2 - $t1;
 
            if ($remoteOrder) {

                if (!is_object($cart)) {
                    $cart = new \common\classes\shopping_cart();
                }
                $cart->reset(true);

                $localOrder = new \common\classes\Order();
                $session = new \yii\web\Session();
                

                $localCustomer = \common\api\models\AR\Customer::find()
                        ->where(['customers_email_address' => $remoteOrder['customer_email']])
                        ->one();
                

                $importArray = $this->mapCustomer($remoteOrder);
                $importArray['customers_status'] = 1;
                
                if (!$localCustomer) {
                    $localCustomer = new \common\api\models\AR\Customer();
                    $importArray['customers_status'] = 0;
                }
                $importArray['platform_id'] = $this->config['assign_platform'];
                $sendto = $billto = null;
                try {
                    $localCustomer->importArray($importArray);
                    $addresses = $localCustomer->initCollectionByLookupKey_Addresses([]);
                } catch (\Exception $ex) {
//                    mail('akoshelev@holbi.co.uk','importOrder', print_r($remoteOrder, 1));
//                    mail('akoshelev@holbi.co.uk','importCustomer', print_r($importArray, 1));
                    echo $ex->getMessage();
                }
                
                if (is_array($addresses) && count($addresses)) {
                    $addresses[0]->is_default = 1;
                }
                
                
                if ($localCustomer->validate() && $localCustomer->isNewRecord) {                    
                    try{
                        $localCustomer->save(false);
                        $addresses = $localCustomer->initCollectionByLookupKey_Addresses([]);
                    } catch (\Exception $ex) {
                        mail('akoshelev@holbi.co.uk', 'saveError', print_r($ex ,1));
                    }
                } else if ($localCustomer->validate() && !$localCustomer->isNewRecord){ // update addresses
                    $addresses = $localCustomer->initCollectionByLookupKey_Addresses([]);
                    if (is_array($addresses) && count($addresses)){
                        foreach($addresses as $_address){
                            $_address->pendingRemoval = false;
                            try{
                                if ($_address->validate()){
                                    $_address->save(false);
                                }
                            } catch (\Exception $ex) {
                                echo $ex->getMessage();
                            }
                        }
                    }
                }
                
                if (is_array($addresses)) {
                    if (count($addresses) == 1) {
                        $sendto = $billto = $addresses[0]->address_book_id;
                    } else if (count($addresses) == 2) {
                        $sendto = $addresses[0]->address_book_id;
                        $billto = $addresses[1]->address_book_id;
                    }
                }
                
                if (empty($sendto)||empty($billto)){
                    $cInfo = \common\helpers\Customer::getCustomerData($localCustomer->customers_id);
                    if ($cInfo){
                        if (empty($sendto)) $sendto = $cInfo['customers_default_address_id'];
                        if (empty($billto)) $billto = $cInfo['customers_default_address_id'];
                    }
                }
                
                if (is_null($sendto)) $sendto = $billto;
                if (is_null($billto)) $billto = $sendto;
                
                $session->set('customer_id', $localCustomer->customers_id);
                $cart->currency = $remoteOrder['order_currency_code'];
                $localOrder->cart();                
                $localOrder->info['payment_method'] = $remoteOrder['payment']['method'];
                $localOrder->info['payment_class'] = $remoteOrder['payment']['method'];
                $localOrder->info['shipping_class'] = $remoteOrder['shipping_method'];
                $localOrder->info['shipping_weight'] = $remoteOrder['weight'];
                $localOrder->info['shipping_method'] = $remoteOrder['shipping_description'];
                $localOrder->info['shipping_cost'] = (float)$remoteOrder['shipping_amount']+ (float)$remoteOrder['shipping_tax_amount'];
                $localOrder->info['shipping_cost_inc_tax'] = (float)$remoteOrder['shipping_amount'] + (float)$remoteOrder['shipping_tax_amount'];
                $localOrder->info['shipping_cost_exc_tax'] = (float)$remoteOrder['shipping_amount'];
                $localOrder->info['subtotal'] = (float)$remoteOrder['subtotal_incl_tax'];
                $localOrder->info['subtotal_inc_tax'] = (float)$remoteOrder['subtotal_incl_tax'];
                $localOrder->info['subtotal_exc_tax'] = (float)$remoteOrder['subtotal'];
                $localOrder->info['tax'] = (float)$remoteOrder['tax_amount'];
                $localOrder->info['total_inc_tax'] = (float)$remoteOrder['grand_total'];
                $localOrder->info['total_exc_tax'] = (float)$remoteOrder['grand_total'] - (float)$remoteOrder['tax_amount'];
                $localOrder->info['total'] = (float)$remoteOrder['grand_total'];
                $localOrder->info['total_paid_inc_tax'] = (float)$remoteOrder['total_paid'];
                $localOrder->info['total_paid_exc_tax'] = (float)$remoteOrder['total_paid'];
                $localOrder->info['order_status'] = $this->config['predefined_status'][$remoteOrder['status']];
                
                global $payment;

                try{
                    if (!is_object($GLOBALS[$remoteOrder['payment']['method']]) && !empty($remoteOrder['payment']['method'])){
                        eval("class {$remoteOrder['payment']['method']} { }");
                        $GLOBALS[$remoteOrder['payment']['method']] = new $remoteOrder['payment']['method'];
                        $GLOBALS[$remoteOrder['payment']['method']]->dont_update_stock = true;
                        $payment = $remoteOrder['payment']['method'];
                        $localOrder->info['payment_method'] = 'Payment ' . $remoteOrder['payment']['method'] . ' from Magento';
                        $localOrder->info['payment_class'] = $remoteOrder['payment']['method'];
                    } else if(is_object($GLOBALS[$remoteOrder['payment']['method']])){
                        $localOrder->info['payment_method'] = $GLOBALS[$remoteOrder['payment']['method']]->title;
                    }
                } catch (\Exception $ex) {
                    throw new \Exception($ex->getMessage());
                }
                $tools = new Tools();
                
                if (is_array($remoteOrder['items'])){
                    $products = $remoteOrder['items'];
                    $index = 0;
                    $add_manual_products = [];
                    for($i=0;$i<$remoteOrder['total_item_count'];$i++){
                        //echo '<pre>';print_r(unserialize($products[$i]['product_options']));
                        $tlProduct = \common\api\models\AR\Products::find()->where(['products_id' => (int)$this->lookupLocalProductId($products[$i]['product_id'])])->one();
                        $attributes = [];
                        if (!is_null($products[$i]['product_options'])){
                            $unserialize = unserialize($products[$i]['product_options']);
                            
                            if (is_array($unserialize['options']) && count($unserialize['options']) > 0){
                                
                                for($j=0;$j<count($unserialize['options']);$j++){
                                    if (in_array($unserialize['options'][$j]['option_type'], ['checkbox', 'drop_down'])) {  
                                        $option_id = $tools->get_option_by_name($unserialize['options'][$j]['label']);
                                        $option_value_id = $tools->get_option_value_by_name($option_id, $unserialize['options'][$j]['value']);
                                        if ($option_id && $option_value_id){
                                            $attributes[] = [
                                                'products_options' => $unserialize['options'][$j]['label'],
                                                'products_options_values' => $unserialize['options'][$j]['value'],
                                                'option_id' => $option_id,
                                                'value_id' => $option_value_id,
                                            ];                                            
                                        } 
                                    }
                                }
                            }
                        }
                        $_product  = array('qty' => $products[$i]['qty_ordered'],
                                                'name' => $products[$i]['name'],
                                                'model' => $products[$i]['sku'],
                                                'is_virtual' => isset($products[$i]['is_virtual']) ? intval($products[$i]['is_virtual']) : 0,
                                                'gv_state' => (preg_match('/^GIFT/', $products[$i]['sku']) ? 'pending' : 'none'),
                                                'tax' => $products[$i]['tax_amount'],
                                                'ga' => 0,
                                                'price' => (float)$products[$i]['price'],
                                                'final_price' => (float)$products[$i]['price'] + (float)$products[$i]['tax_amount'],
                                                'weight' => $products[$i]['weight'],
                                                'id' => \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($tlProduct->products_id, $attributes)),
                                                'attributes' => $attributes,
                                                );
                        if ($tlProduct){
                            $localOrder->products[$index] = $_product;
                            $index++;
                        } else {
                            $_product['id'] = 0;
                            $add_manual_products[] = $_product;
                        }
                    }
                }
                
                $insert_id = $localOrder->save_order();
                if($insert_id){
                    tep_db_query("update ". $localOrder->table_prefix . TABLE_ORDERS ." set date_purchased = '" .$remoteOrder['created_at']. "' where orders_id = '" . (int)$insert_id. "'");
                }
                
                global $order_totals;
                $order_totals = [];
                if(is_object($GLOBALS['ot_subtotal'])){
                    $order_totals[] = array('code' => 'ot_subtotal',
                            'title' => $GLOBALS['ot_subtotal']->title,
                            'text' => $currencies->format($localOrder->info['subtotal'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'value' => $localOrder->info['subtotal'],
                            'sort_order' => 1,
                            'text_exc_tax' => $currencies->format($localOrder->info['subtotal_exc_tax'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'text_inc_tax' => $currencies->format($localOrder->info['subtotal_inc_tax'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'adjusted' => 0,
                            'tax_class_id' => 0,
                            'value_exc_vat' =>$localOrder->info['subtotal_exc_tax'],
                            'value_inc_tax' => $localOrder->info['subtotal_inc_tax'],
                            'difference' => 0,
                        );
                }
                if ((float)$remoteOrder['discount_amount'] != 0 && is_object($GLOBALS['ot_coupon'])){
                    $remoteOrder['discount_amount'] = (float)$remoteOrder['discount_amount'];
                    $text = $currencies->format($remoteOrder['discount_amount'], true, $localOrder->info['currency'], $localOrder->info['currency_value']);
                    $order_totals[] = array('code' => 'ot_coupon',
                            'title' => $GLOBALS['ot_coupon']->title. ' ('.$remoteOrder['discount_description'].')',
                            'text' => $text,
                            'value' => $localOrder->info['discount_amount'],
                            'sort_order' => 2,
                            'text_exc_tax' => $text,
                            'text_inc_tax' => $text,
                            'adjusted' => 0,
                            'tax_class_id' => 0,
                            'value_exc_vat' => $remoteOrder['discount_amount'],
                            'value_inc_tax' => $remoteOrder['discount_amount'],
                            'difference' => 0,
                        );
                }
                if(is_object($GLOBALS['ot_shipping'])){
                    $order_totals[] = array('code' => 'ot_shipping',
                            'title' => $GLOBALS['ot_shipping']->title,
                            'text' => $currencies->format($localOrder->info['shipping_cost'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'value' => $localOrder->info['shipping_cost'],
                            'sort_order' => 3,
                            'text_exc_tax' => $currencies->format($localOrder->info['shipping_cost_exc_tax'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'text_inc_tax' => $currencies->format($localOrder->info['shipping_cost_inc_tax'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'adjusted' => 0,
                            'tax_class_id' => 0,
                            'value_exc_vat' =>$localOrder->info['shipping_cost_exc_tax'],
                            'value_inc_tax' => $localOrder->info['shipping_cost_inc_tax'],
                            'difference' => 0,
                        );
                }
                if(is_object($GLOBALS['ot_subtax'])){
                    $value = $localOrder->info['subtotal'] + $localOrder->info['shipping_cost'] - $remoteOrder['discount_amount'];
                    $value_inc = $localOrder->info['subtotal_inc_tax'] + $localOrder->info['shipping_cost_inc_tax'] - $remoteOrder['discount_amount'];
                    $value_exc = $localOrder->info['subtotal_exc_tax'] + $localOrder->info['shipping_cost_exc_tax'] - $remoteOrder['discount_amount'];
                    $order_totals[] = array('code' => 'ot_subtax',
                            'title' => $GLOBALS['ot_subtax']->title,
                            'text' => $currencies->format($value, true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'value' => $value,
                            'sort_order' => 4,
                            'text_exc_tax' => $currencies->format($value_exc, true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'text_inc_tax' => $currencies->format($value_inc, true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'adjusted' => 0,
                            'tax_class_id' => 0,
                            'value_exc_vat' => $value_exc,
                            'value_inc_tax' => $value_inc,
                            'difference' => 0,
                        );
                }
                if(is_object($GLOBALS['ot_tax'])){
                    $text =  $currencies->format($localOrder->info['tax'], true, $localOrder->info['currency'], $localOrder->info['currency_value']);
                    $order_totals[] = array('code' => 'ot_tax',
                            'title' => $GLOBALS['ot_tax']->title,
                            'text' => $text,
                            'value' => $localOrder->info['tax'],
                            'sort_order' => 5,
                            'text_exc_tax' => $text,
                            'text_inc_tax' => $text,
                            'adjusted' => 0,
                            'tax_class_id' => 0,
                            'value_exc_vat' =>$localOrder->info['tax'],
                            'value_inc_tax' => $localOrder->info['tax'],
                            'difference' => 0,
                        );
                }
                if(is_object($GLOBALS['ot_total'])){
                    $order_totals[] = array('code' => 'ot_total',
                            'title' => $GLOBALS['ot_total']->title,
                            'text' => $currencies->format($localOrder->info['total'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'value' => $localOrder->info['total'],
                            'sort_order' => 6,
                            'text_exc_tax' => $currencies->format($localOrder->info['total_exc_tax'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'text_inc_tax' => $currencies->format($localOrder->info['total_inc_tax'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'adjusted' => 0,
                            'tax_class_id' => 0,
                            'value_exc_vat' =>$localOrder->info['total_exc_tax'],
                            'value_inc_tax' => $localOrder->info['total_inc_tax'],
                            'difference' => 0,
                        );
                }
                if(is_object($GLOBALS['ot_paid'])){
                    $order_totals[] = array('code' => 'ot_paid',
                            'title' => $GLOBALS['ot_paid']->title,
                            'text' => $currencies->format($localOrder->info['total_paid_inc_tax'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'value' => $localOrder->info['total_paid_inc_tax'],
                            'sort_order' => 7,
                            'text_exc_tax' => $currencies->format($localOrder->info['total_paid_exc_tax'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'text_inc_tax' => $currencies->format($localOrder->info['total_paid_inc_tax'], true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'adjusted' => 0,
                            'tax_class_id' => 0,
                            'value_exc_vat' =>$localOrder->info['total_paid_exc_tax'],
                            'value_inc_tax' => $localOrder->info['total_paid_inc_tax'],
                            'difference' => 0,
                        );
                }
                if(is_object($GLOBALS['ot_due'])){
                    $value_inc = $localOrder->info['total_inc_tax'] - $localOrder->info['total_paid_inc_tax'];
                    $value_exc = $localOrder->info['total_exc_tax'] - $localOrder->info['total_paid_exc_tax'];
                    $order_totals[] = array('code' => 'ot_due',
                            'title' => $GLOBALS['ot_due']->title,
                            'text' => $currencies->format($value_inc, true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'value' => $value_inc,
                            'sort_order' => 8,
                            'text_exc_tax' => $currencies->format($value_exc, true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'text_inc_tax' => $currencies->format($value_inc, true, $localOrder->info['currency'], $localOrder->info['currency_value']),
                            'adjusted' => 0,
                            'tax_class_id' => 0,
                            'value_exc_vat' => $value_exc,
                            'value_inc_tax' => $value_inc,
                            'difference' => 0,
                        );
                }
                $localOrder->status = 'migrated';
                $localOrder->migrated = $this->config['client']['location'];
                
                $localOrder->save_details();
                
                if(is_array($remoteOrder['status_history']) && count($remoteOrder['status_history'])){
                    tep_db_query("delete from ". $localOrder->table_prefix . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . $localOrder->order_id. "'");
                    $history = $remoteOrder['status_history'];
                    try{
                        $history = \yii\helpers\ArrayHelper::index($history, 'created_at');
                    } catch (\Exception $ex) {
                        $history = null;
                    }
                    if(!is_null($history) && $localOrder->order_id){
                        ksort($history);
                        
                        foreach($history as $date => $_history){
                            $sql_array = [
                                'orders_id' => $localOrder->order_id,
                                'orders_status_id' => $this->config['predefined_status'][$_history['status']],
                                'date_added' => $date,
                                'customer_notified' => (int)$_history['is_customer_notified'],
                                'comments' => tep_db_prepare_input($_history['comment']),
                            ];
                            tep_db_perform($localOrder->table_prefix . TABLE_ORDERS_STATUS_HISTORY, $sql_array);                            
                        }
                    }
                }
                try{
                    $localOrder->save_products(false);
                    if (is_array($add_manual_products) && count($add_manual_products) > 0){
                        foreach($add_manual_products as $_product){
                            $sql_data_array = array('orders_id' => $localOrder->order_id,
                                'products_id' => \common\helpers\Inventory::get_prid($_product['id']),
                                'products_model' => $_product['model'],
                                'products_name' => $_product['name'],
                                'products_price' => $_product['price'],
                                'final_price' => $_product['final_price'],
                                'products_tax' => $_product['tax'],
                                'products_quantity' => $_product['qty'],
                                'is_giveaway' => $_product['ga'],
                                'is_virtual' => $_product['is_virtual'],
                                'gv_state' => $_product['gv_state'],
                                'uprid' => $_product['id'],
                                );

                            tep_db_perform($localOrder->table_prefix . TABLE_ORDERS_PRODUCTS, $sql_data_array);

                            $order_products_id = tep_db_insert_id();
                            if (isset($_product['attributes'])) {
                                  for ($j = 0, $n2 = sizeof($_product['attributes']); $j < $n2; $j++) {
                                     $sql_data_array = array('orders_id' => $localOrder->order_id,
                                        'orders_products_id' => $order_products_id,
                                        'products_options' => $_product['attributes'][$j]['products_options'],
                                        'products_options_values' => $_product['attributes'][$j]['products_options_values'],
                                        'options_values_price' => 0,
                                        'price_prefix' => '+',
                                        'products_options_id' => $_product['attributes'][$j]['option_id'],
                                        'products_options_values_id' => $_product['attributes'][$j]['value_id']);
                                    tep_db_perform($localOrder->table_prefix . TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array); 
                                  }
                            }
                        }
                    }
                } catch (\Exception $ex) {
                    echo '<pre>';print_r($ex);
                }               

                if ($localOrder->order_id){
                    $this->linkRemoteWithLocalId($remoteOrderId, $localOrder->order_id);
                }
                //echo '<pre>';print_r($localOrder);die;
                //$localAdresses = $localCustomer->initCollectionByLookupKey_Addresses(null);
                unset($localCustomer);
                unset($localOrder);
            }
        }
        //echo'<pre>';print_r($localCustomer);print_r($localOrder);die;
        $t3 = microtime(true);
        $timing['local'] += $t3 - $t2;
        //echo '<pre>';  var_dump($timing);    echo '</pre>';
    }
    
    protected function lookupLocalProductId($remoteId) {
        $get_local_id_r = tep_db_query(
                "SELECT local_products_id " .
                "FROM ep_holbi_soap_link_products " .
                "WHERE ep_directory_id='" . (int) $this->config['directoryId'] . "' " .
                " AND remote_products_id='" . $remoteId . "'"
        );
        if (tep_db_num_rows($get_local_id_r) > 0) {
            $_local_id = tep_db_fetch_array($get_local_id_r);
            tep_db_free_result($get_local_id_r);
            return $_local_id['local_products_id'];
        }
        return false;
    }

    protected function lookupLocalId($remoteId) {
        $get_local_id_r = tep_db_query(
                "SELECT local_order_id " .
                "FROM ep_holbi_soap_link_orders " .
                "WHERE ep_directory_id='" . (int) $this->config['directoryId'] . "' " .
                " AND remote_order_id='" . $remoteId . "'"
        );
        if (tep_db_num_rows($get_local_id_r) > 0) {
            $_local_id = tep_db_fetch_array($get_local_id_r);
            tep_db_free_result($get_local_id_r);
            return $_local_id['local_order_id'];
        }
        return false;
    }

    protected function linkRemoteWithLocalId($remoteId, $localId) {
        tep_db_query(
                "INSERT INTO ep_holbi_soap_link_orders(ep_directory_id, remote_order_id, local_order_id ) " .
                " VALUES " .
                " ('" . (int) $this->config['directoryId'] . "', '" . $remoteId . "','" . $localId . "') " .
                "ON DUPLICATE KEY UPDATE ep_directory_id='" . (int) $this->config['directoryId'] . "', remote_order_id='" . $remoteId . "'"
        );
        return true;
    }

    protected function mapCustomer($responseObject) {
        $order = json_decode(json_encode($responseObject), true);

        $t1 = microtime(true);
        $simple = OrderCustomerMap::apllyMapping($order);

        //$simple['customer_id'] = (int) $order['customer_id'];

        return $simple;
    }

}
