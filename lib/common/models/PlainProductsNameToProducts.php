<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "plain_products_name_to_products".
 *
 * @property int $plain_id
 * @property int $products_id
 * @property int $platform_id
 * @property int $department_id
 */
class PlainProductsNameToProducts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plain_products_name_to_products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plain_id', 'products_id', 'platform_id', 'department_id'], 'required'],
            [['plain_id', 'products_id', 'platform_id', 'department_id'], 'integer'],
            [['plain_id', 'products_id', 'platform_id', 'department_id'], 'unique', 'targetAttribute' => ['plain_id', 'products_id', 'platform_id', 'department_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'plain_id' => 'Plain ID',
            'products_id' => 'Products ID',
            'platform_id' => 'Platform ID',
            'department_id' => 'Department ID',
        ];
    }

    public function getPlainProducts()
    {
      return $this->hasOne(PlainProductsNameSearch::class, ['id' => 'plain_id']);
    }
}
