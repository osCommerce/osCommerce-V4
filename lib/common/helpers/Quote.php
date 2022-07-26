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

class Quote {
    
    use StatusTrait;
    
    public static function getStatusTypeId()
    {
        return 3;
    }

    public static function getStatusGroup() {
        $order_status_query = tep_db_query("SELECT orders_status_groups_id FROM " . TABLE_ORDERS_STATUS_GROUPS . " WHERE orders_status_groups_name = 'Quotation'");
        $order_status = tep_db_fetch_array($order_status_query);
        return $order_status['orders_status_groups_id'];
        //return 7;
    }

    public static function getStatusGroups(){
        $order_group_query = tep_db_query("SELECT orders_status_groups_id FROM " . TABLE_ORDERS_STATUS_GROUPS . " as osg, " . TABLE_ORDERS_STATUS_TYPE ." as ost WHERE orders_status_type_name = 'Quotations' and ost.orders_status_type_id = osg.orders_status_type_id group by orders_status_groups_id");


        $groups = [];
        while ($orders_group = tep_db_fetch_array($order_group_query)){
            $groups[] = $orders_group['orders_status_groups_id'];
        }

        return $groups;
    }

    public static function getStatus($name) {
        $order_status_query = tep_db_query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = '" . $name . "' AND orders_status_groups_id IN (" . implode(',', self::getStatusGroups()) . ")");
        $order_status = tep_db_fetch_array($order_status_query);
        return $order_status['orders_status_id'] ?? null;
        /*
        $status_id = 0;
        switch ($name) {
            case 'Active':
                $status_id = 100010;
                break;
            case 'Canceled':
                $status_id = 100011;
                break;
            case 'Processed':
                $status_id = 100012;
                break;
            case 'Awaits confirmation':
                $status_id = 100013;
                break;
            default:
                break;
        }
        return $status_id;
        */
    }

}
