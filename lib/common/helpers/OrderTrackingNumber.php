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

namespace common\helpers;


use common\models\TrackingCarriers;
use yii\helpers\ArrayHelper;

class OrderTrackingNumber
{

    public static function getCarriersVariants()
    {
        return ArrayHelper::map(
            TrackingCarriers::find()
                ->orderBy(['tracking_carriers_name'=>SORT_ASC])
                ->asArray()
                ->all(),
            'tracking_carriers_id', 'tracking_carriers_name'
        );
    }

    public static function getCarrierId($name)
    {
        if ($carrier = TrackingCarriers::findOne(['tracking_carriers_name'=>$name])){
            return $carrier->tracking_carriers_id;
        }
        return 0;
    }

    public static function getCarrierName($id)
    {
        if ($carrier = TrackingCarriers::findOne(['tracking_carriers_id'=>$id])){
            return $carrier->tracking_carriers_name;
        }
        return '';
    }

}