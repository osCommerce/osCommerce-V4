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

class Attribute extends AbstractClass
{
    public $attributeId = 0;
    public $attributeRecord = array();
    public $descriptionRecordArray = array();
    public $valueRecordArray = array();
    public $productRecordArray = array();

    private static $descriptionRecordFieldList = [
        'language_id' => true,
        'products_options_name' => true,
        'products_options_image' => true,
        'products_options_color' => true
    ];

    private static $valueDescriptionRecordFieldList = [
        'language_id' => true,
        'products_options_values_name' => true,
        'products_options_values_image' => true,
        'products_options_values_color' => true
    ];

    public function getId()
    {
        return $this->attributeId;
    }

    public function setId($attributeId)
    {
        $attributeId = (int)$attributeId;
        if ($attributeId >= 0) {
            $this->attributeId = $attributeId;
            return true;
        }
        return false;
    }

    public function load($attributeId)
    {
        $this->clear();
        $attributeId = (int)$attributeId;
        foreach (\common\models\ProductsOptions::find()->where(['products_options_id' => $attributeId])
            ->asArray(true)->all() as $attributeRecord
        ) {
            $this->attributeId = $attributeId;
            $attributeRecord['products_options_name'] = trim($attributeRecord['products_options_name']);
            if (!isset($this->attributeRecord['products_options_id'])
                OR ($this->attributeRecord['products_options_name'] == '')
                OR (($attributeRecord['language_id'] == \common\classes\language::defaultId()) AND ($attributeRecord['products_options_name'] != ''))
            ) {
                $this->attributeRecord = $attributeRecord;
            }
            // DESCRIPTION
            $this->descriptionRecordArray[] = $attributeRecord;
            // EOF DESCRIPTION
        }
        unset($attributeRecord);
        if ($this->attributeId > 0) {
            // VALUE
            foreach (\common\models\ProductsOptionsValues::find()->alias('av')
                ->leftJoin(\common\models\ProductsOptions2ProductsOptionsValues::tableName() . ' AS atv', 'atv.products_options_values_id = av.products_options_values_id')
                ->where(['atv.products_options_id' => $attributeId])
                ->select(['*'])->asArray(true)->all() as $valueRecord
            ) {
                $valueRecord['products_options_values_name'] = trim($valueRecord['products_options_values_name']);
                $valueRecord['descriptionRecordArray'] = (isset($this->valueRecordArray[$valueRecord['products_options_values_id']]['descriptionRecordArray'])
                    ? $this->valueRecordArray[$valueRecord['products_options_values_id']]['descriptionRecordArray'] : array());
                if (!isset($this->valueRecordArray[$valueRecord['products_options_values_id']])
                    OR ($this->valueRecordArray[$valueRecord['products_options_values_id']]['products_options_values_name'] == '')
                    OR (($valueRecord['language_id'] == \common\classes\language::defaultId()) AND ($valueRecord['products_options_values_name'] != ''))
                ) {
                    $this->valueRecordArray[$valueRecord['products_options_values_id']] = $valueRecord;
                }
                unset($valueRecord['descriptionRecordArray']);
                $this->valueRecordArray[$valueRecord['products_options_values_id']]['descriptionRecordArray'][] = $valueRecord;
            }
            // EOF VALUE
            // PRODUCT
            foreach ((\common\models\ProductsAttributes::find()->alias('pa')
                ->leftJoin(\common\models\Products::tableName() . ' AS p', 'p.products_id = pa.products_id')
                ->select(['pa.*', 'p.products_model'])->where(['options_id' => $attributeId])
                ->asArray(true)->all()) as $productRecordArray
            ) {
                $productRecordArray['downloadRecordArray'] = \common\models\ProductsAttributesDownload::find()->where(['products_attributes_id' => $productRecordArray['products_attributes_id']])->asArray(true)->all();
                $productRecordArray['priceRecordArray'] = \common\models\ProductsAttributesPrices::find()->where(['products_attributes_id' => $productRecordArray['products_attributes_id']])->asArray(true)->all();
                $this->productRecordArray[] = $productRecordArray;
            }
            unset($productRecordArray);
            // EOF PRODUCT
            return true;
        }
        return false;
    }

    public function unrelate()
    {
        if (is_array($this->valueRecordArray)) {
            foreach ($this->valueRecordArray as &$valueRecord) {
                unset($valueRecord['products_options_values_id']);
            }
            unset($valueRecord);
        }
        return parent::unrelate();
    }

