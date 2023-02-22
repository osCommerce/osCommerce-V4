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

namespace common\api\models\AR;


use common\api\models\AR\Categories\AssignedCustomerGroups as CategoryAssignedCustomerGroups;
use common\api\models\AR\Categories\AssignedPlatforms;
use common\api\models\AR\Categories\Description;
use common\api\models\AR\Categories\AssignedDepartments;
use yii\db\Expression;
use yii\helpers\FileHelper;

class Categories extends EPMap
{

    protected $hideFields = [
        'previous_status',
        'last_xml_import',
        'last_xml_export',
        //'categories_level',
        'categories_left',
        'categories_right',
    ];

    protected $childCollections = [
        'descriptions' => [],
        'assigned_platforms' => false,
        'assigned_customer_groups' => false,
    ];

    protected $indexedCollections = [
        'assigned_platforms' => 'common\api\models\AR\Categories\AssignedPlatforms',
        'assigned_customer_groups' => 'common\api\models\AR\Categories\AssignedCustomerGroups'
    ];

    public $categories_image_data = '';
    public $categories_image_source_url = '';
    public $categories_image_2_data = '';
    public $categories_image_2_source_url = '';
    public $categories_image_3_data = '';
    public $categories_image_3_source_url = '';

    protected $auto_status = null;

    private $changedName = false;

    public function __construct(array $config = [])
    {
        if ( defined('TABLE_DEPARTMENTS_CATEGORIES') ) {
            $this->childCollections['assigned_departments'] = false;
            $this->indexedCollections['assigned_departments'] = 'common\api\models\AR\Categories\AssignedDepartments';
        }
        if (!($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed'))) {
            unset($this->childCollections['assigned_customer_groups']);
            unset($this->indexedCollections['assigned_customer_groups']);
        }

        parent::__construct($config);
    }


    public static function tableName()
    {
        return TABLE_CATEGORIES;
    }

    public static function primaryKey()
    {
        return ['categories_id'];
    }

    public function customFields()
    {
        $fields = parent::customFields();
        $fields[] = 'categories_image_data';
        $fields[] = 'categories_image_source_url';
        $fields[] = 'categories_image_2_data';
        $fields[] = 'categories_image_2_source_url';
        $fields[] = 'categories_image_3_data';
        $fields[] = 'categories_image_3_source_url';
        return $fields;
    }

    public function setAutoStatus($value)
    {
        $this->auto_status = $value;
    }

    public function initCollectionByLookupKey_Descriptions($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(Description::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['descriptions'][$keyCode] = null;
            if ( is_null($this->categories_id) ) {
                $this->childCollections['descriptions'][$keyCode] = new Description($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['descriptions'][$keyCode])) {
                    $lookupPK['categories_id'] = $this->categories_id;
                    $this->childCollections['descriptions'][$keyCode] = Description::findOne($lookupPK);
                    if (!is_object($this->childCollections['descriptions'][$keyCode])) {
                        $this->childCollections['descriptions'][$keyCode] = new Description($lookupPK);
                    }
                }
            }
        }
        return $this->childCollections['descriptions'];
    }

