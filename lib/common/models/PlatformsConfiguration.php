<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "platforms_configuration".
 *
 * @property int $configuration_id
 * @property string $configuration_title
 * @property string $configuration_key
 * @property string $configuration_value
 * @property string $configuration_description
 * @property int $configuration_group_id
 * @property int $sort_order
 * @property string $last_modified
 * @property string $date_added
 * @property string $use_function
 * @property string $set_function
 * @property int $platform_id
 */
class PlatformsConfiguration extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'platforms_configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['sort_order', 'platform_id'], 'integer'],
            [['last_modified', 'date_added'], 'safe'],
            [['configuration_title', 'configuration_key'], 'string', 'max' => 64],
            [['configuration_group_id'], 'string', 'max' => 128],
            [['configuration_value'], 'string', 'max' => 2048],
            [['configuration_description', 'use_function', 'set_function'], 'string', 'max' => 255],
            [['platform_id', 'configuration_key'], 'unique', 'targetAttribute' => ['platform_id', 'configuration_key']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'configuration_id' => 'Configuration ID',
            'configuration_title' => 'Configuration Title',
            'configuration_key' => 'Configuration Key',
            'configuration_value' => 'Configuration Value',
            'configuration_description' => 'Configuration Description',
            'configuration_group_id' => 'Configuration Group ID',
            'sort_order' => 'Sort Order',
            'last_modified' => 'Last Modified',
            'date_added' => 'Date Added',
            'use_function' => 'Use Function',
            'set_function' => 'Set Function',
            'platform_id' => 'Platform ID',
        ];
    }

    public function getPlatform()
    {
      return $this->hasOne(Platforms::class, ['platform_id' => 'platform_id']);
    }
}
