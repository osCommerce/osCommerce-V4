<?php

namespace common\services;
use common\models\repositories\ZoneCountriesRepository;
use common\models\PlatformsZoneCountries;
use common\models\repositories\ZonesRepository;


class ZonesService
{
    /** @var ZoneCountriesRepository */
    private $platformsZoneCountriesRepository;
    /** @var ZonesRepository */
    private $zonesRepository;

    public function __construct(
        ZoneCountriesRepository $platformsZoneCountriesRepository,
        ZonesRepository $zonesRepository
    )
    {
        $this->platformsZoneCountriesRepository = $platformsZoneCountriesRepository;
        $this->zonesRepository = $zonesRepository;
    }
    
    public function saveZoneCountries($array, int $platformId)
    {
        $platformsZoneCountries = PlatformsZoneCountries::create($array, $platformId);
        $this->platformsZoneCountriesRepository->save($platformsZoneCountries);
    }
    
    public function deleteZoneCountries(int $platformId)
    {
        $this->platformsZoneCountriesRepository->deleteZoneCountry($platformId);
    }
    public function getAllByGeoZoneIdAndCountryId(int $geoZone, int $countryId, bool $asArray = false)
    {
        return $this->zonesRepository->getAllByGeoZoneIdAndCountryId($geoZone, $countryId, $asArray);
    }
}
