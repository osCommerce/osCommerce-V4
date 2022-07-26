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

namespace common\api\models\XML;

use yii\base\Model;
use yii\db\ActiveRecord;

class RelatedSerialize /*extends \yii\base\Component*/
{
    protected $_configureMap = [];

    protected $modelsComparePK = [
        //'common\models\GroupsDiscounts' => [],
    ];

    public function setConfigureMap($configureMap)
    {
        $this->_configureMap = $configureMap;
    }

    public function import($filename)
    {
        $xmlParser = new XMLtoDataParser();
        $xmlParser->setConfigureMap($this->_configureMap['Data']);
        $xmlParser->parseFile($filename);
        $processConfigure = [];
        foreach ($this->_configureMap['Data'] as $processModel=>$_processConfigure) {
            $processConfigure = $_processConfigure;
            $collectTag = '/data/'.str_replace('>','/',$processConfigure['xmlCollection']);
            $xmlParser->setCollectPath($collectTag);
            break;
        }

        while($data = $xmlParser->read()){
            if ( is_object($data) && $data instanceof IOData ) {
                if ($data->isImportable($processModel)){
                    $this->importModel($processModel, $data, $processConfigure);
                }else{

                }
            }
        }
    }

    public function importModel($processModel, $data, $processConfigure, $parentObject=null)
    {
        $processObj = false;

        if (isset($processConfigure['importFind']) && is_callable($processConfigure['importFind']) ) {
            $updateObject = call_user_func_array($processConfigure['importFind'], [$data, $parentObject]);
        }

        if( is_object($processModel) && $processModel instanceof \yii\db\ActiveRecord) {
            $updateObject = $processModel;
            $processModel = $updateObject->className();
        }else{
            $processObj = \Yii::createObject($processModel);
            $lookupByPk = [];
            foreach (array_keys($processObj->getPrimaryKey(true)) as $property) {
                if ($data->data['@' . $property]??null) {
                    $lookupByPk[$property] = $data->data['@' . $property];
                }elseif(isset($data->data[$property])){
                    if ( is_object($data->data[$property]) && $data->data[$property] instanceof IOMap){
                        $data->data[$property]->table = $processModel::tableName();
                        $data->data[$property]->attribute = $property;
                        $lookupByPk[$property] = $data->data[$property]->toImportModel();
                    }else
                    if ( is_object($data->data[$property]) && $data->data[$property] instanceof Complex){
                        $lookupByPk[$property] = $data->data[$property]->toImportModel();
                    }else {
                        $lookupByPk[$property] = $data->data[$property];
                    }
                }
            }
            if ( !count($lookupByPk)==0 || array_search(null,$lookupByPk,true)===false ) {
                $updateObject = $processModel::findOne($lookupByPk);
            }
        }
        /**
         * @var $updateObject \yii\db\ActiveRecord
         */
        if ( $updateObject ) {
            // fill all related collections
            if (isset($processConfigure['withRelated']) && is_array($processConfigure['withRelated']) ) {
                foreach ($processConfigure['withRelated'] as $collectionProperty => $collectionConfig) {
                    $_dummyArray = $updateObject->{$collectionProperty};
                    unset($_dummyArray);
                }
            }
        }else{
            $updateObject = is_object($processObj)?$processObj:\Yii::createObject($processModel);
            $updateObject->loadDefaultValues();
        }

        $isInsertedNewRecord = $updateObject->isNewRecord;

        $updateObject->detachBehaviors();
        if ( is_object($parentObject) && $updateObject->canSetProperty('parentObject') ) {
            $updateObject->parentObject = $parentObject;
        }

        /**
         * @var $updateObject \yii\db\ActiveRecord
         */
        // {{ populate data in model
        foreach ( $data->data as $property=>$propertyValue ) {
            if ($updateObject->hasAttribute($property)){

            }elseif ( $updateObject->canSetProperty($property) ) {

            }else{
                continue;
            }

            if ( is_object($propertyValue) ){
                if ( $propertyValue instanceof IOAttachment ){
                    $propertyValue = $propertyValue->toImportModel();
                }elseif($propertyValue instanceof Complex){
                    $propertyValue = $propertyValue->toImportModel();
                }
            }

            $propertyValue = $this->castInputValue($updateObject, $property, $propertyValue);
            $updateObject->{$property} = $propertyValue;
        }
        // }} populate data in model

        if (isset($processConfigure['beforeImportSave']) && is_callable($processConfigure['beforeImportSave']) ) {
            call_user_func_array($processConfigure['beforeImportSave'], [$updateObject, $data]);
        }

        if (!$updateObject->save(false)) {
            // save fail
            //echo '<pre>'; var_dump('SAVE FAIL '.var_export($updateObject->getErrors(),true)); echo '</pre>';
            return;
        }
        $updateObject->refresh();

        $tableSchema = $updateObject->getTableSchema();
        //echo '<pre>'; var_dump($updateObject->getPrimaryKey(true)); echo '</pre>';

        // {{ pass new AutoInc id to mapping
        foreach ($tableSchema->primaryKey as $name) {
            if ($tableSchema->columns[$name]->autoIncrement && isset($data->data[$name])) {
                $propertyValue = $data->data[$name];
                if (is_object($propertyValue) && $propertyValue instanceof IOMap) {
                    $propertyValue->afterImportModel($updateObject->{$name});
                }
            }
        }
        // }} pass new AutoInc id to mapping

        // current model processed

        if (isset($processConfigure['withRelated']) && is_array($processConfigure['withRelated']) ) {
            foreach ($processConfigure['withRelated'] as $collectionProperty => $collectionConfig) {
                if (!isset($data->data[$collectionProperty]) || !is_object($data->data[$collectionProperty]) || !($data->data[$collectionProperty] instanceof IODataRelated) ) {
                    continue;
                }

                $importedCollection = $data->data[$collectionProperty]->data;

                // {{ load up current dp data & remember existing refs
                $relatedCollection = false;
                if ( !$isInsertedNewRecord ) {
                    $relatedCollection = $updateObject->{$collectionProperty};
                }
                if ( !is_array($relatedCollection) ) {
                    $relatedCollection = [];
                }else{
                    // {{ set parent object for import
                    foreach ($relatedCollection as $relatedModel) {
                        if ( !$relatedModel->canSetProperty('parentObject') ) break;
                        $relatedModel->parentObject = $updateObject;
                    }
                    // }} set parent object for import
                }
                $relatedRemovalKeys = array_flip(array_keys($relatedCollection));
                // }} load up current dp data & remember existing refs


                $relatedCollectionActiveQueryGetter = 'get'.ucfirst($collectionProperty);
                /**
                 * @var $relatedActiveQuery \yii\db\ActiveQuery
                 */
                $relatedActiveQuery = $updateObject->$relatedCollectionActiveQueryGetter();
                unset($relatedCollectionActiveQueryGetter);

                // {{ make $relatedObject as model skel
                /**
                 * @var $relatedObject \yii\db\ActiveRecord
                 */
                $relatedObject = \Yii::createObject($relatedActiveQuery->modelClass);
                $relatedObject->loadDefaultValues(false);
                // }} make $relatedObject as model skel

                if (!isset($this->modelsComparePK[$relatedActiveQuery->modelClass])) {
                    $relatedPkKeys = $relatedObject->getPrimaryKey(true);
                    $comparePkKeys = array_keys($relatedPkKeys);
/*
                    if ( isset($relatedActiveQuery->link) && is_array($relatedActiveQuery->link) ) {
                        $comparePkKeys = array_diff($comparePkKeys, array_values($relatedActiveQuery->link));
                    }
*/
                    $this->modelsComparePK[$relatedActiveQuery->modelClass] = $comparePkKeys;
                }

                //'link array map to related import data';
                foreach ($importedCollection as $idx=>$recordData) {
                    /**
                     * @var IOData $recordData
                     */
                    if ( isset($relatedActiveQuery->link) && is_array($relatedActiveQuery->link) ) {
                        foreach ($relatedActiveQuery->link as $relateKey=>$parentKey) {
                            if ( $updateObject->hasAttribute($parentKey) ) {
                                $importedCollection[$idx]->data[$relateKey] = $updateObject->{$parentKey};
                            }
                        }
                    }
                }

                // find match in related collection
                foreach ($importedCollection as $idx=>$importedIOData)
                {
                    $matchedIdxInDbCollection = false;
                    foreach ( $relatedCollection as $dbIdx=>$databaseRecord )
                    {
                        if ( !isset($relatedRemovalKeys[$dbIdx]) ) continue;

                        if ($this->collectionModelCompare($databaseRecord, $importedIOData)){
                            $matchedIdxInDbCollection = $dbIdx;
                        }
                        if ( $matchedIdxInDbCollection!==false ) break;
                    }
                    if ( $matchedIdxInDbCollection!==false ) {
                        $this->importModel($relatedCollection[$matchedIdxInDbCollection], $importedIOData, $collectionConfig, $updateObject);
                        unset($relatedRemovalKeys[$matchedIdxInDbCollection]);
                        unset($importedCollection[$idx]);
                    }
                    //echo '<pre>??MATCH '; var_dump($matchedIdxInDbCollection); echo '</pre>';
                }
                // {{ remove not processed ActiveRecords
                foreach ( $relatedRemovalKeys as $relatedRemovalKey )
                {
                    //echo '$relatedCollection[$relatedRemovalKey]->delete()';
                    $relatedCollection[$relatedRemovalKey]->delete();
                }
                // }} remove not processed ActiveRecords

                // {{ insert new records
                foreach ($importedCollection as $importData)
                {
                    $this->importModel($relatedActiveQuery->modelClass, $importData, $collectionConfig, $updateObject);
                }
                // }} insert new records
            }
        }

        if (isset($processConfigure['afterImport']) && is_callable($processConfigure['afterImport']) ) {
            call_user_func_array($processConfigure['afterImport'],[$updateObject, $data]);
        }
    }

