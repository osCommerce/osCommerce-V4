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

use backend\models\EP\Formatter;
use backend\models\EP;
use backend\models\EP\Messages;
use common\models\Reviews;
use common\models\ReviewsDescription;

class ProductReviews extends ProviderAbstract implements ImportInterface, ExportInterface
{

    /**
     * @var EP\Tools
     */
    protected $EPtools;
    protected $fields;
    public $data;

    protected $entry_counter = 0;
    protected $remove_counter = 0;

    function init()
    {
        parent::init();
        $this->initFields();

        $this->EPtools = new EP\Tools();
    }

    protected function initFields()
    {
        $this->fields = [];


        $model = new Reviews();
        $columnsSchema = $model->getTableSchema()->columns;
        $attributes = $model->attributes();
        $rename_attributes = [
            'status' => 'Is Approved',
            'new' => 'New Review',
        ];
        foreach ($attributes as $attribute){
            if ($attribute=='products_id') {
                $this->fields['products_model'] = array( 'name' => 'products_model', 'value' => 'Products Model', 'is_key'=>true );
                continue;
            }
            if ($attribute=='customers_id'){
                $this->fields[$attribute] = array('name' => $attribute, 'value' => 'Customers Email', 'get'=>'get_customer_email', 'set'=>'set_customers_id');
            }else {
                $label = isset($rename_attributes[$attribute])?$rename_attributes[$attribute]:$model->generateAttributeLabel($attribute);
                $this->fields[$attribute] = array('name' => $attribute, 'value' => $label,);
            }
            if ( isset($columnsSchema[$attribute]) ){
                $this->fields[$attribute]['type'] = $columnsSchema[$attribute]->phpType;
            }
            if ($attribute=='reviews_id') {
                $this->fields['_delete_row_'] = array( 'name' => '_delete_row_', 'value' => 'Delete?','calculated'=>true);
            }
        }

        $model = new ReviewsDescription();
        $columnsSchema = $model->getTableSchema()->columns;
        $attributes = $model->attributes();
        foreach ($attributes as $attribute){
            if ($attribute=='reviews_id') continue;
            if ($attribute=='languages_id') {
                $this->fields[$attribute] = array('name' => $attribute, 'value' => 'Language Code', 'get'=>'get_language_code', 'set'=>'set_language_id');
            }else {
                $this->fields[$attribute] = array('name' => $attribute, 'value' => $model->generateAttributeLabel($attribute),);
            }
            if ( isset($columnsSchema[$attribute]) ){
                $this->fields[$attribute]['type'] = $columnsSchema[$attribute]->phpType;
            }
        }

    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);

        $main_source = $this->main_source;
        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['products_id']) && is_array($filter['products_id']) && count($filter['products_id'])>0 ) {
                $filter_sql .= "AND p.products_id IN ('".implode("','", array_map('intval',$filter['products_id']))."') ";
            }
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                $filter_sql .= "AND i.prid IN(SELECT products_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id IN('".implode("','",$categories)."')) ";
            }
        }

        $this->export_query = (new \yii\db\Query())->from('products p')
            ->select(['p.products_model', 'c.customers_email_address', 'r.*', 'rd.*'])
            ->leftJoin(['r'=>Reviews::tableName()], 'r.products_id=p.products_id')
            ->leftJoin(['c'=>\common\models\Customers::tableName()], 'c.customers_id=r.customers_id')
            ->leftJoin(['rd'=>ReviewsDescription::tableName()], 'rd.reviews_id=r.reviews_id')
            ->where(['!=','p.products_model', ''])
            ->andWhere(['IS NOT','r.reviews_id', NULL])
            ->orderBy(new \yii\db\Expression('IF(r.products_id IS NULL,1,0)'))
            ->addOrderBy(['p.products_model'=>SORT_ASC])
            ->all()
