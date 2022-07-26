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

namespace common\models;


use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class ZonesToShipZones extends ActiveRecord
{

    public static function tableName()
    {
        return 'zones_to_ship_zones';
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_added', 'last_modified'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['last_modified'],
                ],              
                 'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
    
    public static function create($shipZoneId, $contryId, $zoneId, $platformId){
        $zSz = new static([
            'ship_zone_id' => (int)$shipZoneId,
            'zone_country_id' => (int)$contryId,
            'zone_id' => (int)$zoneId,
            'platform_id' => (int)$platformId,
        ]);
        return $zSz;
    }

}