    protected function castInputValue(\yii\db\ActiveRecord $record, $attribute, $inputValue)
    {
        $propertyValue = $inputValue;

        try {
            $schemaColumns = $record->getTableSchema()->columns;
            if ( !is_array($schemaColumns) ) $schemaColumns = [];
        }catch(\yii\base\InvalidConfigException $ex){
            $schemaColumns = [];
        }

        // {{ some type cast using db schema
        if ( isset($schemaColumns[$attribute]) ) {
            $tableColumn = $schemaColumns[$attribute];
            /**
             * @var $tableColumn \yii\db\ColumnSchema
             */
            if ( $propertyValue==='' && in_array($tableColumn->phpType,['integer','boolean','double']) ) {
                $propertyValue = 0;
            }
            if ( $propertyValue!=='' && !is_null($propertyValue) && !is_object($propertyValue) && !is_array($propertyValue)) {
                $propertyValue = $tableColumn->phpTypecast($propertyValue);
            }
            if (is_null($propertyValue) && !$tableColumn->allowNull){
                if ( is_null($tableColumn->defaultValue) ) {
                    $propertyValue =
                        !is_null($tableColumn->phpTypecast(''))?$tableColumn->phpTypecast(''):$tableColumn->phpTypecast(0);
                }else{
                    $propertyValue = $tableColumn->defaultValue;
                }
            }
            if ( ($tableColumn->type==='decimal' || $tableColumn->type==='float') && is_string($propertyValue) && strlen($propertyValue)!==0 ) {
                $dotPosition = strpos($propertyValue,'.');

                if ( $dotPosition===false ) {
                    $propertyValue = number_format((float)$propertyValue,$tableColumn->scale,'.','');
                }else{
                    $inputValueScale = (strlen($propertyValue)-$dotPosition-1);
                    if ( $inputValueScale>$tableColumn->scale){
                        $propertyValue = number_format((float)$propertyValue,$tableColumn->scale,'.','');
                    }elseif ( $inputValueScale < $tableColumn->scale ) {
                        $propertyValue = number_format((float)$propertyValue,$tableColumn->scale,'.','');
                    }
                }
            }
        }
        // }} some type cast using db schema
        return $propertyValue;
    }

