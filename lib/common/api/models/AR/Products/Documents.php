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

namespace common\api\models\AR\Products;


use common\api\models\AR\EPMap;
use common\api\models\AR\Products\Documents\Title;

class Documents extends EPMap
{

    protected $hideFields = [
        'products_documents_id',
        'products_id',
    ];

    protected $childCollections = [
        'titles' => [],
    ];


    public static function tableName()
    {
        return TABLE_PRODUCTS_DOCUMENTS;
    }

    public static function primaryKey()
    {
        return ['products_documents_id'];
    }

    public function initCollectionByLookupKey_Titles($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(Title::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['titles'][$keyCode] = null;
            if ( is_null($this->products_documents_id) ) {
                $this->childCollections['titles'][$keyCode] = new Title($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['titles'][$keyCode])) {
                    $lookupPK['products_documents_id'] = $this->products_documents_id;
                    $this->childCollections['titles'][$keyCode] = Title::findOne($lookupPK);
                    if (!is_object($this->childCollections['titles'][$keyCode])) {
                        $this->childCollections['titles'][$keyCode] = new Title($lookupPK);
                    }
                }
            }
        }
        return $this->childCollections['titles'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if (
            !is_null($importedObject->document_types_id) && !is_null($this->document_types_id) && $importedObject->document_types_id==$this->document_types_id
            &&
            !is_null($importedObject->filename) && !is_null($this->filename) && $importedObject->filename==$this->filename
        ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function importArray($data)
    {
        if (isset($data['document_types_name'])) {
            $data['document_types_id'] = \backend\models\EP\Tools::getInstance()->get_document_types_by_name($data['document_types_name']);
        }
        if ( $this->hasAttribute('is_link') && array_key_exists('is_link', $data) && $data['is_link'] ) {

        }elseif( !empty($data['document_url']) ){
            $document_filename = basename($data['filename']);
            $targetFilename = rtrim(DIR_FS_CATALOG,'/').'/'.'documents/'.$document_filename;
            $file_time_match = false;
            if ( array_key_exists('document_modify_time',$data) && $data['document_modify_time'] ) {
                $file_time_match = $data['document_modify_time'];
            }
            if ( !is_file($targetFilename) || ( $file_time_match!==false && $file_time_match>filemtime($targetFilename) ) ) {
                @copy($data['document_url'], $targetFilename);
                @chmod($targetFilename,0666);
                if ( $file_time_match!==false ) {
                    @touch($targetFilename, $file_time_match);
                }
            }
            $data['filename'] = $document_filename;
        }
        return parent::importArray($data);
    }

    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);

        if (count($fields)==0 || in_array('document_types_name',$fields)) {
            $data['document_types_name'] = \backend\models\EP\Tools::getInstance()->get_document_types_name($this->document_types_id, \common\classes\language::defaultId() );
        }

        if ( $this->hasAttribute('is_link') && $this->is_link ) {
            $data['document_url'] = $this->filename;
        }else {
            $targetFilename = rtrim(DIR_FS_CATALOG,'/').'/'.'documents/'.strval($this->filename);
            if ( is_file($targetFilename) ) {
                $data['document_modify_time'] = filemtime($targetFilename);
            }
            $data['document_url'] = \Yii::$app->get('platform')->config()->getCatalogBaseUrl(true) . 'documents/' . $this->filename;
        }

        return $data;
    }

    public function beforeDelete()
    {
        $targetFilename = rtrim(DIR_FS_CATALOG,'/').'/'.'documents/'.strval($this->filename);
        if ( false && $this->products_documents_id && !empty($this->filename) && is_file($targetFilename) ) {
            $check_remove_file = true;
            if ($this->hasAttribute('is_link') && $this->is_link) {
                $check_remove_file = false;
            }
            if ($check_remove_file) {
                $check_use_in_other = tep_db_fetch_array(tep_db_query(
                    "SELECT COUNT(*) AS c ".
                    "FROM " . TABLE_PRODUCTS_DOCUMENTS . " ".
                    "WHERE products_documents_id!='".intval($this->products_documents_id)."' ".
                    " AND filename='".tep_db_input($this->filename)."'"
                ));
                if ( $check_use_in_other['c']==0 ) {
                    @unlink($targetFilename);
                }
            }
        }
        return parent::beforeDelete();
    }

}