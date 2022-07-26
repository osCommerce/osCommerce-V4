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

namespace common\api\models;


use yii\base\Behavior;

class DataMapBehavior extends Behavior
{

    public $related = [];

    protected $datetimeFields = [];

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

    public function populateAR($data)
    {
        foreach ($data as $property=>$propertyValue){
            if (!$this->owner->hasAttribute($property) || !$this->owner->canSetProperty($property) ){
                continue;
            }


            $propertyValue = $this->castInputValue($this->owner, $property, $propertyValue);
            $this->owner->{$property} = $propertyValue;
        }
    }

    public function populateObject($obj)
    {
/*        if ( count($this->related)>0 ) {
            foreach ($this->related as $children){
                echo '<pre>'; var_dump($this->owner->{$children}); echo '</pre>';
            }
        }*/
        foreach ( get_object_vars($obj) as $property=>$_dummy ){
            if (!$this->owner->hasAttribute($property) || !$this->owner->canGetProperty($property) ){
                continue;
            }
            $obj->{$property} = $this->owner->{$property};
        }
    }

    public function getChangedAttributes($names = null)
    {
        $dirtyAttributes = $this->owner->getDirtyAttributes($names);
        if ( $this->owner->isNewRecord ) {
            return $dirtyAttributes;
        }

        foreach ($dirtyAttributes as $column => $newValue) {
            if (is_null($newValue) || is_null($this->owner->getOldAttribute($column))){

            }elseif (!$this->owner->isAttributeChanged($column,false)){
                unset($dirtyAttributes[$column]);
            }
        }
        return $dirtyAttributes;

    }

    public function isModified()
    {
        $modified = false;
        if ( !$this->owner->isNewRecord ) {
            $dirtyList = $this->getDirtyAttributes();
            if (count($dirtyList) > 0) {
                $modified = true;
            }
        }
        return $modified;
    }

}