    protected function collectionModelCompare(\yii\db\ActiveRecord $databaseRecord, IOData $importedRecord)
    {
        $same = false;

        $useAttributeMatch = true;
        $modelClass = get_class($databaseRecord);
        $comparePkKeys = isset($this->modelsComparePK[$modelClass])?$this->modelsComparePK[$modelClass]:[];

        if ( count($comparePkKeys)>0 ) {
            $allPkMatch = true;
            foreach ($comparePkKeys as $relatedPkKey ) {
                if ( !isset($importedRecord->data[$relatedPkKey]) ) {
                    $allPkMatch = false;
                    break;
                }
                $importedValue = $importedRecord->data[$relatedPkKey];
                if ( is_object($importedValue) ) {
                    if ( $importedValue instanceof Complex ){
                        $importedValue = $importedValue->toImportModel();
                    }else{
                        $importedValue = (string)$importedValue;
                    }
                }
                if ($databaseRecord->$relatedPkKey!=$importedValue){
                    $allPkMatch = false;
                    break;
                }
            }
            if ( $allPkMatch ) {
                $same = true;
                $useAttributeMatch = false;
            }
        }
        if ($useAttributeMatch){
            $matchByAttributes = true;
            foreach( $importedRecord->data as $importAttribute=>$importValue ) {
                if ( $databaseRecord->hasAttribute($importAttribute) ) {
                    if ( is_object($importValue) ) {
                        if ( $importValue instanceof Complex ){
                            $importValue = $importValue->toImportModel();
                        }else{
                            $importValue = (string)$importValue;
                        }
                    }
                    $importValue = $this->castInputValue($databaseRecord, $importAttribute, $importValue);
                    if ($databaseRecord->{$importAttribute} !== $importValue) {
                        $matchByAttributes = false;
                        break;
                    }
                }
            }
            if ( $matchByAttributes ) {
                $same = true;
            }
        }
        return $same;
    }

