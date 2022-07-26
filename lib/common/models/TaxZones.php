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

use Yii;
use yii\db\ActiveRecord;

class TaxZones extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'tax_zones';
    }

    public function getLocations()
    {
        return $this->hasMany(ZonesToTaxZones::className(), ['geo_zone_id' => 'geo_zone_id']);
    }

    public function getZoneZones()
    {
        return $this->hasMany(TaxZonesZones::className(), ['geo_zone_id' => 'geo_zone_id']);
    }
}