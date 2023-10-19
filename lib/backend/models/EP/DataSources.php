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

namespace backend\models\EP;


use yii\helpers\FileHelper;

class DataSources
{
    public static $sourcePath = '\\backend\\models\\EP\\Datasource\\';

    static public function getAvailableList()
    {
        static $list = false;

        if ( !is_array($list) ) {
            $list = [];
            try {
                foreach( FileHelper::findFiles(dirname(__FILE__).'/Datasource/', ['recursive' => false, 'only'=>['pattern'=>'*.php']]) as $file){
                    $className = pathinfo($file,PATHINFO_FILENAME);
                    $instance = \Yii::createObject( static::$sourcePath . $className);
                    if ( is_object($instance) && $instance instanceof DatasourceBase ) {
                        $list[] = [
                            'class' => $className,
                            'className' => static::$sourcePath . $className,
                            'name' => $instance->getName(),
                        ];
                    }
                }
                $list = array_merge($list, \common\helpers\Acl::getExtensionEpDataSources());
            }catch(\Exception $ex){
                \Yii::warning('Error in DataSources::getAvailableList: ' . $ex->getMessage() . "\n" . $ex->getTraceAsString());
            }
        }

        return $list;
    }
    
    /** @var array $condition like ['class' => 'PdfCatalogues']*/
    static private function findAvailable(array $condition)
    {
        foreach(self::getAvailableList() as $dataSource)
        {
            $matched = false;
            foreach($condition as $key => $value) {
                $matched = $dataSource[$key] == $value;
                if (!$matched) {
                    break;
                }
            }
            if ($matched) {
                return $dataSource;
            }
        }
    }

    static public function add($data)
    {
        tep_db_perform('ep_datasources',[
            'code' => $data['name'],
            'class' => $data['class'],
        ]);
        $dsRoot = Directory::loadById(5);
        FileHelper::createDirectory(  $dsRoot->filesRoot().$data['name'],0777);
        $dsRoot->synchronizeDirectories(false);
        $get_created_id_r = tep_db_query(
            "SELECT directory_id FROM ".TABLE_EP_DIRECTORIES." WHERE directory='".tep_db_input($data['name'])."' AND parent_id=5"
        );
        if ( tep_db_num_rows($get_created_id_r)>0 ) {
            $get_created_id = tep_db_fetch_array($get_created_id_r);
            $createdDir = Directory::loadById($get_created_id['directory_id']);

            FileHelper::createDirectory(  $createdDir->filesRoot().'processed',0777);
            $createdDir->synchronizeDirectories(false);
        }
    }

    static public function remove($name){
        tep_db_query("DELETE FROM ep_datasources WHERE code='".tep_db_input($name)."'");
    }

    static public function getByName($name)
    {
        $datasource = false;
        $get_data_r = tep_db_query("SELECT * FROM ep_datasources WHERE code='".tep_db_input($name)."'");

        if ( tep_db_num_rows($get_data_r)>0 ) {
            $data = tep_db_fetch_array($get_data_r);
            $availableDS = self::findAvailable(['class' => $data['class']]);
            if ($availableDS) {
                $datasource = \Yii::createObject([
                    'class' => $availableDS['className'],
                    'code' => $data['code'],
                    'settings' => $data['settings'],
                ]);
            }
        }
        return $datasource;
    }

    static public function getActiveByClass($name)
    {
        $datasources = [];
        $get_data_r = tep_db_query(
            "SELECT ds.*, ed.directory_id ".
            "FROM ep_datasources ds ".
            " INNER JOIN ep_directories ed ON ed.directory_type='datasource' AND ds.code=ed.directory ".
            "WHERE ds.class='".tep_db_input($name)."'"
        );

        if ( tep_db_num_rows($get_data_r)>0 ) {
            while ($data = tep_db_fetch_array($get_data_r)) {
                $availableDS = self::findAvailable(['class' => $name]);
                if ($availableDS) {
                    $datasources[$data['directory_id']] = \Yii::createObject([
                        'class' => $availableDS['className'],
                        'code' => $data['code'],
                        'settings' => $data['settings'],
                    ]);
                }
            }
        }
        return $datasources;
    }

