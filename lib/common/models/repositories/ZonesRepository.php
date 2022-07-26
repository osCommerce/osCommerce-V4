<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\models\repositories;


use common\models\Zones;
use common\models\ZonesToGeoZones;
use yii\db\ActiveQuery;

final class ZonesRepository
{
    /**
     * @param array|int $id
     * @param bool $asArray
     * @return array|Zones|Zones[]|null
     */
    public function findById($id, bool $asArray = false)
    {
        $zones = Zones::find()
            ->where(['county_id' => $id])
            ->asArray($asArray);
        if (is_array($id)) {
            $zones->limit(1)->one();
        }
        return $zones->all();
    }

    /**
     * @param array|int $id
     * @param bool $asArray
     * @return array|Zones|Zones[]|null
     */
    public function get($id, bool $asArray = false)
    {
        if (!$zones = $this->findById($id, $asArray)) {
            throw new NotFoundException('Zones is not found.');
        }
        return $zones;
    }

    /**
     * @param Zones $zones
     */
    public function save(Zones $zones)
    {
        if (!$zones->save()) {
            throw new \RuntimeException('Zones saving  error.');
        }
    }

    /**
     * @param Zones $zones
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(Zones $zones)
    {
        if ($zones->delete() === false) {
            throw new \RuntimeException('Zones remove error.');
        }
    }

    /**
     * @param Zones $zones
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(Zones $zones, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$zones->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $zones->setAttributes($params, $safeOnly);
        if ($zones->update($validation, array_keys($params)) === false) {
            return $zones->getErrors();
        }
        return true;
    }

    public function getAllByCountry(int $countryId, bool $asArray = false)
    {
        return Zones::find()->where(['zone_country_id' => $countryId])->asArray($asArray)->all();
    }

    public function getAllByGeoZoneIdAndCountryId(int $geoZone, int $countryId, bool $asArray = false)
    {
        return ZonesToGeoZones::find()
            ->andWhere(['zone_country_id' => $countryId, 'geo_zone_id' => $geoZone])
            ->asArray($asArray)->all();
    }
}
