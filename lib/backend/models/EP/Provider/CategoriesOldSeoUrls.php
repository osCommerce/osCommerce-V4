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

class CategoriesOldSeoUrls extends ProviderAbstract implements ImportInterface, ExportInterface
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
        $this->fields[] = array( 'name' => 'key_field', 'value' => 'KEY_FIELD', 'is_key'=>true, 'calculated'=>true, 'get'=>'get_key_field' );

        foreach( \common\helpers\Language::get_languages() as $_lang ) {
            $data_descriptor = '%|' . TABLE_SEO_REDIRECTS_NAMED . '|' . $_lang['id'] . '|0';
            $this->fields[] = array(
                //'data_descriptor' => $data_descriptor,
                //'column_db' => 'old_seo_page_name',
                'calculated' => true,
                'lang_id' => $_lang['id'],
                'name' => 'old_seo_page_name_' . $_lang['id'] . '_0',
                'value' => 'Old SEO URL ' . $_lang['code'],
            );
        }

        foreach( \common\helpers\Language::get_languages() as $_lang ) {
            $data_descriptor = '%|' . TABLE_CATEGORIES_DESCRIPTION . '|' . $_lang['id'] . '|0';
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'categories_name',
                'name' => 'categories_name_' . $_lang['code'] . '_0',
                'value' => 'Categories Name ' . $_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'categories_seo_page_name',
                'name' => 'categories_seo_page_name_' . $_lang['code'] . '_0',
                'value' => 'Categories SEO page name (URL) ' . $_lang['code'],
            );
        }

    }

    function get_key_field( $field_data, $categories_id ){
        return $this->EPtools->tep_get_categories_full_path((int)$this->data['categories_id']);
    }

    public function prepareExport($useColumns, $filter){
        $this->buildSources($useColumns);
        
        $main_source = $this->main_source;
        
        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                //$filter_sql .= "AND categories_id IN('".implode("','",$categories)."') ";
            }
        }
/*
        $main_sql =
            "SELECT {$main_source['select']} categories_id " .
            "FROM " . TABLE_CATEGORIES . " " .
            "WHERE 1 {$filter_sql} ".
            "ORDER BY categories_left";

        $this->export_query = tep_db_query($main_sql);
 *
 */
        $q = \common\models\Categories::find()
            ->select("{$main_source['select']} categories_id, categories_left ")
            ->addOrderBy('categories_left')
            ;
        if ( is_array($categories) && !empty($categories)) {
          $q->andWhere(['categories_id' => $categories]);
        }
        $q->with('seoRedirects')->asArray();
        $this->export_query = $q->all();
        for ($n=count($this->export_query); $n>=0; $n--) {
            if (!empty($this->export_query[$n]['seoRedirects'])) {
                $row = [];
                $lngGroups = \yii\helpers\ArrayHelper::index($this->export_query[$n]['seoRedirects'], null, 'language_id');
                $maxRedirects = 0;
                unset($this->export_query[$n]['seoRedirects']);
                foreach($lngGroups as $lid => $data) {
                    $maxRedirects = max($maxRedirects, count($data));
                    $row['old_seo_page_name_' . $lid . '_0'] = $data[0]['old_seo_page_name'];
                    $this->export_query[$n] += $row;
                }

                if ($maxRedirects>1) {
                    //add extra rows with the same category id
                    for($i=1; $i<$maxRedirects; $i++) {
                        $row = [];
                        foreach($lngGroups as $lid => $data) {
                            if (!empty($data[$i])) {
                                $row['old_seo_page_name_' . $lid . '_0'] = $data[$i]['old_seo_page_name'];
                            }
                        }
                        $this->export_query[] = [
                              'categories_id' => $this->export_query[$n]['categories_id'],
                              'categories_left' => $this->export_query[$n]['categories_left']                              
                            ] + $row;

                    }

                }
            }
        }
        \yii\helpers\ArrayHelper::multisort($this->export_query, 'categories_left');

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
                if ( $source_data['table']==TABLE_CATEGORIES_DESCRIPTION ) {
                    $data_sql .= "AND categories_id='{$this->data['categories_id']}' AND language_id='{$source_data['params'][0]}' AND affiliate_id='{$source_data['params'][1]}'";
                } elseif ( $source_data['table']==TABLE_SEO_REDIRECTS_NAMED ) {
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

        //echo '<pre>'; var_dump($data); echo '</pre>';
        $this->data = $data;
        $is_updated = false;

        if (!array_key_exists($file_primary_column, $data)) {
            throw new EP\Exception('Primary key not found in file');
        }
        $file_primary_value = $data[$file_primary_column];
        $category_id = $this->EPtools->tep_get_categories_by_name($file_primary_value, $message);
        if ( empty($category_id) ) {
            $message->info('Invalid KEY value');
            return false;
        }

        $get_main_data_r = tep_db_query(
            "SELECT * FROM " . TABLE_CATEGORIES . " WHERE categories_id='" . (int)$category_id . "'"
        );
        $found_rows = tep_db_num_rows($get_main_data_r);
        if ($found_rows == 0) {
            // dummy
            $message->info('Category not found. Skipped'); // N/A message
            return false;
        }else{
            $db_main_data = tep_db_fetch_array($get_main_data_r);
            $categories_id = $db_main_data['categories_id'];

            foreach ($export_columns as $file_column => $db_column) {
                if (!array_key_exists($file_column, $this->data)) continue;
                if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $categories_id, $message));
                }
                if (!empty($db_column['calculated']) && !empty($db_column['lang_id']) && !empty($this->data[$file_column])
                     && substr($file_column, 0, 17) == 'old_seo_page_name'){

                    /** @var \common\extensions\SeoRedirectsNamed\SeoRedirectsNamed $ext */
                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')) {
                        $ext::appendChangedName('category', $categories_id, $db_column['lang_id'], null, $this->data[$file_column], '');
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