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
use common\classes\modules\ModuleDropShipping;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;


class xtrader extends ModuleDropShipping {

    var $code, $title, $description, $enabled, $sort_order;

    protected $defaultTranslationArray = [
        'MODULE_DROPSHIPPING_XTRADER_TITLE' => 'xTrader Direct Order',
        'MODULE_DROPSHIPPING_XTRADER_DESCRIPTION' => 'xTrader Direct ordering'
    ];

    public function __construct() {
        parent::__construct();

        $this->code = 'xtrader';
        $this->version = 'v2';
        $this->title = MODULE_DROPSHIPPING_XTRADER_TITLE;
        $this->description = MODULE_DROPSHIPPING_XTRADER_DESCRIPTION;
        if (!defined('MODULE_DROPSHIPPING_XTRADER_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_DROPSHIPPING_XTRADER_STATUS == 'True') ? true : false);
        $this->sort_order = MODULE_DROPSHIPPING_XTRADER_SORT_ORDER;        

        $this->output = array();
    }

    function process($params = []) {

        if (MODULE_DROPSHIPPING_XTRADER_STATUS != 'True') {
            return;
        }

        if (isset($params['orders_id']) && $params['orders_id']) {
            $order = \common\models\Orders::find()->where('orders_id =:orders_id', [":orders_id" => (int) $params['orders_id']])->one();
        } else {
            $customer_id = Yii::$app->user->getId();
            $order = \common\models\Orders::find()->where('customers_id =:customers_id', [":customers_id" => (int) $customer_id])->orderBy('date_purchased desc')->limit('1')->one();
        }
        $response = false;
        if ($order) {
            Yii::$app->get('platform')->config($order->platform_id)->constant_up();
            if ($order->orders_status != MODULE_DROPSHIPPING_XTRADER_DOSUCCESS){
                $response = $this->directorder_process($order);
            }
        }
        return $response;
    }

    function directorder_process($order) {
        global $language, $languages_id;

        $orders_id = $order->orders_id;
        
        $query_params = [];
        
        if (in_array($order->orders_status, $this->getAllowedPushStatuses())){
            $products_array = [];
            $ShippingModule = '';
            if ($order->shipping_class) {
                $shipping_class = explode("_", $order->shipping_class);
                $result = $this->getShippingCode($shipping_class[0]);
                if ($result){
                    $ShippingModule = $result;
                }
            }
            
            $country = \common\helpers\Country::get_country_info_by_name($order->delivery_country, $order->language_id);
            $postage = 1;
            if ($country){
                if ($country['iso_code_2'] == 'GB' || $country['iso_code_3'] == 'GBR'){
                    $postage = 1;
                } else {
                    $postage = 3;
                }
            }
            
            $platform_config = new \common\classes\platform_config($order->platform_id);
            
            $query_params = [
                'Type' => 'ORDEREXTOC',
                'testingmode' => MODULE_DROPSHIPPING_XTRADER_TESTING,
                'VendorCode' => MODULE_DROPSHIPPING_XTRADER_VENDOR,
                'VendorTxCode' => "IDWEB-" . MODULE_DROPSHIPPING_XTRADER_VENDOR . '-' . $orders_id . '-' . date('Ymdhis'),
                'VenderPass' => MODULE_DROPSHIPPING_XTRADER_PASSWORD,
                'VenderSite' => $platform_config->getCatalogBaseUrl(true),
                'Venderserial' => MODULE_DROPSHIPPING_XTRADER_SERIAL,
                'ShippingModule' => $ShippingModule,
                'postage' => $postage,
                'customerFirstName' => $order->delivery_firstname,
                'customerLastName' => $order->delivery_lastname,
                'deliveryCompany' => $order->delivery_company,
                'deliveryAddress1' => $order->delivery_street_address,
                'deliveryAddress2' => $order->delivery_suburb,
                'deliveryTown' => $order->delivery_city,
                'deliveryCounty' => $order->delivery_state,
                'deliveryPostcode' => $order->delivery_postcode,
                'deliveryCountry' => @$country['iso_code_2'],
                'deliveryTelephone' => $order->customers_telephone,
                //'notifyEmail' => $order->customers_email_address,
                'ProductCodes' => 'MODEL'
                ];
            
            $products = \common\models\OrdersProducts::find()->select('orders_products_id, products_model, products_quantity, sets_array')->where("orders_id = '{$orders_id}'")->all();
            $elements = [];
            if ($products){
                foreach($products as $product){
                    //check_attributes
                    $push = $product->products_model;
                    $attributes = \common\models\OrdersProductsAttributes::find()->select('products_options, products_options_values, products_options_id, products_options_values_id')->where("orders_products_id = '{$product->orders_products_id}'")->all();
                    if ($attributes) {
                        foreach($attributes as $attribute){
                            if ($attribute->products_options_id && $attribute->products_options_values_id){ //if bundle restore sets_array options
                                $push .= '{' . $attribute->products_options . '}' . $attribute->products_options_values;
                            }
                        }
                    }
                    $push .= ":".$product->products_quantity;
                    $elements[] = $push;
                    if ($product->sets_array){
                        $sets_array = unserialize($product->sets_array);
                        if (is_array($sets_array) && count($sets_array)) {
                            foreach ($sets_array as $set){
                                $push = $set['model'];
                                if ( isset($set['attributes']) && is_array($set['attributes']) && count($set['attributes']) ){
                                    foreach ($set['attributes'] as $option => $value){
                                        $option_name = \common\models\ProductsOptions::find()->select('products_options_name')->where("products_options_id = '{$option}' and language_id = {$order->language_id}")->one();
                                        $option_value_name = \common\models\ProductsOptionsValues::find()->select('products_options_values_name')->where("products_options_values_id = '{$value}' and language_id = {$order->language_id}")->one();
                                        if ($option_name && $option_value_name){
                                            $push .= '{' . $option_name->products_options_name . '}' . $option_value_name->products_options_values_name;
                                        }
                                    }
                                }
                                $push .= ":".$set['qty'];
                                $elements[] = $push;
                            }
                        }
                    }
                }
            }
            
            $query_params['Products'] = implode("|", $elements);

            $response = $this->setTransaction($query_params);
            
            if ($response){
                if ($this->analyzeResponse($response, $order)){
                    return true;
                }
            }
            
            return false;
        }
        return false;
    }
    
    private function analyzeResponse($response, $order){
        $data_array = $response['data'];
        $send_mail = false;
        $email_subject = '';
        switch ($response['key']) {
            case "DOFailed_insuf_funds":
                $send_mail = true;
                $email_subject = 'Direct Order Failed - insufcient funds';
                $status_id = MODULE_DROPSHIPPING_XTRADER_DOFINSUFUNDS;
                $status_comment = $data_array['error_message'];
                break;
            case "DOFailed_invalid_details":
                $send_mail = true;
                $email_subject = 'Direct Order Failed - invaild details';
                $status_id = MODULE_DROPSHIPPING_XTRADER_DOINVTRANSDET;
                $status_comment = $data_array['error_message'];
                break;
            case "DOSuccess":
                $status_id = MODULE_DROPSHIPPING_XTRADER_DOSUCCESS;
                $status_comment = "Order Placed, the sexshop's order reference id is - '" . $data_array['IDWEB_Order_id'] . "'";
                break;
            default:
                $send_mail = true;
                $email_subject = 'Direct Order Failed - No server response';
                $status_id = MODULE_DROPSHIPPING_XTRADER_DOFNORETURN;
                $status_comment = "ORDER NOT PLACED with sexshop, sexshop's server failed to respond, please check manually'";
                break;
        }
        
        if ($send_mail){
            $platform_config = new \common\classes\platform_config($order->platform_id);
            $eMail_store = $platform_config->const_value('STORE_NAME');
            $eMail_address = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
            $eMail_store_owner = $platform_config->const_value('STORE_OWNER');
            
            $email_params = array();
            $email_params['STORE_NAME'] = $eMail_store;
            $email_params['ORDER_NUMBER'] = method_exists($order, 'getOrderNumber')?$order->getOrderNumber():$order->orders_id;
            $email_params['ORDER_INVOICE_URL'] = \common\helpers\Output::get_clickable_link(tep_catalog_href_link(MODULE_DROPSHIPPING_XTRADER_ORDERSCRIPT, 'orders_id=' . $order->orders_id, 'SSL'));
            $email_params['ORDER_DATE_LONG'] = \common\helpers\Date::date_long($order->date_purchased);
            $email_params['ORDER_COMMENTS'] = $status_comment;
            $email_params['NEW_ORDER_STATUS'] = \common\helpers\Order::get_order_status_name($status_id);
            $emailTemplate = 'Order Update';

            list($_email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template($emailTemplate, $email_params, -1, $order->platform_id);
            if (empty($email_subject)){
                $email_subject = $_email_subject;
            }
            \common\helpers\Mail::send($eMail_store_owner, $eMail_address, $email_subject, $email_text, $eMail_store_owner, $eMail_address, []);
        }
        
        if (isset($data_array['IDWEB_Order_id']) && !empty($data_array['IDWEB_Order_id'])){
            $order->setAttributes(
                    [
                        'idweb_code' => $data_array['IDWEB_Order_id'],
                    ], false);
        }
        
        if ( $status_id ){
            $order->setAttributes([
                'orders_status' => $status_id,
                'last_modified' => 'now()',
            ], false);
        }
        global $login_id;
        if (!$order->hasErrors()){
            $order->update(false);
            $osHistory = new \common\models\OrdersStatusHistory();
            if ($osHistory && $status_id){
                $osHistory->setAttributes([
                    'orders_id' => $order->orders_id,
                    'orders_status_id' => $status_id,
                    'date_added' => date("Y-m-d H:i:s"),
                    'comments' => $status_comment,
                    'admin_id' => $login_id,
                ], false);
                $osHistory->save(false);
            }
        }
        
        if ($send_mail) {
            return false;
        } else {
            return true;
        }
    }
    
    private function setTransaction($params){
        
        $urlstring = urldecode(http_build_query($params));
        
        $urlConn = curl_init();
        curl_setopt($urlConn, CURLOPT_URL, MODULE_DROPSHIPPING_XTRADER_URL);
        curl_setopt($urlConn, CURLOPT_HEADER, false);
        curl_setopt($urlConn, CURLOPT_POST, true);
        curl_setopt($urlConn, CURLOPT_POSTFIELDS, $urlstring);
        curl_setopt($urlConn, CURLOPT_FRESH_CONNECT, true);

        curl_setopt($urlConn, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($urlConn, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($urlConn, CURLOPT_RETURNTRANSFER, true);

        $returned_info = curl_exec($urlConn);
        curl_close($urlConn);
        
        list($returned_key, $returned_details) = explode("|:|", $returned_info);
        $data_array = array();
        $returned_details_array = explode("\n", $returned_details);
        foreach ($returned_details_array as $returned_line) {
            $returned_line = trim($returned_line);
            if ($returned_line != '') {
                list($key, $value) = explode("=", $returned_line);
                $data_array[$key] = $value;
            }
        }
        
        return ['key' => $returned_key, 'data' => $data_array];
    }

    public function configure_keys() {
        $config = array(
            'MODULE_DROPSHIPPING_XTRADER_STATUS' =>
            array(
                'title' => 'Enable direct ordering',
                'value' => 'True',
                'description' => 'Do you want to enable Sexshop Direct Ordering module?',
                'sort_order' => '0',
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
            ),
            'MODULE_DROPSHIPPING_XTRADER_VENDOR' =>
            array(
                'title' => 'Your Sexshop Account ID',
                'value' => '',
                'description' => 'This is your sexshop account id (found in the top right of your account details on the dropship)',
                'sort_order' => '1',
            ),
            'MODULE_DROPSHIPPING_XTRADER_PASSWORD' =>
            array(
                'title' => 'Your DirectOrder Password',
                'value' => '',
                'description' => 'This is your sexshop vendor password (to avoid fraud by someone using your vendor account)',
                'sort_order' => '2',
            ),
            'MODULE_DROPSHIPPING_XTRADER_SERIAL' =>
            array(
                'title' => 'Your DirectOrder Serial',
                'value' => '',
                'description' => 'This is your Directorder serial code supplied to you by email',
                'sort_order' => '3',
            ),
            'MODULE_DROPSHIPPING_XTRADER_URL' =>
            array(
                'title' => 'xTrader\'s direct ordering url',
                'value' => 'http://www.xtrader.co.uk/catalog/directorder_interfaceV2_oc.php',
                'description' => 'Unless otherwise directed leave as default',
                'sort_order' => '4',
            ),
            'MODULE_DROPSHIPPING_XTRADER_ORDERSCRIPT' =>
            array(
                'title' => 'Path to your admin order script',
                'value' => DIR_WS_ADMIN . FILENAME_ORDERS . '/process-order',
                'description' => 'Unless you have moved the orders.php script you shouldn\'t need to change this',
                'sort_order' => '5',
            ),
            'MODULE_DROPSHIPPING_XTRADER_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '0',
                'description' => 'Sort order of display.',
                'sort_order' => '6',
            ),
            'MODULE_DROPSHIPPING_XTRADER_SEND_WITH_STATUS' =>
            array(
                'title' => 'Send Orders with Statuses',
                'value' => '',
                'description' => 'Send Orders with Next Statuses',
                'sort_order' => '6',
                'use_function' => '\\common\\helpers\\Order::get_status_name',
                'set_function' => 'tep_cfg_select_multioption_order_statuses(',
            ),
            'MODULE_DROPSHIPPING_XTRADER_TESTING' =>
            array(
                'title' => 'Testing direct ordering',
                'value' => 'True',
                'description' => 'Do you wish this module to go into testing mode (no orders will be placed on the dropship side nor will your dropship account be debited but emails will be sent and orders will be placed on your own store systems)?',
                'sort_order' => '7',
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
            ),
            'MODULE_DROPSHIPPING_XTRADER_AUTO' =>
            array(
                'title' => 'AutoDirect',
                'value' => 'True',
                'description' => 'Switch On if you need to send order to idWeb automatically',
                'sort_order' => '8',
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
            ),
        );
        return $config;
    }

    function install($platform_id) {

        if (!defined('MODULE_DROPSHIPPING_XTRADER_DOSUCCESS')) {
            $status_name_array = array('MODULE_DROPSHIPPING_XTRADER_DOFINSUFUNDS' => 'DOF: Insufficient Funds',
                'MODULE_DROPSHIPPING_XTRADER_DOINVTRANSDET' => 'DOF: Invalid Transaction Details',
                'MODULE_DROPSHIPPING_XTRADER_DOFNORETURN' => 'DOF: Server failed to respond',
                'MODULE_DROPSHIPPING_XTRADER_DOSUCCESS' => 'DirectOrder Success');
            $languages = \common\helpers\Language::get_languages();
            $def_lid = \common\helpers\Language::get_default_language_id();
            foreach ($status_name_array as $status_key => $status_value) {
                $new_status = (new \yii\db\Query())->select('max(orders_status_id) as status_id')->from(TABLE_ORDERS_STATUS)->where("orders_status_id <> 99999 and language_id = '" . (int) $def_lid . "'")->one();
                if ($new_status) {
                    $set_status = $new_status['status_id'];
                    $set_status++;
                    $status_saved = false;
                    foreach ($languages as $lc => $lv) {
                        $orderStatus = new \common\models\OrdersStatus();
                        if ($orderStatus) {
                            $orderStatus->setAttribute('language_id', $lv['id']);
                            $orderStatus->setAttribute('orders_status_id', $set_status);
                            $orderStatus->setAttribute('orders_status_groups_id', 1); //2do: detect status group
                            $orderStatus->setAttribute('orders_status_name', $status_value);
                            if (!$orderStatus->hasErrors()) {
                                $orderStatus->save(false);
                                $status_saved = true;
                            }
                        }
                    }
                    if ($status_saved) {
                        if (!$platform_id) {
                            tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values "
                                    . "('" . $status_value . "', '" . $status_key . "', '" . $set_status . "', 'DO NOT REMOVE - status key for the order_status table.', '6', '3', now())");
                        } else {
                            tep_db_query("insert into " . TABLE_PLATFORMS_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, platform_id) values "
                                    . "('" . $status_value . "', '" . $status_key . "', '" . $set_status . "', 'DO NOT REMOVE - status key for the order_status table.', '6', '3', now(), '{$platform_id}')");
                        }
                    }
                }
            }
        }
        tep_db_query("alter table " . TABLE_ORDERS . " add column idweb_code varchar(15) not null, add index (idweb_code)");
        parent::prepareShippingTable();
        parent::install($platform_id);
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_DROPSHIPPING_XTRADER_STATUS', 'True', 'False');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_DROPSHIPPING_XTRADER_SORT_ORDER');
    }

    public function extra_params() {
        \common\helpers\Translation::init('ordertotal');
        $platform_id = (int) \Yii::$app->request->get('platform_id');
        if ($platform_id == 0) {
            $platform_id = (int) \Yii::$app->request->post('platform_id');
        }

        $method_action = \Yii::$app->request->post('action', '');
        $method_value = \Yii::$app->request->post('id', '');
        if (!empty($method_action)) {
            switch ($method_action) {
                case 'add':
                    $sql_data_array = array(
                        'platform_id' => $platform_id,
                        'shipping_code' => $method_value,
                        'dropshipping_module' => $this->code,
                    );
                    tep_db_perform('dropshipping_ships', $sql_data_array);
                    break;
                case 'del':
                    tep_db_query("delete from dropshipping_ships where dropshipping_ships_id = '" . (int) $method_value . "'");
                    break;
                default:
                    break;
            }
        }

        $updates = \Yii::$app->request->post('dropshipping_code', array());
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                tep_db_query("update dropshipping_ships set dropshipping_code = '" . $value . "' where dropshipping_ships_id = '" . $key . "'");
            }
        }