    public function initCollectionByLookupKey_AssignedPlatforms($lookupKeys)
    {
        if ( !is_array($this->childCollections['assigned_platforms']) ) {
            $this->childCollections['assigned_platforms'] = [];
            if ($this->categories_id) {
                $this->childCollections['assigned_platforms'] =
                    AssignedPlatforms::find()
                        ->where(['categories_id' => $this->categories_id])
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
            if ($this->categories_id) {
                $this->childCollections['assigned_customer_groups'] =
                    CategoryAssignedCustomerGroups::find()
                        ->where(['categories_id' => $this->categories_id])
                        ->orderBy(['groups_id' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['assigned_customer_groups'];
    }

    public function initCollectionByLookupKey_AssignedDepartments($lookupKeys)
    {
        if ( !is_array($this->childCollections['assigned_departments']) ) {
            $this->childCollections['assigned_departments'] = [];
            if ($this->categories_id) {
                $this->childCollections['assigned_departments'] =
                    AssignedDepartments::find()
                        ->where(['categories_id' => $this->categories_id])
                        ->orderBy(['departments_id' => SORT_ASC])
                        ->all();
            }
        }
        return $this->childCollections['assigned_departments'];
    }

    public function exportArray(array $fields = [])
    {
        if ( !empty($this->categories_image) && is_file(\common\classes\Images::getFSCatalogImagesPath().$this->categories_image) ) {
            if (count($fields) == 0 || array_key_exists('categories_image_data', $fields)) {
                //$this->categories_image_data = file_get_contents(\common\classes\Images::getFSCatalogImagesPath().$data['categories_image']);
            }
            if (count($fields) == 0 || array_key_exists('categories_image_source_url', $fields)) {
                $this->categories_image_source_url = \Yii::$app->get('platform')->config()->getCatalogBaseUrl(true).DIR_WS_IMAGES/*.\common\classes\Images::getWSCatalogImagesPath(false)*/.rawurlencode($this->categories_image);
            }
        }
        if ( !empty($this->categories_image_2) && is_file(\common\classes\Images::getFSCatalogImagesPath().$this->categories_image_2) ) {
            if (count($fields) == 0 || array_key_exists('categories_image_2_data', $fields)) {
                //$this->categories_image_2_data = file_get_contents(\common\classes\Images::getFSCatalogImagesPath().$data['categories_image_2']);
            }
            if (count($fields) == 0 || array_key_exists('categories_image_2_source_url', $fields)) {
                $this->categories_image_2_source_url = \Yii::$app->get('platform')->config()->getCatalogBaseUrl(true).DIR_WS_IMAGES/*.\common\classes\Images::getWSCatalogImagesPath(false)*/.rawurlencode($this->categories_image_2);
            }
        }
        if ( !empty($this->categories_image_3) && is_file(\common\classes\Images::getFSCatalogImagesPath().$this->categories_image_3) ) {
            if (count($fields) == 0 || array_key_exists('categories_image_3_data', $fields)) {
                //$this->categories_image_3_data = file_get_contents(\common\classes\Images::getFSCatalogImagesPath().$data['categories_image_3']);
            }
            if (count($fields) == 0 || array_key_exists('categories_image_3_source_url', $fields)) {
                $this->categories_image_3_source_url = \Yii::$app->get('platform')->config()->getCatalogBaseUrl(true).DIR_WS_IMAGES/*.\common\classes\Images::getWSCatalogImagesPath(false)*/.rawurlencode($this->categories_image_3);
            }
        }
        $data = parent::exportArray($fields);
        if ( (count($fields)==0 || array_key_exists('categories_image_data', $fields)) && !empty($this->categories_image_data) ) {
            $data['categories_image_data'] = base64_encode($this->categories_image_data);
        }
        if ( (count($fields)==0 || array_key_exists('categories_image_2_data', $fields)) && !empty($this->categories_image_2_data) ) {
            $data['categories_image_2_data'] = base64_encode($this->categories_image_2_data);
        }
        if ( (count($fields)==0 || array_key_exists('categories_image_3_data', $fields)) && !empty($this->categories_image_3_data) ) {
            $data['categories_image_3_data'] = base64_encode($this->categories_image_3_data);
        }
        if ( (count($fields)==0 || array_key_exists('categories_image_source_url', $fields)) && !is_null($this->categories_image_source_url) ) {
            $data['categories_image_source_url'] = $this->categories_image_source_url;
        }
        if ( (count($fields)==0 || array_key_exists('categories_image_2_source_url', $fields)) && !is_null($this->categories_image_2_source_url) ) {
            $data['categories_image_2_source_url'] = $this->categories_image_2_source_url;
        }
        if ( (count($fields)==0 || array_key_exists('categories_image_3_source_url', $fields)) && !is_null($this->categories_image_3_source_url) ) {
            $data['categories_image_3_source_url'] = $this->categories_image_3_source_url;
        }

        return $data;
    }

    public function importArray($data)
    {
        $result = parent::importArray($data);

        if ( isset($data['categories_image_data']) && !empty($data['categories_image_data']) ) {
            $this->categories_image_data = base64_decode($data['categories_image_data']);
        }elseif ( array_key_exists('categories_image_source_url',$data) && !empty($data['categories_image_source_url']) ){
            $this->categories_image_source_url = $data['categories_image_source_url'];
        }

        if ( isset($data['categories_image_2_data']) && !empty($data['categories_image_2_data']) ) {
            $this->categories_image_2_data = base64_decode($data['categories_image_2_data']);
        }elseif ( array_key_exists('categories_image_2_source_url',$data) && !empty($data['categories_image_2_source_url']) ){
            $this->categories_image_2_source_url = $data['categories_image_2_source_url'];
        }
        if ( isset($data['categories_image_3_data']) && !empty($data['categories_image_3_data']) ) {
            $this->categories_image_3_data = base64_decode($data['categories_image_3_data']);
        }elseif ( array_key_exists('categories_image_3_source_url',$data) && !empty($data['categories_image_3_source_url']) ){
            $this->categories_image_3_source_url = $data['categories_image_3_source_url'];
        }

        if ( isset($data['AutoStatus']) ){
            $this->AutoStatus = $data['AutoStatus'];
        }

        return $result;
    }

    public function beforeSave($insert)
    {
        if ( $ext = \common\helpers\Acl::checkExtensionAllowed('AutomaticallyStatus', 'allowed') && isset($this->auto_status) ) {
            unset($this->categories_status);
        }

        $targetDir = \common\classes\Images::getFSCatalogImagesPath();
        if ( !empty($this->categories_image_source_url) || !empty($this->categories_image_data) ) {
            $targetFilename = !empty($this->categories_image)?$this->categories_image:basename($this->categories_image_source_url);
            if ( !empty($this->categories_image_source_url) ) {
                if ( !is_dir(dirname($targetDir.$targetFilename)) ) {
                    try {
                        FileHelper::createDirectory(dirname($targetDir.$targetFilename), 0777);
                    }catch (\Exception $ex){}
                }
                @copy($this->categories_image_source_url, $targetDir.$targetFilename);
            }elseif (!empty($this->categories_image_data) && !empty($targetFilename)) {
                @file_put_contents($targetDir.$targetFilename, $this->categories_image_data);
                unset($this->categories_image_data);
            }
        }
        if ( !empty($this->categories_image_2_source_url) || !empty($this->categories_image_2_data) ) {
            $targetFilename = !empty($this->categories_image_2)?$this->categories_image_2:basename($this->categories_image_2_source_url);
            if ( !empty($this->categories_image_2_source_url) ) {
                if ( !is_dir(dirname($targetDir.$targetFilename)) ) {
                    try {
                        FileHelper::createDirectory(dirname($targetDir.$targetFilename), 0777);
                    }catch (\Exception $ex){}
                }
                @copy($this->categories_image_2_source_url, $targetDir.$targetFilename);
            }elseif (!empty($this->categories_image_2_data) && !empty($targetFilename)) {
                @file_put_contents($targetDir.$targetFilename, $this->categories_image_2_data);
                unset($this->categories_image_2_data);
            }
        }
        if ( !empty($this->categories_image_3_source_url) || !empty($this->categories_image_3_data) ) {
            $targetFilename = !empty($this->categories_image_3)?$this->categories_image_3:basename($this->categories_image_3_source_url);
            if ( !empty($this->categories_image_3_source_url) ) {
                if ( !is_dir(dirname($targetDir.$targetFilename)) ) {
                    try {
                        FileHelper::createDirectory(dirname($targetDir.$targetFilename), 0777);
                    }catch (\Exception $ex){}
                }
                @copy($this->categories_image_3_source_url, $targetDir.$targetFilename);
            }elseif (!empty($this->categories_image_3_data) && !empty($targetFilename)) {
                @file_put_contents($targetDir.$targetFilename, $this->categories_image_3_data);
                unset($this->categories_image_3_data);
            }
        }

        if ( $insert ) {
            if (is_null($this->categories_status)) $this->categories_status = 0; // override default from table schema
            if ( empty($this->date_added) ) {
                $this->date_added = new Expression("NOW()");
            }
        }else{
            if ( $this->isModified() ) {
                $this->last_modified = new Expression("NOW()");
            }
        }

        $this->changedName = false;
        $defaultKey = \common\classes\language::get_code(\common\classes\language::defaultId()).'_0';
        if( is_array($this->childCollections['descriptions']) && isset($this->childCollections['descriptions'][$defaultKey]) && is_object($this->childCollections['descriptions'][$defaultKey]) ){
            $defaultDescription = $this->childCollections['descriptions'][$defaultKey];
            /**
             * @var EPMap $defaultDescription
             */
            if (  $defaultDescription->isAttributeChanged('categories_name',false) ) {
                $this->changedName = true;
            }
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ( array_key_exists('sort_order', $changedAttributes) || $this->changedName ) {
            \common\helpers\Categories::update_categories();
        }

        if ( isset($this->auto_status) && $ext = \common\helpers\Acl::checkExtensionAllowed('AutomaticallyStatus', 'allowed') ) {
            $ext::setAutoStatusCategory($this->categories_id, $this->auto_status, true);
            unset($this->auto_status);
        }
        if ( $insert && !is_array($this->childCollections['assigned_customer_groups']) ) {
            /** @var \common\extensions\UserGroupsRestrictions\UserGroupsRestrictions $ext */
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
                if ($groupService = $ext::getGroupsService()) {
                    $groupService->addCategoryToAllGroups($this->categories_id);
                }
            }
        }

    }


}