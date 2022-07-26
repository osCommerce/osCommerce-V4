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

namespace common\api\Classes;

class Property extends AbstractClass
{
    public $propertyId = 0;
    public $propertyRecord = array();
    public $descriptionRecordArray = array();
    public $valueRecordArray = array();
    public $productRecordArray = array();

    private static $valueDescriptionRecordFieldList = [
        'language_id' => true,
        'values_text' => true
    ];

    public function getId()
    {
        return $this->propertyId;
    }

    public function setId($propertyId)
    {
        $propertyId = (int)$propertyId;
        if ($propertyId >= 0) {
            $this->propertyId = $propertyId;
            return true;
        }
        return false;
    }

    public function load($propertyId)
    {
        $this->clear();
        $propertyId = (int)$propertyId;
        $propertyRecord = \common\models\Properties::find()->where(['properties_id' => $propertyId])->asArray(true)->one();
        unset($propertyId);
        if (is_array($propertyRecord)) {
            $this->propertyId = (int)$propertyRecord['properties_id'];
            $this->propertyRecord = $propertyRecord;
        }
        unset($propertyRecord);
        if ($this->propertyId > 0) {
            // DESCRIPTION
            foreach (\common\models\PropertiesDescription::find()->where(['properties_id' => $this->propertyId])
                ->asArray(true)->all() as $propertyDescriptionRecord
            ) {
                $propertyDescriptionRecord['properties_name'] = trim($propertyDescriptionRecord['properties_name']);
                $propertyDescriptionRecord['properties_description'] = trim($propertyDescriptionRecord['properties_description']);
                $this->descriptionRecordArray[] = $propertyDescriptionRecord;
            }
            unset($propertyDescriptionRecord);
            // EOF DESCRIPTION
            // VALUE
            foreach (\common\models\PropertiesValues::find()->where(['properties_id' => $this->propertyId])
                ->asArray(true)->all() as $valueRecord
            ) {
                $valueRecord['values_text'] = trim($valueRecord['values_text']);
                $valueRecord['descriptionRecordArray'] = (isset($this->valueRecordArray[$valueRecord['values_id']]['descriptionRecordArray'])
                    ? $this->valueRecordArray[$valueRecord['values_id']]['descriptionRecordArray'] : array());
                if (!isset($this->valueRecordArray[$valueRecord['values_id']])
                    OR ($this->valueRecordArray[$valueRecord['values_id']]['values_text'] == '')
                    OR (($valueRecord['language_id'] == \common\classes\language::defaultId()) AND ($valueRecord['values_text'] != ''))
                ) {
                    $this->valueRecordArray[$valueRecord['values_id']] = $valueRecord;
                }
                unset($valueRecord['descriptionRecordArray']);
                $this->valueRecordArray[$valueRecord['values_id']]['descriptionRecordArray'][] = $valueRecord;
            }
            // EOF VALUE
            // PRODUCT
            $this->productRecordArray = (\common\models\Properties2Propducts::find()->alias('pp')
                ->leftJoin(\common\models\Products::tableName() . ' AS p', 'p.products_id = pp.products_id')
                ->where(['pp.properties_id' => $this->propertyId])->select(['pp.*', 'p.products_model'])
                ->asArray(true)->all());
            // EOF PRODUCT
            return true;
        }
        return false;
    }

    public function unrelate()
    {
        if (is_array($this->valueRecordArray)) {
            foreach ($this->valueRecordArray as &$valueRecord) {
                unset($valueRecord['values_id']);
            }
            unset($valueRecord);
        }
        return parent::unrelate();
    }