    /**
     *
     */
    public function export(XMLWriter $writer)
    {
        $writer->exportBegin(isset($this->_configureMap['Header'])?$this->_configureMap['Header']:[]);

        foreach (array_keys($this->_configureMap['Data']) as $modelClass)
        {
            //$object = Yii::createObject($modelClass);
            $collectionConfig = $this->_configureMap['Data'][$modelClass];
            $data = $this->exportCollection($modelClass::find(), $collectionConfig, $writer);
            unset($data);
            //$IOProject->exportData($data);
        }
        $writer->exportEnd();
    }

    public function exportCollection(\yii\db\ActiveQuery $collection, array $collectionConfig, $writer=null, $parentObject=null)
    {
        $data = new IODataRelated();
        $data->meta = $collectionConfig;
        //$collection->limit(2);

        if ( isset($collectionConfig['softGroup']) && !empty($collectionConfig['softGroup']['column']) ) {
            $groupColumn = $collectionConfig['softGroup']['column'];
            $collection->select($groupColumn)->distinct()->orderBy($groupColumn);
        }
        if ( !empty($collectionConfig['where']) ) {
            $collection->andWhere($collectionConfig['where']);
        }
        if ( !empty($collectionConfig['orderBy']) ) {
            $collection->orderBy($collectionConfig['orderBy']);
        }

        foreach ($collection->batch(200) as $records){
            foreach ($records as $record)
            {
                /**
                 * @var $record ActiveRecord
                 */
                if (is_object($parentObject) && $record->canSetProperty('parentObject')) {
                    $record->parentObject = $parentObject;
                }
                if ( is_object($writer) ) {
                    $writer->exportData($this->exportModel($record, $collectionConfig));
                }else {
                    $data->data[] = $this->exportModel($record, $collectionConfig);
                }
            }
        }
        return $data;
    }

