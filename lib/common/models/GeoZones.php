<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "geo_zones".
 *
 * @property int $geo_zone_id
 * @property string $geo_zone_name
 * @property string $geo_zone_description
 * @property datetime $last_modified
 * @property datetime $date_added
 * @property int $billing_status
 * @property int $shipping_status
 */
class GeoZones extends ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string {
        return 'geo_zones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array {
        return [
            [['billing_status', 'shipping_status'], 'integer'],
            [['last_modified', 'date_added'], 'safe'],
            [['geo_zone_name'], 'string', 'max' => 32],
            [['geo_zone_description'], 'string', 'max' => 255],
        ];
    }
    
    public function getZones(){
        return $this->hasMany(ZonesToGeoZones::class, [ 'geo_zone_id' => 'geo_zone_id']);
    }

    public function getPlatformZones(){
        return $this->hasMany(PlatformsGeoZones::class, [ 'geo_zone_id' => 'geo_zone_id']);
    }

}
