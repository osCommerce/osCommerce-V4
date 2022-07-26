<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_suppliers".
 *
 * @property int $admin_id
 * @property int $suppliers_id
 */
class AdminSuppliers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_suppliers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'suppliers_id'], 'required'],
            [['admin_id', 'suppliers_id'], 'integer'],
            [['admin_id', 'suppliers_id'], 'unique', 'targetAttribute' => ['admin_id', 'suppliers_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'admin_id' => 'Admin ID',
            'suppliers_id' => 'Suppliers ID',
        ];
    }
}