    public function validate()
    {
        $this->propertyId = (int)(((int)$this->propertyId > 0) ? $this->propertyId : 0);
        if (!is_array($this->propertyRecord)) {
            $this->messageAdd('Property Record is invalid!');
            return false;
        }
        if (!parent::validate()) {
            $this->messageAdd('Property is invalid!');
            return false;
        }
        $defaultName = '';
        // DESCRIPTION
        $this->descriptionRecordArray = (is_array($this->descriptionRecordArray) ? $this->descriptionRecordArray : array());
        foreach ($this->descriptionRecordArray as $keyD => &$descriptionRecord) {
            $descriptionRecord['language_id'] = (int)(isset($descriptionRecord['language_id']) ? $descriptionRecord['language_id'] : 0);
            if (isset($descriptionRecord['language_code'])) {
                $descriptionRecord['language_id'] = $this->getLanguageIdByCode($descriptionRecord['language_code'], $descriptionRecord['language_id']);
            }
            if ($descriptionRecord['language_id'] > 0) {
                $descriptionRecord['properties_name'] = trim(isset($descriptionRecord['properties_name'])
                    ? $descriptionRecord['properties_name'] : ''
                );
                $defaultName = (($defaultName == '') ? $descriptionRecord['properties_name'] : $defaultName);
                continue;
            }
            unset($this->descriptionRecordArray[$keyD]);
        }
        unset($descriptionRecord);
        unset($keyD);
        // EOF DESCRIPTION
        if (($defaultName == '') OR (count($this->descriptionRecordArray) == 0)) {
            $this->messageAdd('Property Description is invalid!');
            return false;
        }
        unset($this->propertyRecord['properties_id']);
        $this->propertyRecord['properties_name_default'] = $defaultName;
        unset($defaultName);
        // VALUE
        $this->valueRecordArray = (is_array($this->valueRecordArray) ? $this->valueRecordArray : array());
        foreach ($this->valueRecordArray as $keyV => &$valueRecord) {
            unset($valueRecord['properties_id']);
            $defaultValueName = trim(isset($valueRecord['values_text']) ? $valueRecord['values_text'] : '');
            // VALUE DESCRIPTION
            $valueRecord['descriptionRecordArray'] = ((isset($valueRecord['descriptionRecordArray']) AND is_array($valueRecord['descriptionRecordArray']))
                ? $valueRecord['descriptionRecordArray'] : array()
            );
            foreach ($valueRecord['descriptionRecordArray'] as $keyVD => &$descriptionRecord) {
                $descriptionRecord['language_id'] = (int)(isset($descriptionRecord['language_id']) ? $descriptionRecord['language_id'] : 0);
                if (isset($descriptionRecord['language_code'])) {
                    $descriptionRecord['language_id'] = $this->getLanguageIdByCode($descriptionRecord['language_code'], $descriptionRecord['language_id']);
                }
                if ($descriptionRecord['language_id'] > 0) {
                    $descriptionRecord['values_text'] = trim(isset($descriptionRecord['values_text'])
                        ? $descriptionRecord['values_text'] : ''
                    );
                    $defaultValueName = (($defaultValueName == '') ? $descriptionRecord['values_text'] : $defaultValueName);
                    foreach ($descriptionRecord as $field => $null) {
                        if (!isset(self::$valueDescriptionRecordFieldList[$field])) {
                            unset($descriptionRecord[$field]);
                        }
                    }
                    unset($field);
                    unset($null);
                    continue;
                }
                unset($valueRecord['descriptionRecordArray'][$keyVD]);
            }
            unset($descriptionRecord);
            unset($keyVD);
            // EOF VALUE DESCRIPTION
            if (($defaultValueName != '') AND (count($valueRecord['descriptionRecordArray']) > 0)) {
                $valueRecord['values_text_default'] = $defaultValueName;
                foreach ($valueRecord as $field => $null) {
                    if (isset(self::$valueDescriptionRecordFieldList[$field])) {
                        unset($valueRecord[$field]);
                    }
                }
                unset($field);
                unset($null);
                continue;
            }
            unset($this->valueRecordArray[$keyV]);
        }
        unset($defaultValueName);
        unset($valueRecord);
        unset($keyV);
        // VALUE
        $this->productRecordArray = (is_array($this->productRecordArray) ? $this->productRecordArray : array());
        return true;
    }

    public function create()
    {
        $this->propertyId = 0;
        return $this->save();
    }