    public function exportModel(\yii\db\ActiveRecord $record, $recordConfig)
    {
        $data = new IOData();
        $data->meta = $recordConfig;

        /**
         * @var $record \yii\db\BaseActiveRecord
         */
        if ( !isset($recordConfig['properties']) || !is_array($recordConfig['properties']) ) $recordConfig['properties'] = [];

        $exportProperties = [];

        //$tableSchema = $record->getTableSchema();
        /**
         * @var $tableSchema yii\db\TableSchema
         */
        $primaryKeys = $record->getPrimaryKey(true);

        $modelAttributes = $record->attributes();
        $describedAttributes = (isset($recordConfig['properties']) && is_array($recordConfig['properties']))?array_keys($recordConfig['properties']):[];
        $unknown = array_diff($describedAttributes,$modelAttributes);

        foreach ( $unknown As $unknownAttribute ) {
            if ( isset($recordConfig['properties'][$unknownAttribute]) && $recordConfig['properties'][$unknownAttribute]===false ) continue;
            if ( $record->canGetProperty($unknownAttribute) ) $modelAttributes[] = $unknownAttribute;
        }
        foreach ($modelAttributes as $attribute) {
            if (isset($recordConfig['hideProperties']) && in_array($attribute, $recordConfig['hideProperties'])) continue; // hide relation
            $attributeValue = $record->{$attribute};

            if ( isset($recordConfig['properties'][$attribute]) ) {
                $propertyMapper = $recordConfig['properties'][$attribute];
                if ( $propertyMapper===false ) {
                    continue; // hide
                }elseif ( is_string($propertyMapper) && !empty($propertyMapper) ) {
                    $attribute = $propertyMapper; // rename
                }elseif ( is_array($propertyMapper) ) {
                    if ( !empty($propertyMapper['rename']) ) $attribute = $propertyMapper['rename'];
                    if (empty($propertyMapper['table'])) $propertyMapper['table'] = $record::tableName();
                    if (empty($propertyMapper['attribute'])) $propertyMapper['attribute'] = $attribute;
                    $propertyMapper['value'] = $attributeValue;
                    if ( isset($propertyMapper['record']) && $propertyMapper['record']===true ) {
                        $propertyMapper['record'] = $record;
                    }
                    $attributeValue = IOCore::createObject($propertyMapper);
                }
            }elseif(array_key_exists($attribute, $primaryKeys)){
                $attributeValue = IOCore::createObject([
                    'class' => 'IOPK',
                    'table' => $record::tableName(),
                    'attribute' => $attribute,
                    'value' => $attributeValue,
                ]);
            }
            $exportProperties[$attribute] = $attributeValue;
        }

        $data->data = $exportProperties;
        $data->exportPK = [];
        foreach ( array_keys($primaryKeys) as $pkAttribute ) {
            if ( array_key_exists($pkAttribute, $exportProperties) ) {
                $data->exportPK[] = $pkAttribute;
            }
        }

        if ( isset($recordConfig['withRelated']) ) {
            foreach ($recordConfig['withRelated'] as $collectionProperty=>$collectionConfig ){
                $relateActiveQueryGetter = 'get'.ucfirst($collectionProperty);
                if ( !$record->hasMethod($relateActiveQueryGetter) ) continue;
                $relatedActiveQuery = $record->$relateActiveQueryGetter();
                /**
                 * @var $relatedActiveQuery \yii\db\ActiveQuery
                 */
                if ( isset($relatedActiveQuery->link) && is_array($relatedActiveQuery->link) ) {
                    if ( !isset($collectionConfig['hideProperties']) ) $collectionConfig['hideProperties'] = [];
                    foreach( $relatedActiveQuery->link as $relProp=>$currentProp ) {
                        //$collectionConfig['hideProperties'][] = $relProp;
                        if ( !in_array($currentProp,$data->exportPK) ) {
                            unset($data->data[$currentProp]);
                        }
                    }
                    //$collectionConfig['hideProperties'] = array_merge($collectionConfig['hideProperties'],array_keys($relatedActiveQuery->link));
                }
                //$relatedClass = $relatedActiveQuery->modelClass;
                $data->data[$collectionProperty] = $this->exportCollection( $relatedActiveQuery, $collectionConfig, null, $record);
            }
        }

        return $data;
    }

}