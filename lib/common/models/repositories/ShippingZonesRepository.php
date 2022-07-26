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
use yii\db\ActiveQuery;

final class ShippingZonesRepository
{
    
    public function createShippingOption($name, $platformId, $access = 0, $sort = 0){
        $defLid = \common\helpers\Language::get_default_language_id();
        $option = \common\models\ShippingOptions::create($platformId, $defLid);
        if ($option){
            $option->setName($name);
            $option->setAccess($access);
            $option->setOrder($sort);
            $oOption = clone $option;
            $option->language_id = $defLid;
            if ($option->validate()){
                if ($option->save()){
                    foreach(\common\helpers\Language::get_languages(true) as $lang){
                        if ($lang['id'] != $defLid){
                            $cOption = clone $oOption;
                            $cOption->language_id = $lang['id'];
                            $cOption->validate() && $cOption->save();
                        }
                    }
                    return $option;
                }
            }
        }
        return false;
    }
    
    public function clearShippingExtra(){
        \common\models\ShippingOptions::deleteAll();
        \common\models\ShippingZones::deleteAll();
        \common\models\ShippingZonesTable::deleteAll();
        \common\models\ZonesToShipZones::deleteAll();
        \common\models\ShippingZoneTableCheckoutNote::deleteAll();
    }
    
    public function getShippingZonesMax($platformId){
        return \common\models\ShippingZones::getMax($platformId);
    }
    
    public function createShippingZone($name, $platformId){
        return \common\models\ShippingZones::create($name, $platformId);
    }
    
    public function createZoneToShipZone($shipZoneId, $countryId, $zoneId, $platformId){
        return \common\models\ZonesToShipZones::create($shipZoneId, $countryId, $zoneId, $platformId);
    }
    
    public function createShippingZoneTable($shipZoneId, $shipOptionId, $platformId, $enabled = 0, $type = 'order'){
        return \common\models\ShippingZonesTable::create($shipZoneId, $shipOptionId, $platformId, $enabled, $type);
    }
    
    public function setRateShippingZoneTable(\common\models\ShippingZonesTable $zoneTable, string $rate){
        $zoneTable->rate = $rate;
    }
    
    public function saveShippingZoneTable(\common\models\ShippingZonesTable $zoneTable){
        if (!$zoneTable->zone_table_id) $zoneTable->zone_table_id  = \common\models\ShippingZonesTable::getMax($zoneTable->platform_id) + 1;
        return $zoneTable->validate() && $zoneTable->save();
    }
    
}
