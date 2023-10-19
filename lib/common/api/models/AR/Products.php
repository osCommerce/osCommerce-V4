<?php

namespace common\api\models\AR;

use backend\models\EP\Tools;
use common\api\models\AR\Products\AssignedCategories;
use common\api\models\AR\Products\AssignedCustomerGroups as ProductAssignedCustomerGroups;
use common\api\models\AR\Products\AssignedDepartments;
use common\api\models\AR\Products\AssignedPlatforms as ProductAssignedPlatforms;
use common\api\models\AR\Products\Attributes;
use common\api\models\AR\Products\Description;
use common\api\models\AR\Products\Documents;
use common\api\models\AR\Products\Featured;
use common\api\models\AR\Products\GiftWrap;
use common\api\models\AR\Products\Images;
use common\api\models\AR\Products\Inventory;
use common\api\models\AR\Products\Prices;
use common\api\models\AR\Products\Properties;
use common\api\models\AR\Products\Special;
use common\api\models\AR\Products\SuppliersData;
use common\api\models\AR\Products\Xsell;
use common\api\models\AR\Products\SupplierProduct;
use common\api\models\AR\Products\SetProducts;
use common\api\models\AR\Products\WarehousesProducts;
use yii\db\Expression;

class Products extends EPMap
{

    protected $hideFields = [
        'products_image',
        'products_image_med',
        'products_image_lrg',
        'products_image_sm_1',
        'products_image_xl_1',
        'products_image_sm_2',
        'products_image_xl_2',
        'products_image_sm_3',
        'products_image_xl_3',
        'products_image_sm_4',
        'products_image_xl_4',
        'products_image_sm_5',
        'products_image_xl_5',
        'products_image_sm_6',
        'products_image_xl_6',
        'products_image_sm_7',
        'products_image_xl_7',
        'products_image_alt_1',
        'products_image_alt_2',
        'products_image_alt_3',
        'products_image_alt_4',
        'products_image_alt_5',
        'products_image_alt_6',
        //'products_date_added',
        //'products_last_modified',
        'products_seo_page_name',
        'last_xml_import',
        'last_xml_export',
        'previous_status',
        'vendor_id',
    ];

    protected $childCollections = [
        'descriptions' => [],
        'prices' => [],
        'gift_wrap' => false,
        'featured' => false,
        'special' => false,
        'assigned_categories' => false,
        'assigned_platforms' => false,
        'assigned_customer_groups' => false,
        'attributes' => false,
        'inventory' => false,
        'suppliers_data' => false,
        'images' => false,
        'properties' => false,
        'xsell' => false,
        'documents' => false,
        'suppliers_product' => false,
        'set_products' => false,
        'warehouses_products' => [],
    ];

    protected $indexedCollections = [
        'assigned_categories' => 'common\api\models\AR\Products\AssignedCategories',
        'assigned_platforms' => 'common\api\models\AR\Products\AssignedPlatforms',
        'assigned_customer_groups' => 'common\api\models\AR\Products\AssignedCustomerGroups',
        'attributes' => 'common\api\models\AR\Products\Attributes',
        'inventory' => 'common\api\models\AR\Products\Inventory',
        'suppliers_data' => 'common\api\models\AR\Products\SuppliersData',
        'images' => 'common\api\models\AR\Products\Images',
        'properties' => 'common\api\models\AR\Products\Properties',
        'xsell' => 'common\api\models\AR\Products\Xsell',
        'documents' => 'common\api\models\AR\Products\Documents',
        'suppliers_product' => 'common\api\models\AR\Products\SupplierProduct',
        'set_products' => 'common\api\models\AR\Products\SetProducts',
        'gift_wrap' => 'common\api\models\AR\Products\GiftWrap',
        'featured' => 'common\api\models\AR\Products\Featured',
        'special' => 'common\api\models\AR\Products\Special',
        'warehouses_products' => 'common\api\models\AR\Products\WarehousesProducts',
    ];

