<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "configuration".
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
 */
class Configuration extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'configuration';
    }

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_added', 'last_modified'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['last_modified'],
                ],
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort_order'], 'integer'],
            [['last_modified', 'date_added'], 'safe'],
            [['configuration_title', 'configuration_key'], 'string', 'max' => 64],
            [['configuration_group_id'], 'string', 'max' => 128],
            [['configuration_value'], 'string', 'max' => 2048],
            [['configuration_description', 'use_function', 'set_function'], 'string', 'max' => 255],
        ];
    }

    public static function updateByKey($key, $value){
        $conf = self::find()->where(['configuration_key' => $key])->one();
        if ($conf){
            $conf->configuration_value = $value;
            $conf->save(false);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
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
        ];
    }
}
