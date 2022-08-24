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

namespace backend\models\EP\Provider;

use backend\models\EP\Messages;
use backend\models\EP;
use common\classes\payment;
use common\classes\shipping;
use common\classes\shopping_cart;
use common\helpers\Html;
use common\models\OrdersHistory;
use common\models\OrdersStatusHistory;
use common\models\OrdersTransactions;
use yii\db\ColumnSchema;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class Order extends ProviderAbstract implements ExportInterface, ImportInterface
{
    protected $fields = array();
    protected $export_query;

    protected $export_rows = [];
    protected $skip_external_on_import = [];
    protected $collectOrder = [];

    protected $lastProcessProductError = '';
    protected $processed_count = 0;

    protected $site_modules = [];

    protected $import_defaults = [
        'check_valid_product' => 'check_valid',
        'import_mode' => 'create_recalculate',
        'recalculate' => 'no',
    ];

    /**
     * @var EP\Tools
     */
    protected $EPtools;

    function init()
    {
        parent::init();
        $this->initFields();

        $this->EPtools = new EP\Tools();
    }

    public function exchangeXml()
    {
        return [
            [
                'header' => [
                    'type' => 'orders'
                ],
                'rowsTag' => 'Orders',
                'rowTag' => 'Order',
            ],
        ];
    }

    protected function initFields()
    {
        $this->fields['intId'] = array('name' => 'info.orders_id', 'value' => 'OrderID');
        $this->fields['extId'] = array('name' => 'info.external_orders_id', 'value' => 'External Order ID');
        $this->fields[] = array('name' => 'customer.email_address', 'value' => 'Customer Email');
        $this->fields[] = array('name' => 'customer.newsletter', 'value' => 'Subscribed to Newsletters');
        $this->fields[] = array('name' => 'customer.telephone', 'value' => 'Customer Phone');
        $this->fields[] = array('name' => 'customer.name', 'value' => 'Customer Name');
        $this->fields[] = array('name' => 'customer.firstname', 'value' => 'Customer First Name');
        $this->fields[] = array('name' => 'customer.lastname', 'value' => 'Customer Last Name');
        $this->fields[] = array('name' => 'customer.company', 'value' => 'Customer Company');
        $this->fields[] = array('name' => 'customer.street_address', 'value' => 'Customer Street Address');
        $this->fields[] = array('name' => 'customer.suburb', 'value' => 'Customer Suburb');
        $this->fields[] = array('name' => 'customer.city', 'value' => 'Customer City');
        $this->fields[] = array('name' => 'customer.postcode', 'value' => 'Customer Postcode');
        $this->fields[] = array('name' => 'customer.state', 'value' => 'Customer State');
        $this->fields[] = array('name' => 'customer.country.iso_code_2', 'value' => 'Customer Country');
        $this->fields[] = array('name' => 'billing.name', 'value' => 'Billing Name');
        $this->fields[] = array('name' => 'billing.firstname', 'value' => 'Billing First Name');
        $this->fields[] = array('name' => 'billing.lastname', 'value' => 'Billing Last Name');
        $this->fields[] = array('name' => 'billing.company', 'value' => 'Billing Company');
        $this->fields[] = array('name' => 'billing.street_address', 'value' => 'Billing Street Address');
        $this->fields[] = array('name' => 'billing.suburb', 'value' => 'Billing Suburb');
        $this->fields[] = array('name' => 'billing.city', 'value' => 'Billing City');
        $this->fields[] = array('name' => 'billing.postcode', 'value' => 'Billing Postcode');
        $this->fields[] = array('name' => 'billing.state', 'value' => 'Billing State');
        $this->fields[] = array('name' => 'billing.country.iso_code_2', 'value' => 'Billing Country');
        $this->fields[] = array('name' => 'delivery.name', 'value' => 'Shipping Name');
        $this->fields[] = array('name' => 'delivery.firstname', 'value' => 'Shipping First Name');
        $this->fields[] = array('name' => 'delivery.lastname', 'value' => 'Shipping Last Name');
        $this->fields[] = array('name' => 'delivery.company', 'value' => 'Shipping Company');
        $this->fields[] = array('name' => 'delivery.street_address', 'value' => 'Shipping Street Address');
        $this->fields[] = array('name' => 'delivery.suburb', 'value' => 'Shipping Suburb');
        $this->fields[] = array('name' => 'delivery.city', 'value' => 'Shipping City');
        $this->fields[] = array('name' => 'delivery.postcode', 'value' => 'Shipping Postcode');
        $this->fields[] = array('name' => 'delivery.state', 'value' => 'Shipping State');
        $this->fields[] = array('name' => 'delivery.country.iso_code_2', 'value' => 'Shipping Country');
        $this->fields[] = array('name' => 'products.*.model', 'value' => 'Product Model');
        $this->fields[] = array('name' => 'products.*.name', 'value' => 'Product Name');
        $this->fields[] = array('name' => 'products.*.units', 'value' => 'Product Unit Qty');
        $this->fields[] = array('name' => 'products.*.packagings', 'value' => 'Product Pack Qty');
        $this->fields[] = array('name' => 'products.*.packs', 'value' => 'Product Carton Qty');
        $this->fields[] = array('name' => 'products.*.tax', 'value' => 'Product Tax Rate');
        $this->fields[] = array('name' => 'products.*.units_price', 'value' => 'Product Unit Price');
        $this->fields[] = array('name' => 'products.*.packagings_price', 'value' => 'Product Pack Price');
        $this->fields[] = array('name' => 'products.*.packs_price', 'value' => 'Product Carton Price');
        $this->fields[] = array('name' => 'products.*.weight', 'value' => 'Product Weight');
        // {{ totals
        foreach ( $this->getOrderTotalModules() as $totalCode=>$totalTitle){
            $this->fields[] = array('name' => 'totals.'.$totalCode.'.title', 'value' => 'Total: '.$totalTitle .' (Title)');
            $this->fields[] = array('name' => 'totals.'.$totalCode.'.value_exc_vat', 'value' => 'Total: '.$totalTitle.' (value exl vat)');
            $this->fields[] = array('name' => 'totals.'.$totalCode.'.value_inc_tax', 'value' => 'Total: '.$totalTitle.' (value inc vat)');
        }
        // }} totals
        $this->fields[] = array('name' => 'info.payment_class', 'value'=>'Payment class',);
        $this->fields[] = array('name' => 'info.payment_method', 'value'=>'Payment Method',);
        $this->fields[] = array('name' => 'transaction.transaction_id', 'value'=>'Payment Transaction ID',);
        $this->fields[] = array('name' => 'transaction.transaction_amount', 'value'=>'Payment Transaction Amount',);
        $this->fields[] = array('name' => 'transaction.date_created', 'value'=>'Payment Date',);
        $this->fields[] = array('name' => 'info.shipping_class', 'value'=>'Shipping class',);
        $this->fields[] = array('name' => 'info.shipping_method', 'value'=>'Shipping Method',);
        $this->fields[] = array('name' => 'info.shipping_weight', 'value'=>'Order Shipping Weight',);
        $this->fields[] = array('name' => 'info.order_status', 'value' => 'Order Status');
        $this->fields[] = array('name' => 'info.currency', 'value' => 'Currency Code');
        $this->fields[] = array('name' => 'info.currency_value', 'value' => 'Currency Rate');
        $this->fields[] = array('name' => 'info.language', 'value' => 'Language');
        $this->fields[] = array('name' => 'info.platform_id', 'value' => 'Sale Channel');
        $this->fields[] = array('name' => 'info.date_purchased', 'value' => 'Date Purchased');
        $this->fields[] = array('name' => 'info.comments', 'value' => 'Order Comments');

    }

    protected function getOrderTotalModules()
    {
        if ( count($this->site_modules)==0 ) {
            $MODULE_ORDER_TOTAL_INSTALLED = [];
            foreach (\common\classes\platform::getList() as $platform) {
                $platform_config = \Yii::$app->get('platform')->getConfig($platform['id']);
                $MODULE_ORDER_TOTAL_INSTALLED = array_merge($MODULE_ORDER_TOTAL_INSTALLED, explode(';', $platform_config->const_value('MODULE_ORDER_TOTAL_INSTALLED')));
            }
            $MODULE_ORDER_TOTAL_INSTALLED = array_unique($MODULE_ORDER_TOTAL_INSTALLED);
            $modules = [];

            $manager = new \common\services\OrderManager(\Yii::$app->get('storage'));
            $builder = new \common\classes\modules\ModuleBuilder($manager);

            foreach ($MODULE_ORDER_TOTAL_INSTALLED as $MODULE_ORDER_TOTAL_) {
                $MODULE_ORDER_TOTAL_CODE = preg_replace('/\.php$/', '', $MODULE_ORDER_TOTAL_);
                $title_const = 'MODULE_ORDER_TOTAL_' . strtoupper(preg_replace('/^ot_/', '', $MODULE_ORDER_TOTAL_CODE)) . '_TITLE';
                $MODULE_ORDER_TOTAL_TITLE = \common\helpers\Translation::getTranslationValue($title_const, 'ordertotal');
                if (empty($MODULE_ORDER_TOTAL_TITLE)) {
                    \common\helpers\Translation::init('ordertotal');
                    if (\frontend\design\Info::isTotallyAdmin()) {
                        if (!is_file(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $MODULE_ORDER_TOTAL_))
                            continue;
                        include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $MODULE_ORDER_TOTAL_);
                    } else {
                        if (!is_file(DIR_WS_MODULES . 'order_total/' . $MODULE_ORDER_TOTAL_))
                            continue;
                        include_once(DIR_WS_MODULES . 'order_total/' . $MODULE_ORDER_TOTAL_);
                    }
                    $class = $MODULE_ORDER_TOTAL_CODE;

                    try {
                        $moduleInstance = $builder(['class' => $class]);
                        if ($moduleInstance && $moduleInstance->title) {
                            $MODULE_ORDER_TOTAL_TITLE = $moduleInstance->title;
                        }
                    } catch (\Exception $ex) {
                    }
                }
                $modules[$MODULE_ORDER_TOTAL_CODE] = $MODULE_ORDER_TOTAL_TITLE;
            }
            $this->site_modules = $modules;
        }
        return $this->site_modules;
    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);

        $filter_sql = '';
        if ( is_array($filter) ) {
            $order_filter = (isset($filter['order']) && is_array($filter['order']))?$filter['order']:[];

            if (isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='exact')
            {
                if (!empty($order_filter['date_from'])) {
                    $filter_sql .= " AND o.date_purchased >= '" . tep_db_input(substr($order_filter['date_from'], 0, 10)) . " 00:00:00' ";
                }
                if (!empty($order_filter['date_to'])) {
                    $filter_sql .= " AND o.date_purchased <= '" . tep_db_input(substr($order_filter['date_to'], 0, 10)) . " 23:59:59' ";
                }
            }
            elseif(isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='year/month')
            {
                $year = $order_filter['year'];
                $filter_sql .= " AND YEAR(o.date_purchased)='" . tep_db_input($year) . "' ";
                $month = $order_filter['month'];
                if ( !empty($month) ) {
                    $filter_sql .= " AND DATE_FORMAT(o.date_purchased,'%Y%m')='".tep_db_input($year.sprintf('%02s',(int)$month))."' ";
                }
            }
            elseif(isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='presel')
            {


                switch ($order_filter['interval']) {
                    case 'week':
                        $filter_sql .= " AND o.date_purchased >= '" . date('Y-m-d', strtotime('monday this week')) . "' ";
                        break;
                    case 'month':
                        $filter_sql .= " AND o.date_purchased >= '" . date('Y-m-d', strtotime('first day of this month')) . "' ";
                        break;
                    case 'year':
                        $filter_sql .= " AND o.date_purchased >= '" . date("Y") . "-01-01" . "' ";
                        break;
                    case '1':
                        $filter_sql .= " AND o.date_purchased >= '" . date('Y-m-d') . "' ";
                        break;
                    case '3':
                    case '7':
                    case '14':
                    case '30':
                        $filter_sql .= " AND o.date_purchased >= '".date('Y-m-d',strtotime('-'.$filters['interval'].' days'))."' ";
                        break;
                }
            }
        }

        $main_sql =
            "SELECT o.orders_id ".
            "FROM ".TABLE_ORDERS." o ".
            "WHERE 1 {$filter_sql} ";

        $this->export_query = tep_db_query( $main_sql );
        $this->export_rows = false;
        return $this->export_query;
    }

    public function importOptions()
    {

        $check_valid_product = isset($this->import_config['check_valid_product'])?$this->import_config['check_valid_product']:'check_valid';
        $import_mode = isset($this->import_config['import_mode'])?$this->import_config['import_mode']:'create_recalculate';
        $recalculate = isset($this->import_config['recalculate'])?$this->import_config['recalculate']:'no';
        return '
<div class="widget box box-no-shadow">
    <div class="widget-header"><h4>Import options</h4></div>
    <div class="widget-content">
        <div class="row">
            <div class="col-md-6"><label>Import mode</label></div>
            <div class="col-md-6">'.Html::dropDownList('import_config[import_mode]',$import_mode, [
                'update'=>'Update existing',
                'create'=>'Create not existing',
                'create_update'=>'Create not existing and update existing',
            ],['class'=>'form-control']).'</div>
        </div>
        <div class="row">
            <div class="col-md-6"><label>Recalculate</label></div>
            <div class="col-md-6">'.Html::dropDownList('import_config[recalculate]',$recalculate, [
                'no' => 'Import as is',
                'yes' => 'Recalculate',
            ],['class'=>'form-control','options'=>['yes'=>['disabled'=>'disabled']]]).'</div>
        </div>
        <div class="row form-group">
            <div class="col-md-6"><label>Check product exist?</label></div>
            <div class="col-md-6">'.Html::dropDownList('import_config[check_valid_product]',$check_valid_product, ['check_valid'=>'Yes, product need exist in catalog', 'add_dummy'=>'No, just insert'],['class'=>'form-control']).'</div>
        </div>
    </div>
