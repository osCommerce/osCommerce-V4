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

use backend\models\EP;
use backend\models\EP\Messages;
use yii\helpers\FileHelper;

class ProductsOldSeoUrls extends ProviderAbstract implements ImportInterface, ExportInterface
{

    protected $data = array();
    protected $EPtools;
    protected $entry_counter = 0;
    
    protected $export_query;


    function init()
    {
        parent::init();
        $this->initFields();
        $this->EPtools = new EP\Tools();
    }

    protected function initFields(){
        $this->fields = array();

        $checkUniqKey = \Yii::$app->getDb()->createCommand("select count(*) as c from products group by products_model having count(*)>1")->queryScalar();
        if ( $checkUniqKey ){
            $this->fields[] = array( 'name' => 'products_id', 'value' => 'Products Id', 'is_key'=>true, 'is_key_part'=>true );
            $this->fields[] = array( 'name' => 'products_model', 'value' => 'Products Model', 'is_key_part'=>true );
        }else{
            $this->fields[] = array( 'name' => 'products_model', 'value' => 'Products Model', 'is_key'=>true );
        }

        foreach( \common\helpers\Language::get_languages() as $_lang ) {

        }

        $platforms = \common\models\Platforms::getPlatformsByType("non-virtual")->all();
        $hiddenLangs = \common\helpers\Language::getAdminHiddenLanguages();

        foreach($platforms as $platform){
            $platform_postfix = '_'.$platform->platform_id;
            $platform_name_postfix = ($platform->is_default?'':' on '.$platform->platform_name);

            foreach( \common\helpers\Language::get_languages() as $_lang ) {
                $data_descriptor = '%|' . TABLE_SEO_REDIRECTS_NAMED . '|' . $_lang['id'] . '|0';
                $this->fields[] = array(
                    //'data_descriptor' => $data_descriptor,
                    //'column_db' => 'old_seo_page_name',
                    'calculated' => true,
                    'lang_id' => $_lang['id'],
                    'platform_id' => $platform->platform_id,
                    'name' => 'old_seo_page_name_' . $_lang['id'] . $platform_postfix,
                    'value' => 'Old SEO URL ' . $_lang['code'] . $platform_name_postfix,
                );
            }

            foreach( \common\helpers\Language::get_languages() as $_lang ) {
                $data_descriptor = '%|'.TABLE_PRODUCTS_DESCRIPTION.'|'.$_lang['id'].'|'.$platform->platform_id;
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_name',
                    'name' => 'products_name_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products Name '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_seo_page_name',
                    'name' => 'products_seo_page_name_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products SEO page name '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
            }
        }

    }

    public function prepareExport($useColumns, $filter){
        $this->buildSources($useColumns);

        $main_source = $this->main_source;

        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
               // $filter_sql .= "AND products_id IN(SELECT products_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id IN('".implode("','",$categories)."')) ";
            }
        }
        /*
        $main_sql =
            "SELECT {$main_source['select']} products_id, ".
            " manufacturers_id as _manufacturers_id, products_tax_class_id AS _tax_id, ".
            " parent_products_id AS _parent_products_id, products_id_price, products_id_price as _products_id_price, ".
            " products_price AS products_price_def, products_price_discount AS products_price_discount_def ".
            "FROM ".TABLE_PRODUCTS." ".
            "WHERE 1 {$filter_sql} ".
            "/*LIMIT 3* /";

        $this->export_query = tep_db_query( $main_sql );*/



        $q = \common\models\Products::find()->alias('p')
            ->select("{$main_source['select']} products_id, parent_products_id AS _parent_products_id ")
            ;
        if ( is_array($categories) && !empty($categories)) {
          $q->andWhere([
            'exists', (new \yii\db\Query())
              ->from(TABLE_PRODUCTS_TO_CATEGORIES . ' p2c')
              ->andWhere('p2c.products_id=p.products_id')
              ->andWhere(['p2c.categories_id' => $categories])
            ]);
        }
//$q->andWhere(['p.products_id' => 1068]);
        $q->with('seoRedirects')->asArray();

        $this->export_query = $q->all();