    protected $auto_status = null;

    private $inventoryPresent = false;
    private $virtual_fields = [];

    public function __construct(array $config = [])
    {
        $this->inventoryPresent = (\common\helpers\Extensions::isAllowed('Inventory'));

        if (!$this->inventoryPresent) {
            unset($this->childCollections['inventory']);
            unset($this->indexedCollections['inventory']);
        }
        if ( defined('TABLE_DEPARTMENTS_PRODUCTS') ) {
            $this->childCollections['assigned_departments'] = false;
            $this->indexedCollections['assigned_departments'] = 'common\api\models\AR\Products\AssignedDepartments';
        }
        $marketPresent = defined('USE_MARKET_PRICES') && USE_MARKET_PRICES=='True';
        $groupsPresent = \common\helpers\Extensions::isCustomerGroupsAllowed();
        if ( !$marketPresent && !$groupsPresent ) {
            unset($this->childCollections['prices']);
        }
        $this->afterSaveHooks['Product::doCache'] = 'reCalculateStock';
        $this->afterSaveHooks['Product::SpecialClean'] = 'removeInvalidSpecials';

        if (!($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed'))) {
            unset($this->childCollections['assigned_customer_groups']);
            unset($this->indexedCollections['assigned_customer_groups']);
        }
        parent::__construct($config);
    }

    public static function tableName()
    {
        return TABLE_PRODUCTS;
    }

    public static function primaryKey()
    {
        return ['products_id'];
    }

    public function setAutoStatus($value)
    {
        $this->auto_status = $value;
    }

    public function rules() {
        return array_merge(parent::rules(), [
            ['products_quantity', 'default', 'value' => 0]
        ]);
    }

    // {{ XTrader
    public function setSuppliers_id($value)
    {
        $this->virtual_fields['suppliers_id'] = $value;
    }
    public function getSuppliers_id()
    {
        return isset($this->virtual_fields['suppliers_id'])?$this->virtual_fields['suppliers_id']:false;
    }
    // }} XTrader

    public function initCollectionByLookupKey_Descriptions($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);

        if ( !is_null($this->products_id) ) {
            $dbMapCollect = [];
            foreach(Description::findAll(['products_id' => $this->products_id]) as $obj){
                $code = \common\classes\language::get_code($obj->language_id,true);
                if ( $code==false ) continue;
                $dbMapCollect[$code.'_'.$obj->platform_id] = $obj;
            }
            foreach(Description::getAllKeyCodes() as $keyCode=>$lookupPK){
                if( $loadAll || in_array($keyCode,$lookupKeys) ) {
                    if (isset($dbMapCollect[$keyCode])) {
                        $this->childCollections['descriptions'][$keyCode] = $dbMapCollect[$keyCode];
                    } else {
                        $lookupPK['products_id'] = $this->products_id;
                        $this->childCollections['descriptions'][$keyCode] = new Description($lookupPK);
                    }
                }
            }
        }else{
            foreach(Description::getAllKeyCodes() as $keyCode=>$lookupPK){
                $this->childCollections['descriptions'][$keyCode] = new Description($lookupPK);
            }
        }
/*
        foreach(Description::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['descriptions'][$keyCode] = null;
            if ( is_null($this->products_id) ) {
                $this->childCollections['descriptions'][$keyCode] = new Description($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['descriptions'][$keyCode])) {
                    $lookupPK['products_id'] = $this->products_id;
                    $this->childCollections['descriptions'][$keyCode] = Description::findOne($lookupPK);
                    if (!is_object($this->childCollections['descriptions'][$keyCode])) {
                        $this->childCollections['descriptions'][$keyCode] = new Description($lookupPK);
                    }
                }
            }
        }
*/
        return $this->childCollections['descriptions'];
    }

