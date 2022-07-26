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

use common\models\OrdersStatusType;

class Status {

    const OST_SUBSCRIPTION = 2;
    const OST_QUOTATIONS = 3;
    const OST_SAMPLES = 4;
    const OST_PURCHASE_ORDERS = 5;
    // order_status_type => extension
    const OST2EXT = [self::OST_QUOTATIONS => 'Quotations', self::OST_SAMPLES => 'Samples', self::OST_PURCHASE_ORDERS => 'PurchaseOrders'];

    public static function getStatusTypeList($withAll = false) {
        global $languages_id;
        $ordersStatusType = [];
        if ($withAll) {
            $ordersStatusType[''] = TEXT_ALL_ORDERS_STATUS_TYPES;
        }
        $orders_status_types_query = tep_db_query("select orders_status_type_id, orders_status_type_name, orders_status_type_color from " . TABLE_ORDERS_STATUS_TYPE . " where language_id = '" . (int) $languages_id . "'");
        while ($orders_status_types = tep_db_fetch_array($orders_status_types_query)) {
            $ordersStatusType[$orders_status_types['orders_status_type_id']] = $orders_status_types['orders_status_type_name'];
        }
        foreach(self::OST2EXT as $id=>$name) {
            if (!\common\helpers\Acl::checkExtensionAllowed($name)) {
                unset($ordersStatusType[$id]);
            }
        }
        return $ordersStatusType;
    }

/**
 * 
 * @param bool $withAll optional (false) Add " all " option
 * @param int $type type id optional (0 - all types)
 * @return array [type_name][status_groups_id => status_groups_name]
 */
    public static function getStatusGroupsList($withAll = false, $type = 0) {
        $ordersStatusGroups = [];
        if ($withAll) {
            $ordersStatusGroups[''] = TEXT_ALL_ORDERS_STATUS_GROUPS;
        }
        $statusType = OrdersStatusType::find()->with('groups');
        if ((int)$type>0) {
          $statusType->andWhere(['orders_status_type_id' => (int)$type]);
        }
        $tmp = $statusType->asArray()->all();
        if (is_array($tmp)) {
          foreach ($tmp as $type) {
            if (is_array($type['groups'])) {
              foreach ($type['groups'] as $group) {
                $ordersStatusGroups[$type['orders_status_type_name']][$group['orders_status_groups_id']] = $group['orders_status_groups_name'];
              }
            }
          }
        }

        /*
        $orders_status_types_query = tep_db_query("select orders_status_type_id, orders_status_type_name, orders_status_type_color from " . TABLE_ORDERS_STATUS_TYPE . " where language_id = '" . (int) $languages_id . "'");
        while ($orders_status_types = tep_db_fetch_array($orders_status_types_query)) {
            $orders_status_groups_query = tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int) $languages_id . "' and orders_status_type_id = '" . (int) $orders_status_types['orders_status_type_id'] . "'");
            while ($orders_status_groups = tep_db_fetch_array($orders_status_groups_query)) {
                $ordersStatusGroups[$orders_status_types['orders_status_type_name']][$orders_status_groups['orders_status_groups_id']] = $orders_status_groups['orders_status_groups_name'];
            }
        }

         */
        return $ordersStatusGroups;
    }
    
    public static function getStatusList($withAll = false) {
        global $languages_id;
        $ordersStatuses = [];
        if ($withAll) {
            $ordersStatuses[''] = TEXT_ALL_ORDERS_STATUS;
        }
        $orders_status_types_query = tep_db_query("select orders_status_type_id, orders_status_type_name, orders_status_type_color from " . TABLE_ORDERS_STATUS_TYPE . " where language_id = '" . (int) $languages_id . "'");
        while ($orders_status_types = tep_db_fetch_array($orders_status_types_query)) {
            $orders_status_groups_query = tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int) $languages_id . "' and orders_status_type_id = '" . (int) $orders_status_types['orders_status_type_id'] . "'");
            while ($orders_status_groups = tep_db_fetch_array($orders_status_groups_query)) {
                //$orders_status_groups['orders_status_groups_id']
                $orders_status_query = tep_db_query("select orders_status_id, orders_status_name, automated from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $languages_id . "' and orders_status_groups_id = '" . (int) $orders_status_groups['orders_status_groups_id'] . "' ORDER BY orders_status_name ASC");
                while ($orders_status = tep_db_fetch_array($orders_status_query)) {
                    $ordersStatuses[$orders_status_types['orders_status_type_name']][$orders_status_groups['orders_status_groups_name']][$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
                }
            }
        }
        return $ordersStatuses;
    }
/**
 * @deprecated
 * @param type $name
 * @param type $selected
 * @return string
 */
    public static function getStatusListByTypeName($name, $selected = ''){

	    $statusType = OrdersStatusType::find()
	                                  ->where(['orders_status_type_name' => $name])
	                                  ->joinWith('groups.statuses')
	                                  ->one();
	    $statuses = [];
	    $statuses[] = [
		    'name' => TEXT_ALL_ORDERS,
		    'value' => '',
		    'selected' => '',
	    ];
      if (is_array($statusType->groups)) {
        foreach($statusType->groups as $group){
          $statuses[] = [
            'name' => $group->orders_status_groups_name,
            'value' => 'group_' . $group->orders_status_groups_id,
            'selected' => '',
          ];
          foreach($group->statuses as $status){
            if (!empty($status['hidden'])) {
              continue;
            }
            $statuses[] = [
              'name' => '&nbsp;&nbsp;&nbsp;&nbsp;' . $status->orders_status_name,
              'value' => 'status_' . $status->orders_status_id,
              'selected' => '',
            ];
          }
        }
      }
	    if($selected){
		    foreach ($statuses as $key => $value) {
			    if ($value['value'] == $selected) {
				    $statuses[$key]['selected'] = 'selected';
			    }
		    }
	    }
	    return $statuses;

    }

}
