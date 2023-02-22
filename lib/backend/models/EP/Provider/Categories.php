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
use common\classes\Images as CommonImages;

class Categories extends ProviderAbstract implements ImportInterface, ExportInterface
{

    protected $data = array();
    protected $EPtools;
    protected $entry_counter = 0;
    
    protected $export_query;

    protected $import_folder = '';

    protected $withImages = false;

    function init()
    {
        parent::init();
        $this->initFields();
        $this->EPtools = new EP\Tools();

        if ( $this->directoryObj ) {
            $this->setImagesDirectory($this->directoryObj->filesRoot(EP\Directory::TYPE_IMAGES));
        }
    }

    public function setImagesDirectory($imagesFolder)
    {
        $this->import_folder = $imagesFolder;
    }

    protected function initFields(){
        $this->fields = array();
        $this->fields[] = array( 'name' => 'key_field', 'value' => 'KEY_FIELD', 'is_key'=>true, 'calculated'=>true, 'get'=>'get_key_field' );
        $this->fields[] = array( 'name' => 'categories_image', 'value' => 'Categories Image' );
        $this->fields[] = array( 'name' => 'categories_image_2', 'value' => 'Categories Image (Hero image)' );
        $this->fields[] = array( 'name' => 'categories_image_3', 'value' => 'Categories Image (Homepage image)' );
        $this->fields[] = array( 'name' => 'sort_order', 'value' => 'Categories Sort Order' );
        $this->fields[] = array( 'name' => 'categories_status', 'value' => 'Categories Status' );
        $this->fields[] = array( 'name' => 'show_on_home', 'value' => 'Show on homepage' );
        $this->fields[] = array(
            'name' => 'categories_old_seo_page_name',
            'value' => 'Old SEO page name (URL)',
        );


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
            /*      $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'categories_old_seo_page_name',
                    'name' => 'categories_old_seo_page_name_' . $_lang['code'] . '_0',
                    'value' => 'Old SEO page name (URL) ' . $_lang['code'],
                  );*/

            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'categories_description',
                'name' => 'categories_description_' . $_lang['code'] . '_0',
                'value' => 'Categories Description ' . $_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'categories_head_title_tag',
                'name' => 'categories_head_title_tag_' . $_lang['code'] . '_0',
                'value' => 'Meta Title Tag ' . $_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'categories_head_keywords_tag',
                'name' => 'categories_head_keywords_tag_' . $_lang['code'] . '_0',
                'value' => 'Meta Keywords Tag ' . $_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'categories_head_desc_tag',
                'name' => 'categories_head_desc_tag_' . $_lang['code'] . '_0',
                'value' => 'Meta Description Tag ' . $_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'categories_h1_tag',
                'name' => 'categories_h1_tag_' . $_lang['code'] . '_0',
                'value' => 'Categories H1 tag ' . $_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'categories_h2_tag',
                'name' => 'categories_h2_tag_' . $_lang['code'] . '_0',
                'value' => 'Categories H2 tags ' . $_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'categories_h3_tag',
                'name' => 'categories_h3_tag_' . $_lang['code'] . '_0',
                'value' => 'Categories H3 tags ' . $_lang['code'],
            );
        }

        foreach(\common\classes\platform::getList() as $platformInfo){
            $this->fields[] = array(
                'data_descriptor' => '@|assigned_platforms|0',
                'name' => 'platform_assign_'.$platformInfo['id'],
                'value' => 'Platform - ' . $platformInfo['text'],
                'get' => 'get_platform',
                'set' => 'set_platform',
            );
        }

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
            if ( $ext::select() ){
                foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                    $this->fields[] = array(
                        'data_descriptor' => '@|assigned_customer_groups|0',
                        'name' => 'customer_group_assigned_' . $groups_data['groups_id'],
                        'value' => 'Assign to customer group - ' . $groups_data['groups_name'],
                        'get' => 'get_products_group_assign', 'set' => 'set_products_group_assign',
                    );
                }
            }
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
            $this->withImages = ( isset($filter['with_images']) && $filter['with_images']);
            if ( isset($filter['products_id']) && is_array($filter['products_id']) && count($filter['products_id'])>0 ) {
                $product_categories_ids = \common\models\Products2Categories::find()
                    ->where(['IN', 'products_id', array_map('intval',$filter['products_id'])])
                    ->select(['categories_id'])->distinct()
                    ->asArray()->column();
                if ( count($product_categories_ids)>0 ) {
                    $filter_product_categories = [];
                    foreach ($product_categories_ids as $product_categories_id) {
                        $filter_product_categories[] = $product_categories_id;
                        \common\helpers\Categories::get_parent_categories($filter_product_categories, $product_categories_id);
                    }
                }
                $filter_sql .= "AND categories_id IN ('".implode("','",$filter_product_categories)."') ";
            }
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                $filter_sql .= "AND categories_id IN('".implode("','",$categories)."') ";
            }
        }

        $main_sql =
            "SELECT {$main_source['select']} categories_id " .
            "FROM " . TABLE_CATEGORIES . " " .
            "WHERE 1 {$filter_sql} ".
            "ORDER BY categories_left";

        $this->export_query = tep_db_query($main_sql);
    }

    public function exportRow(){
        $this->data = tep_db_fetch_array($this->export_query);
        if ( !is_array($this->data) ) return $this->data;

        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;
        
        foreach ( $data_sources as $source_key=>$source_data ) {
            if ( $source_data['table'] ) {
                $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ( $source_data['table']==TABLE_CATEGORIES_DESCRIPTION ) {
                    $data_sql .= "AND categories_id='{$this->data['categories_id']}' AND language_id='{$source_data['params'][0]}' AND affiliate_id='{$source_data['params'][1]}'";
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

        if( $this->withImages ) {
            $filesAdd = [];

            foreach (['categories_image', 'categories_image_2', 'categories_image_3'] as $imageColumn) {
                if (empty($this->data[$imageColumn])) continue;
                $fsImageName = CommonImages::getFSCatalogImagesPath() . $this->data[$imageColumn];
                if (!is_file($fsImageName)) continue;

                $filesAdd[] = [
                    'filename' => $fsImageName,
                    'localname' => 'images/' . $this->data[$imageColumn],
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

        $image_target_path = \common\classes\Images::getFSCatalogImagesPath();
        $image_path_import = \common\classes\Images::getFSCatalogImagesPath(). 'import/';
        if ( $this->import_folder && is_dir($this->import_folder) ) {
            $image_path_import = $this->import_folder;
        }

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
            $update_data_array = array();
            foreach ($main_source['columns'] as $file_column => $db_column) {
                if (!array_key_exists($file_column, $this->data)) continue;
                if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $categories_id, $message));
                }
                if (($file_column == 'categories_image' || $file_column == 'categories_image_2' || $file_column == 'categories_image_3') && strlen($this->data[$file_column])>0){
                    $target_filename = $image_target_path . $this->data[$file_column];
                    if (!is_file($target_filename)){
                        if (is_file($image_path_import . $this->data[$file_column])){
                            if ( !is_dir( dirname($target_filename) ) ) {
                                try{
                                    FileHelper::createDirectory(dirname($target_filename),0777);
                                }catch (\Exception $ex){}
                            }
                            if (@copy($image_path_import . $this->data[$file_column], $target_filename)){
                                @chmod($target_filename,0666);
                            }
                        } else {
                            $message->info($file_primary_value . ' category image "'.\common\helpers\Output::output_string($this->data[$file_column]).'" not found');
                            $this->data[$file_column] = '';
                        }
                    }
                }
                $update_data_array[$db_column] = $this->data[$file_column];
            }
            //echo '<pre>'; var_dump($update_data_array); echo '</pre>';
            if (count($update_data_array) > 0) {
                tep_db_perform(TABLE_CATEGORIES, $update_data_array, 'update', "categories_id='" . (int)$categories_id . "'");
                $is_updated = true;
            }
        }
        //$entry_counter++;
        $this->data['categories_id'] = $categories_id;

        foreach ($data_sources as $source_key => $source_data) {
            if ($source_data['table']) {

                $new_data = array();
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (!array_key_exists($file_column, $this->data)) continue;
                    $new_data[$db_column] = $this->data[$file_column];
                }
                if (count($new_data) == 0) continue;

                $data_sql = "SELECT {$source_data['select_raw']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ($source_data['table'] == TABLE_CATEGORIES_DESCRIPTION) {
                    $update_pk = "categories_id='{$categories_id}' AND language_id='{$source_data['params'][0]}' AND affiliate_id='{$source_data['params'][1]}'";
                    $insert_pk = array('categories_id' => $categories_id, 'language_id' => $source_data['params'][0], 'affiliate_id' => $source_data['params'][1]);
                    $data_sql .= "AND {$update_pk}";

                    if (empty($new_data['categories_seo_page_name'])) {
                        $new_data['categories_seo_page_name'] = \common\helpers\Seo::makeSlug($new_data['categories_name']);
                    }
                    /*
                    if (empty($new_data['products_seo_page_name']) && !empty($new_data['products_name']) ) {
                      $new_data['products_seo_page_name'] = \common\helpers\Seo::makeSlug($new_data['products_name']);
                    }
                    if (empty($new_data['products_seo_page_name'])) {
                      $new_data['products_seo_page_name'] = \common\helpers\Seo::makeSlug($new_data['products_model']);
                    }
                    if (empty($new_data['products_seo_page_name'])) {
                      $new_data['products_seo_page_name'] = $products_id;
                    }
                    */
//            if (empty($new_data['categories_seo_page_name'])) {
//              $new_data['categories_seo_page_name'] = $products_id;
//            }
                } else {
                    continue;
                }
                //echo $data_sql.'<hr>';
                $data_sql_r = tep_db_query($data_sql);
                if (tep_db_num_rows($data_sql_r) > 0) {
                    $_old_data = tep_db_fetch_array($data_sql_r);
                    tep_db_free_result($data_sql_r);
                    //echo '<pre>update rel '; var_dump($source_data['table'],$new_data,'update', $update_pk); echo '</pre>';
                    tep_db_perform($source_data['table'], $new_data, 'update', $update_pk);
                    if ( $source_data['table']==TABLE_CATEGORIES_DESCRIPTION ) {
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')) {
                            $ext::trackCategoryLinks($categories_id, $insert_pk['language_id'], null, $new_data, $_old_data);
                        }
                    }
                } else {
                    //echo '<pre>insert rel '; var_dump($source_data['table'],array_merge($new_data,$insert_pk)); echo '</pre>';
                    tep_db_perform($source_data['table'], array_merge($new_data, $insert_pk));
                }
                $is_updated = true;
            } elseif ($source_data['init_function'] && method_exists($this, $source_data['init_function'])) {
                call_user_func_array(array($this, $source_data['init_function']), $source_data['params']);
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['categories_id'], $message));
                    }
                }
            }
        }

        if ($is_updated) {
            //-- products_seo_page_name
            tep_db_perform(TABLE_CATEGORIES, array(
                'last_modified' => 'now()',
            ), 'update', "categories_id='" . (int)$categories_id . "'");
        }
        $this->entry_counter++;

        return true;
    }

    public function postProcess(Messages $message){

        $message->info('Processed '.$this->entry_counter.' categories');
        $message->info('Done');

        $this->EPtools->done('categories_import');
    }

    function assigned_platforms()
    {
        if ( !isset($this->data['assigned_platforms']) ) {
            $this->data['assigned_platforms'] = [];
            $get_assigned_r = tep_db_query("SELECT platform_id FROM ".TABLE_PLATFORMS_CATEGORIES." WHERE categories_id='".$this->data['categories_id']."'");
            if ( tep_db_num_rows($get_assigned_r)>0 ){
                while($_assigned = tep_db_fetch_array($get_assigned_r)){
                    $this->data['assigned_platforms'][ (int)$_assigned['platform_id'] ] = (int)$_assigned['platform_id'];
                }
            }
        }
    }

    function get_platform($field_data)
    {
        $idx = intval(str_replace('platform_assign_','', $field_data['name']));
        $this->data[$field_data['name']] = isset($this->data['assigned_platforms'][$idx])?'1':'';
        return $this->data[$field_data['name']];
    }

    function set_platform($field_data, $categories_id)
    {
        if ( empty($categories_id) || !array_key_exists($field_data['name'], $this->data) ) return;

        $platform_id = intval(str_replace('platform_assign_','', $field_data['name']));
        $fileValue = 0;
        if (is_numeric($this->data[$field_data['name']])){
            $fileValue = intval($this->data[$field_data['name']])?1:0;
        }elseif ( !empty($this->data[$field_data['name']]) && in_array(strtolower($this->data[$field_data['name']]),['y','yes','true','1']) ) {
            $fileValue = 1;
        }

        if ( isset($this->data['assigned_platforms'][$platform_id]) ) {
            if (!$fileValue) {
                tep_db_query("DELETE FROM ".TABLE_PLATFORMS_CATEGORIES." WHERE platform_id='".(int)$platform_id."' AND categories_id='".(int)$categories_id."'");
            }
        }else{
            if ($fileValue) {
                tep_db_query("INSERT IGNORE INTO ".TABLE_PLATFORMS_CATEGORIES." (platform_id, categories_id) VALUES('".(int)$platform_id."', '".(int)$categories_id."')");
            }
        }
    }

    function assigned_customer_groups()
    {
        if ( !isset($this->data['assigned_customer_groups']) && \Yii::$app->db->schema->getTableSchema('groups_categories')) {
            $this->data['assigned_customer_groups'] = [];
            $get_assigned_r = tep_db_query("SELECT groups_id FROM groups_categories WHERE categories_id='".$this->data['categories_id']."'");
            if ( tep_db_num_rows($get_assigned_r)>0 ){
                while($_assigned = tep_db_fetch_array($get_assigned_r)){
                    $this->data['assigned_customer_groups'][ (int)$_assigned['groups_id'] ] = (int)$_assigned['groups_id'];
                }
            }
        }
    }

    function get_products_group_assign($field_data)
    {
        $idx = intval(str_replace('customer_group_assigned_','', $field_data['name']));
        $this->data[$field_data['name']] = isset($this->data['assigned_customer_groups'][$idx])?'1':'';
        return $this->data[$field_data['name']];
    }

    function set_products_group_assign($field_data, $categories_id)
    {
        if ( empty($categories_id) || !array_key_exists($field_data['name'], $this->data)) return;

        $groups_id = intval(str_replace('customer_group_assigned_','', $field_data['name']));
        $fileValue = 0;
        if (is_numeric($this->data[$field_data['name']])){
            $fileValue = intval($this->data[$field_data['name']])?1:0;
        }elseif ( !empty($this->data[$field_data['name']]) && in_array(strtolower($this->data[$field_data['name']]),['y','yes','true','1']) ) {
            $fileValue = 1;
        }

        if (!\Yii::$app->db->schema->getTableSchema('groups_categories')) return;
        if ( isset($this->data['assigned_customer_groups'][$groups_id]) ) {
            if (!$fileValue) {
                tep_db_query("DELETE FROM groups_categories WHERE groups_id='".(int)$groups_id."' AND categories_id='".(int)$categories_id."'");
            }
        }else{
            if ($fileValue) {
                tep_db_query("INSERT IGNORE INTO groups_categories (groups_id, categories_id) VALUES('".(int)$groups_id."', '".(int)$categories_id."')");
            }
        }
    }

}