        $updates = \Yii::$app->request->post('dropshipping_desc', array());
        if (is_array($updates)) {
            foreach ($updates as $key => $value) {
                tep_db_query("update dropshipping_ships set dropshipping_desc = '" . $value . "' where dropshipping_ships_id = '" . $key . "'");
            }
        }

        $html = '';
        if (!Yii::$app->request->isAjax) {
            $html .= '<div id="modules_extra_params">';
        }

        \common\helpers\Translation::init('shipping');

        $directory_array = $this->directoryList();

        $modules_files = [];
        $avaiable_files = [];
        for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
            $file = $directory_array[$i];

            if (file_exists(DIR_FS_CATALOG_MODULES . 'shipping/' . $file)) {
                require_once(DIR_FS_CATALOG_MODULES . 'shipping/' . $file);
            }

            $class = substr($file, 0, strrpos($file, '.'));
            if (class_exists($class) && is_subclass_of($class, "common\\classes\\modules\\ModuleShipping")) {
                $module = new $class;

                $modules_files[$module->code] = $module->title;
                $check_query = tep_db_query("select * from dropshipping_ships where platform_id='" . $platform_id . "' and dropshipping_module = '" . $this->code . "' and shipping_code='" . $module->code . "'");
                if (tep_db_num_rows($check_query) == 0) {
                    $avaiable_files[] = array('id' => $module->code, 'text' => $module->title);
                }
            } elseif ($file == 'free_free') {
                $check_query = tep_db_query("select * from dropshipping_ships where platform_id='" . $platform_id . "' and dropshipping_module = '" . $this->code . "' and shipping_code='free_free'");
                if (tep_db_num_rows($check_query) == 0) {
                    $avaiable_files[] = array('id' => 'free', 'text' => FREE_SHIPPING_TITLE);
                }
            }
        }
        $html .= HEADING_TITLE_MODULES_SHIPPING;
        $html .= '<table width="100%" class="selected-methods">';
        $html .= '<tr><th width="10%">' . TABLE_HEADING_ACTION . '</th><th width="20%">' . TABLE_HEADING_TITLE . '</th><th width="30%">Code</th><th width="40%">' . TEXT_PRODUCTS_DESCRIPTION_SHORT . '</th></tr>';
        $shipping_modules_query = tep_db_query("select * from dropshipping_ships where platform_id='" . $platform_id . "' and dropshipping_module = '" . $this->code . "' order by shipping_code");
        while ($shipping_modules = tep_db_fetch_array($shipping_modules_query)) {
            $html .= '<tr><td><span class="delMethod" onclick="delShipMethod(\'' . $shipping_modules['dropshipping_ships_id'] . '\')"></span></td><td>';
            $html .= $modules_files[$shipping_modules['shipping_code']] ?? null;
            $html .= '</td><td>';            
            $html .= tep_draw_input_field('dropshipping_code[' . $shipping_modules['dropshipping_ships_id'] . ']', $shipping_modules['dropshipping_code']);
            $html .= '</td><td>';
            $html .= tep_draw_input_field('dropshipping_desc[' . $shipping_modules['dropshipping_ships_id'] . ']', $shipping_modules['dropshipping_desc']);
            $html .= '</td></tr>';
        }
        $html .= '</table><br>';


        if (count($avaiable_files) > 0) {
            $html .= '<div>' . tep_draw_pull_down_menu('ship_method_code', $avaiable_files) . ' <span class="btn" onclick="return addShipMethod();">' . TEXT_ADD_SHIPPING_METHOD . '</span></div>';
        }

        if (!Yii::$app->request->isAjax) {
            $html .= '</div>';
            $html .= '<script type="text/javascript">
function delShipMethod(id) {
    $.post("' . tep_href_link('modules/extra-params') . '", {"set": "dropshipping", "module": "' . $this->code . '", "platform_id": "' . (int) $platform_id . '", "action": "del", "id": id}, function(data, status) {
        if (status == "success") {
            $(\'#modules_extra_params\').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function addShipMethod() {
var id = $(\'select[name="ship_method_code"]\').val();
    $.post("' . tep_href_link('modules/extra-params') . '", {"set": "dropshipping", "module": "' . $this->code . '", "platform_id": "' . (int) $platform_id . '", "action": "add", "id": id}, function(data, status) {
        if (status == "success") {
            $(\'#modules_extra_params\').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
</script>';
        }

        return $html;
    }

    private function directoryList() {
        $directory_array = array('free_free');
        if ($dir = @dir(DIR_FS_CATALOG_MODULES . 'shipping/')) {
            while ($file = $dir->read()) {
                if (!is_dir(DIR_FS_CATALOG_MODULES . 'shipping/' . $file)) {
                    if (in_array(substr($file, strrpos($file, '.') + 1), array('php'))) {
                        $directory_array[] = $file;
                    }
                }
            }
            sort($directory_array);
            $dir->close();
        }
        return $directory_array;
    }
    
    public function getAllowedPushStatuses(){
        $statuses = [MODULE_DROPSHIPPING_XTRADER_DOFINSUFUNDS, MODULE_DROPSHIPPING_XTRADER_DOINVTRANSDET, MODULE_DROPSHIPPING_XTRADER_DOFNORETURN];
        if (defined('MODULE_DROPSHIPPING_XTRADER_SEND_WITH_STATUS')){
            $statuses = array_merge($statuses, explode(", ", MODULE_DROPSHIPPING_XTRADER_SEND_WITH_STATUS));
        }
        return $statuses;
    }   
}

?>