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
use common\classes\Images as CommonImages;
use common\api\models\AR\Manufacturer;

class Brands extends ProviderAbstract implements ImportInterface, ExportInterface
{
    protected $data = array();
    protected $EPtools;

    protected $entry_counter = 0;

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

    protected function initFields()
    {
        $this->fields = array();
        $this->fields[] = array( 'name' => 'key_field', 'value' => 'KEY_FIELD', 'is_key'=>true, 'calculated'=>true, 'get'=>'get_key_field' );
        $dummy = new Manufacturer();
        $attr = $dummy->getPossibleKeys();
        $columnCover = [
            'manufacturers_id' => false,
            'date_added' => false,
            'last_modified' => false,
            'manufacturers_image_data' => false,
            'manufacturers_image_source_url' => false,
            'manufacturers_old_seo_page_name' => ['value' => 'Old Seo Page Name'],
        ];
        foreach ( $attr as $key ) {
            $columnDescribe = array( 'name' => $key, 'value' => ucwords(preg_replace('/[ \._]/',' ',$key)), );
            if ( isset($columnCover[$key]) ) {
                if ($columnCover[$key]==false) continue;
                if (is_array($columnCover[$key])) {
                    $columnDescribe = array_merge($columnDescribe, $columnCover[$key]);
                }
            }
            $this->fields[] = $columnDescribe;
        }
    }

    function get_key_field( $field_data, $id ){
        return $id;
    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);

        $main_source = $this->main_source;

        $filter_sql = '';
        if ( is_array($filter) ) {
            $this->withImages = ( isset($filter['with_images']) && $filter['with_images']);
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);

                $get_categories_brands_r = tep_db_query(
                    "SELECT DISTINCT p.manufacturers_id ".
                    "FROM ".TABLE_PRODUCTS." p ".
                    "  INNER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ON p2c.products_id=p.products_id AND p2c.categories_id IN('".implode("','",$categories)."') ".
                    "WHERE (p.manufacturers_id IS NOT NULL OR p.manufacturers_id!=0) "
                );
                if ( tep_db_num_rows($get_categories_brands_r)>0 ) {
                    $brand_ids = [];
                    while($_categories_brand = tep_db_fetch_array($get_categories_brands_r)){
                        $brand_ids[] = $_categories_brand['manufacturers_id'];
                    }
                    $filter_sql .= "AND manufacturers_id IN('" . implode("','", $brand_ids) . "') ";
                }else{
                    $filter_sql .= "AND 1=0 ";
                }
            }
        }

        $main_sql =
            "SELECT manufacturers_id " .
            "FROM " . TABLE_MANUFACTURERS . " " .
            "WHERE 1 {$filter_sql} ".
            "ORDER BY manufacturers_name";

        $this->export_query = tep_db_query($main_sql);
    }

    public function exportRow()
    {
        $this->data = tep_db_fetch_array($this->export_query);
        if ( !is_array($this->data) ) return $this->data;

        //$data_sources = $this->data_sources;
        $export_columns = $this->export_columns;

        $dataObject = Manufacturer::findOne(['manufacturers_id'=>$this->data['manufacturers_id']]);
        $objectMultiData = $dataObject->exportArray([]);
        $objectFlatData = EP\ArrayTransform::convertMultiDimensionalToFlat($objectMultiData);
        $this->data = array_merge($this->data, $objectFlatData);

        foreach( $export_columns as $db_key=>$export ) {
            if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['manufacturers_id']));
            }
            $this->data[$db_key] = isset($this->data[$db_key])?$this->data[$db_key]:'';
        }

        if( $this->withImages ) {
            $filesAdd = [];

            foreach (['manufacturers_image',] as $imageColumn) {
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

        $this->data = $data;
        $multi_data = EP\ArrayTransform::convertFlatToMultiDimensional($this->data);

        $brandModel = Manufacturer::findOne(['manufacturers_id'=>$this->data['key_field']]);
        if ( !$brandModel ) {

            $brandModel =  Manufacturer::findOne(['manufacturers_name' => $multi_data['manufacturers_name']]);
            if ( !empty($brandModel) ){
                $message->info('Duplicate name. Skipped');
                return false;
            }

            $brandModel = new Manufacturer();
            $brandModel->loadDefaultValues();
            unset($multi_data['key_field']);
        }

        if ( !empty($multi_data['manufacturers_image']) ) {
            if (preg_match('/^https?:\/\//', $multi_data['manufacturers_image'])) {
                // download remote images
                $multi_data['manufacturers_image_source_url'] = $multi_data['manufacturers_image'];
                $multi_data['manufacturers_image'] = basename($multi_data['manufacturers_image_source_url']);
            } elseif ($this->import_folder && is_dir($this->import_folder) && is_file($this->import_folder . $multi_data['manufacturers_image'])) {
                $multi_data['manufacturers_image_source_url'] = $this->import_folder . $multi_data['manufacturers_image'];
            } elseif (is_file(\common\classes\Images::getFSCatalogImagesPath() . 'import/' . $multi_data['manufacturers_image'])) {
                $multi_data['manufacturers_image_source_url'] = \common\classes\Images::getFSCatalogImagesPath() . 'import/' . $multi_data['manufacturers_image'];
            } elseif (is_file(\common\classes\Images::getFSCatalogImagesPath() . $multi_data['manufacturers_image'])) {
                $multi_data['manufacturers_image_source_url'] = \common\classes\Images::getFSCatalogImagesPath() . $multi_data['manufacturers_image'];
            }
        }

        $brandModel->importArray($multi_data);
        if ($brandModel->save(false)) {
            $this->entry_counter++;
        }
    }

    public function postProcess(Messages $message)
    {
        $message->info('Brands processed: ' . $this->entry_counter);
        $message->info('Done');
    }

}