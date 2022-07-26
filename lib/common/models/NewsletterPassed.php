<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "newsletter_passed".
 *
 * @property int $local_id
 * @property int $remote_id
 * @property int $platform_id
 * @property string $provider
 */
class NewsletterPassed extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'newsletter_passed';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['local_id', 'remote_id', 'platform_id', 'provider'], 'required'],
            [['local_id', 'remote_id', 'platform_id'], 'integer'],
            [['provider'], 'string', 'max' => 120],
            [['local_id', 'remote_id', 'platform_id', 'provider'], 'unique', 'targetAttribute' => ['local_id', 'remote_id', 'platform_id', 'provider']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'local_id' => 'Local ID',
            'remote_id' => 'Remote ID',
            'platform_id' => 'Platform ID',
            'provider' => 'Provider',
        ];
    }
}
