<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "products_options".
 *
 * @property int $products_options_id
 * @property int $language_id
 * @property string $products_options_name
 * @property int $products_options_sort_order
 * @property string $type
 * @property string $products_options_image
 * @property string $products_options_color
 * @property int $is_virtual
 * @property int $display_filter
 * @property int $display_search
 */
class ProductsOptions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_options';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_options_id', 'language_id', 'products_options_sort_order', 'is_virtual', 'display_filter', 'display_search'], 'integer'],
            [['products_options_name', 'products_options_image'], 'string', 'max' => 32],
            [['type'], 'string', 'max' => 16],
            [['products_options_color'], 'string', 'max' => 8],
            [['products_options_id', 'language_id'], 'unique', 'targetAttribute' => ['products_options_id', 'language_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_options_id' => 'Products Options ID',
            'language_id' => 'Language ID',
            'products_options_name' => 'Products Options Name',
            'products_options_sort_order' => 'Products Options Sort Order',
            'type' => 'Type',
            'products_options_image' => 'Products Options Image',
            'products_options_color' => 'Products Options Color',
            'is_virtual' => 'Is Virtual',
            'display_filter' => 'Display Filter',
            'display_search' => 'Display Search',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOptions2Values()
    {
        return $this->hasMany(ProductsOptions2ProductsOptionsValues::class, ['products_options_id' => 'products_options_id']); 
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductsOptionsValues()
    {
        return $this->hasMany(ProductsOptionsValues::class, ['products_options_values_id' => 'products_options_values_id'])->via('options2Values');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductsAttributes()
    {
        return $this->hasMany(ProductsAttributes::class, ['options_id' => 'products_options_id']);
    }

    public function getAssignedValuesIds()
    {
        return $this->hasMany(ProductsOptions2ProductsOptionsValues::className(), ['products_options_id' => 'products_options_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getValues()
    {
        return $this->hasMany(ProductsOptionsValues::className(), ['products_options_values_id' => 'products_options_values_id'])->andOnCondition(['language_id' => $this->language_id])->via('assignedValuesIds');
        /*
        return $this->hasMany(ProductsOptionsValues::class, ['products_options_values_id' => 'products_options_values_id'])
                ->andOnCondition(['language_id' => $this->language_id])
                ->viaTable(ProductsOptions2ProductsOptionsValues::tableName(), ['products_options_id' => 'products_options_id']);
        */
    }

    /**
     * @return ActiveQuery
     */
    public function findIds()
    {
        return static::find()->distinct()->select('products_options_id')->orderBy('products_options_id');
    }

    /*
    public function getTexts()
    {
        return $this->hasMany(ProductsOptions::className(), ['products_options_id' => 'products_options_id']);
    }
    */

    public function beforeDelete()
    {
        if (!parent::beforeDelete())
        {
            return false;
        }

        /*ProductsOptions2ProductsOptionsValues::deleteAll(
            'products_options_id=:products_options_id',
            [':products_options_id' => $this->products_options_id]
        );*/

        return true;
    }


    public static function nextID()
    {
        return static::find()->max('products_options_id') + 1;
    }
}