    public function initCollectionByLookupKey_Prices($lookupKeys)
    {
        $loadAll = in_array('*', $lookupKeys);
        if (true) {
            if ( !is_null($this->products_id) ) {
                $dbMapCollect = [];
                foreach(Prices::findAll(['products_id' => $this->products_id]) as $obj){
                    $keyCode = $obj->currencies_id.'_'.$obj->groups_id;
                    $dbMapCollect[$keyCode] = $obj;
                }
                foreach(Prices::getAllKeyCodes() as $keyCode=>$lookupPK){
                    if( $loadAll || in_array($keyCode,$lookupKeys) ) {
                        $dbKeyCode = $lookupPK['currencies_id'].'_'.$lookupPK['groups_id'];
                        if (isset($dbMapCollect[$dbKeyCode])) {
                            $this->childCollections['prices'][$keyCode] = $dbMapCollect[$dbKeyCode];
                        } else {
                            $lookupPK['products_id'] = $this->products_id;
                            $this->childCollections['prices'][$keyCode] = new Prices($lookupPK);
                        }
                    }
                }
                unset($dbMapCollect);
            }else{
                foreach(Prices::getAllKeyCodes() as $keyCode=>$lookupPK){
                    $this->childCollections['prices'][$keyCode] = new Prices($lookupPK);
                }
            }
        }else{
            foreach (Prices::getAllKeyCodes() as $keyCode => $lookupPK) {
                $this->childCollections['prices'][$keyCode] = null;
                if (is_null($this->products_id)) {
                    $this->childCollections['prices'][$keyCode] = new Prices($lookupPK);
                } elseif ($loadAll || in_array($keyCode, $lookupKeys)) {
                    if (!isset($this->childCollections['prices'][$keyCode])) {
                        $lookupPK['products_id'] = $this->products_id;
                        $this->childCollections['prices'][$keyCode] = Prices::findOne($lookupPK);
                        if (!is_object($this->childCollections['prices'][$keyCode])) {
                            $this->childCollections['prices'][$keyCode] = new Prices($lookupPK);
                        }
                    }
                }
            }
        }
        return $this->childCollections['prices'];
    }

    public function initCollectionByLookupKey_GiftWrap($lookupKeys)
    {
        if ( !is_array($this->childCollections['gift_wrap']) ) {
            $this->childCollections['gift_wrap'] = [];
            if ( $this->products_id ){
                $this->childCollections['gift_wrap'] =
                    GiftWrap::find()
                        ->where(['products_id' => $this->products_id])
                        ->all();
            }
        }
        return $this->childCollections['gift_wrap'];
    }

    public function initCollectionByLookupKey_Featured($lookupKeys)
    {
        if ( !is_array($this->childCollections['featured']) ) {
            $this->childCollections['featured'] = [];
            if ( $this->products_id ){
                $this->childCollections['featured'] =
                    Featured::find()
                        ->where(['products_id' => $this->products_id])
                        ->all();
            }
        }
        return $this->childCollections['featured'];
    }

