<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "countries".
 *
 * @property int $countries_id
 * @property string $countries_name
 * @property string $countries_iso_code_2
 * @property string $countries_iso_code_3
 * @property int $address_format_id
 * @property int $language_id
 * @property int $status
 * @property int $sort_order
 * @property double $lat
 * @property double $lng
 * @property string $zoom
 * @property int $vat_code_type
 * @property string $vat_code_prefix
 * @property int $vat_code_chars
 * @property string $dialling_prefix
 */
class Countries extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'countries';
    }

    public function checkPhone($phoneString = '')
    {
        $return = false;
        $phoneString = preg_replace('/[^0-9]/', '', trim($phoneString));
        $match = [];
        if (preg_match('/^(.+\d+)\|(\d+)$/', $this->dialling_prefix, $match)) {
            $baseLength = ((int)$match[2] - strlen($match[1]));
            if (($baseLength > 0) AND ($baseLength <= strlen($phoneString))) {
                $return = ($match[1] . substr($phoneString, -$baseLength));
            }
        }
        return $return;
    }

    public function getPlatforms() {
        return $this->hasMany(PlatformsCountries::class, ['countries_id' => 'countries_id']);
    }

    public function getGeoZones() {
        return $this->hasMany(ZonesToGeoZones::class, ['zone_country_id' => 'countries_id']);
    }

    public function getShipGeoZones() {
        return $this->hasMany(GeoZones::class, ['geo_zone_id' => 'geo_zone_id'])->andOnCondition('shipping_status=1')->via('geoZones');
    }

    public function getShipZonePlatforms() {
        return $this->hasMany(PlatformsGeoZones::class, ['geo_zone_id' => 'geo_zone_id'])->via('shipGeoZones');
    }

}