</div>
        ';
    }

    public function exportRow()
    {
        if ( is_array($this->export_rows) && current($this->export_rows) ) {
            $this->data = current($this->export_rows);
            next($this->export_rows);
            return $this->data;
        }
        $order_id = tep_db_fetch_array($this->export_query);
        if ( !is_array($order_id) ) return false;

        $this->export_rows = false;
        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;

        try {
            $order = new \common\classes\Order((int)$order_id['orders_id']);
        }catch (\Exception $ex){
            return [':empty'=>'data'];
        }

        $order = [
            'customer' => $order->customer,
            'delivery' => $order->delivery,
            'billing' => $order->billing,
            'products' => $order->products,
            'info' => $order->info,
            'totals' => $order->totals,
            'transaction' => \common\models\OrdersTransactions::find()->where(['orders_id'=>$order->order_id])->orderBy(['orders_transactions_id'=>SORT_DESC])->asArray()->one(),
        ];

        if ($this->format=='XML') {
            return $order;
        }

        if ( isset($order['totals']) && is_array($order['totals']) ) {
            $order_totals = $order['totals'];
            $order['totals'] = [];
            foreach ($order_totals as $total) {
                $order['totals'][$total['code']] = $total;
            }
        }

        $order_wo_product = $order;
        unset($order_wo_product['products']);
        $order_wo_product = EP\ArrayTransform::convertMultiDimensionalToFlat($order_wo_product);
        $order_wo_product['info.language'] = \common\classes\language::get_code($order_wo_product['info.language']);
        $order_wo_product['info.platform_id'] = $this->EPtools->getPlatformName($order_wo_product['info.platform_id']);
        //$order_wo_product['info.order_status'] = \common\helpers\Order::get_status_name($order_wo_product['info.order_status']);
        $order_wo_product['info.order_status'] = $order_wo_product['info.orders_status_name'];

        $order_rows = [];
        if ( count($order['products'])>0 && preg_grep('/^products\.\*/',array_keys($export_columns)) ) {
            foreach ( $order['products'] as $product ) {
                if ($product['packs'] || $product['packagings'] || $product['units']) {

                }else{
                    $product['units_price'] = $product['final_price'];
                    $product['units'] = $product['qty'];
                }

                $append_row = $order_wo_product;
                $flat_product = EP\ArrayTransform::convertMultiDimensionalToFlat($product);
                foreach ($flat_product as $key=>$val) {
                    $append_row['products.*.'.$key] = $val;
                }
                $order_rows[] = $append_row;
            }
        }else{
            $order_rows[] = $order_wo_product;
        }

        $this->export_rows = $order_rows;
        reset($this->export_rows);

        if ( is_array($this->export_rows) && current($this->export_rows) ) {
            $this->data = current($this->export_rows);
            next($this->export_rows);
            return $this->data;
        }
        return false;

    }

    public function importRow($data, Messages $message)
    {
        $allowInsertNotExistingProducts = false;
        if (is_array($this->import_config) && isset($this->import_config['check_valid_product']) && $this->import_config['check_valid_product']=='add_dummy'){
            $allowInsertNotExistingProducts = true;
        }

        if ($this->format == 'CSV') {
            // remove empty
            $data = array_filter($data,function($v, $k){
                return (strlen($v)>0 || strpos($k,'iso_code_2')!==false);
            },ARRAY_FILTER_USE_BOTH);
            $data = EP\ArrayTransform::convertFlatToMultiDimensional($data);
        }else{
            $data['products'] = ArrayHelper::isIndexed($data['products']['product'])?$data['products']['product']:[$data['products']['product']];
        }

        //$data['info']['platform_id'] = \common\classes\platform::defaultId();
        if ( $data['info'] && $data['info']['language'] ) {
            $data['info']['language_id'] = \common\classes\language::get_id($data['info']['language']);
        }

        $extIdPresent = (isset($data['info']['external_orders_id']) && !empty($data['info']['external_orders_id']));
        $orderIdPresent = (isset($data['info']['orders_id']) && !empty($data['info']['orders_id']));
        if ( !$orderIdPresent && !$extIdPresent ) {
            if ($this->format == 'CSV' && count($this->collectOrder)>0) {
                $this->persistCollectedOrder($message);
            }
            $message->info("Missing or empty ".$this->fields['intId']['value'].', '.$this->fields['extId']['value']);
            $this->collectOrder = [];
            return false;
        }

        if ($orderIdPresent){
            $group_by = 'INT^' . $data['info']['orders_id'];
            $orderKey = 'orders_id';
            $keyName = $this->fields['intId']['value'];
        }else{
            $group_by = 'EXT^' . $data['info']['external_orders_id'];
            $orderKey = 'external_orders_id';
            $keyName = $this->fields['extId']['value'];
        }
        /*
        if ( $extIdPresent ) {
            $group_by = 'EXT^' . $data['info']['external_orders_id'];
            $orderKey = 'external_orders_id';
            $keyName = $this->fields['extId']['value'];
        }else{
            $group_by = 'INT^' . $data['info']['orders_id'];
            $orderKey = 'orders_id';
            $keyName = $this->fields['intId']['value'];
        }
        */
        $data['info']['.import_key'] = $orderKey;

        // {{ required check
        if ( isset($skip_external_on_import[$group_by]) ) {
            //$message->info("".$this->fields['extId']['value']." =".$data['info.external_orders_id']." skipped ");
            $this->collectOrder = [];
            return false;
        }
        /*
        if ( !isset($data['customer']['email_address']) || empty($data['customer']['email_address']) ) {
            $skip_external_on_import[$group_by] = $data['info'][$orderKey];

            $message->info("".$keyName." =".$data['info'][$orderKey]." skipped: Empty customer email");
            $this->collectOrder = [];
            return false;
        }
        */

        $skipByProductCheck = false;
        if (isset($data['products'])){
            foreach( $data['products'] as $_productIdx=>$productData ) {
                if ( empty($productData['model']) && !$allowInsertNotExistingProducts ) {
                    $skipByProductCheck = true;
                    break;
                }else{
                    $product = $this->processImportProductData($productData, $allowInsertNotExistingProducts);
                    if ( $product===false ) {
                        $skip_external_on_import[$data['info'][$orderKey]] = $data['info'][$orderKey];

                        $message->info("" . $keyName . " =" . $data['info'][$orderKey] . " skipped: ".$this->lastProcessProductError);
                        $this->collectOrder = [];
                        return false;
                    }else{
                        $data['products'][$_productIdx] = $product;
                    }
                }
            }
        }else{
            $skipByProductCheck = true;
        }

        if ( $skipByProductCheck ) {
            $message->info("".$keyName." =".$data['info'][$orderKey]." skipped: Empty product model");
            $this->collectOrder = [];
            return false;
        }

        // }} required check

        if ($this->format == 'XML') {
            $this->collectOrder = $data;
            $this->persistCollectedOrder($message);
            return true;
        }else{
            // CSV - per line
            if ( count($this->collectOrder)>0 && $this->collectOrder['info'][$orderKey]!=$data['info'][$orderKey] ) {
                $this->persistCollectedOrder($message);
            }

            $product = $data['products']['*'];

            if ( count($this->collectOrder)==0 ) {
                unset($data['products']['*']);
                $this->collectOrder = $data;
            }else{
                unset($data['products']['*']);
                // "merge" non empty - common data from any line
                // 2 level array
                foreach( $data as $L1Key=>$L1Val ) {
                    if ( is_array($L1Val) ) {
                        foreach ($L1Val as $L2Key=>$L2Val){
                            if ( is_array($L2Val) ) {
                                if ($L2Key=='*') {
                                    $this->collectOrder[$L1Key][] = $L2Val;
                                    continue;
                                }
                                foreach ($L2Val as $L3Key=>$L3Val){
                                    if ($L3Key=='*') {
                                        $this->collectOrder[$L1Key][$L2Key][] = $L3Val;
                                        continue;
                                    }
                                    $this->collectOrder[$L1Key][$L2Key][$L3Key] = $L3Val;
                                }
                            }else{
                                $this->collectOrder[$L1Key][$L2Key] = $L2Val;
                            }
                        }
                    }else{
                        $this->collectOrder[$L1Key] = $L1Val;
                    }
                }
            }

            $this->collectOrder['products'][] = $product;
            return true;
        }
    }

    protected function processImportProductData($product, $allowInsertNotExistingProducts=false)
    {
        $this->lastProcessProductError = '';

        if ( !isset($product['qty']) ) $product['qty'] = 1;
        $product['qty'] = (int)$product['qty'];

        if ( $allowInsertNotExistingProducts && empty($product['model']) ) {
            $get_product_r = tep_db_query("SELECT products_id FROM ".TABLE_PRODUCTS." WHERE 1=0");
        }else {
            $get_product_r = tep_db_query(
                "SELECT IFNULL(i.products_id, p.products_id) AS uprid, p.products_id, " .
                " IFNULL(i.products_id, p.products_id) AS id, " .
                " p.products_tax_class_id, " .
                " p.pack_unit, p.packaging " .
                "FROM " . TABLE_PRODUCTS . " p " .
                " LEFT JOIN " . TABLE_INVENTORY . " i ON i.prid=p.products_id " .
                "WHERE 1 " .
                "  AND (" .
                "    IF(LENGTH(i.products_model)>0,i.products_model,p.products_model)='" . tep_db_input($product['model']) . "' " .
                "    OR " .
                "    p.products_model='" . tep_db_input($product['model']) . "' " .
                "  ) " .
                "ORDER BY IF(p.products_model='" . tep_db_input($product['model']) . "',1,0) " .
                "LIMIT 1 "
            );
        }
        if ( $allowInsertNotExistingProducts==false && tep_db_num_rows($get_product_r)==0 ) {
            $this->lastProcessProductError = "can't find product model \"".$product['model']."\"";
            return false;
        }
        /*if ($allowInsertNotExistingProducts && empty($product['name'])) {
            $this->lastProcessProductError = "can't insert product without name";
            return false;
        }*/

        if ( tep_db_num_rows($get_product_r)>0 ) {
            $shop_product = tep_db_fetch_array($get_product_r);
        }else{
            $shop_product = [
                'uprid' => '',
                'products_id' => 0,
                'id' => 0,
            ];
        }
        $product['id'] = $shop_product['id'];
        $product['uprid'] = $shop_product['uprid'];
        $product['products_id'] = $shop_product['products_id'];
        if ( empty($product['name']) && $shop_product['id']>0 ){
            $product['name'] = \common\helpers\Product::get_products_name($shop_product['id']);
        }

        \common\helpers\Php8::nullProps($product, ['packs', 'packagings', 'units']);
        if ( $product['packs'] || $product['packagings'] || $product['units'] ) {
            if ( intval($shop_product['pack_unit'])==0 && (int)$product['packs']!=0 ) {
                $this->lastProcessProductError = "product model \"".$product['model']."\" Carton qty not enabled";
                return false;
            }
            if ( intval($shop_product['packaging'])==0 && (int)$product['packagings']!=0 ) {
                $this->lastProcessProductError = "product model \"".$product['model']."\" Pack qty not enabled";
                return false;
            }

            $product['qty'] = $product['units']+$product['packs']*$shop_product['pack_unit']+$product['packagings']*($shop_product['pack_unit']*$shop_product['packaging']);

            if ( $product['units_price'] && empty($product['packs']) && empty($product['packagings']) ) {
                $product['final_price'] = $product['units_price'];
            }else{
                $item_amount = $product['units_price']*$product['units']+($product['packs']*$product['packs_price'])+$product['packagings']*($product['packagings_price']);
                $product['final_price'] = $item_amount/$product['qty'];
            }

            $product['price'] = $product['final_price'];
        }else{
            $product['qty'] = max(1,isset($product['units'])?intval($product['units']):1);
            $product['final_price'] = $product['units_price'];
            $product['price'] = $product['units_price'];
        }
        $product['final_price'] = number_format(floatval($product['final_price']),5,'.','');
        $product['price'] = number_format(floatval($product['price']),5,'.','');

        if ( isset($product['name']) ) $product['products_name'] = $product['name'];
        if ( isset($product['model']) ) $product['products_model'] = $product['model'];
        if ( isset($product['price']) ) $product['products_price'] = $product['price'];
        if ( isset($product['qty']) ) $product['products_quantity'] = $product['qty'];
        if ( isset($product['tax']) ) $product['products_tax'] = $product['tax'];
        if ( isset($product['weight']) ) $product['products_weight'] = $product['weight'];

        return $product;
    }

    protected function assignCustomer(\common\models\Orders $order)
    {
        /**
         * @var $platform_config \common\classes\platform_config
         */
        $platform_config = \Yii::$app->get('platform')->getConfig($order->platform_id);
        $customer = null;
        if ( $order->customers_email_address ) {
            $customer = \common\models\Customers::findByEmail($order->customers_email_address);
            if ( !$customer ) {
                $customer = \common\models\Customers::findByMultiEmail($order->customers_email_address);
            }
        }elseif ( $order->customers_firstname && $order->customers_lastname ){
            $customer = \common\models\Customers::find()
                ->where([
                    'customers_firstname' => $order->customers_firstname,
                    'customers_lastname' => $order->customers_lastname,
                    ])
                ->one();
        }
        if ( !$customer ) {
            $customer = new \common\models\Customers();
            $customer->loadDefaultValues();
        }

        if ($customer->isNewRecord){
            $customerData = $order->getAttributes([
                'customers_firstname',
                'customers_lastname',
                'customers_telephone',
                'customers_company',
                'customers_company_vat',
            ]);
            $customerData = array_filter($customerData,'strlen');

            $customer->setAttributes($customerData,false);

            $customer->dob_flag = 1;
            $customer->customers_email_address = $order->customers_email_address;
            $customer->platform_id = $platform_config->getId();
            $customer->customers_password = \common\helpers\Password::encrypt_password(\common\helpers\Password::create_random_value(12), 'frontend');
            $customer->groups_id = $platform_config->const_value('DEFAULT_USER_LOGIN_GROUP',0);
            $customer->save(false);
            $customer->refresh();
        }

        $order->customers_id = $customer->customers_id;

        $customerInfo = \common\models\CustomersInfo::findOne($customer->customers_id);
        if ( !$customerInfo ) {
            $customerInfo = new \common\models\CustomersInfo([
                'customers_info_id' => $customer->customers_id,
                'customers_info_number_of_logons' => 0,
                'customers_info_date_account_created' => new Expression('NOW()'),
            ]);
            $customerInfo->loadDefaultValues();
            $customerInfo->save(false);
            $customerInfo->refresh();
        }

        if (!$customer->customers_default_address_id){
            $PlatformAddress = $platform_config->getPlatformAddress();
            $defaultAddressBook = new \common\models\AddressBook([
                'customers_id' => $customer->customers_id,
                'entry_country_id'=> $PlatformAddress['country_id'],
            ]);
            $defaultAddressBook->loadDefaultValues();
            $orderCustomer = $order->getAttributes([
                'customers_company',
                'customers_company_vat',
                'customers_firstname',
                'customers_lastname',
                'customers_street_address',
                'customers_suburb',
                'customers_postcode',
                'customers_city',
                'customers_state',
                'customers_country',
            ]);
            if ( $orderCustomer['customers_country'] ) {
                $orderCustomer['customers_country_id'] = EP\Tools::getInstance()->getCountryId($orderCustomer['customers_country']);
            }
            if (!$orderCustomer['customers_country_id']) $orderCustomer['customers_country_id'] = $PlatformAddress['country_id'];
            if ($orderCustomer['customers_country_id'] && $orderCustomer['customers_state']) {
                $orderCustomer['customers_zone_id'] = EP\Tools::getInstance()->getCountryZoneId($orderCustomer['customers_state'],$orderCustomer['customers_country_id']);
            }

            foreach ($orderCustomer as $_key=>$_val){
                $ab_PropertyName = 'entry_'.str_replace('customers_','',$_key);
                if ( $defaultAddressBook->canSetProperty($ab_PropertyName) )
                    $defaultAddressBook->setAttribute($ab_PropertyName,$_val);
            }
            foreach ($defaultAddressBook->getTableSchema()->columns as $column){
                /**
                 * @var $column ColumnSchema
                 */
                if ($defaultAddressBook->getAttribute($column->name)===null && !$column->allowNull){
                    $defaultAddressBook->setAttribute($column->name,'');
                }
            }

            $defaultAddressBook->save(false);
            $customer->customers_default_address_id = $defaultAddressBook->address_book_id;
            $customer->save(false);
        }

        return $order->customers_id;
    }

    protected function persistCollectedOrder(Messages $message)
    {
        /**
         'update'=>'Update existing',
         'create'=>'Create not existing',
         'create_update'=>'Create not existing and update existing',
         */
        $import_mode = $this->import_defaults['import_mode'];
        /**
        'no' => 'Import as is',
        'yes' => 'Recalculate',
         */
        $recalculate = $this->import_defaults['recalculate'];
        if (is_array($this->import_config)) {
            if ( !empty($this->import_config['import_mode']) ) {
                $import_mode = $this->import_config['import_mode'];
            }
            if ( !empty($this->import_config['recalculate']) ) {
                $recalculate = $this->import_config['recalculate'];
            }
        }


        $import_key = $this->collectOrder['info']['.import_key'];
        $import_key_value = $this->collectOrder['info'][$import_key];


        $orderModel = \common\models\Orders::find()
            ->where([$import_key=>$import_key_value])
            ->limit(1)
            ->one();

        if ( $orderModel ) {
            if ( strpos($import_mode,'update')===false ) {
                $message->info("".$import_key." =".$import_key_value." skipped: already exists");
                return false;
            }
        }else{
            if ( strpos($import_mode,'create')===false ) {
                $message->info("".$import_key." =".$import_key_value." skipped: not exists");
                return false;
            }
        }

        $this->collectOrder['info']['external_orders_id'] = $this->collectOrder['info']['external_orders_id'] ?? null;
        $newOrderAdded = false;
        if ( !$orderModel ) {
            $newOrderAdded = true;
            $orderModel = new \common\models\Orders();
            $orderModel->loadDefaultValues();
            if ( $this->collectOrder['info']['orders_id'] ) {
                $orderModel->orders_id = (int)$this->collectOrder['info']['orders_id'];
            }
            if ( $this->collectOrder['info']['external_orders_id'] ) {
                $orderModel->external_orders_id = strval($this->collectOrder['info']['external_orders_id']);
            }
        }else{
            if ( $import_key=='orders_id' ){
                if ( $this->collectOrder['info']['external_orders_id'] ) {
                    $orderModel->external_orders_id = strval($this->collectOrder['info']['external_orders_id']);
                }
            }
        }
        if ( !empty($this->collectOrder['info']['platform_id']) ) {
            $this->collectOrder['info']['platform_id'] = $this->EPtools->getPlatformId($this->collectOrder['info']['platform_id']);
            if ( empty($this->collectOrder['info']['platform_id']) ) {
                $this->collectOrder['info']['platform_id'] = \common\classes\platform::defaultId();
            }
            $orderModel->platform_id = $this->collectOrder['info']['platform_id'];
        }
        if ( empty($orderModel->platform_id) ) {
            $orderModel->platform_id = \common\classes\platform::defaultId();
        }
        /**
         * @var $platform_config \common\classes\platform_config
         */
        $platform_config = \Yii::$app->get('platform')->getConfig($orderModel->platform_id);

        if ( !empty($this->collectOrder['info']['language']) ) {
            if ($languageIdArray = \common\helpers\Language::get_language_id($this->collectOrder['info']['language'])){
                $this->collectOrder['info']['language_id'] = (int)$languageIdArray['languages_id'];
            }
        }
        if ( !empty($this->collectOrder['info']['language_id']) ) {
            $orderModel->language_id = $this->collectOrder['info']['language_id'];
        }
        if ( empty($orderModel->language_id) && $orderModel->isNewRecord ) {
            if ( $languageIdArray = \common\helpers\Language::get_language_id($platform_config->getDefaultLanguage()) ) {
                $orderModel->language_id = (int)$languageIdArray['languages_id'];
            }elseif ( $languageIdArray = \common\helpers\Language::get_language_id(\Yii::$app->get('platform')->getConfig(\common\classes\platform::defaultId())->getDefaultLanguage()) ) {
                $orderModel->language_id = (int)$languageIdArray['languages_id'];
            }
        }

        // {{ get missing customer address from billing address
        if ($orderModel->isNewRecord) {
            $address_columns = ['name', 'firstname', 'lastname', 'company', 'street_address', 'suburb', 'city', 'postcode', 'state', 'country',];
            if (!isset($this->collectOrder['customer']) || count(array_intersect_key($this->collectOrder['customer'], array_flip($address_columns))) == 0) {
                if (!isset($this->collectOrder['customer'])) $this->collectOrder['customer'] = [];
                if (isset($this->collectOrder['billing']) && is_array($this->collectOrder['billing'])) {
                    $this->collectOrder['customer'] = array_merge($this->collectOrder['billing'], $this->collectOrder['customer']);
                }
            }
        }
        // }} get missing customer address from billing address

        foreach (['customer','billing','delivery'] as $copyKey) {
            if ( !isset($this->collectOrder[$copyKey]) ) continue;
            $targetPrefixKey = $copyKey=='customer'?'customers':$copyKey;

            if ( !isset($this->collectOrder[$copyKey]['firstname']) && !isset($this->collectOrder[$copyKey]['lastname']) ){
                if ( isset($this->collectOrder[$copyKey]['name']) ) {
                    list( $f_n, $l_n ) = explode(' ',trim($this->collectOrder[$copyKey]['name']),2);
                    $this->collectOrder[$copyKey]['firstname'] = $f_n;
                    $this->collectOrder[$copyKey]['lastname'] = $l_n;
                }
            }

            $PlatformAddress = \Yii::$app->get('platform')->getConfig(\common\classes\platform::defaultId())->getPlatformAddress();
            $importCountryId = null;
            if ( isset($this->collectOrder[$copyKey]['country']['iso_code_2']) ) {
                $importCountryId = $this->EPtools->getCountryId($this->collectOrder[$copyKey]['country']['iso_code_2']);
                if ( empty($importCountryId) ){
                    $message->info("".$import_key." =".$import_key_value." Country '".$this->collectOrder[$copyKey]['country']['iso_code_2']."' not found set to default");
                    $importCountryId = $PlatformAddress['country_id'];
                }
                $importCountryInfo = $this->EPtools->getCountryInfo($importCountryId);
                if ( strlen($orderModel->getAttribute($targetPrefixKey.'_country'))==0 || $this->EPtools->getCountryId($orderModel->getAttribute($targetPrefixKey.'_country'))!=$importCountryId ) {
                    $orderModel->setAttribute($targetPrefixKey.'_country', $importCountryInfo['countries_name']);
                    $orderModel->setAttribute($targetPrefixKey.'_address_format_id', $importCountryInfo['address_format_id']);
                }
            }

            foreach ($this->collectOrder[$copyKey] as $address_key => $address_value) {
                if ( is_array($address_value) ) continue;
                $target_key = $targetPrefixKey.'_'.$address_key;
                if ($orderModel->canSetProperty($target_key)){
                    $orderModel->{$target_key} = $address_value;
                }
            }
            if ( $orderModel->isAttributeChanged($targetPrefixKey.'_firstname') || $orderModel->isAttributeChanged($targetPrefixKey.'_lastname') ){
                $orderModel->{$targetPrefixKey.'_name'} = $orderModel->{$targetPrefixKey.'_firstname'}.' '.$orderModel->{$targetPrefixKey.'_lastname'};
            }
        }


        $this->assignCustomer($orderModel);


        $currencies = \Yii::$container->get('currencies');
        /**
         * @var $currencies \common\classes\Currencies
         */
        if (!empty($this->collectOrder['info']['currency']) && $currencies->is_set(strtoupper($this->collectOrder['info']['currency']))){
            $currency = strtoupper($this->collectOrder['info']['currency']);
            $orderModel->currency = $currency;

        }
        if ( empty($orderModel->currency) ) {
            $orderModel->currency = $platform_config->getDefaultCurrency();
            if ( empty($orderModel->currency) ) $orderModel->currency = \Yii::$app->get('platform')->getConfig(\common\classes\platform::defaultId())->getDefaultCurrency();
        }
        $currency = $orderModel->currency;
        \Yii::$app->settings->set('currency',$currency);
        \Yii::$app->settings->set('currency_id', $currencies->currencies[$currency]['id']);

        if ( !empty($this->collectOrder['info']['currency_value']) ) $orderModel->currency_value = $this->collectOrder['info']['currency_value'];
        if ( empty($orderModel->currency_value) ) {
            $orderModel->currency_value = $currencies->get_value($orderModel->currency);
        }

        if ( isset($this->collectOrder['info']) && is_array($this->collectOrder['info']) ) {
            foreach ($this->collectOrder['info'] as $_key=>$_val) {
                if ( in_array($_key,['date_purchased', 'comments', 'order_status', 'language', 'platform_id', 'currency', 'currency_value']) ) continue;
                if ( $orderModel->canSetProperty($_key) ) $orderModel->setAttribute($_key,$_val);
            }
        }

        if ( isset($this->collectOrder['info']['date_purchased']) && strlen($this->collectOrder['info']['date_purchased'])>10 ) {
            $orderModel->date_purchased = $this->EPtools->parseDate($this->collectOrder['info']['date_purchased']);
        }
        if ( !empty($this->collectOrder['info']['order_status']) ) {
            static $cache_status = [];
            if (!isset($cache_status[$this->collectOrder['info']['order_status']])) {
                $cache_status[$this->collectOrder['info']['order_status']] = $this->EPtools->lookupOrderStatusId($this->collectOrder['info']['order_status'], \common\helpers\Order::getStatusTypeId());
            }
            if ( empty($cache_status[$this->collectOrder['info']['order_status']]) ) {
                $message->info('Order status "'.$this->collectOrder['info']['order_status'].'" not found');
            }
            if ( empty($orderModel->orders_status) ) {
                $orderModel->orders_status = intval($cache_status[$this->collectOrder['info']['order_status']]);
            }
        }
        if ( empty($orderModel->orders_status) ) {
            $orderModel->orders_status = intval($platform_config->const_value('DEFAULT_ORDERS_STATUS_ID'));
        }
        $isOrdersStatusChanged = $orderModel->isAttributeChanged('orders_status');

        foreach ($orderModel->getTableSchema()->columns as $column){
            /**
             * @var $column ColumnSchema
             */
            if (!$column->allowNull && $orderModel->getAttribute($column->name)===null){
                $orderModel->setAttribute($column->name,'');
            }
        }
        if (!$orderModel->save(false)){
            foreach ($orderModel->getErrors() as $attrib=>$attrErrors) {
                $message->info("[!] " . $import_key . " =" . $import_key_value . " skipped: Save Error {$attrib} ".implode(', ',$attrErrors));
            }
            return false;
        }
        $orderModel->refresh();

        // {{ products
        $dbProductCollection = $orderModel->getOrdersProducts()->all();
        foreach( $dbProductCollection as $orderedProduct ) {
            $matchedProduct = false;
            foreach ($this->collectOrder['products'] as $_idx=>$importedProduct){
                if ( $importedProduct['matched'] ) continue;
                if ( $importedProduct['products_id'] && strval($importedProduct['products_id'])==strval($orderedProduct->products_id) && strval($importedProduct['uprid'])==strval($orderedProduct->products_id) ){
                    $this->collectOrder['products'][$_idx]['matched'] = $orderedProduct->orders_products_id;
                    $importedProduct['matched'] = $orderedProduct->orders_products_id;
                    $matchedProduct = $importedProduct;
                    break;
                }
            }
            if ( $matchedProduct===false ) {
                foreach ($this->collectOrder['products'] as $_idx => $importedProduct) {
                    if ($importedProduct['matched']) continue;
                    if (strval($importedProduct['model']) == strval($orderedProduct->products_model)) {
                        $this->collectOrder['products'][$_idx]['matched'] = $orderedProduct->orders_products_id;
                        $importedProduct['matched'] = $orderedProduct->orders_products_id;
                        $matchedProduct = $importedProduct;
                        break;
                    }
                }
                if ( $matchedProduct===false ) {
                    foreach ($this->collectOrder['products'] as $_idx => $importedProduct) {
                        if ($importedProduct['matched']) continue;
                        if (strval($importedProduct['name']) == strval($orderedProduct->products_name)) {
                            $this->collectOrder['products'][$_idx]['matched'] = $orderedProduct->orders_products_id;
                            $importedProduct['matched'] = $orderedProduct->orders_products_id;
                            $matchedProduct = $importedProduct;
                            break;
                        }
                    }
                }
            }
            if ( is_array($matchedProduct) ) {
                $orderedProduct->setAttributes($matchedProduct, false);
                $orderedProduct->save(false);
            }else{
                $orderedProduct->delete();
            }
        }
        foreach ($this->collectOrder['products'] as $_iidx => $importedProduct){

            if ( $importedProduct['matched'] ?? null ) continue;
            $orderedProduct = new \common\models\OrdersProducts([
                'orders_id' => $orderModel->orders_id,
                'products_quantity' => 1,
                'products_id' => $importedProduct['products_id'],
                'uprid' => $importedProduct['uprid'],
                'template_uprid' => $importedProduct['uprid'],
            ]);
            $orderedProduct->loadDefaultValues();
            $orderedProduct->setAttributes($importedProduct,false);
            $orderedProduct->save(false);
        }
        //$this->collectOrder['products'];
        // }} products

        if ( !is_array($this->collectOrder['totals']) ) $this->collectOrder['totals'] = [];
        $importTotals = $this->collectOrder['totals'];
        $totalPaidAmount = 0;
        foreach ($importTotals as $__idx=>$_totalInfo){
            if ( strlen($_totalInfo['value_exc_vat'] ?? null)>0 ) $importTotals[$__idx]['value_exc_vat'] = number_format($_totalInfo['value_exc_vat'],6,'.','');
            if ( strlen($_totalInfo['value_inc_tax'] ?? null)>0 ) $importTotals[$__idx]['value_inc_tax'] = number_format($_totalInfo['value_inc_tax'],6,'.','');
            if ( strlen($_totalInfo['value'] ?? null)>0 ) {
                $importTotals[$__idx]['value'] = number_format($_totalInfo['value'],6,'.','');
            }else{
                if ($platform_config->const_value('DISPLAY_PRICE_WITH_TAX','true')=='true'){
                    $importTotals[$__idx]['value'] = $importTotals[$__idx]['value_inc_tax'];
                }else{
                    $importTotals[$__idx]['value'] = $importTotals[$__idx]['value_exc_vat'];
                }
            }
            if ( $__idx=='ot_paid' ) {
                $totalPaidAmount = $_totalInfo['value_inc_tax'];
            }
        }

        $total_modules = $this->getOrderTotalModules();
        $order_keys = array_flip(array_keys($total_modules));

        $dbTotalsCollection = $orderModel->getOrdersTotals()->all();
        foreach ($dbTotalsCollection as $dbTotal){
            /**
             * @var $dbTotal \common\models\OrdersTotal
             */
            if ( empty($dbTotal->sort_order)) {
                $dbTotal->sort_order = $order_keys[$dbTotal->class];
            }
            if ( isset($importTotals[$dbTotal->class]) ) {
                $dbTotal->setAttributes($importTotals[$dbTotal->class],false);
                foreach ($dbTotal->getDirtyAttributes(['value_exc_vat','value_inc_tax','value']) as $changedKey=>$changedValue){
                    if ($changedKey=='value_exc_vat'){
                        $dbTotal->text_exc_tax = $currencies->format($changedValue);
                    }elseif ($changedKey=='value_inc_tax'){
                        $dbTotal->text_inc_tax = $currencies->format($changedValue);
                    }elseif ($changedKey=='value'){
                        $dbTotal->text = $currencies->format($changedValue);
                    }
                }
                if ( empty($dbTotal->title) && isset($total_modules[$dbTotal->class]) ) {
                    $dbTotal->title = $total_modules[$dbTotal->class].':';
                }
                $dbTotal->save(false);
                unset($importTotals[$dbTotal->class]);
            }else{
                $dbTotal->delete();
            }
        }
        // add missing
        foreach ($importTotals as $__idx=>$_totalInfo){
            $missingTotal  = new \common\models\OrdersTotal([
                'orders_id' => $orderModel->orders_id,
                'class' => $__idx,
                'currency' => $orderModel->currency,
                'currency_value' => $orderModel->currency_value,
                'sort_order' => (isset($order_keys[$__idx])?$order_keys[$__idx]:0) + 300,
            ]);
            if (!isset($_totalInfo['value_exc_vat']) && isset($_totalInfo['value_inc_tax'])){
                $_totalInfo['value_exc_vat'] = $_totalInfo['value_inc_tax'];
            }elseif (isset($_totalInfo['value_exc_vat']) && !isset($_totalInfo['value_inc_tax'])){
                $_totalInfo['value_inc_tax'] = $_totalInfo['value_exc_vat'];
            }
            if ( !isset($_totalInfo['value']) ) {
                if ($platform_config->const_value('DISPLAY_PRICE_WITH_TAX', 'true') == 'true') {
                    $_totalInfo['value'] = $_totalInfo['value_inc_tax'];
                } else {
                    $_totalInfo['value'] = $_totalInfo['value_exc_vat'];
                }
            }

            $missingTotal->setAttributes($_totalInfo,false);
            $missingTotal->text_exc_tax = $currencies->format($missingTotal->value_exc_vat);
            $missingTotal->text_inc_tax = $currencies->format($missingTotal->value_inc_tax);
            $missingTotal->text = $currencies->format($missingTotal->value);
            if ( empty($missingTotal->title) && isset($total_modules[$missingTotal->class]) ) {
                $missingTotal->title = $total_modules[$missingTotal->class].':';
            }

            $missingTotal->save(false);
        }

        if ( $isOrdersStatusChanged ){
            $statusHistory = new OrdersStatusHistory();
            $statusHistory->detachBehavior('date_added');
            $statusHistory->loadDefaultValues();
            $statusHistory->orders_id = $orderModel->orders_id;
            $statusHistory->orders_status_id = $orderModel->orders_status;
            $statusHistory->customer_notified = 0;
            $statusHistory->comments = empty($this->collectOrder['info']['comments'])?'':$this->collectOrder['info']['comments'];
            $statusHistory->admin_id = (int)($_SESSION['login_id'] ?? null);
            $statusHistory->save(false);
        }

        if ( $newOrderAdded ){
            $OrdersHistory = new OrdersHistory([
                'orders_id' => $orderModel->orders_id,
                'comments' => 'Order imported',
                'admin_id'=>(int)($_SESSION['login_id'] ?? null),
                'date_added'=> new Expression('NOW()'),
            ]);
            $OrdersHistory->loadDefaultValues();
            $OrdersHistory->save(false);
        }

        if ( isset($this->collectOrder['transaction']) && is_array($this->collectOrder['transaction']) ) {
            if ( !empty($this->collectOrder['transaction']['transaction_id']) ) {
                if (OrdersTransactions::find()
                    ->where(['orders_id'=>$orderModel->orders_id])
                    ->andWhere(['transaction_id'=>$this->collectOrder['transaction']['transaction_id']])
                    ->count() == 0){

                    $paidAmount = 0;
                    if ( !isset($this->collectOrder['transaction']['transaction_amount']) || strlen($this->collectOrder['transaction']['transaction_amount'])==0 ) {
                        $paidAmount = $totalPaidAmount;
                    }
                    $transactionDate = 0;
                    if (!empty($this->collectOrder['transaction']['date_created'])) {
                        $transactionDate = EP\Tools::getInstance()->parseDate($this->collectOrder['transaction']['date_created']);
                    }
                    if ( $transactionDate<1971 ) $transactionDate = $orderModel->date_purchased;

                    $transaction = new OrdersTransactions([
                        'orders_id' => $orderModel->orders_id,
                        'payment_class' => $orderModel->payment_class,
                        'transaction_id' => $this->collectOrder['transaction']['transaction_id'],
                        'transaction_amount' => floatval($paidAmount),
                        'transaction_status' => '',
                        'transaction_currency' => $orderModel->currency,
                        'splinters_suborder_id' => null,
                        'comments' => 'Imported payment transaction',
                        'admin_id' => (int)$_SESSION['login_id'],
                        'date_created' => $transactionDate,
                    ]);
                    $transaction->loadDefaultValues();
                    $transaction->save(false);
                }
            }
        }
        if ( !isset($this->collectOrder['info']['shipping_weight']) || strlen($this->collectOrder['info']['shipping_weight'])==0 ){
            \Yii::$app->getDb()->createCommand(
                "UPDATE orders ".
                "SET shipping_weight = (".
                " SELECT SUM(products_quantity*products_weight) FROM orders_products WHERE orders_id='".intval($orderModel->orders_id)."'".
                ") ".
                "WHERE orders_id='".intval($orderModel->orders_id)."'"
            )->execute();

        }

        if ( $recalculate!='yes' ) {
            $this->collectOrder = [];
            $this->processed_count++;
            return true;
        }

        $manager = \common\services\OrderManager::loadManager();
        if ( $orderModel ) {
            $order = $manager->getOrderInstanceWithId('\common\classes\Order',$orderModel->orders_id);
        }else{
            $order = $manager->createOrderInstance('\common\classes\Order');
        }

        echo '<pre>'; var_dump($order); echo '</pre>';

        return true;
        die;





        echo '<pre>'; var_dump($this->collectOrder); echo '</pre>'; die;

        $this->collectOrder['info']['platform_id'] = $this->EPtools->getPlatformName($this->collectOrder['info']['platform_id']);

        $orderModel = false;
        if ( count($orders)==1 ) {
            $orderModel = $orders[0];
        }


        global $cart, $order_total_modules, $order;
        global $payment;
        $payment = '';
        if ( !is_object($cart) ) {
            $cart = new \common\classes\shopping_cart();
        }
        $cart->reset(true);

        $orderData = $this->collectOrder;

        if ( isset($orderData['products']) ) {
            foreach ($orderData['products'] as $product) {
                $cart->contents[$product['uprid']] = [
                    'qty' => $product['qty'],
                ];
            }
        }

        global $languages_id;
        $keep_language = $languages_id;
        $languages_id = \common\classes\language::defaultId();
        if ( $orderData['info']['language_id'] ) {
            $languages_id = (int)$orderData['info']['language_id'];
        }
        if ( $orderData['info']['platform_id'] )
        \Yii::$app->get('platform')->config((int)$orderData['info']['platform_id']);

        $tools = new EP\Tools();

        // arrange countries
        foreach (['customer','billing','delivery'] as $abKey) {
            if (isset($orderData[$abKey]) && isset($orderData[$abKey]['country']['iso_code_2'])) {
                $countryId = $tools->getCountryId($orderData[$abKey]['country']['iso_code_2']);
                $orderData[$abKey]['country_id'] = $countryId;
                $country_info = \common\helpers\Country::get_country_info_by_id($countryId);
                $orderData[$abKey]['country'] = [
                    'id' => $countryId,
                    'title' => $country_info['countries_name'],
                    'iso_code_2' => $country_info['countries_iso_code_2'],
                    'iso_code_3' => $country_info['countries_iso_code_3'],
                ];
                $orderData[$abKey]['format_id'] = \common\helpers\Address::get_address_format_id($countryId);
            }
            if (isset($orderData[$abKey]) && !empty($orderData[$abKey]['country_id']) && !empty($orderData[$abKey]['state'])) {
                $orderData[$abKey]['zone_id'] = \common\helpers\Zones::get_zone_id($orderData[$abKey]['country_id'],$orderData[$abKey]['state']);
            }
        }

        $check_customer_exists_r = tep_db_query(
            "SELECT customers_id ".
            "FROM ".TABLE_CUSTOMERS." ".
            "WHERE customers_email_address='".tep_db_query($orderData['customer']['email_address'])."' ".
            "LIMIT 1"
        );
        if ( tep_db_num_rows($check_customer_exists_r)>0 )
        {
            $check_customer_exists = tep_db_fetch_array($check_customer_exists_r);
            $customer_id = $check_customer_exists['customers_id'];
        }
        else
        {
            $customer_data = [
                'customers_firstname' => strval($orderData['customer']['firstname']),
                'customers_lastname' => strval($orderData['customer']['lastname']),
                'customers_email_address' => strval($orderData['customer']['email_address']),
                'customers_password' => \common\helpers\Password::encrypt_password(\common\helpers\Password::create_random_value(12), 'frontend'),
                'platform_id' => \common\classes\platform::defaultId(),
                'customers_telephone' => strval($orderData['customer']['telephone']),
                //'groups_id' => '',
                'customers_company' => strval($orderData['customer']['company']),
            ];
            tep_db_perform(TABLE_CUSTOMERS, $customer_data);
            $customer_id = tep_db_insert_id();

            tep_db_perform(TABLE_CUSTOMERS_INFO, [
                'customers_info_id' => $customer_id,
                'customers_info_date_account_created' => 'now()',
            ]);

            $customer_address_book = [
                'customers_id' => $customer_id,
                'entry_company' => strval($orderData['customer']['company']),
                'entry_firstname' => strval($orderData['customer']['firstname']),
                'entry_lastname' => strval($orderData['customer']['lastname']),
                'entry_street_address' => strval($orderData['customer']['street_address']),
                'entry_suburb' => strval($orderData['customer']['suburb']),
                'entry_postcode' => strval($orderData['customer']['postcode']),
                'entry_city' => strval($orderData['customer']['city']),
                'entry_state' => strval($orderData['customer']['state']),
                'entry_country_id' => strval($orderData['customer']['country']['id']),
                'entry_zone_id' => strval($orderData['customer']['zone_id']),
            ];
            tep_db_perform(TABLE_ADDRESS_BOOK, $customer_address_book);
            $customers_default_address_id = tep_db_insert_id();

            tep_db_perform(TABLE_CUSTOMERS,[
                'customers_default_address_id' => $customers_default_address_id,
            ],'update',"customers_id='".(int)$customer_id."'");
        }
        $orderData['customer']['customer_id'] = $customer_id;

        $storeOrder = new \common\classes\Order();
        foreach (['customer','billing','delivery'] as $abKey) {
            if ( !isset($orderData[$abKey]) ) continue;
            $orderData[$abKey]['address_book_id'] = $tools->addressBookFind($customer_id,$orderData[$abKey]);
            foreach (array_keys($storeOrder->{$abKey}) as $key) {
                $storeOrder->{$abKey}[$key] = isset($orderData[$abKey][$key]) ? $orderData[$abKey][$key] : null;
            }
        }
        if ( isset($orderData['products']) ) {
            foreach ($orderData['products'] as $imported_product) {
                foreach ($storeOrder->products as $_idx=>$order_product) {
                    if ( $imported_product['id']!=$order_product['id'] ) continue;
                    foreach (array_keys($order_product) as $copy_key) {
                        if ( array_key_exists($copy_key,$imported_product)) {
                            $storeOrder->products[$_idx][$copy_key] = $imported_product[$copy_key];
                        }
                    }
                }
            }
        }
        if ( isset($orderData['info']) && is_array($orderData['info']) ) {
            foreach ($orderData['info'] as $__key=>$__val) {
                $storeOrder->info[$__key] = $__val;
            }
        }

        $storeOrderId = $storeOrder->save_order();
        $storeOrder->save_products(false);
        $message->info("".$this->fields['extId']['value']." =".$orderData['info']['external_orders_id']." imported. Shop order #{$storeOrderId}");

        if ( false && isset($orderData['products']) ) {
            foreach ($orderData['products'] as $imported_product) {
                $update_set = '';
                $update_set .="units = '".(int)$imported_product['units']."', ";
                $update_set .="units_price = '".tep_db_input($imported_product['units_price'])."', ";
                $update_set .="packs = '".(int)$imported_product['packs']."', ";
                $update_set .="packs_price = '".tep_db_input($imported_product['packs_price'])."', ";
                $update_set .="packagings = '".(int)$imported_product['packagings']."', ";
                $update_set .="packagings_price = '".tep_db_input($imported_product['packagings_price'])."', ";
                $update_set = substr($update_set,0,-2);

                tep_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS." SET {$update_set} WHERE orders_id='".(int)$storeOrderId."' AND uprid='".tep_db_input($imported_product['id'])."'");
            }
        }