    public function initCollectionByLookupKey_Special($lookupKeys)
    {
        if ( !is_array($this->childCollections['special']) ) {
            $this->childCollections['special'] = [];
            if ( $this->products_id ){
                $this->childCollections['special'] =
                    Special::find()
                        ->where(['products_id' => $this->products_id])
                        ->andWhere(['OR',['status'=>1], ['>=', 'start_date', new Expression('NOW()')]])
                        ->orderBy(['status'=>SORT_DESC, 'start_date'=>SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['special'];
    }

    public function initCollectionByLookupKey_AssignedCategories($lookupKeys)
    {
        if ( !is_array($this->childCollections['assigned_categories']) ) {
            $this->childCollections['assigned_categories'] = [];
            if ($this->products_id) {
                $this->childCollections['assigned_categories'] =
                    AssignedCategories::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['sort_order' => SORT_ASC, 'categories_id' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['assigned_categories'];
    }

    public function initCollectionByLookupKey_SetProducts($lookupKeys)
    {
        if ( !is_array($this->childCollections['set_products']) ) {
            $this->childCollections['set_products'] = [];
            if ($this->products_id) {
                $this->childCollections['set_products'] =
                    SetProducts::find()
                        ->where(['sets_id' => $this->products_id])
                        ->all();
            }
        }
        return $this->childCollections['set_products'];
    }

    public function initCollectionByLookupKey_WarehousesProducts($lookupKeys)
    {
        $this->childCollections['warehouses_products'] = [];
        if (!$this->hasAssignedProductAttributes()) {
            $loadAll = in_array('*', $lookupKeys);

            if ( false ) {
                if (!is_null($this->products_id)) {
                    $dbMapCollect = [];
                    foreach (WarehousesProducts::findAll([new Expression('CONCAT(\'\',:products_id)', ['products_id' => (int)$this->products_id])]) as $obj) {
                        $keyCode = $obj->warehouse_id . '_' . $obj->suppliers_id;
                        $dbMapCollect[$keyCode] = $obj;
                    }
                    foreach (WarehousesProducts::getAllKeyCodes() as $keyCode => $lookupPK) {
                        $lookupPK['products_id'] = (int)$this->products_id;
                        if ($loadAll || in_array($keyCode, $lookupKeys)) {
                            if (isset($dbMapCollect[$keyCode])) {
                                $this->childCollections['warehouses_products'][$keyCode] = $dbMapCollect[$keyCode];
                            } else {
                                $this->childCollections['warehouses_products'][$keyCode] = new WarehousesProducts($lookupPK);
                            }
                        }
                    }
                } else {
                    foreach (WarehousesProducts::getAllKeyCodes() as $keyCode => $lookupPK) {
                        $this->childCollections['warehouses_products'][$keyCode] = new WarehousesProducts($lookupPK);
                    }
                }
            }else {
                if (!is_null($this->products_id)) {
                    $dbMapCollect = [];
                    foreach (WarehousesProducts::findAll([new Expression('CONCAT(\'\',:products_id)', ['products_id' => (int)$this->products_id])]) as $obj) {
                        $keyCode = $obj->warehouse_id . '_' . $obj->suppliers_id;
                        if ( !empty($obj->location_id) ) $keyCode .= '_'.$obj->location_id;
                        $dbMapCollect[$keyCode] = $obj;
                    }
                    foreach (WarehousesProducts::getAllKeyCodes() as $keyCode => $lookupPK) {
                        if ($loadAll || in_array($keyCode, $lookupKeys)) {
                            if (isset($dbMapCollect[$keyCode])) {
                                $this->childCollections['warehouses_products'][$keyCode] = $dbMapCollect[$keyCode];
                            }
                        }
                    }
                }
            }
        }
        return $this->childCollections['warehouses_products'];
    }

    public function initCollectionByLookupKey_AssignedDepartments($lookupKeys)
    {
        if ( !is_array($this->childCollections['assigned_departments']) ) {
            $this->childCollections['assigned_departments'] = [];
            if ($this->products_id) {
                $this->childCollections['assigned_departments'] =
                    AssignedDepartments::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['departments_id' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['assigned_departments'];
    }

    public function initCollectionByLookupKey_AssignedPlatforms($lookupKeys)
    {
        if ( !is_array($this->childCollections['assigned_platforms']) ) {
            $this->childCollections['assigned_platforms'] = [];
            if ($this->products_id) {
                $this->childCollections['assigned_platforms'] =
                    ProductAssignedPlatforms::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['platform_id' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['assigned_platforms'];
    }

    public function initCollectionByLookupKey_AssignedCustomerGroups($lookupKeys)
    {
        if ( !is_array($this->childCollections['assigned_customer_groups']) ) {
            $this->childCollections['assigned_customer_groups'] = [];
            if ($this->products_id) {
                $this->childCollections['assigned_customer_groups'] =
                    ProductAssignedCustomerGroups::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['groups_id' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['assigned_customer_groups'];
    }

    public function initCollectionByLookupKey_Attributes($lookupKeys)
    {
        if ( !is_array($this->childCollections['attributes']) ) {
            $this->childCollections['attributes'] = [];
            if ($this->products_id) {
                $this->childCollections['attributes'] =
                    Attributes::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['options_id' => SORT_ASC, 'options_values_id' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['attributes'];
    }

    public function getCollectionProductName()
    {
        if ( isset($this->childCollections['descriptions'][ DEFAULT_LANGUAGE ]) && is_object($this->childCollections['descriptions'][ DEFAULT_LANGUAGE ]) ){
            return $this->childCollections['descriptions'][ DEFAULT_LANGUAGE ]->products_name;
        }
        return null;
    }

    public function getAssignedAttributeIds($excludeVirtual=false)
    {
        if ( !is_array($this->childCollections['attributes']) ) {
            $this->initCollectionByLookupKey_Attributes([]);
        }
        $ids = [];
        foreach ( $this->childCollections['attributes'] as $attrAR ){
            if ( $attrAR->pendingRemoval ) continue;
            if ( $excludeVirtual && Tools::getInstance()->is_option_virtual($attrAR->options_id) ) continue;
            if ( !is_array($ids[$attrAR->options_id]) ) $ids[$attrAR->options_id] = [];
            $ids[$attrAR->options_id][] = $attrAR->options_values_id;
        }
        return $ids;
    }

    public function hasAssignedProductAttributes()
    {
        if ( is_array($this->childCollections['attributes']) ) {
            $_check = $this->getAssignedAttributeIds();
            return count($_check)>0;
        }
        if ( $this->products_id ) {
            $inDatabaseCount = \common\api\models\AR\Products\Attributes::find()
                ->where(['products_id' => $this->products_id])
                ->count();
            return $inDatabaseCount>0;
        }
        return false;
    }

    public function initCollectionByLookupKey_Inventory($lookupKeys)
    {
        if ( !is_array($this->childCollections['inventory']) ) {
            $this->childCollections['inventory'] = [];
            if ($this->products_id) {
                $this->childCollections['inventory'] =
                    Inventory::find()
                        ->where(['prid' => $this->products_id])
                        ->orderBy(['products_id' => SORT_ASC,])
                        ->all();
            }
        }
        return $this->childCollections['inventory'];
    }

    public function initCollectionByLookupKey_Images($lookupKeys)
    {
        if ( !is_array($this->childCollections['images']) ) {
            $this->childCollections['images'] = [];
            if ($this->products_id) {
                $this->childCollections['images'] =
                    Images::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['sort_order' => SORT_ASC,])
                        ->all();
            }
        }
        return $this->childCollections['images'];
    }

    public function initCollectionByLookupKey_Properties($lookupKeys)
    {
        if ( !is_array($this->childCollections['properties']) ) {
            $this->childCollections['properties'] = [];
            if ($this->products_id) {
                $this->childCollections['properties'] =
                    Properties::find()
                        ->where(['products_id' => $this->products_id])
                        //->orderBy(['sort_order' => SORT_ASC,])
                        ->all();
            }
        }
        return $this->childCollections['properties'];
    }

    public function initCollectionByLookupKey_Xsell($lookupKeys)
    {
        if ( !is_array($this->childCollections['xsell']) ) {
            $this->childCollections['xsell'] = [];
            if ($this->products_id) {
                $this->childCollections['xsell'] =
                    Xsell::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['sort_order' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['xsell'];
    }

    public function initCollectionByLookupKey_Documents($lookupKeys)
    {
        if ( !is_array($this->childCollections['documents']) ) {
            $this->childCollections['documents'] = [];
            if ($this->products_id) {
                $this->childCollections['documents'] =
                    Documents::find()
                        ->where(['products_id' => $this->products_id])
                        ->orderBy(['sort_order' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['documents'];
    }

    public function initCollectionByLookupKey_SuppliersData($lookupKeys)
    {
        if ( !is_array($this->childCollections['suppliers_data']) ) {
            $this->childCollections['suppliers_data'] = [];
            if ($this->products_id) {
                $this->childCollections['suppliers_data'] =
                    SuppliersData::find()
                        ->where(['products_id' => $this->products_id])
                        ->all();
            }
        }
        return $this->childCollections['suppliers_data'];
    }

    // {{ XTrader
    public function initCollectionByLookupKey_SuppliersProduct($lookupKeys)
    {
        if ( !is_array($this->childCollections['suppliers_product']) ) {
            $this->childCollections['suppliers_product'] = [];
            if ($this->products_id && $this->suppliers_id) {
                $this->childCollections['suppliers_product'] =
                    SupplierProduct::find()
                        ->where(['products_id' => $this->products_id])
                        ->andWhere(['suppliers_id' => $this->suppliers_id])
                        ->all();
            }
        }
        return $this->childCollections['suppliers_product'];
    }
    // }} XTrader

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescriptions()
    {
        return $this->hasMany(Description::className(), ['products_id'=>'products_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInventory()
    {
        return $this->hasMany(Inventory::className(), ['prid'=>'products_id']);
    }


    public function getFeatured()
    {
        return $this->hasOne(Featured::className(),['products_id'=>'products_id'])->where(['affiliate_id'=>0]);
    }
    /*public function extraFields()
    {
        return ['descriptions'=>'descriptions'];
    }*/

    public function exportArray(array $fields = [])
    {
        $tools = \backend\models\EP\Tools::getInstance();
        $export = parent::exportArray($fields);
        if ( array_key_exists('stock_delivery_terms_id', $export) || in_array('stock_delivery_terms_text',$fields) ){
            $export['stock_delivery_terms_text'] = $tools->getStockDeliveryTerms($this->stock_delivery_terms_id);
        }
        if ( array_key_exists('stock_indication_id', $export) || in_array('stock_indication_text',$fields) ){
            $export['stock_indication_text'] = $tools->getStockIndication($this->stock_indication_id);
        }
        if ( array_key_exists('manufacturers_id', $export) || in_array('manufacturers_name',$fields) ) {
            $export['manufacturers_name'] = \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $this->manufacturers_id);
        }
        return $export;
    }

    public function importArray($data)
    {
        $tools = \backend\models\EP\Tools::getInstance();
        if ( array_key_exists('stock_delivery_terms_text', $data) ){
            $data['stock_delivery_terms_id'] = $tools->lookupStockDeliveryTermId($data['stock_delivery_terms_text']);
        }
        if ( array_key_exists('stock_indication_text', $data) ){
            $data['stock_indication_id'] = $tools->lookupStockIndicationId($data['stock_indication_text']);
        }
        if ( array_key_exists('manufacturers_name', $data) ) {
            $data['manufacturers_id'] = $tools->get_brand_by_name($data['manufacturers_name']);
            if ( $data['manufacturers_id']==='null' ) $data['manufacturers_id'] = null;
        }

        if ( isset($data['warehouses_products']) && is_array($data['warehouses_products']) ) {
            unset($data['products_quantity']);
        }

        $importResult = parent::importArray($data);

        if ( array_key_exists('attributes', $data) ) {
            $this->checkInventory();
        }

        if ( isset($data['AutoStatus']) ){
            $this->AutoStatus = $data['AutoStatus'];
        }

        return $importResult;
    }

    public function reCalculateStock()
    {
        \common\helpers\Product::doCache($this->products_id);
    }

    public function removeInvalidSpecials()
    {
        foreach (Special::find()->where(['products_id'=>$this->products_id,'status'=>0, 'start_date'=>null, 'expires_date'=>null, ])
                     ->all() as $removeInactive){
            $removeInactive->delete();
        }
    }

    public function checkInventory()
    {
        if ( !$this->inventoryPresent ) return;
        $attr = $this->getAssignedAttributeIds(true);
        $options = $attr;
        ksort($options);
        reset($options);
        $i = 0;
        $idx = 0;
        foreach ($options as $key => $value) {
            if ($i == 0) {
                $idx = $key;
                $i = 1;
            }
            asort($options[$key]);
        }

        $inventory_options = array();
        if ( count($options)>0 ) {
            $inventory_options = \common\helpers\Inventory::get_inventory_uprid($options, $idx);
        }

        if ( !is_array($this->childCollections['inventory']) ) {
            $this->initCollectionByLookupKey_Inventory([]);
        }

        foreach ( $this->childCollections['inventory'] as $idx=>$inventoryObj ) {
            $partialUprid = preg_replace('/^\d+/','',$inventoryObj->products_id);

            $haveValidIdx = array_search($partialUprid,$inventory_options);
            if ( $haveValidIdx!==false ) {
                // valid inventory uprid
                unset($inventory_options[$haveValidIdx]);
                $inventoryObj->pendingRemoval = false;
            }else{
                $inventoryObj->pendingRemoval = true;
            }
        }
        // not checked need add
        foreach ($inventory_options as $partialUprid) {

            $newInventory = new Inventory();
            $newInventory->products_id = strval($this->products_id).$partialUprid;
            $newInventory->fillOptionValueList();
            $newInventory->parentEPMap($this);

            $this->childCollections['inventory'][] = $newInventory;
        }
    }

    public function beforeSave($insert)
    {
        if ( $ext = \common\helpers\Acl::checkExtensionAllowed('AutomaticallyStatus', 'allowed') && isset($this->auto_status) ) {
            unset($this->products_status);
        }

        if ($this->inventoryPresent && $this->hasAssignedProductAttributes()){
            $this->childCollections['warehouses_products'] = [];
        }else{
            if ($this->getDirtyAttributes(['products_quantity'])) {
                $default_warehouse_id = intval(\common\helpers\Warehouses::get_default_warehouse());
                $defaultWH = $default_warehouse_id . '_' . \common\helpers\Suppliers::getDefaultSupplierId();
                if ( count($this->childCollections['warehouses_products'])==0 ) {
                    $this->initCollectionByLookupKey_WarehousesProducts(['*']);
                }
                if ( !isset($this->childCollections['warehouses_products'][$defaultWH]) ) {
                    $this->childCollections['warehouses_products'][$defaultWH] = new WarehousesProducts([]);
                    $this->childCollections['warehouses_products'][$defaultWH]->warehouse_id = $default_warehouse_id;
                    $this->childCollections['warehouses_products'][$defaultWH]->suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
                    $this->childCollections['warehouses_products'][$defaultWH]->parentEPMap($this);
                }
                $this->childCollections['warehouses_products'][$defaultWH]->warehouse_stock_quantity = $this->products_quantity;
                $this->initiateAfterSave('Product::doCache');
                unset($this->products_quantity);
            }
        }
        if ( $insert ) {
            if ( empty($this->products_date_added) ) {
                $this->products_date_added = new Expression("NOW()");
            }
            if (defined('NEW_MARK_UNTIL_DAYS') && intval(constant('NEW_MARK_UNTIL_DAYS'))>0 && empty($this->products_new_until)) {
                $this->products_new_until = date(\common\helpers\Date::DATABASE_DATE_FORMAT, strtotime('+' . intval(constant('NEW_MARK_UNTIL_DAYS')) . ' day') );
            }
        }else{
            if ( $this->isModified() ) {
                $this->products_last_modified = new Expression("NOW()");
            }
        }
        if ( $insert ){
            if ($this->parent_products_id){
                $this->products_id_stock = $this->parent_products_id;
                $this->products_id_price = $this->parent_products_id;
            }
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ( $insert ) {
            if ( !$this->parent_products_id ) {
                // parented product handled in before save
                static::updateAll(
                    [
                        'products_id_stock' => $this->parent_products_id ? intval($this->parent_products_id) : intval($this->products_id),
                        'products_id_price' => $this->parent_products_id ? intval($this->parent_products_id) : intval($this->products_id),
                    ],
                    ['products_id' => intval($this->products_id)]
                );
            }else{
                $childCount = static::find()
                    ->where(['parent_products_id' => intval($this->parent_products_id)])
                    ->count();
                static::updateAll(
                    ['sub_product_children_count'=>(int)$childCount],
                    ['products_id'=>intval($this->parent_products_id)]
                );
            }
        }
        static::updateAll(['products_price_full'=>$this->products_price_full],['parent_products_id'=>$this->products_id, 'products_id_price'=>$this->products_id]);

        if ( $insert && !is_array($this->childCollections['assigned_customer_groups']) ) {
            if ( $ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
                if ( $ext::select() ){
                    /** @var \backend\services\GroupsService $groupService */
                    try {
                        $groupService = \Yii::createObject(\backend\services\GroupsService::class);
                        $groupService->addProductToAllGroups($this->products_id);
                        unset($groupService);
                    }catch (\Exception $ex){
                        \common\helpers\Php::logError($ex);
                    }
                }
            }
        }

        if ( isset($this->auto_status) && ($ext = \common\helpers\Acl::checkExtensionAllowed('AutomaticallyStatus')) ) {
            $ext::setAutoStatusProduct($this->products_id, $this->auto_status, true);
            unset($this->auto_status);
        }

        $used_suppliers_products_ids = [];
        $get_used_ids_r = tep_db_query(
            "SELECT DISTINCT suppliers_id ".
            "FROM ".TABLE_SUPPLIERS_PRODUCTS." ".
            "WHERE products_id='".$this->products_id."'"
        );
        if ( tep_db_num_rows($get_used_ids_r)>0 ) {
            while($get_used_id = tep_db_fetch_array($get_used_ids_r)){
                $used_suppliers_products_ids[(int)$get_used_id['suppliers_id']] = (int)$get_used_id['suppliers_id'];
            }
        }
        if ( count($used_suppliers_products_ids)==0 ) {
            $used_suppliers_products_ids[intval(\common\helpers\Suppliers::getDefaultSupplierId())] = intval(\common\helpers\Suppliers::getDefaultSupplierId());
        }

        $getWhDel = WarehousesProducts::find()
            ->where(['prid'=>$this->products_id]);
        if ( count($used_suppliers_products_ids)>0 ) {
            $getWhDel->andWhere(['NOT IN', 'suppliers_id', array_values($used_suppliers_products_ids)]);
        }

        if ($getWhDel->count()>0) {
            foreach ($getWhDel->all() as $deleteWarehouseProduct) {
                $deleteWarehouseProduct->delete();
            }
            \common\helpers\Warehouses::update_products_quantity($this->products_id,\common\helpers\Warehouses::get_default_warehouse(),0,'+');
        }

        /* @var $ext \common\extensions\PlainProductsDescription\PlainProductsDescription */
        $ext = \common\helpers\Acl::checkExtensionAllowed('PlainProductsDescription', 'allowed');
        if ($ext && $ext::isEnabled()) {
            $ext::reindex((int)$this->products_id);
        }

        if ( array_key_exists('products_groups_id', $changedAttributes) ) {
            \common\helpers\ProductsGroupSortCache::update($this->products_id);
        }

    }

    public function afterDelete()
    {
        parent::afterDelete();
        if ( $this->parent_products_id ) {
            $childCount = static::find()
                ->where(['parent_products_id' => intval($this->parent_products_id)])
                ->count();
            static::updateAll(
                ['sub_product_children_count'=>(int)$childCount],
                ['products_id'=>intval($this->parent_products_id)]
            );
        }else{
            foreach (static::find()
                         ->where(['parent_products_id'=>$this->products_id])
                         ->select(['products_id'])
                         ->asArray()
                         ->all() as $childProduct){
                \common\helpers\Product::remove_product($childProduct['products_id']);
            }
        }
    }


}