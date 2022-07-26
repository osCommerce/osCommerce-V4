<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "configuration_group".
 *
 * @property int $configuration_group_id
 * @property string $configuration_group_title
 * @property string $configuration_group_description
 * @property int $sort_order
 * @property int $visible
 */
class ConfigurationGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'configuration_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['configuration_group_title', 'configuration_group_description'], 'required'],
            [['sort_order', 'visible'], 'integer'],
            [['configuration_group_title'], 'string', 'max' => 64],
            [['configuration_group_description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'configuration_group_id' => 'Configuration Group ID',
            'configuration_group_title' => 'Configuration Group Title',
            'configuration_group_description' => 'Configuration Group Description',
            'sort_order' => 'Sort Order',
            'visible' => 'Visible',
        ];
    }
}
