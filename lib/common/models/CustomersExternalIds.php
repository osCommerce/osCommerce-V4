<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customers_external_ids".
 *
 * @property int $id
 * @property int $customers_id
 * @property string $system_name
 * @property string $external_id
 * @property string $date_added
 */
class CustomersExternalIds extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customers_external_ids';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customers_id'], 'integer'],
            [['date_added'], 'safe'],
            [['system_name'], 'string', 'max' => 127],
            [['external_id'], 'string', 'max' => 256],
            [['customers_id', 'system_name'], 'unique', 'targetAttribute' => ['customers_id', 'system_name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customers_id' => 'Customers ID',
            'system_name' => 'System Name',
            'external_id' => 'External ID',
            'date_added' => 'Date Added',
        ];
    }
}
