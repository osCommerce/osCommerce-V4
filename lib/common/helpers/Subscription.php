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

class Subscription {
    
    use StatusTrait;
    
    public static function getStatusTypeId()
    {
        return 2;
    }

    public static function getStatusGroup() {
        $order_status_query = tep_db_query("SELECT orders_status_groups_id FROM " . TABLE_ORDERS_STATUS_GROUPS . " WHERE orders_status_groups_name = 'Subscription'");
        $order_status = tep_db_fetch_array($order_status_query);
        return $order_status['orders_status_groups_id'];
    }
    
    public static function getStatus($name) {
        $order_status_query = tep_db_query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = '" . $name . "' AND orders_status_groups_id = '" . self::getStatusGroup() . "'");
        $order_status = tep_db_fetch_array($order_status_query);
        return $order_status['orders_status_id'];
    }

}
