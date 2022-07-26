<?php

namespace common\models\repositories;

use common\models\PlatformsCountries;

class CountriesRepositiry 
{
    public function getPlatformsCountries(int $platformId)
    {
        $countries = PlatformsCountries::find()->where(['platform_id' => $platformId])->all();
        return $countries;
        /*
        if ($countries) {
            return $countries;
        } else {
            throw new NotFoundException('Countries is not found.');
        }
         * 
         */
    }
    
    public function deleteCountry(int $platformId)
    {
        return PlatformsCountries::deleteAll(['platform_id' => $platformId]);
    }

    public function save(array $platformsCountries)
    {
        foreach ($platformsCountries as $item) {
            if (!$item->save()) {
                throw new \RuntimeException('Saving error.');
            }
        }
    }
}
