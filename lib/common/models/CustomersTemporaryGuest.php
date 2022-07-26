<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customers_temporary_guest".
 *
 * @property int $customers_id
 * @property int $expiration
 */
class CustomersTemporaryGuest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customers_temporary_guest';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customers_id'], 'required'],
            [['customers_id', 'expiration'], 'integer'],
            [['customers_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'customers_id' => 'Customers ID',
            'expiration' => 'Expiration',
        ];
    }
}