//            ->batch(5)
        ;
    }

    public function exportRow()
    {
        $this->data = current($this->export_query);
        if ( !is_array($this->data) ) return false;
        next($this->export_query);

        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;
        foreach ( $data_sources as $source_key=>$source_data ) {
            if ( $source_data['table'] ) {
                $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ( $source_data['table']==TABLE_INVENTORY_PRICES  ) {
                    $data_sql .= "AND inventory_id='{$this->data['inventory_id']}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
                }else{
                    $data_sql .= "AND 1=0 ";
                }
                //echo $data_sql.'<hr>';
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
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_id']));
            }
        }

        return $this->data;
    }

    public function importRow($data, Messages $message)
    {
        $this->buildSources(array_keys($data));

        $this->data = $data;

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_column = $this->file_primary_column;

        if (!array_key_exists('reviews_id', $this->data) && !array_key_exists($file_primary_column, $this->data)) {
            throw new EP\Exception('Primary key not found in file');
        }
        $file_primary_value = $this->data[$file_primary_column];
        $reviews_id = isset($this->data['reviews_id'])?(int)$this->data['reviews_id']:0;

        $reviewModel = false;
        if ( $reviews_id ) {
            $reviewModel = Reviews::findOne($reviews_id);
        }
        if ( !$reviewModel ) {
            $matched_products = \common\models\Products::find()
                ->where([$file_primary_column => $file_primary_value])
                ->select(['products_id'])
                ->asArray()
                ->all();

            $this->data['products_id'] = 0;
            if (count($matched_products) != 1) {
                // error data not unique
                $message->info('"' . $file_primary_value . '" not unique - found ' . count($matched_products) . ' rows. Skipped');
                return false;
            } else {
                $this->data['products_id'] = $matched_products[0]['products_id'];
            }
        }

        if ( !empty($this->data['_delete_row_']) ){
            if ( $reviewModel ) {
                $reviewModel->delete();
                $this->remove_counter++;
            }
            return;
        }

        if ( !$reviewModel ) {
            $reviewModel = new Reviews();
            $reviewModel->loadDefaultValues();
        }

        foreach ($main_source['columns'] as $file_column => $db_column) {
            if (!array_key_exists($file_column, $this->data)) continue;

            if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['products_id'], $message));
            }
            if (isset($export_columns[$file_column]['type'])){
                $data_key = $file_column;
                if ( array_key_exists($data_key, $this->data) ) {
                    if ($export_columns[$file_column]['type'] == 'integer') {
                        $this->data[$data_key] = intval($this->data[$data_key]);
                    }
                }
            }
        }
        $reviewModel->setAttributes($this->data,false);
        if ($reviewModel->save()){
            $reviewModel->reviews_id;
            if ( !$this->data['languages_id'] ) $this->data['languages_id'] = \common\helpers\Language::get_default_language_id();
            $Descriptions = ReviewsDescription::find()
                ->where(['reviews_id'=>$reviewModel->reviews_id, 'languages_id' => $this->data['languages_id']])
                ->all();
            if ( count($Descriptions)==0 ){
                $Description = new ReviewsDescription([
                    'reviews_id' => $reviewModel->reviews_id,
                    'languages_id' => $this->data['languages_id'],
                ]);
                $Description->setAttributes($this->data,false);
                $Description->save();
            }else{
                $Descriptions[0]->setAttributes($this->data,false);
                $Descriptions[0]->save();
            }
        }else{
            $message->info('"' . $file_primary_value . '" Error: "'.implode('", "',$reviewModel->getErrors()).'"');
            return false;
        }
        $this->entry_counter++;
        return true;
    }

    public function postProcess(Messages $message)
    {
        $message->info('Processed '.$this->entry_counter.' product reviews');
        if ( $this->remove_counter ){
            $message->info('Deleted '.$this->remove_counter.' product reviews');
        }
        $message->info('Done.');
    }

    protected function get_customer_email($field_data, $products_id)
    {
        return $this->data['customers_email_address'];
    }

    protected function set_customers_id( $field_data, $products_id, $message )
    {
        $customers_email = $this->data[$field_data['name']];

        $customers_id = \common\models\Customers::find()
            ->select(['customers_id'])
            ->where(['customers_email_address'=>$customers_email])
            ->orderBy(['opc_temp_account'=>SORT_ASC])
            ->scalar();
        if ( !$customers_id && is_object($message) && $message instanceof Messages) {
            $message->info("* Customer '".$customers_email."' not found");
        }
        $this->data[$field_data['name']] = (int)$customers_id;
    }

    protected function get_language_code($field_data, $products_id)
    {
        return \common\classes\language::get_code($this->data[$field_data['name']]);
    }

    protected function set_language_id($field_data, $products_id, $message)
    {
        $language_code = $this->data[$field_data['name']];
        $language_array = \common\helpers\Language::get_language_id($language_code);
        if ( is_array($language_array) ) {
            $this->data[$field_data['name']] = intval($language_array['languages_id']);
        }else{
            $this->data[$field_data['name']] = intval(\common\helpers\Language::get_default_language_id());
        }
    }
}