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


use backend\models\EP\ArrayTransform;
use backend\models\EP\Exception;
use backend\models\EP\Messages;

class Documents extends ProviderAbstract implements ImportInterface, ExportInterface
{
    protected $product_data = array();
    protected $data = array();
    protected $rows_data = array();
    protected $entry_counter = 0;

    protected $export_query;
    protected $withFiles = false;

    function init()
    {
        parent::init();
        $this->initFields();
    }

    protected function initFields()
    {
        $this->fields = array();
        $this->fields[] = array('name' => 'products_model', 'calculated' => true, 'value' => 'Products Model', 'is_key' => true,);

        $this->fields[] = array('name' => 'document.document_types_name', 'calculated' => true, 'value' => 'Document Group Name',);
        $this->fields[] = array('name' => 'document.filename', 'calculated' => true, 'value' => 'Document Filename',);
        $this->fields[] = array('name' => 'document.is_link', 'calculated' => true, 'value' => 'Is Document External Link?',);
        $this->fields[] = array('name' => 'document.sort_order', 'calculated' => true, 'value' => 'Sort Order',);
        foreach( \common\helpers\Language::get_languages() as $_lang ) {
            $this->fields[] = array('name' => 'document.titles.'.$_lang['code'].'.title', 'calculated' => true, 'value' => 'Document title '.$_lang['code'],);
        }
//'',
//'document_url',

    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);

        $main_source = $this->main_source;

        $filter_sql = '';
        if ( is_array($filter) ) {
            $this->withFiles = ( isset($filter['with_images']) && $filter['with_images']);
            if ( isset($filter['products_id']) && is_array($filter['products_id']) && count($filter['products_id'])>0 ) {
                $filter_sql .= "AND p.products_id IN ('".implode("','", array_map('intval',$filter['products_id']))."') ";
            }
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                $filter_sql .= "AND p.products_id IN(SELECT products_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id IN('".implode("','",$categories)."')) ";
            }
        }
        $main_sql =
            "SELECT {$main_source['select']} p.products_id ".
            "FROM ".TABLE_PRODUCTS." p ".
            "  INNER JOIN ".TABLE_PRODUCTS_DOCUMENTS." pd ON pd.products_id=p.products_id ".
            "WHERE 1 {$filter_sql} ".
            "GROUP BY p.products_id ".
            "/*LIMIT 3*/";

        $this->export_query = tep_db_query( $main_sql );
    }

    public function exportRow()
    {
        $this->data = current($this->rows_data);
        if ( !$this->data ) {
            $this->product_data = tep_db_fetch_array($this->export_query);
            if (!is_array($this->product_data)) return $this->product_data;

            $this->rows_data = [];
            $product = \common\api\models\AR\Products::findOne($this->product_data['products_id']);
            $product_documents = $product->exportArray(['documents'=>['*'=>['*']]]);
            foreach ( $product_documents['documents'] as $product_document ){
                $document_row = ArrayTransform::convertMultiDimensionalToFlat(['document'=>$product_document]);
                $document_row['products_model'] = $product_documents['products_model'];
                $this->rows_data[] = $document_row;
            }
            $this->data = reset($this->rows_data);
        }
        next($this->rows_data);

        $export_columns = $this->export_columns;

        foreach( $export_columns as $db_key=>$export ) {
            if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_images_id']));
            }
        }

        if( $this->withFiles ) {
            $filesAdd = [];
            $documentFilename = rtrim(DIR_FS_CATALOG,'/').'/'.'documents/'.$this->data['document.filename'];
            if ( !$this->data['document.is_link'] && !empty($this->data['document.filename']) && is_file($documentFilename) ){
                $filesAdd[] = [
                    'filename' => $documentFilename,
                    'localname' => 'documents/'.$this->data['document.filename'],
                ];
            }

            if ( count($filesAdd)>0 ) {
                return [
                    ':feed_data' => $this->data,
                    ':attachments' => $filesAdd,
                ];
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

        if (!array_key_exists($file_primary_column, $this->data)) {
            throw new Exception('Primary key not found in file');
        }

        $file_primary_value = $this->data[$file_primary_column];

        if (!isset($this->rows_data[$file_primary_value])) {
            $this->processCollectedData();
            $this->rows_data = [];
            $this->rows_data[$file_primary_value] = [
                'model' => false,
                'documents' => [],
            ];
            $matchedProducts = \common\api\models\AR\Products::find()
                ->where([$file_primary_column=>$file_primary_value])
                ->all();
            if (count($matchedProducts) != 1) {
                // error data not unique
                $message->info('"' . $file_primary_value . '" not unique - found ' . count($matchedProducts) . ' rows. Skipped');
                return false;
            } else {
                $this->rows_data[$file_primary_value]['model'] = $matchedProducts[0];
            }
        }
        if ( !is_object($this->rows_data[$file_primary_value]['model']) ) return false;

        $doc_data = ArrayTransform::convertFlatToMultiDimensional($this->data);
        if ( !isset($doc_data['document']) || !is_array($doc_data['document']) ){

            return false;
        }

        if ( isset($doc_data['document']['filename']) && !empty($doc_data['document']['filename']) ) {
            if (!preg_match('/^[a-z]{3,4}:\/\//i', $doc_data['document']['filename']) ) {
                if (is_file($this->directoryObj->filesRoot() . 'documents/' . $doc_data['document']['filename'])) {
                    $doc_data['document']['document_url'] = $this->directoryObj->filesRoot() . 'documents/' . $doc_data['document']['filename'];
                }elseif (is_file($this->directoryObj->filesRoot() . $doc_data['document']['filename'])) {
                    $doc_data['document']['document_url'] = $this->directoryObj->filesRoot() . $doc_data['document']['filename'];
                }
            }
        }
        $this->rows_data[$file_primary_value]['documents'][] = $doc_data['document'];

        return true;
    }

    protected function processCollectedData()
    {
        if ( is_array($this->rows_data) && count($this->rows_data)>0 ) {
            foreach ($this->rows_data as $model=>$data){
                if ( !is_object($data['model']) ) continue;
                $data['model']->importArray(['documents'=>$data['documents']]);
                $data['model']->save();
                $this->entry_counter++;
            }
        }
    }

    public function postProcess(Messages $message)
    {
        $this->processCollectedData();

        $message->info('Processed '.$this->entry_counter.' products');

        $message->info('Done.');

    }


}