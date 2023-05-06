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

use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\ColumnSchema;
use yii\helpers\Inflector;

class EPMap extends ActiveRecord
{
    protected $hideFields = [];
    public $pendingRemoval = false;

    protected $afterSaveHooks = []; // map methods
    protected $_afterSaveHooks = []; // set for call

    protected $childCollections = [];
    protected $indexedCollections = [];
    protected $indexedCollectionsAppendFlag = [];

    protected $loadedCollections = [];

    protected $modelFlags = [];

    public function __construct(array $config = [])
    {
        $initArray = [];
        if ( count($config)>0 ) {
            $fields = array_flip($this->attributes());
            foreach( array_keys($config) as $field){
                if ( isset($fields[$field]) ) {
                    $initArray[$field] = $config[$field];
                }
            }
        }

        parent::__construct($initArray);
    }

    public function getPossibleKeys()
    {
        $keys = array_merge($this->attributes(),$this->customFields());
        if ( count($this->hideFields)>0 ) {
            $keys = array_diff($keys, $this->hideFields);
        }
        return $keys;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        if ( count($this->hideFields)>0 ) {
            // {{ allow force export hidden fields
            $entityFields = @array_merge($this->attributes(),$this->customFields());
            if ( count($fields)>0 ) {
                $fields = @array_intersect($entityFields, $fields);
            }else {
                $fields = @array_diff($entityFields, $this->hideFields);
            }
            // }}
        }
        return parent::toArray($fields, $expand, $recursive);
    }

    public function indexedCollectionAppendMode($collectionName, $appendAppend=true)
    {
        if ( isset($this->indexedCollections[$collectionName]) ) {
            $this->indexedCollectionsAppendFlag[$collectionName] = $appendAppend;
        }
    }

    public function initiateAfterSave($action){
        $this->_afterSaveHooks[$action] = $action;
    }

    public function setModelFlag($flagName, $flagValue)
    {
        $this->modelFlags[$flagName] = $flagValue;
    }

