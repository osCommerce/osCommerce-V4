<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "departments_products".
 *
 * @property int $departments_id
 * @property int $products_id
 */
class DepartmentsProducts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'departments_products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['departments_id', 'products_id'], 'required'],
            [['departments_id', 'products_id'], 'integer'],
            [['departments_id', 'products_id'], 'unique', 'targetAttribute' => ['departments_id', 'products_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'departments_id' => 'Departments ID',
            'products_id' => 'Products ID',
        ];
    }
}