    public function validate()
    {
        $this->attributeId = (int)(((int)$this->attributeId > 0) ? $this->attributeId : 0);
        if (!is_array($this->attributeRecord)) {
            $this->messageAdd('Attribute Record is invalid!');
            return false;
        }
        if (!parent::validate()) {
            $this->messageAdd('Attribute is invalid!');
            return false;
        }
        $defaultName = trim(isset($this->attributeRecord['products_options_name'])
            ? $this->attributeRecord['products_options_name'] : ''
        );
        // DESCRIPTION
        $this->descriptionRecordArray = (is_array($this->descriptionRecordArray) ? $this->descriptionRecordArray : array());
        foreach ($this->descriptionRecordArray as $keyD => &$descriptionRecord) {
            $descriptionRecord['language_id'] = (int)(isset($descriptionRecord['language_id']) ? $descriptionRecord['language_id'] : 0);
            if (isset($descriptionRecord['language_code'])) {
                $descriptionRecord['language_id'] = $this->getLanguageIdByCode($descriptionRecord['language_code'], $descriptionRecord['language_id']);
            }
            if ($descriptionRecord['language_id'] > 0) {
                $descriptionRecord['products_options_name'] = trim(isset($descriptionRecord['products_options_name'])
                    ? $descriptionRecord['products_options_name'] : ''
                );
                $defaultName = (($defaultName == '') ? $descriptionRecord['products_options_name'] : $defaultName);
                foreach ($descriptionRecord as $field => $null) {
                    if (!isset(self::$descriptionRecordFieldList[$field])) {
                        unset($descriptionRecord[$field]);
                    }
                }
                unset($field);
                unset($null);
                continue;
            }
            unset($this->descriptionRecordArray[$keyD]);
        }
        unset($descriptionRecord);
        unset($keyD);
        // EOF DESCRIPTION
        if (($defaultName == '') OR (count($this->descriptionRecordArray) == 0)) {
            $this->messageAdd('Attribute Description is invalid!');
            return false;
        }
        unset($this->attributeRecord['products_options_id']);
        $this->attributeRecord['products_options_name_default'] = $defaultName;
        unset($defaultName);
        foreach ($this->attributeRecord as $field => $null) {
            if (isset(self::$descriptionRecordFieldList[$field])) {
                unset($this->attributeRecord[$field]);
            }
        }
        unset($field);
        unset($null);
        // VALUE
        $this->valueRecordArray = (is_array($this->valueRecordArray) ? $this->valueRecordArray : array());
        foreach ($this->valueRecordArray as $keyV => &$valueRecord) {
            unset($valueRecord['products_options_id']);
            unset($valueRecord['products_options_values_to_products_options_id']);
            $defaultValueName = trim(isset($valueRecord['products_options_values_name'])
                ? $valueRecord['products_options_values_name'] : ''
            );
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
                    $descriptionRecord['products_options_values_name'] = trim(isset($descriptionRecord['products_options_values_name'])
                        ? $descriptionRecord['products_options_values_name'] : ''
                    );
                    $defaultValueName = (($defaultValueName == '') ? $descriptionRecord['products_options_values_name'] : $defaultValueName);
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
                $valueRecord['products_options_values_name_default'] = $defaultValueName;
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
        $this->attributeId = 0;
        return $this->save();
    }

    public function save($isReplace = false)
    {
        $return = false;
        if (!$this->validate()) {
            return $return;
        }
        // DESCRIPTION
        foreach ($this->descriptionRecordArray as $keyD => &$descriptionRecord) {
            $isSaveD = false;
            if ($descriptionRecord['products_options_name'] == '') {
                $descriptionRecord['products_options_name'] = $this->attributeRecord['products_options_name_default'];
            }
            try {
                $attributeClass = \common\models\ProductsOptions::find()->where(['products_options_id' => $this->attributeId, 'language_id' => $descriptionRecord['language_id']])->one();
                if (!($attributeClass instanceof \common\models\ProductsOptions)) {
                    $attributeClass = new \common\models\ProductsOptions();
                    $attributeClass->loadDefaultValues();
                    if ($this->attributeId <= 0) {
                        $attributeIdMax = (int)\common\models\ProductsOptions::find()->max('products_options_id');
                        if ($attributeIdMax > 0) {
                            $this->attributeId = ($attributeIdMax + 1);
                        }
                        unset($attributeIdMax);
                    }
                    if ($this->attributeId > 0) {
                        $attributeClass->products_options_id = $this->attributeId;
                    }
                }
                $attributeClass->setAttributes($this->attributeRecord, false);
                $attributeClass->setAttributes($descriptionRecord, false);
                if ($attributeClass->save(false)) {
                    $isSaveD = true;
                    $descriptionRecord = $attributeClass->toArray();
                    $this->attributeId = $attributeClass->products_options_id;
                } else {
                    $this->messageAdd($attributeClass->getErrorSummary(true));
                }
            } catch (\Exception $exc) {
                $this->messageAdd($exc->getMessage());
            }
            unset($attributeClass);
            if ($isSaveD != true) {
                unset($this->descriptionRecordArray[$keyD]);
            }
            unset($isSaveD);
        }
        unset($this->attributeRecord['products_options_name_default']);
        unset($descriptionRecord);
        unset($keyD);
        // EOF DESCRIPTION
        if (count($this->descriptionRecordArray) == 0) {
            return $return;
        }
        $return = $this->attributeId;
        $this->descriptionRecordArray = array_values($this->descriptionRecordArray);
        $this->attributeRecord = ($this->attributeRecord + $this->descriptionRecordArray[0]);
        // VALUE
        foreach ($this->valueRecordArray as $keyV => &$valueRecord) {
            $attributeValueId = (int)(isset($valueRecord['products_options_values_id']) ? $valueRecord['products_options_values_id'] : 0);
            unset($valueRecord['products_options_values_id']);
            // VALUE DESCRIPTION
            foreach ($valueRecord['descriptionRecordArray'] as $keyVD => &$valueDescriptionRecord) {
                $isSaveVD = false;
                $valueDescriptionRecord['products_options_values_name'] = trim($valueDescriptionRecord['products_options_values_name']);
                if ($valueDescriptionRecord['products_options_values_name'] == '') {
                    $valueDescriptionRecord['products_options_values_name'] = $valueRecord['products_options_values_name_default'];
                }
                try {
                    $attributeValueClass = \common\models\ProductsOptionsValues::find()->where(['products_options_values_id' => $attributeValueId, 'language_id' => $valueDescriptionRecord['language_id']])->one();
                    if (!($attributeValueClass instanceof \common\models\ProductsOptionsValues)) {
                        $attributeValueClass = new \common\models\ProductsOptionsValues();
                        $attributeValueClass->loadDefaultValues();
                        if ($attributeValueId <= 0) {
                            $attributeValueIdMax = (int)\common\models\ProductsOptionsValues::find()->max('products_options_values_id');
                            if ($attributeValueIdMax > 0) {
                                $attributeValueId = ($attributeValueIdMax + 1);
                            }
                            unset($attributeValueIdMax);
                        }
                        if ($attributeValueId > 0) {
                            $attributeValueClass->products_options_values_id = $attributeValueId;
                        }
                    }
                    $attributeValueClass->setAttributes($valueRecord, false);
                    $attributeValueClass->setAttributes($valueDescriptionRecord, false);
                    if ($attributeValueClass->save(false)) {
                        $isSaveVD = true;
                        $valueDescriptionRecord = $attributeValueClass->toArray();
                    } else {
                        $this->messageAdd($attributeValueClass->getErrorSummary(true));
                    }
                } catch (\Exception $exc) {
                    $this->messageAdd($exc->getMessage());
                }
                unset($attributeValueClass);
                if ($isSaveVD != true) {
                    unset($valueRecord['descriptionRecordArray'][$keyVD]);
                }
                unset($isSaveVD);
            }
            unset($valueDescriptionRecord);
            unset($keyVD);
            // EOF VALUE DESCRIPTION
            unset($valueRecord['products_options_values_name_default']);
            // VALUE TO ATTRIBUTE
            $isSaveVA = false;
            if (($attributeValueId > 0) AND (count($valueRecord['descriptionRecordArray']) > 0)) {
                $valueRecord['descriptionRecordArray'] = array_values($valueRecord['descriptionRecordArray']);
                try {
                    $attributeValueClass = \common\models\ProductsOptions2ProductsOptionsValues::findOne(['products_options_id' => $this->attributeId, 'products_options_values_id' => $attributeValueId]);
                    if (!($attributeValueClass instanceof \common\models\ProductsOptions2ProductsOptionsValues)) {
                        $attributeValueClass = new \common\models\ProductsOptions2ProductsOptionsValues();
                        $attributeValueClass->products_options_id = $this->attributeId;
                        $attributeValueClass->products_options_values_id = $attributeValueId;
                    }
                    if ($attributeValueClass->save(false)) {
                        $isSaveVA = true;
                        foreach ($valueRecord['descriptionRecordArray'] as &$valueDescriptionRecord) {
                            $valueDescriptionRecord = ($valueDescriptionRecord + $attributeValueClass->toArray());
                        }
                        unset($valueDescriptionRecord);
                        $valueRecord = ($valueRecord + $valueRecord['descriptionRecordArray'][0]);
                    } else {
                        $this->messageAdd($attributeValueClass->getErrorSummary(true));
                    }
                } catch (\Exception $exc) {
                    $this->messageAdd($exc->getMessage());
                }
                unset($attributeValueClass);
            }
            if ($isSaveVA != true) {
                unset($this->valueRecordArray[$keyV]);
            }
            unset($isSaveVA);
            // EOF VALUE TO ATTRIBUTE
            unset($attributeValueId);
        }
        unset($valueRecord);
        unset($keyV);
        // EOF VALUE
        unset($isReplace);
        return $return;
    }
}