    public function beforeSave($insert)
    {
        if ( $insert ) {
            // {{ fix SQL ERROR insert NULL into non null column
            $schemaColumns = $this->getTableSchema()->columns;
            if ( is_array($schemaColumns) ) {
                foreach ($schemaColumns as $columnName=>$tableColumn) {
                    /**
                     * @var $tableColumn \yii\db\ColumnSchema
                     */
                    if (!$tableColumn->allowNull && $tableColumn->dbTypecast($this->$columnName)===null){
                        if ( is_null($tableColumn->defaultValue) ) {
                            $this->setAttribute(
                                $columnName,
                                !is_null($tableColumn->phpTypecast(''))?$tableColumn->phpTypecast(''):$tableColumn->phpTypecast(0)
                            );
                        }else{
                            $this->setAttribute($columnName, $tableColumn->defaultValue);
                        }
                    }
                }
            }
            // }} fix SQL ERROR insert NULL into non null column
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        foreach ($this->childCollections as $collectionName=>$collection) {
            if ( !is_array($collection) ) continue;
            if ( $insert ) {
                //$newPrimaryValues = $this->getPrimaryKey(true);
                foreach( $collection as $collectionItem ) {
                    /**
                     * @var EPMap $collectionItem
                     */
                    $collectionItem->parentEPMap($this);
                    /*foreach ( $newPrimaryValues as $key=>$val ) {
                        if ( $collectionItem->hasAttribute($key) ) $collectionItem->setAttribute($key, $val);
                    }*/
                    if ( $collectionItem->pendingRemoval ) continue;
                    $collectionItem->insert();
                }
            }else{
                foreach( $collection as $idx=>$collectionItem ) {
                    $collectionItem->parentEPMap($this);
                    if ( $collectionItem->pendingRemoval ) {
                        $collectionItem->delete();
                        unset($collection[$idx]);
                    }else {
                        if ( $collectionItem->isNewRecord ) {
                            $collectionItem->insert();
                        }else {
                            $collectionItem->update();
                        }
                    }
                }
            }
        }

        foreach (array_keys($this->_afterSaveHooks) as $hookMethod){
            if ( !isset($this->afterSaveHooks[$hookMethod]) ) continue;
            $method = $this->afterSaveHooks[$hookMethod];
            if ( $this->hasMethod($method) ) call_user_func_array([$this,$method],[]);
        }
    }

    public function exportArray(array $fields = [])
    {
        $data = $this->toArray($fields, [], false);
        if ( count($fields)==0 ) {
            foreach( array_keys($this->childCollections) as $childKey ) {
                $fields[$childKey]['*'] = [];
            }
        }

        foreach( array_keys($this->childCollections) as $collectionName ) {
            if ( !isset($fields[$collectionName]) ) continue;
            $childFields = $fields[$collectionName];

            $filterChild = isset($childFields['*'])?$childFields['*']:[];

            $methodName = 'initCollectionByLookupKey_'.Inflector::id2camel($collectionName,'_');
            if ( method_exists($this, $methodName) ) {
                call_user_func_array([$this, $methodName],[array_keys($childFields)]);
            }
            if ( is_array($this->childCollections[$collectionName]) ){
                $data[$collectionName] = [];
            }
            foreach($this->childCollections[$collectionName] as $exportKey=>$childAR){
                $filterExportChild = $filterChild;
                if ( isset($childFields[$exportKey]) ) {
                    $filterExportChild = array_merge($filterChild, $childFields[$exportKey]);
                }elseif ( !isset($childFields['*']) ) {
                    continue;
                }
                if ( in_array('*',$filterExportChild) ) $filterExportChild = [];
                $data[$collectionName][$exportKey] = $childAR->exportArray($filterExportChild);
            }
        }

        return $data;
    }

    public function importArray($data)
    {
        if ( !is_array($data) ) return false;
        try {
            $schemaColumns = $this->getTableSchema()->columns;
            if ( !is_array($schemaColumns) ) $schemaColumns = [];
        }catch(InvalidConfigException $ex){
            $schemaColumns = [];
        }

        foreach( $data as $key=>$value ) {
            if ( $this->hasAttribute($key) ){
                // {{ some type cast using db schema
                if ( isset($schemaColumns[$key]) ) {
                    if ( $value==='' && in_array($schemaColumns[$key]->phpType,['integer','boolean','double']) ) {
                        $value = 0;
                    }
                    if ( $value!=='' && !is_null($value) && !is_object($value) && !is_array($value)) {
                        $value = $schemaColumns[$key]->phpTypecast($value);
                    }
                }
                // }} some type cast using db schema
                $this->setAttribute($key, $value);
            }elseif ( isset($this->childCollections[$key]) ) {
                $methodName = 'initCollectionByLookupKey_'.Inflector::id2camel($key,'_');
                if ( method_exists($this, $methodName) ) {
                    call_user_func_array([$this, $methodName],[['*']]);
                }


                if ( $key=='warehouses_products' ) {
                    foreach ($this->childCollections[$key] as $importKey => $childAR) {
                        if (isset($value[$importKey]) && is_array($value[$importKey])) {
                            $childAR->importArray($value[$importKey]);
                            unset($value[$importKey]);
                        } else {
                            $childAR->pendingRemoval = true;
                        }
                    }

                    if (count($value) > 0) {
                        foreach ($value as $importKey => $importData) {
                            $instance = \Yii::createObject(['class'=>$this->indexedCollections[$key], 'keyCode'=>$importKey]);
                            /**
                             * @var $instance EPMap
                             */
                            $instance->loadDefaultValues();
                            $instance->parentEPMap($this);
                            if (!$instance->importArray($importData)) continue;
                            if (method_exists($instance, 'getKeyCode')) {
                                if ( isset($this->childCollections[$key][$instance->getKeyCode()]) ){
                                    $this->childCollections[$key][$instance->getKeyCode()]->importArray($importData);
                                    $this->childCollections[$key][$instance->getKeyCode()]->pendingRemoval = false;
                                }else {
                                    $this->childCollections[$key][$instance->getKeyCode()] = $instance;
                                }
                            } else {
                                $this->childCollections[$key][] = $instance;
                            }
                        }
                    }
                }else
                if ( isset($this->indexedCollections[$key]) ) {
                    foreach ($this->childCollections[$key] as $currentIdx => $childAR) {
                        $childAR->pendingRemoval = true;
                    }

                    $matchedIdxList = [];
                    if (is_array($value)) foreach ($value as $indexedValue) {
                        $instance = \Yii::createObject($this->indexedCollections[$key]);
                        $instance->parentEPMap($this);
                        if (!$instance->importArray($indexedValue)) continue;

                        $matchCurrentAR = false;
                        foreach ($this->childCollections[$key] as $currentIdx => $childAR) {
                            if (isset($matchedIdxList[$currentIdx])) continue;
                            /**
                             * @var EPMap $childAR
                             */
                            if ($childAR->matchIndexedValue($instance)) {
                                $matchedIdxList[$currentIdx] = $currentIdx;
//                                echo '<pre>'; var_dump($indexedValue); echo '</pre>';
                                // {{
                                $childAR->pendingRemoval = false;
                                $childAR->parentEPMap($this);
                                $childAR->importArray($indexedValue);
                                // }}
                                $matchCurrentAR = true;
                                break;
                            }
                        }
                        if (!$matchCurrentAR) {
                            $this->childCollections[$key][] = $instance;
                        }
                    }
                    if (isset($this->indexedCollectionsAppendFlag[$key]) && $this->indexedCollectionsAppendFlag[$key]) {
                        foreach ($this->childCollections[$key] as $currentIdx => $childAR) {
                            $childAR->pendingRemoval = false;
                        }
                    }
                } else {
                    $importDataArray = isset($value['*'])?$value['*']:[];
                    foreach ($this->childCollections[$key] as $importKey => $childAR) {
                        if (isset($value[$importKey]) && is_array($value[$importKey])) {
                            if ( count($importDataArray)>0 ) {
                                $childAR->importArray(array_replace_recursive($importDataArray, $value[$importKey]));
                            }else{
                                $childAR->importArray($value[$importKey]);
                            }
                        }elseif(count($importDataArray)>0){
                            $childAR->importArray($importDataArray);
                        }
                    }
                }
            }
        }
        return true;
    }

    public function customFields()
    {
        return [];
    }

    public function parentEPMap(EPMap $parentObject)
    {

    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        return false;
    }

    public function getDirtyAttributes($names = null)
    {
        $dirtyAttributes = parent::getDirtyAttributes($names);
        if ( $this->isNewRecord ) {
            return $dirtyAttributes;
        }

        foreach ($dirtyAttributes as $column => $newValue) {
            if (is_null($newValue) || is_null($this->getOldAttribute($column))){

            }elseif (!$this->isAttributeChanged($column,false)){
                unset($dirtyAttributes[$column]);
            }
        }
        return $dirtyAttributes;
    }

    public function getChildCollectionNames()
    {
        return array_keys($this->childCollections);
    }

    public function refresh()
    {
        $this->_afterSaveHooks = [];
        foreach ( array_keys($this->childCollections) as $collectionName ) {
            if ( isset($this->indexedCollections[$collectionName]) ) {
                $this->childCollections[$collectionName] = false;
            }else {
                $this->childCollections[$collectionName] = [];
            }
        }
        return parent::refresh();
    }

    public function isModified()
    {
        $modified = false;
        if ( !$this->isNewRecord ) {
            $dirtyList = $this->getDirtyAttributes();
            if (count($dirtyList) > 0) {
                $modified = true;
            } else {
                foreach ($this->childCollections as $childCollectionName=>$childARs) {
                    if (is_array($childARs) && count($childARs) > 0) {
                        foreach ($childARs as $childAR) {
                            if ($childAR->pendingRemoval || $childAR->isNewRecord || $childAR->isModified()) {
                                $modified = true;
                                break;
                            }
                        }
                    }
                    if ( $modified ) break;
                }
            }
        }
        return $modified;
    }

}