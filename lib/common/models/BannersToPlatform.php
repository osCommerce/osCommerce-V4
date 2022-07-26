<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "banners_to_platform".
 *
 * @property int $banners_id
 * @property int $platform_id
 */
class BannersToPlatform extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banners_to_platform';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['banners_id', 'platform_id'], 'required'],
            [['banners_id', 'platform_id'], 'integer'],
            [['banners_id', 'platform_id'], 'unique', 'targetAttribute' => ['banners_id', 'platform_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'banners_id' => 'Banners ID',
            'platform_id' => 'Platform ID',
        ];
    }
}
