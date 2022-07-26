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
use common\api\models\AR\Categories;
use common\api\models\AR\Products;
use common\classes\language;
use backend\models\EP\Provider\Magento\helpers\SoapClient;
use backend\models\EP\Directory;
use backend\models\EP\Provider\Magento\helpers\ImageSource;
use common\helpers\Seo;

class ImportProducts implements DatasourceInterface {

    protected $total_count = 0;
    protected $row_count = 0;
    protected $products_list;
    protected $config = [];
    protected $afterProcessFilename = '';
    protected $afterProcessFile = false;
    protected $client;
    protected $categories_tree;
    protected $attributes_options = [];
    public $job_id;

    function __construct($config) {
        if (substr($config['client']['location'], -1) == '/') {
            $config['client']['location'] = substr($config['client']['location'], 0, -1);
        }
        $this->config = $config;
        $this->initDB();
    }

    public function allowRunInPopup() {
        return true;
    }

    public function initDB() {
        tep_db_query("CREATE TABLE IF NOT EXISTS ep_holbi_soap_link_products_cols(
   ep_directory_id INT(11) NOT NULL,
   remote_products_id INT(11) NOT NULL,
   remote_products_sku varchar(255),
   remote_group_name varchar(128),
   KEY(ep_directory_id, remote_products_id),
   UNIQUE KEY(remote_products_id)
);");

        tep_db_query("CREATE TABLE IF NOT EXISTS ep_holbi_soap_link_products(
   ep_directory_id INT(11) NOT NULL,
   remote_products_id INT(11) NOT NULL,
   local_products_id INT(11) NOT NULL,
   KEY(ep_directory_id, remote_products_id),
   UNIQUE KEY(local_products_id)
);");

        tep_db_query("CREATE TABLE IF NOT EXISTS ep_holbi_soap_link_categories(
   ep_directory_id INT(11) NOT NULL,
   remote_category_id INT(11) NOT NULL,
   local_category_id INT(11) NOT NULL,
   PRIMARY KEY(ep_directory_id, remote_category_id),
   KEY(local_category_id)
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
        //$key = "jkajsdhfajfg&^jsaji0123";
        $mg = new SoapClient($this->config['client']);
        $this->client = $mg->getClient();
        $this->session = $mg->loginClient();

        $this->config['assign_platform'] = \common\classes\platform::defaultId();

        $this->getStoresList();
        $this->getCategoriesTree()->importCategories();

        $this->total_count = count($this->getProductsList());
        
        if (is_array($this->config['attributes']) && count($this->config['attributes'])){
            $this->loadProductAttributes();
        }

        $this->afterProcessFilename = tempnam($this->config['workingDirectory'], 'after_process');
        $this->afterProcessFile = fopen($this->afterProcessFilename, 'w+');
    }

    public function getStoresList() {
        try {
            $result = $this->client->call($this->session, 'store.list');
        } catch (\Exception $ex) {
            throw new \Exception('Download remote stores info error');
        }
    }
    
    public function loadProductAttributes(){
        $sets = [];
        if (is_array($this->config['attributes'])){
            foreach($this->config['attributes'] as $at){
                if (!empty($at) && preg_match("/;/", $at)){
                    $ex = explode(";", $at);
                    if (!isset($sets[$ex[0]])) $sets[$ex[0]] = [];
                    $sets[$ex[0]][] = $ex[1];
                }
            }
            $sets = array_unique($sets);
        }
        $to_load = [];
        try {
            foreach($sets as $setID => $attr_code){
                $result = $this->client->call($this->session, 'catalog_product_attribute.list',$setID);
                if (is_array($result)){
                    foreach($result as $rem_att){
                        if (isset($rem_att['code']) && in_array($rem_att['code'], $attr_code)){
                            $to_load[] = $rem_att['code'];
                        }
                    }
                }
            }
            if (count($to_load)){
                $this->loadAttributesOptions($to_load);
            }            
        } catch (\Exception $ex) {
            throw new \Exception('Download remote category info error');
        }
        return $result;
    }
    
    public function loadAttributesOptions($to_load = []){
        try {
            foreach($to_load as $attr_code){
                $result = $this->client->call($this->session, 'catalog_product_attribute.options', $attr_code);
                if ($result){
                    $this->attributes_options[$attr_code] = $result;
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception('Download remote products error');
        }
        return $result;
    }

    public function importCategories() {
        if (is_array($this->categories_tree)) {
            if (isset($this->config['trunkate_categories'])) {
                \common\helpers\Categories::trunk_categories();
                tep_db_query("delete from ep_holbi_soap_link_categories where ep_directory_id = '" . (int) $this->config['directoryId'] . "'");
            }
            $top_level = $this->categories_tree['children'][0]['children'];
            $this->processCategory($top_level, 0);
        }

        if (isset($this->config['trunkate_products'])) {
            \common\helpers\Product::trunk_products();
            tep_db_query("delete from ep_holbi_soap_link_products where ep_directory_id = '" . (int) $this->config['directoryId'] . "'");
            tep_db_query("delete from ep_holbi_soap_link_products_cols where ep_directory_id = '" . (int) $this->config['directoryId'] . "'");
            if ($model = \common\helpers\Acl::checkExtensionTableExist('ProductsCollections', 'Collections')) {
                $model::deleteFull();
            }
        }
    }

    public function getCategoryInfo($id) {
        try {
            $result = $this->client->call($this->session, 'catalog_category.info', $id);
        } catch (\Exception $ex) {
            throw new \Exception('Download remote category info error');
        }
        return $result;
    }

    public function getLoacalAssignedCatetegoryID($id) {
        $check = tep_db_fetch_array(tep_db_query("select local_category_id from ep_holbi_soap_link_categories where remote_category_id = '" . (int) $id . "' and ep_directory_id = '" . (int) $this->config['directoryId'] . "'"));
        if ($check) {
            return $check['local_category_id'];
        }
        return false;
    }

    public function processCategory($top_level, $parent_id) {
        if (is_array($top_level)) {
            foreach ($top_level as $mg_category) {
                $category_id = $this->getLoacalAssignedCatetegoryID($mg_category['category_id']);
                //$data = $this->getCategoryInfo($mg_category['category_id']);
                //echo '<pre>';print_r($data);
                if (!$category_id) {
                    $data = $this->getCategoryInfo($mg_category['category_id']);
                    $category = new Categories();
                    //                  $seo = Seo::makeSlug($data['name']);
                    $category->importArray([
                        'categories_status' => $data['is_active'],
                        'parent_id' => $parent_id,
//                        'categories_seo_page_name' => (string) $seo,
                        'categories_old_seo_page_name' => (string) $data['url_path'],
                        'descriptions' => [
                            '*' => [
                                'categories_name' => (string) $data['name'],
                                'categories_description' => (string) $data['description'],
                                'categories_heading_title' => (string) $data['meta_title'],
//                                'categories_seo_page_name' => (string) $seo,
                                'categories_head_desc_tag' => (string) $data['meta_description'],
                                'categories_head_keywords_tag' => (string) $data['meta_keywords'],
                            ]
                        ],
                        'assigned_platforms' => [
                            ['platform_id' => $this->config['assign_platform']],
                        ]
                    ]);

                    $category->save();
                    $category_id = $category->categories_id;

                    tep_db_perform('ep_holbi_soap_link_categories', [
                        'ep_directory_id' => (int) $this->config['directoryId'],
                        'remote_category_id' => (int) $mg_category['category_id'],
                        'local_category_id' => (int) $category_id,
                    ]);

                    if (isset($data['image']) && !empty($data['image'])) {
                        $source = ImageSource::getInstance($this->config)->loadResource($data['image'], 'category');
                        if ($source) {
                            $category->importArray([
                                'categories_image' => $source
                            ]);
                            $category->update();
                        }
                    }
                }

                if (isset($mg_category['children'])) {
                    $this->processCategory($mg_category['children'], $category_id);
                }
            }
        }
    }

    public function getCategoriesTree() {
        try {
            $result = $this->client->call($this->session, 'catalog_category.tree');
        } catch (\Exception $ex) {
            throw new \Exception('Download remote categories error');
        }

        if (is_array($result)) {
            $this->categories_tree = $result;
        }
        return $this;
    }

    protected function getProductsList() {
        try {
            /*$filters = array(
                'filters' => array(
                    'product_id' => [886]
                ),
            );*/
            $filters = [];
            $this->products_list = $this->client->call($this->session, 'catalog_product.list', $filters);
        } catch (\Exception $ex) {
            throw new \Exception('Download remote products count error');
        }
        if (count($this->products_list)) {
            $this->products_list = \yii\helpers\ArrayHelper::index($this->products_list, 'product_id');
        }
        //echo '<pre>';print_r($this->products_list);die;
        return $this->products_list;
    }

    public function processRow(Messages $message) {
        set_time_limit(0);
        $remoteProductId = current($this->products_list);

        if (!$remoteProductId)
            return false;
        try {
            $this->processRemoteProduct($remoteProductId['product_id'], true);
            tep_db_perform(TABLE_EP_JOB, array(
                'last_cron_run' => 'now()',
                    ), 'update', "job_id='" . $this->job_id . "'");
        } catch (\Exception $ex) {
            throw new \Exception('Processing product error (' . $remoteProductId['sku'] . ')');
        }


        $this->row_count++;
        next($this->products_list);
        return true;
    }

    public function getProductInfo($id) {
        try {
            $result = $this->client->call($this->session, 'catalog_product.info', $id);
            $stock = $this->client->call($this->session, 'cataloginventory_stock_item.list', $id);
            if (is_array($stock[0])) {
                $result = array_merge($result, $stock[0]);
            }
        } catch (\Exception $ex) {
            throw new \Exception('Download remote product info error');
        }
        return $result;
    }

    public function getProductOptions($remoteProductId) {
        try {
            $result = $this->client->call($this->session, 'product_custom_option.list', $remoteProductId);
            if ($result) {
                $this->SynhronizeOptions($result);
            }
        } catch (\Exception $ex) {
            throw new \Exception('Download remote products error');
        }
        return $result;
    }

    public function getProductOptionsValues($option_id) {
        try {
            $result = $this->client->call($this->session, 'product_custom_option.info', $option_id);
        } catch (\Exception $ex) {
            throw new \Exception('Download remote products error');
        }
        return $result;
    }

    public function SynhronizeOptions(&$options) {
        global $languages_id;
        try {
            if (is_array($options)) {
                foreach ($options as $key => $values) {
                    $option_id = $values['option_id'];
                    if (in_array($values['type'], ['checkbox', 'drop_down'])) {
                        $mg_values = $this->getProductOptionsValues($option_id);
                        if (is_array($mg_values['additional_fields']) && count($mg_values['additional_fields'])) {
                            $options[$key]['additional_fields'] = $mg_values['additional_fields'];
                        }
                    } else {
                        unset($options[$key]);
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception('Transformation options error');
        }
        return;
    }

    public function getProductMedia($remoteProductId) {
        try {
            $result = $this->client->call($this->session, 'catalog_product_attribute_media.list', $remoteProductId);
        } catch (\Exception $ex) {
            throw new \Exception('Download remote products error');
        }
        return $result;
    }

    protected function processRemoteProduct($remoteProductId, $useAfterProcess = false) {

        static $timing = [
            'soap' => 0,
            'local' => 0,
        ];
        $t1 = microtime(true);

        $localId = $this->lookupLocalId($remoteProductId);
        if (!$localId) {

            $remoteProduct = $this->getProductInfo($remoteProductId);
            $t2 = microtime(true);
            $timing['soap'] += $t2 - $t1;

            if ($remoteProduct) {
                $localProduct = false;

                if ($remoteProduct['has_options']) {
                    $remoteProduct['attributes'] = $this->getProductOptions($remoteProductId);
                }
                if (($images = $this->getProductMedia($remoteProductId)) !== false) {
                    $remoteProduct['images'] = $images;
                }
                $importArray = [];
                switch ($remoteProduct['type']) {
                    case 'grouped':
                        $this->transformGrouppedProduct($remoteProduct);
                        return true;
                        break;
                    case 'simple':
                    default:
                        $importArray = $this->transformSimpleProduct($remoteProduct);
                        break;
                }

                if (!is_array($importArray) || !count($importArray))
                    return false;

                $localId = false;
                $localProduct = \common\api\models\AR\Products::find()
                                ->where(['products_model' => $remoteProduct['sku']])->one();

                if (!$localProduct) {
                    $localProduct = new \common\api\models\AR\Products();
                }

                if (1) {
                    unset($importArray['products_id']);
                    $patch_platform_assign = false;
                    if ($localProduct->isNewRecord) {
                        $patch_platform_assign = true;
                        $importArray['assigned_platforms'] = [
                            [
                                'platform_id' => $this->config['assign_platform'],
                            ]
                        ];
                    }
                    unset($importArray['product_id']);
                    //echo '<pre>';print_r($importArray);
                    $localProduct->importArray($importArray);
                    $localProduct->validate();
                    if ($localProduct->save()) {
                        $localId = $localProduct->products_id;
                        $this->linkRemoteWithLocalId($remoteProductId, $localId);

                        if ($patch_platform_assign) {
                            tep_db_query(
                                    "INSERT IGNORE INTO platforms_products (platform_id, products_id) " .
                                    "VALUES ('" . intval(\common\classes\platform::defaultId()) . "', '" . intval($localProduct->products_id) . "')"
                            );
                        }

                        //echo '<pre>!!! '; var_dump($localId); echo '</pre>';
                    }
                }
                unset($localProduct);
            }
        }
        $t3 = microtime(true);
        $timing['local'] += $t3 - $t2;
        //echo '<pre>';  var_dump($timing);    echo '</pre>';
    }

    protected function lookupLocalId($remoteId) {
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

    protected function linkRemoteWithLocalId($remoteId, $localId) {
        tep_db_query(
                "INSERT INTO ep_holbi_soap_link_products(ep_directory_id, remote_products_id, local_products_id ) " .
                " VALUES " .
                " ('" . (int) $this->config['directoryId'] . "', '" . $remoteId . "','" . $localId . "') " .
                "ON DUPLICATE KEY UPDATE ep_directory_id='" . (int) $this->config['directoryId'] . "', remote_products_id='" . $remoteId . "'"
        );
        return true;
    }

    public function categories($data) {
        if (is_array($data)) {
            $newAssignedCategories = [];
            foreach ($data as $assigned_category) {
                $localCategoryId = $this->lookupLocalCategoryId($assigned_category);
                $newAssignedCategories[] = [
                    'categories_id' => $localCategoryId,
                ];
            }
            $data = $newAssignedCategories;
        } else {
            $data = [];
        }
        return $data;
    }

    public function attributes($data) {
        if (is_array($data)) {
            $newAttributes = [];
            $current = current($this->products_list);
            foreach ($data as $options) {
                if ($options['additional_fields']) {
                    foreach ($options['additional_fields'] as $value) {
                        $newAttributes[] = [
                            'options_name' => $options['title'],
                            'options_values_name' => $value['title'],
                            'options_values_price' => ($value['price_type'] == 'fixed' ? $value['price'] : ($current['price'] * $value['price'] / 100)),
                            'price_prefix' => '+',
                            'products_model' => $value['sku'],
                            'prices' => [
                                'attributes_group_price' => '-2',
                            ]
                        ];
                    }
                } else {
                    $newAttributes[] = [
                        'options_name' => $options['title'],
                    ];
                }
            }
            $data = $newAttributes;
        } else {
            $data = [];
        }

        return $data;
    }

    public function inventory($data) {
        if (is_array($data)) {
            $newInventory = [];
            foreach ($data as $options) {
                $pattern = [];
                if (!is_array($pattern['attribute_map'])) {
                    $pattern['attribute_map'] = [];
                }
                $pattern['inventory_price'] += $options['options_values_price'];
                $pattern['attribute_map'][] = [
                    'options_name' => $options['options_name'],
                    'options_values_name' => $options['options_values_name'],
                ];
                $newInventory[] = $pattern;
            }
            $data = $newInventory;
        } else {
            $data = [];
        }

        return $data;
    }

    public function images($data) {
        if (is_array($data)) {
            $newImages = [];
            $is_default = true;
            foreach ($data as $images) {
                $source = ImageSource::getInstance($this->config)->loadResource($images['url']);
                if ($source) {
                    $pattern = [];
                    if ($is_default) {
                        $pattern['default_image'] = 1;
                        $is_default = false;
                    }
                    $pattern['image_status'] = 1;
                    $pattern['sort_order'] = (int) $images['position'];
                    $pattern['image_description'] = [
                        '00' => [
                            'image_source_url' => $source,
                            'orig_file_name' => pathinfo($source, PATHINFO_BASENAME),
                            'image_title' => (string) $images['label'],
                        ]
                    ];
                    $newImages[] = $pattern;
                }
            }
            $data = $newImages;
        } else {
            $data = [];
        }
        return $data;
    }

    /*
    * grouped products in magento are union of simple products
    * grouped products saved during process and joined to collection afterprocess
    */
    protected function transformGrouppedProduct($responseObject) {
        $product = json_decode(json_encode($responseObject), true);
        $this->saveGrouppedProducts($product);
        return;
    }

    protected function transformSimpleProduct($responseObject) {
        $product = json_decode(json_encode($responseObject), true);

        $map = [
            'products_model' => 'sku',
            'products_price' => 'price',
            'weight_cm' => 'weight',
            'products_old_seo_page_name' => 'url_path',
            'products_status' => 'status',
            'assigned_categories' => 'categories',
            'products_head_title_tag' => 'meta_title',
            'products_head_desc_tag' => 'meta_description',
            'products_head_keywords_tag' => 'meta_keyword',
            'products_quantity' => 'qty',
            'descriptions' => [
                'products_name' => 'name',
                'products_description' => 'description',
                'products_description_short' => 'short_description'
            ],
            'products_status' => 'status',
            'attributes' => 'attributes',
            'images' => 'images'
        ];

        $t1 = microtime(true);
        $simple = [];
        foreach ($map as $tl_key => $mg_key) {
            if (is_array($map[$tl_key])) {
                $rebuld = [];
                foreach ($map[$tl_key] as $simple_tl_key => $simple_mg_key) {
                    $rebuld[$simple_tl_key] = $product[$simple_mg_key];
                    unset($product[$simple_mg_key]);
                }
                $simple[$tl_key]['*'] = $rebuld;
            } elseif (!is_array($map[$tl_key]) && is_array($product[$mg_key])) {
                try {
                    if (method_exists($this, $mg_key)) {
                        $simple[$tl_key] = $this->$mg_key($product[$mg_key]);
                        if ($mg_key != $tl_key) {
                            unset($product[$mg_key]);
                        }
                    }
                } catch (\Exception $ex) {
                    echo $ex->getMessage();
                }
            } else {
                if (isset($product[$mg_key])) {
                    $simple[$tl_key] = $product[$mg_key];
                }
                unset($product[$mg_key]);
            }
        }
        
        $simple['attributes'] = $this->getMainAttributes($product, $simple['attributes']);
        
        $simple['inventory'] = $this->inventory($simple['attributes']);
        $tools = new \backend\models\EP\Tools();
        $simple['stock_indication_id'] = ((int) $product['is_in_stock'] ? $tools->lookupStockIndicationId('In stock') : $tools->lookupStockIndicationId('Currently out of Stock'));
        $simple['stock_delivery_terms_id'] = $simple['stock_indication_id'];
        $simple['products_status'] = ($simple['products_status'] == 1 ? 1 : 0);
        $simple['product_id'] = $product['product_id'];
        
        
        //echo '<pre>';print_r($simple);die;


        return $simple;
    }
    
    public function getMainAttributes($product, $attributes){
        if (is_array($this->attributes_options) && count($this->attributes_options)){
            foreach($this->attributes_options as $a_code => $values){
                if (isset($product[$a_code]) && is_array($values)){
                    $mapped = \yii\helpers\ArrayHelper::map($values, 'value', 'label');
                    $attributes[] = [
                        'options_name' => ucfirst($a_code),
                        'options_values_name' => $mapped[$product[$a_code]],
                    ];
                }
            }
        }
        return $attributes;
    }

    protected function lookupLocalCategoryId($remoteId) {
        static $cached = [];
        $key = (int) $this->config['directoryId'] . '^' . (int) $remoteId;
        if (isset($cached[$key])) {
            return $cached[$key];
        }
        $getMap_r = tep_db_query(
                "SELECT local_category_id " .
                "FROM ep_holbi_soap_link_categories " .
                "WHERE ep_directory_id='" . (int) $this->config['directoryId'] . "' " .
                " AND remote_category_id='" . $remoteId . "' " .
                "LIMIT 1 "
        );
        if (tep_db_num_rows($getMap_r) > 0) {
            $getMap = tep_db_fetch_array($getMap_r);
            $cached[$key] = $getMap['local_category_id'];
            return $getMap['local_category_id'];
        }
        return false;
    }

    protected function saveGrouppedProducts($remoteProduct) {
        $data = [
            'ep_directory_id' => (int) $this->config['directoryId'],
            'remote_products_id' => (int) $remoteProduct['product_id'],
            'remote_products_sku' => implode(";", array_map('trim', explode("-", $remoteProduct['sku']))),
            'remote_group_name' => $remoteProduct['name'],
        ];
        tep_db_perform('ep_holbi_soap_link_products_cols', $data);
        return;
    }

    public function postProcess(Messages $message) {

        if (!\common\helpers\Acl::checkExtensionAllowed('ProductsCollections')) {
            return;
        }
        
        $grouped = tep_db_query("select * from ep_holbi_soap_link_products_cols where ep_directory_id = '" . (int) $this->config['directoryId'] . "'");
        if (tep_db_num_rows($grouped)) { //do collections
            $languages = \common\helpers\Language::get_languages();
            $default_language = \common\helpers\Language::get_default_language_id();
            if (!array($languages))
                return;
            while ($row = tep_db_fetch_array($grouped)) {
                $collection = \common\models\Collections::find()->where('collections_name = :name and language_id = :lid', [':name' => $row['remote_group_name'], ':lid' => $default_language])->one();
                if (!$collection) {
                    $cid = \common\models\Collections::find()->max('collections_id') + 1;

                    $collection = new \common\models\Collections();
                    $collection->setAttributes([
                        'language_id' => $default_language,
                        'collections_id' => $cid,
                        'collections_name' => $row['remote_group_name'],
                            ], false);
                    
                    $collection->save(false);
                    foreach ($languages as $_l) {
                        if ($_l['id'] == $default_language)
                            continue;
                        $lCollection = new \common\models\Collections();
                        $lCollection->setAttributes([
                            'language_id' => $_l['id'],
                            'collections_id' => $collection->collections_id,
                            'collections_name' => $row['remote_group_name'],
                                ], false);
                        $lCollection->save(false);
                    }
                }
                if (!empty($row['remote_products_sku']) && $collection) {
                    $sku_array = explode(";", $row['remote_products_sku']);
                    if (is_array($sku_array) && count($sku_array)) {
                        \common\models\CollectionsProducts::deleteAll('collections_id =:id', [':id' => $collection->collections_id]);
                        foreach ($sku_array as $sku_item) {
                            $localProduct = \common\api\models\AR\Products::find()
                                            ->where(['products_model' => $sku_item])->one();
                            if ($localProduct) {
                                if (($images = $this->getProductMedia($row['remote_products_id'])) !== false) {
                                    $importArray = [];
                                    $importArray['images'] = $this->images($images);
                                    $localProduct->importArray($importArray);
                                    $localProduct->save();
                                }
                                $cp = new \common\models\CollectionsProducts();
                                $cp->setAttributes([
                                    'collections_id' => $collection->collections_id,
                                    'products_id' => $localProduct->products_id,
                                        ], false);
                                $cp->save(false);
                            }
                        }
                    }
                }
            }
        }
        return;
    }

}