//echo $q->createCommand()->rawSql . " #### <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($this->export_query, true) ."</PRE>"; die;

        for ($n=count($this->export_query); $n>=0; $n--) {
            if (!empty($this->export_query[$n]['seoRedirects'])) {
                $row = [];
                $lngGroups = \yii\helpers\ArrayHelper::index($this->export_query[$n]['seoRedirects'],
                                                                null,
                                                                function ($el) { return $el['language_id'] . '_' . $el['platform_id'];});

                $maxRedirects = 0;
                unset($this->export_query[$n]['seoRedirects']);
                foreach($lngGroups as $lpKey => $data) {
                    $maxRedirects = max($maxRedirects, count($data));
                    $row['old_seo_page_name_' . $lpKey] = $data[0]['old_seo_page_name'];
                    $this->export_query[$n] += $row;
                }

                if ($maxRedirects>1) {
                    //add extra rows with the same product id
                    for($i=1; $i<$maxRedirects; $i++) {
                        $row = [];
                        foreach($lngGroups as $lpKey => $data) {
                            if (!empty($data[$i])) {
                                $row['old_seo_page_name_' . $lpKey] = $data[$i]['old_seo_page_name'];
                            }
                        }
                        $this->export_query[] = [
                              'products_id' => $this->export_query[$n]['products_id'],
                              'products_model' => $this->export_query[$n]['products_model']
                            ] + $row;

                    }

                }
            }
        }
        \yii\helpers\ArrayHelper::multisort($this->export_query, 'products_id');

    }

    public function exportRow(){
        //$this->data = tep_db_fetch_array($this->export_query);
        $this->data = array_shift($this->export_query);
        if ( !is_array($this->data) ) return $this->data;

        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;

        foreach ( $data_sources as $source_key=>$source_data ) {
            if ( $source_data['table'] ) {
                $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ( $source_data['table']==TABLE_PRODUCTS_DESCRIPTION ) {
                    $data_sql .= "AND products_id='{$this->data['products_id']}' AND language_id='{$source_data['params'][0]}' AND platform_id='{$source_data['params'][1]}'";
                }elseif ( $source_data['table']==TABLE_SEO_REDIRECTS_NAMED ) {
                    $data_sql .= "AND owner_id='{$this->data['categories_id']}' AND language_id='{$source_data['params'][0]}' and redirects_type='category'";
                }else{
                    $data_sql .= "AND 1=0 ";
                }

                $data_sql_r = tep_db_query($data_sql);
                if ( tep_db_num_rows($data_sql_r)>0 ) {
                    $_data = tep_db_fetch_array($data_sql_r);
                    $this->data = array_merge($this->data, $_data);
                }
            }elseif($source_data['init_function'] && method_exists($this,$source_data['init_function'])){
                call_user_func_array(array($this,$source_data['init_function']),$source_data['params']);
            }
        }


        foreach( $export_columns as $db_key=>$export ) {
            if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['categories_id']));
            }
            $this->data[$db_key] = isset($this->data[$db_key])?$this->data[$db_key]:'';
        }


        return $this->data;

    }
    
    public function importRow($data, Messages $message)
    {

        $this->buildSources( array_keys($data) );

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_column = $this->file_primary_column;
        $file_primary_columns = $this->file_primary_columns;

        //echo '<pre>'; var_dump($data); echo '</pre>';
        $this->data = $data;
        $is_updated = false;

        $mkey_data = [];
        if ( !empty($file_primary_columns) ) {
            $mkey_data = array_intersect_key($data, $file_primary_columns);
            if (count($mkey_data) != count($file_primary_columns)) {
                $mkey_data = [];
            }
        }
        $lookup_by = [];
        if (array_key_exists($file_primary_column, $data)) {
            if ($file_primary_column=='products_id' && intval($data[$file_primary_column])==0){
                $lookup_by = $mkey_data;
            }else {
                $lookup_by = [$file_primary_column => $data[$file_primary_column]];
            }
        }else{
            $lookup_by = $mkey_data;
        }
        if ( count($lookup_by)==0 ){
            throw new EP\Exception('Primary key not found in file');
        }
        $file_primary_value = implode(' - ',$lookup_by);
        $productsModels = \common\models\Products::find()
            ->where($lookup_by)
            ->all();

        $found_rows = count($productsModels);
        if ($found_rows > 1) {
            unset($productsModels);
            // error data not unique
            $message->info('"'.$file_primary_value.'" not unique - found '.$found_rows.' rows. Skipped');
            return false;
        } elseif ($found_rows == 0) {
            unset($productsModels);
            $message->info('"'.$file_primary_value.'" not found. Skipped');
            return false;
        } else {

            /**
             * @var $productModel \common\models\Products
             **/
            $productModel = $productsModels[0];
            $db_main_data = $productModel->getAttributes();
            //$db_main_data = tep_db_fetch_array($get_main_data_r);
            $products_id = $db_main_data['products_id'];

            foreach ($export_columns as $file_column => $db_column) {
                if (!array_key_exists($file_column, $this->data)) continue;
                if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $products_id, $message));
                }
                if (!empty($db_column['calculated']) && !empty($db_column['lang_id']) && !empty($this->data[$file_column])
                     && substr($file_column, 0, 17) == 'old_seo_page_name'){

                    /** @var \common\extensions\SeoRedirectsNamed\SeoRedirectsNamed $ext */
                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')) {
                        $ext::appendChangedName('product', $products_id, $db_column['lang_id'], $db_column['platform_id'], $this->data[$file_column], '');
                    }
                }
            }
        }
        
        $this->entry_counter++;

        return true;
    }

    public function postProcess(Messages $message){

        $message->info('Processed '.$this->entry_counter.' SEO URLS');
        $message->info('Done');

        $this->EPtools->done('categories_old_seo_urls_import');
    }


}