    static public function orderView($orderId)
    {
        if ( empty($orderId) ) return [];
        
        \common\helpers\Translation::init('admin/easypopulate');

        $result = [];
        $get_any_sources_r = tep_db_query(
            "SELECT * ".
            "FROM ep_holbi_soap_link_orders ".
            "WHERE local_orders_id='".$orderId."' and cfg_export_as = 'order' ".
            "ORDER BY ep_directory_id"
        );
        if ( tep_db_num_rows($get_any_sources_r)>0 ) {
            while( $_source = tep_db_fetch_array($get_any_sources_r) ){
              if ( $_source['remote_orders_id'] == 0 ) {
                continue;
              }
              try {
                $directory = Directory::findById($_source['ep_directory_id']);
                /**
                 * @var \backend\models\EP\Directory $directory
                 */
                if (empty($directory->getDatasource())) {
                  continue; // in case of deleted datasources
                }
                $_moduleOrderView = $directory->getDatasource()->orderView($orderId);
                if ( $_moduleOrderView===false ) continue;
                $_source['datasourceName'] = $directory->getDatasource()->getName();
                $_source['directory'] = $directory;
                $result[] = $_source;
              } catch (\Exception $e) { } // not important - none details available any more
            }
        }

        $info = '';
        if ( count($result)>0 ) {
            foreach ($result as $item) {
                $info .= '<div class="cr-ord-cust cr-ord-cust-datasource" id="jsBlkExchangeInfo' . $item['ep_directory_id'] . '"><span>' . $item['directory']->directory . '</span>';
                if ( $item['remote_orders_id'] > 0 ) {
                  $remoteId = $item['remote_orders_id'];
                  if (!empty($item['remote_order_number'])) {
                    $remoteId = $item['remote_order_number'];
                  } elseif (!empty($item['remote_guid']) && $remoteId==$orderId) {
                    $remoteId = $item['remote_guid'];
                  }

                    $exportDate = $item['date_exported']>2000?\common\helpers\Date::datetime_short($item['date_exported']):'';
                    $info .= '<div>';
                    if (method_exists($directory->getDatasource(), 'getOrderLink')) {
                        $info .= TEXT_EXTERNAL_ORDERS_ID . ' ' . $directory->getDatasource()->getOrderLink($remoteId) . '<br />';
                    } else {
                        $info .= TEXT_EXTERNAL_ORDERS_ID . ' ' . $remoteId . '<br />';
                    }
                    if( $exportDate ){
                        $info .= TEXT_DATE_ADDED . ' ' . $exportDate;
                    }
                    $info .= '</div>';
                } elseif ( $item['remote_orders_id'] == -1) {
                  $info .= TEXT_DISABLED;
                }
                
                $info .= '</div>';
            }
        }

        return $info;
    }

    static public function orderViewExport($orderId)
    {
        if ( empty($orderId) ) return [];

        \common\helpers\Translation::init('admin/easypopulate');

        $result = [];
        $get_any_sources_r = tep_db_query(
            "select ld.directory_id  "
            . " from ep_directories ld "
            . " where ld.directory_config like '%ExportOrders%'  and ld.directory_type='datasource' "
            . " and not exists( SELECT * ".
                "FROM ep_holbi_soap_link_orders lo ".
                "WHERE local_orders_id='".$orderId."' and remote_orders_id>0 and ld.directory_id =lo.ep_directory_id and cfg_export_as = 'order' ".
                ")"
        );
        if ( tep_db_num_rows($get_any_sources_r)>0 ) {
            while( $_source = tep_db_fetch_array($get_any_sources_r) ){
                $result[] = $_source;
            }
        }

        return $result;
    }

}