    public function save($isReplace = false)
    {
        $return = false;
        if (!$this->validate()) {
            return $return;
        }
        try {
            $propertyClass = \common\models\Properties::find()->where(['properties_id' => $this->propertyId])->one();
            if (!($propertyClass instanceof \common\models\Properties)) {
                $propertyClass = new \common\models\Properties();
                $propertyClass->loadDefaultValues();
                if ($this->propertyId > 0) {
                    $propertyClass->properties_id = $this->propertyId;
                } else {
                    $this->unrelate();
                }
            }
            $propertyClass->setAttributes($this->propertyRecord, false);
            if ($propertyClass->save(false)) {
                $defaultName = $this->propertyRecord['properties_name_default'];
                $this->propertyId = $propertyClass->properties_id;
                $this->propertyRecord = $propertyClass->toArray();
                unset($propertyClass);
                $return = $this->propertyId;
                // DESCRIPTION
                foreach ($this->descriptionRecordArray as $keyD => &$descriptionRecord) {
                    $isSaveD = false;
                    if ($descriptionRecord['properties_name'] == '') {
                        $descriptionRecord['properties_name'] = $defaultName;
                    }
                    try {
                        $descriptionRecord['properties_id'] = $this->propertyId;
                        $propertyDescriptionClass = \common\models\PropertiesDescription::find()
                            ->where(['properties_id' => $this->propertyId, 'language_id' => $descriptionRecord['language_id']])->one();
                        if (!($propertyDescriptionClass instanceof \common\models\PropertiesDescription)) {
                            $propertyDescriptionClass = new \common\models\PropertiesDescription();
                            $propertyDescriptionClass->loadDefaultValues();
                        }
                        $propertyDescriptionClass->setAttributes($descriptionRecord, false);
                        if ($propertyDescriptionClass->save(false)) {
                            $isSaveD = true;
                            $descriptionRecord = $propertyDescriptionClass->toArray();
                        } else {
                            $this->messageAdd($propertyDescriptionClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($propertyDescriptionClass);
                    if ($isSaveD != true) {
                        unset($this->descriptionRecordArray[$keyD]);
                    }
                    unset($isSaveD);
                }
                unset($descriptionRecord);
                unset($defaultName);
                unset($keyD);
                // EOF DESCRIPTION
                // VALUE
                foreach ($this->valueRecordArray as $keyV => &$valueRecord) {
                    $valueRecord['properties_id'] = $this->propertyId;
                    $propertyValueId = (int)(isset($valueRecord['values_id']) ? $valueRecord['values_id'] : 0);
                    unset($valueRecord['values_id']);
                    // VALUE DESCRIPTION
                    foreach ($valueRecord['descriptionRecordArray'] as $keyVD => &$valueDescriptionRecord) {
                        $isSaveVD = false;
                        $valueDescriptionRecord['values_text'] = trim($valueDescriptionRecord['values_text']);
                        if ($valueDescriptionRecord['values_text'] == '') {
                            $valueDescriptionRecord['values_text'] = $valueRecord['values_text_default'];
                        }
                        try {
                            $propertyValueClass = \common\models\PropertiesValues::find()
                                ->where(['properties_id' => $this->propertyId, 'language_id' => $valueDescriptionRecord['language_id']]);
                            if ($propertyValueId > 0) {
                                $propertyValueClass->andWhere(['values_id' => $propertyValueId]);
                            } else {
                                $propertyValueClass->andWhere(['values_text' => $valueDescriptionRecord['values_text']]);
                            }
                            $propertyValueClass = $propertyValueClass->one();
                            if (!($propertyValueClass instanceof \common\models\PropertiesValues)) {
                                $propertyValueClass = new \common\models\PropertiesValues();
                                $propertyValueClass->loadDefaultValues();
                                if ($propertyValueId <= 0) {
                                    $propertyValueIdMax = (int)\common\models\PropertiesValues::find()->max('values_id');
                                    if ($propertyValueIdMax > 0) {
                                        $propertyValueId = ($propertyValueIdMax + 1);
                                    }
                                    unset($propertyValueIdMax);
                                }
                                if ($propertyValueId > 0) {
                                    $propertyValueClass->values_id = $propertyValueId;
                                }
                            }
                            $propertyValueClass->setAttributes($valueRecord, false);
                            $propertyValueClass->setAttributes($valueDescriptionRecord, false);
                            if ($propertyValueClass->save(false)) {
                                $isSaveVD = true;
                                $propertyValueId = (int)$propertyValueClass->values_id;
                                $valueDescriptionRecord = $propertyValueClass->toArray();
                            } else {
                                $this->messageAdd($propertyValueClass->getErrorSummary(true));
                            }
                        } catch (\Exception $exc) {
                            $this->messageAdd($exc->getMessage());
                        }
                        unset($propertyValueClass);
                        if ($isSaveVD != true) {
                            unset($valueRecord['descriptionRecordArray'][$keyVD]);
                        }
                        unset($isSaveVD);
                    }
                    unset($valueDescriptionRecord);
                    unset($keyVD);
                    // EOF VALUE DESCRIPTION
                    if (($propertyValueId > 0) AND (count($valueRecord['descriptionRecordArray']) > 0)) {
                        $valueRecord['descriptionRecordArray'] = array_values($valueRecord['descriptionRecordArray']);
                        $valueRecord = ($valueRecord + $valueRecord['descriptionRecordArray'][0]);
                    } else {
                        unset($this->valueRecordArray[$keyV]);
                    }
                    unset($valueRecord['values_text_default']);
                    unset($propertyValueId);
                }
                unset($valueRecord);
                unset($keyV);
                // EOF VALUE
                unset($isReplace);
            } else {
                $this->messageAdd($propertyClass->getErrorSummary(true));
            }
        } catch (\Exception $exc) {
            $this->messageAdd($exc->getMessage());
        }
        return $return;
    }
}