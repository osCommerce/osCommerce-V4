<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "platforms_geo_zones".
 *
 * @property int $platform_id
 * @property int $geo_zone_id
 */
class PlatformsGeoZones extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'platforms_geo_zones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['platform_id', 'geo_zone_id'], 'required'],
            [['platform_id', 'geo_zone_id'], 'integer'],
            [['platform_id', 'geo_zone_id'], 'unique', 'targetAttribute' => ['platform_id', 'geo_zone_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'platform_id' => 'Platform ID',
            'geo_zone_id' => 'Geo Zone ID',
        ];
    }
}
