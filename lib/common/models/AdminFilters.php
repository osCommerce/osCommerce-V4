<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_filters".
 *
 * @property string $filter_type
 * @property string|null $filter_data
 */
class AdminFilters extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_filters';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['filter_type'], 'required'],
            [['filter_data'], 'string'],
            [['filter_type'], 'string', 'max' => 32],
            [['filter_type'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'filter_type' => 'Filter Type',
            'filter_data' => 'Filter Data',
        ];
    }
}