// calculate totals
        //$load_language_id = $storeOrder->info['language_id'];
        $load_language_id = $languages_id;
        \common\helpers\Translation::init('admin/main', $load_language_id);
        \common\helpers\Translation::init('admin/orders', $load_language_id);
        \common\helpers\Translation::init('admin/orders/create', $load_language_id);


        \Yii::$app->get('platform')->config()->constant_up();

        global $cart, $order, $languages_id, $payment, $shipping;
        global $total_weight, $total_count, $order_totals;
        $currencies = \Yii::$container->get('currencies');
        if ($currencies->is_set($storeOrder->info['currency'])) {
            $currency = $storeOrder->info['currency'];
            \Yii::$app->settings->set('currency', $currency);
            \Yii::$app->settings->set('currency_id', $currencies->currencies[$currency]['id']);
        }

        $cart = new shopping_cart($storeOrderId);

        $order = new \common\classes\Order($storeOrderId);
        foreach (array_keys($order->info) as $key) {
            if (isset($orderData['info'][$key])) {
                $order->info[$key] = $orderData['info'][$key];
            }
        }

        $languages_id = $order->info['language_id'];
        $payment = $order->info['payment_class'];

        $payment_modules = new payment(); // $payment_modules - for selected country (was update_status)
        $payment_selection = $payment_modules->selection();

        if ( empty($order->info['payment_class']) && count($payment_selection)>0 ) {
            reset($payment_selection);
            $payment_select = current($payment_selection);
            if ( isset($payment_select['methods']) && is_array($payment_select['methods']) && count($payment_select['methods'])>0 ) {
                $payment_select = array_shift($payment_select['methods']);
            }
            $payment = $payment_select['id'];
            $order->info['payment_class'] = $payment_select['id'];
            $order->info['payment_method'] = $payment_select['module'];
            tep_db_query(
                "UPDATE ".TABLE_ORDERS." ".
                "SET payment_class='".tep_db_input($order->info['payment_class'])."', payment_method='".tep_db_input($order->info['payment_method'])."' ".
                "WHERE orders_id='".(int)$storeOrderId."'"
            );
        }

        // weight and count needed for shipping !
        $total_weight = $order->info['shipping_weight'];
        $total_count = array_reduce($order->products,function($total, $order_product){
            return $total+$order_product['qty'];
        },0);

        $free_shipping = false;
        $quotes = array();
        if (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight')) {

            $shipping_modules = new shipping();

            if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
                $pass = false;

                switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                    case 'national':
                        if ($order->delivery['country_id'] == STORE_COUNTRY) {
                            $pass = true;
                        }
                        break;
                    case 'international':
                        if ($order->delivery['country_id'] != STORE_COUNTRY) {
                            $pass = true;
                        }
                        break;
                    case 'both':
                        $pass = true;
                        break;
                }

                $free_shipping = false;
                if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                    $free_shipping = true;
                }
            } else {
                $free_shipping = false;
            }
            // get all available shipping quotes
            list($shipping_module, $shipping_method) = explode('_',$order->info['shipping_class']);
            $quotes = $shipping_modules->quote($shipping_method, $shipping_module);

            foreach ($quotes as $quote_info) {
                if (!is_array($quote_info['methods']))
                    continue;
                foreach ($quote_info['methods'] as $quote_method) {
                    $shipping = array(
                        'id' => $quote_method['code'],
                        'title' => $quote_info['module'] . (empty($quote_method['title']) ? '' : ' (' . $quote_method['title'] . ')'),
                        'cost' => $quote_method['cost'],
                        'cost_inc_tax' => \common\helpers\Tax::add_tax_always($quote_method['cost'], (isset($quote_info['tax']) ? $quote_info['tax'] : 0))
                    );
                    break;
                }
            }
        }
        if (is_array($shipping)) {
            $order->change_shipping($shipping);
        }

        $order_total_modules = new \common\classes\order_total();
        $order_totals = $order_total_modules->process();

        unset($order->info['date_purchased']);
        $order->save_details();

        $languages_id = $keep_language;
        $this->collectOrder = [];
        $this->processed_count++;
    }

    public function postProcess(Messages $message)
    {

        if ( count($this->collectOrder)>0 ) {
            $this->persistCollectedOrder($message);
        }
        $message->info("Processed ".$this->processed_count." orders");
        $tools = new EP\Tools();
        $tools->done('orders_import');
    }


}