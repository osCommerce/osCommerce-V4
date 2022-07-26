<?php

namespace common\models\repositories;

use common\models\PlatformsZoneCountries;

class ZoneCountriesRepository 
{
    public function getPlatformsZoneCountries(int $platformId)
    {
        $zoneCountries = PlatformsZoneCountries::find()->where(['platform_id' => $platformId])->all();
        return $zoneCountries;
        /*
        if ($zoneCountries) {
            return $zoneCountries;
        } else {
            throw new NotFoundException('ZoneCountries is not found.');
        }
         * 
         */
    }
    
    public function deleteZoneCountry(int $platformId)
    {
        return PlatformsZoneCountries::deleteAll(['platform_id' => $platformId]);
    }
    
    public function save(array $platformsZoneCountries)
    {
        foreach ($platformsZoneCountries as $item) {
            if (!$item->save()) {
                throw new \RuntimeException('Saving error.');
            }
        }
    }
}
