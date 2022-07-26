<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "manufacturers".
 *
 * @property integer $manufacturers_id
 * @property string $manufacturers_name
 * @property string $manufacturers_image
 * @property string $date_added
 * @property string $last_modified
 * @property integer $sort_order
 * @property string $manufacturers_old_seo_page_name
 */
class Manufacturers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'manufacturers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['manufacturers_name', 'sort_order'], 'required'],
            [['date_added', 'last_modified'], 'safe'],
            [['sort_order'], 'integer'],
            [['manufacturers_name'], 'string', 'max' => 32],
            [['manufacturers_image'], 'string', 'max' => 256],
            [['manufacturers_old_seo_page_name'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'manufacturers_id' => 'Manufacturers ID',
            'manufacturers_name' => 'Manufacturers Name',
            'manufacturers_image' => 'Manufacturers Image',
            'date_added' => 'Date Added',
            'last_modified' => 'Last Modified',
            'sort_order' => 'Sort Order',
            'manufacturers_old_seo_page_name' => 'Manufacturers Old Seo Page Name',
        ];
    }

    public function getProducts()
    {
        return $this->hasMany(Products::className(),['manufacturer_id' => 'manufacturers_id']);
    }

    public function getSupplierDiscounts()
    {
        return $this->hasMany(SuppliersCatalogDiscount::className(),['manufacturer_id' => 'manufacturers_id']);
    }

    public function getSupplierPriceRules()
    {
        return $this->hasMany(SuppliersCatalogPriceRules::className(),['manufacturer_id' => 'manufacturers_id']);
    }

    public function getManufacturersInfo()
    {
      $languages_id = \Yii::$app->settings->get('languages_id');
      return $this->hasOne(ManufacturersInfo::className(),['manufacturers_id' => 'manufacturers_id'])->andOnCondition(['languages_id' => (int)$languages_id]);
    }
}
