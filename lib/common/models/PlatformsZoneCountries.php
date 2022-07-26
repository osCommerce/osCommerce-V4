<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @deprecated Useless (there is another platforms to countries table)
 * This is the model class for table "platforms_zone_countries".
 *
 * @property int $platform_id
 * @property int $zone_country_id
 */
class PlatformsZoneCountries extends ActiveRecord 
{
    public static function tableName()
    {
        return '{{platforms_zone_countries}}';
    }
    
    public static function create($array, $platformId)
    {   
        $platformsZoneCountries = [];
        
        foreach ($array as $item => $country) {
            $platformsZoneCountry = new static();
            $platformsZoneCountry->platform_id = $platformId;
            $platformsZoneCountry->zone_country_id = $country;
            $platformsZoneCountries[] = $platformsZoneCountry;
        }
        return $platformsZoneCountries;
    }
}
