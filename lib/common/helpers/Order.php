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

class Order
{
    use StatusTrait;

    const OES_PENDING = 1;
    const OES_PROCESSING = 10;
    const OES_RECEIVED = 20;
    const OES_DISPATCHED = 30;
    const OES_DELIVERED = 40;
    const OES_CANCELLED = 50;
    const OES_PARTIAL_CANCELLED = 60;

    public static function getStatusTypeId()
    {
        return 1;
    }

    public static function isExist($order_id) {
        $_status = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS check_exist FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int) $order_id . "'"
        ));
        return !!$_status['check_exist'];
    }

    public static function is_stock_updated($order_id) {
        $get_stock_status = tep_db_fetch_array(tep_db_query(
                        "SELECT stock_updated FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int) $order_id . "'"
        ));
        return !!($get_stock_status['stock_updated'] ?? null);
    }

    public static function restock($order_id) {
        if (!self::is_stock_updated($order_id)) return;
        $order_query = tep_db_query("select if(length(uprid), uprid, products_id) as uprid, template_uprid, products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $order_id . "'");
        while ($order = tep_db_fetch_array($order_query)) {
            global $login_id;
            tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int) $order['products_id'] . "'");
/*
            \common\helpers\Product::log_stock_history_before_update($order['uprid'], $order['products_quantity'], '+',
                                                                     ['comments' => TEXT_ORDER_STOCK_UPDATE, 'admin_id' => $login_id, 'orders_id' => $order_id]);
            \common\helpers\Product::update_stock($order['uprid'], $order['products_quantity'], 0);
            \common\helpers\Product::get_allocated_stock_quantity($order['uprid']);
*/
            \common\helpers\Warehouses::update_stock_of_order($order_id, (strlen($order['template_uprid']) > 0 ? $order['template_uprid'] : $order['uprid']), 0);
        }
    }

    public static function remove_order($order_id, $restock = false, $reason = '') {
        if ($restock == 'on') {
            self::restock($order_id);
        }

        tep_db_query("delete from " . TABLE_ORDERS . " where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_HISTORY . " where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $order_id . "'");
        \common\models\OrdersProductsAllocate::deleteAll(['orders_id' => (int)$order_id]);
        \common\models\OrdersSplinters::deleteAll(['orders_id' => (int)$order_id]);
        \common\models\OrdersTransactionsChildren::deleteAll(['orders_id' => (int)$order_id]);
        \common\models\OrdersTransactions::deleteAll(['orders_id' => (int)$order_id]);
        \common\models\EcommerceTracking::deleteAll(['orders_id' => (int)$order_id]);
        \common\models\OrdersPayment::deleteAll(['orders_payment_order_id' => (int)$order_id]);

        tep_db_query("delete from tracking_numbers where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from tracking_numbers_to_orders_products where orders_id = '" . (int) $order_id . "'");

        foreach (\common\helpers\Hooks::getList('orders/after-delete') as $filename) {
            include($filename);
        }

        $OrdersDeleteHistory = new \common\models\OrdersDeleteHistory();
        $OrdersDeleteHistory->loadDefaultValues();
        $OrdersDeleteHistory->orders_id = (int) $order_id;
        $OrdersDeleteHistory->comments = 'Deleted ' . ($restock !== false ? 'with' : 'without') . ' restock.' . (!empty($reason) ? ' Reason:' . $reason : '');
        $OrdersDeleteHistory->admin_id = \Yii::$app->session->get('login_id');
        $OrdersDeleteHistory->date_added = date('Y-m-d H:i:s');
        $OrdersDeleteHistory->save(false);

    }

    public static function remove_tmp_order($order_id) {
        // 2do TABLE_PRODUCTS . " set products_ordered = products_ordered -
        $t_o = \common\models\TmpOrders::findOne((int) $order_id);
        if (!empty($t_o->child_id)) {
            return false;
        }

        tep_db_query("delete from tmp_orders where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from tmp_orders_products where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from tmp_orders_products_attributes where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from tmp_orders_products_download where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from tmp_orders_history where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from tmp_orders_status_history where orders_id = '" . (int) $order_id . "'");
        tep_db_query("delete from tmp_orders_total where orders_id = '" . (int) $order_id . "'");

    }

    public static function get_order_status_name($order_status_id, $language_id = '') {
        global $languages_id;

        if ($order_status_id < 1) {
            if ( !defined('TEXT_DEFAULT') ) {
                \common\helpers\Translation::getTranslationValue('TEXT_DEFAULT','admin/main');
            }else{
                $TEXT_DEFAULT = TEXT_DEFAULT;
            }
            return $TEXT_DEFAULT;
        }

        if (!is_numeric($language_id))
            $language_id = $languages_id;

        static $status_names = [];
        $key = (int) $order_status_id .'@'. (int) $language_id;
        if ( !isset($status_names[$key]) ){
            $status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . (int) $order_status_id . "' and language_id = '" . (int) $language_id . "'");
            $status = tep_db_fetch_array($status_query);

            $status_names[$key] = $status['orders_status_name'] ?? null;
        }
        return $status_names[$key];
    }

    public static function get_orders_products_status_name($order_products_status_id, $language_id = '', $isLong = true)
    {
        global $languages_id;
        if (!is_numeric($language_id)) {
            $language_id = $languages_id;
        }
        $status = \common\models\OrdersProductsStatus::findOne([
            'orders_products_status_id' => $order_products_status_id,
            'language_id' => $language_id
        ]);
        return ($status ? ($isLong == true ? $status->orders_products_status_name_long : $status->orders_products_status_name) : '');
    }

    public static function get_orders_products_status_manual_name($order_products_status_manual_id, $language_id = '', $isLong = true)
    {
        global $languages_id;
        if (!is_numeric($language_id)) {
            $language_id = $languages_id;
        }
        $status = \common\models\OrdersProductsStatusManual::findOne([
            'orders_products_status_manual_id' => $order_products_status_manual_id,
            'language_id' => $language_id
        ]);
        return ($status ? ($isLong == true ? $status->orders_products_status_manual_name_long : $status->orders_products_status_manual_name) : '');
    }

    public static function get_status($default = '', $show_group = false) {
        global $languages_id;

        $status_array = array();
        if (!empty($default)) {
            $status_array[] = array(
                'id' => '',
                'text' => $default);
        }
        if ($show_group){
            $status_query = tep_db_query("select os.orders_status_id, concat(osg.orders_status_groups_name, ' / ', os.orders_status_name) as orders_status_name from " . TABLE_ORDERS_STATUS . " os left join " . TABLE_ORDERS_STATUS_GROUPS . " osg on osg.orders_status_groups_id = os.orders_status_groups_id and osg.language_id = '" . $languages_id . "' where os.language_id = '" . $languages_id . "' order by orders_status_name");
        } else {
            $status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . $languages_id . "' order by orders_status_name");
        }
        while ($status = tep_db_fetch_array($status_query)) {
            $status_array[] = array(
                'id' => $status['orders_status_id'],
                'text' => $status['orders_status_name']);
        }
        return $status_array;
    }

    public static function getStatusesGrouped($includeAutomated=false)
    {
        $status = [];

        $list = self::getStatuses(!$includeAutomated);
        if (!empty($list) && is_array($list)){
          foreach ($list as $group) {
            if (!empty($group->statuses) && is_array($group->statuses)){
              $orders_status_groups = $group->attributes;
              $status[] = [
                  'text' => $orders_status_groups['orders_status_groups_name'],
                  'id' => 'group_' . $orders_status_groups['orders_status_groups_id'],
                  'group_color' => $orders_status_groups['orders_status_groups_color'],
                  'status_id' => 0,
                  'group_id' => $orders_status_groups['orders_status_groups_id'],
              ];
              foreach ($group->statuses as $st) {
                $orders_status = $st->attributes;
                $status[] = [
                    'text' => '&nbsp;&nbsp;&nbsp;&nbsp;' . $orders_status['orders_status_name'],
                    'id' => 'status_' . $orders_status['orders_status_id'],
                    'status_id' => $orders_status['orders_status_id'],
                    'group_id' => $orders_status_groups['orders_status_groups_id'],
                ];
              }
            }
          }
        }

        return $status;
/*
        $languages_id = \Yii::$app->settings->get('languages_id');
        $orders_status_groups_query = tep_db_query(
            "select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color ".
            "from " . TABLE_ORDERS_STATUS_GROUPS . " ".
            "where language_id = '" . (int)$languages_id . "' ".
            " AND orders_status_type_id = '".intval(self::getStatusTypeId())."' ".
            "order by orders_status_groups_id"
        );
        while ($orders_status_groups = tep_db_fetch_array($orders_status_groups_query)) {
            $status[] = [
                'text' => $orders_status_groups['orders_status_groups_name'],
                'id' => 'group_' . $orders_status_groups['orders_status_groups_id'],
                'group_color' => $orders_status_groups['orders_status_groups_color'],
                'status_id' => 0,
                'group_id' => $orders_status_groups['orders_status_groups_id'],
            ];
            $orders_status_query = tep_db_query(
                "select orders_status_id, orders_status_name ".
                "from " . TABLE_ORDERS_STATUS . " ".
                "where language_id = '" . (int)$languages_id . "' and orders_status_groups_id='" . $orders_status_groups['orders_status_groups_id'] . "' ".
                " ".($includeAutomated?"":"AND automated=0 ")." ".
                "order by orders_status_name"
            );
            if ( tep_db_num_rows($orders_status_query)>0 ) {
                while ($orders_status = tep_db_fetch_array($orders_status_query)) {
                    $status[] = [
                        'text' => '&nbsp;&nbsp;&nbsp;&nbsp;' . $orders_status['orders_status_name'],
                        'id' => 'status_' . $orders_status['orders_status_id'],
                        'status_id' => $orders_status['orders_status_id'],
                        'group_id' => $orders_status_groups['orders_status_groups_id'],
                    ];
                }
            }elseif($status[ count($status)-1 ]['id']=='group_' . $orders_status_groups['orders_status_groups_id']){
                unset($status[ count($status)-1 ]);
                $status = array_values($status);
            }
        }
        return $status;
 */
    }

    public static function extractStatuses($statuses_string)
    {
        $statuses = array();
        foreach (explode(',',$statuses_string) as $check_status){
            $check_status = trim($check_status);
            if ( strpos($check_status,'group_')===0 ) {
                $orders_status_query = tep_db_query("select distinct orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_groups_id='" . intval( str_replace('group_','', $check_status) ) . "' ");
                while ($orders_status = tep_db_fetch_array($orders_status_query)) {
                    $statuses[(int)$orders_status['orders_status_id']] = (int)$orders_status['orders_status_id'];
                }
            }elseif( strpos($check_status,'status_')===0 ){
                $status_id = intval( str_replace('status_','', $check_status) );
                $statuses[ (int)$status_id ] = (int)$status_id;
            }elseif( (int)$check_status!=0 ){
                $statuses[ (int)$check_status ] = (int)$check_status;
            }
        }

        return array_values($statuses);
    }

    public static function orders_status_groups_name($orders_status_groups_id, $language_id = '') {
        global $languages_id;

        if (!$language_id)
            $language_id = $languages_id;
        $orders_status_groups_query = tep_db_query("select orders_status_groups_name from " . TABLE_ORDERS_STATUS_GROUPS . " where orders_status_groups_id = '" . (int) $orders_status_groups_id . "' and language_id = '" . (int) $language_id . "'");
        $orders_status_groups = tep_db_fetch_array($orders_status_groups_query);

        return $orders_status_groups['orders_status_groups_name'] ?? null;
    }

    public static function get_status_name($id_status) {
        global $languages_id;
        $id_status = $id_status === '--none--' ? '' : $id_status;
        if (strlen(trim($id_status)) == 0) {
            return TEXT_NO_STATUS;
        } else {
            $status_name = [];
            $status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . $languages_id . "' and orders_status_id IN (" . $id_status . ") order by orders_status_name");
            while ($status = tep_db_fetch_array($status_query)) {
                $status_name[] = $status['orders_status_name'];
            }
            return implode(', ', $status_name);
        }
    }

    public static function trunk_orders($prefix = '') {
        tep_db_query("TRUNCATE " . $prefix . TABLE_ORDERS);
        tep_db_query("TRUNCATE " . $prefix . TABLE_ORDERS_HISTORY);
        tep_db_query("TRUNCATE " . $prefix . TABLE_ORDERS_PRODUCTS);
        tep_db_query("TRUNCATE " . $prefix . TABLE_ORDERS_PRODUCTS_ATTRIBUTES);
        tep_db_query("TRUNCATE " . $prefix . TABLE_ORDERS_PRODUCTS_DOWNLOAD);
        tep_db_query("TRUNCATE " . $prefix . TABLE_ORDERS_STATUS_HISTORY);
        tep_db_query("TRUNCATE " . $prefix . TABLE_ORDERS_TOTAL);
        if (empty($prefix)) {
            $schemaCheck = \Yii::$app->get('db')->schema->getTableSchema('admin_shopping_carts');
            if ($schemaCheck) {
                tep_db_query("TRUNCATE TABLE admin_shopping_carts");
            }
            \common\models\OrdersSplinters::deleteAll();
        }
        foreach (\common\helpers\Hooks::getList('orders/after-trunk') as $filename) {
            include($filename);
        }
    }

    public static function parse_tracking_number($tracking_number) {
        if ($tracking_number instanceof \common\classes\OrderTrackingNumber) {
            return array(
                'number' => $tracking_number->number,
                'url' => $tracking_number->tracking_url,
                'carrier' => $tracking_number->carrier,
            );
        }
        $tracking_number = trim($tracking_number, " ,\t\n\r\0\x0B");

        $carrier = '';
        if (strpos($tracking_number, ',') !== false && strpos($tracking_number, ',') < 10) {
            list($carrier, $tracking_number) = explode(',', $tracking_number, 2);
            $carrier = trim($carrier);
            $tracking_number = trim($tracking_number);
        }
        if (filter_var($tracking_number, FILTER_VALIDATE_URL)) {
            $url_query = parse_url($tracking_number, PHP_URL_QUERY);
            $_url_tracking_number = substr($url_query, ($pos = strrpos($url_query, '=')) > 0 ? $pos + 1 : 0);
            //$_url_tracking_number = urldecode($_url_tracking_number);
            if (strlen($_url_tracking_number) < 1) {
                $url_path = parse_url($tracking_number, PHP_URL_PATH);
                $_url_tracking_number = substr($url_path, ($pos = strrpos($url_path, '/')) > 0 ? $pos + 1 : 0);
            }
            if (strlen($_url_tracking_number) < 1) {
                $url_path = parse_url($tracking_number, PHP_URL_FRAGMENT);
                $_url_tracking_number = substr($url_path, ($pos = strrpos($url_path, '/')) > 0 ? $pos + 1 : 0);
            }
            return array(
                'number' => $_url_tracking_number,
                'url' => $tracking_number,
                'carrier' => $carrier,
            );
        } else {
            $tracking_url = TRACKING_NUMBER_URL . str_replace(' ', '', $tracking_number);
            if (stripos($tracking_url, '17track') !== false && strtolower($carrier) == 'fedex') {
                $tracking_url .= '&fc=100003';
            }
            if (stripos($tracking_url, '17track') !== false && strtolower($carrier) == 'dhl') {
                $tracking_url .= '&fc=100001';
            }
            if ($carrier) {
                $tracking_carriers_id = \common\helpers\OrderTrackingNumber::getCarrierId($carrier);
                if ($tracking_carriers_id && $carrier = \common\models\TrackingCarriers::findOne(['tracking_carriers_id' => $tracking_carriers_id])) {
                    if ($carrier->tracking_carriers_url) {
                        $tracking_url = $carrier->tracking_carriers_url . $tracking_number;
                    }
                    $carrier = $carrier->tracking_carriers_name;
                }
            }
            return array(
                'number' => $tracking_number,
                'url' => $tracking_url,
                'carrier' => $carrier,
            );
        }
    }

    public static function getUsedTotalClassList($selected = '') {
      if ($selected=='') {
        $selected = 'ot_total';
      }
      $totals = \common\models\OrdersTotal::find()->select('class')->distinct()->orderBy('class')->all();
      $ret = [];

      if (is_array($totals)) {
        foreach($totals as $total) {
          $name = \common\helpers\Translation::getTranslationValue('MODULE_ORDER_TOTAL_' . strtoupper(str_replace('ot_', '', $total->class)) . '_TITLE', 'ordertotal');
          if ($name === false) {
            $name = ucfirst(str_replace(array('ot_', '_'), array('', ' '), $total->class));
          }
          $ret[] = [
            'name' => $name, //full_name,
            'value' => $total->class,
            'selected' => ($selected && $selected==$total->class?'selected':''),
          ];
        }
      }
      unset($totals);
      return $ret;
    }

    /**
     * Dispatch Order
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @param boolean $isForced defines should be Order products set as Dispatched even if there is no stock available
     * @param integer $orderStatusPreferred try to search for preferred Order Status binded to Order Evaluation State and use it as Default Order Status
     * @return boolean false on any error, true on success
     */
    public static function doDispatch($orderRecord = 0, $isForced = false, $orderStatusPreferred = 0)
    {
        $return = false;
        $orderRecord = self::getRecord($orderRecord);
        $isForced = (((int)$isForced > 0) ? true : false);
        if ($orderRecord instanceof \common\models\Orders) {
            if (self::isValidAllocated($orderRecord) != true) {
                return $return;
            }
            $return = true;
            foreach (\common\models\OrdersProducts::findAll(['orders_id' => (int)$orderRecord->orders_id]) as $orderProductRecord) {
                $return = (\common\helpers\OrderProduct::doDispatch($orderProductRecord, $isForced) AND $return);
            }
            unset($orderProductRecord);
            self::evaluate($orderRecord, $orderStatusPreferred);
        }
        unset($orderStatusPreferred);
        unset($orderRecord);
        unset($isForced);
        return $return;
    }

    /**
     * Deliver Order
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @param boolean $isForced defines should be Order products set as Delivered even if there is quantity awaiting for Dispatch
     * @param integer $orderStatusPreferred try to search for preferred Order Status binded to Order Evaluation State and use it as Default Order Status
     * @return boolean false on any error, true on success
     */
    public static function doDeliver($orderRecord = 0, $isForced = false, $orderStatusPreferred = 0)
    {
        $return = false;
        $orderRecord = self::getRecord($orderRecord);
        $isForced = (((int)$isForced > 0) ? true : false);
        if ($orderRecord instanceof \common\models\Orders) {
            if (self::isValidAllocated($orderRecord) != true) {
                return $return;
            }
            $return = true;
            foreach (\common\models\OrdersProducts::findAll(['orders_id' => (int)$orderRecord->orders_id]) as $orderProductRecord) {
                $return = (\common\helpers\OrderProduct::doDeliver($orderProductRecord, $isForced) AND $return);
            }
            unset($orderProductRecord);
            self::evaluate($orderRecord, $orderStatusPreferred);
        }
        unset($orderStatusPreferred);
        unset($orderRecord);
        unset($isForced);
        return $return;
    }

    /**
     * Cancel Order
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @param boolean $isRestock defines should Dispatched quantity be returned to stock
     * @param integer $orderStatusPreferred try to search for preferred Order Status binded to Order Evaluation State and use it as Default Order Status
     * @return boolean false on any error, true on success
     */
    public static function doCancel($orderRecord = 0, $isRestock = false, $orderStatusPreferred = 0)
    {
        $return = false;
        $orderRecord = self::getRecord($orderRecord);
        $isRestock = (((int)$isRestock > 0) ? true : false);
        if ($orderRecord instanceof \common\models\Orders) {
            if (self::isValidAllocated($orderRecord) != true) {
                return $return;
            }
            $return = true;
            foreach (\common\models\OrdersProducts::findAll(['orders_id' => (int)$orderRecord->orders_id]) as $orderProductRecord) {
                $return = (\common\helpers\OrderProduct::doCancel($orderProductRecord, $isRestock) AND $return);
            }
            unset($orderProductRecord);
            self::evaluate($orderRecord, $orderStatusPreferred);
        }
        unset($orderStatusPreferred);
        unset($orderRecord);
        unset($isRestock);
        return $return;
    }

    /**
     * Pend Order
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @param boolean $isReset defines should Cancelled quantity be reset to 0
     * @param integer $orderStatusPreferred try to search for preferred Order Status binded to Order Evaluation State and use it as Default Order Status
     * @return boolean false on any error, true on success
     */
    public static function doPendent($orderRecord = 0, $isReset = false, $orderStatusPreferred = 0)
    {
        $return = false;
        $orderRecord = self::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            if (self::isValidAllocated($orderRecord) != true) {
                return $return;
            }
            $return = true;
            self::updateAllocateAllow($orderRecord, 0);
            foreach (\common\models\OrdersProducts::findAll(['orders_id' => (int)$orderRecord->orders_id]) as $orderProductRecord) {
                $return = (\common\helpers\OrderProduct::doQuote($orderProductRecord, $isReset) AND $return);
            }
            unset($orderProductRecord);
            self::evaluate($orderRecord, $orderStatusPreferred);
        }
        unset($orderStatusPreferred);
        unset($orderRecord);
        unset($isReset);
        return $return;
    }

    /**
     * Process Order
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @param null $_null reserved for further use
     * @param integer $orderStatusPreferred try to search for preferred Order Status binded to Order Evaluation State and use it as Default Order Status
     * @return boolean false on any error, true on success
     */
    public static function doProcess($orderRecord = 0, $_null = null, $orderStatusPreferred = 0)
    {
        $return = false;
        $orderRecord = self::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            if (self::isValidAllocated($orderRecord) != true) {
                return $return;
            }
            $return = true;
            self::updateAllocateAllow($orderRecord, 1);
            foreach (\common\models\OrdersProducts::findAll(['orders_id' => (int)$orderRecord->orders_id]) as $orderProductRecord) {
                $return = (\common\helpers\OrderProduct::doAllocateAutomatic($orderProductRecord) AND $return);
            }
            unset($orderProductRecord);
            self::evaluate($orderRecord, $orderStatusPreferred);
        }
        unset($orderStatusPreferred);
        unset($orderRecord);
        return $return;
    }

    /**
     * Refresh Order
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @param null $_null reserved for further use
     * @param integer $orderStatusPreferred try to search for preferred Order Status binded to Order Evaluation State and use it as Default Order Status
     * @return boolean false on any error, true on success
     */
    public static function doRefresh($orderRecord = 0, $_null = null, $orderStatusPreferred = 0)
    {
        $return = false;
        $orderRecord = self::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            if (self::isValidAllocated($orderRecord) != true) {
                return $return;
            }
            $return = true;
            foreach (\common\models\OrdersProducts::findAll(['orders_id' => (int)$orderRecord->orders_id]) as $orderProductRecord) {
                $return = (\common\helpers\OrderProduct::doAllocateAutomatic($orderProductRecord, true) AND $return);
            }
            unset($orderProductRecord);
            self::evaluate($orderRecord, $orderStatusPreferred);
        }
        unset($orderStatusPreferred);
        unset($orderRecord);
        return $return;
    }

    /**
     * Validate and updating Product Allocation records.
     * Updating Dispatched based on Delivered and Received based on Disptached.
     * Deleting orphan allocation records or where Received equals 0.
     * Rule: Received >= Dispatched >= Delivered
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @return boolean false on error, true - if validation is passed
     */
    public static function isValidAllocated($orderRecord = 0)
    {
        $orderRecord = self::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            $orderProductSkipList = array();
            foreach (self::getAllocatedArray($orderRecord, false) as $productAllocated) {
                if (!isset($orderProductSkipList[$productAllocated->orders_products_id])) {
                    $orderProductSkipList[$productAllocated->orders_products_id] = $productAllocated->orders_products_id;
                    $orderProductRecord = \common\helpers\OrderProduct::getRecord($productAllocated->orders_products_id);
                    if ($orderProductRecord instanceof \common\models\OrdersProducts AND $orderProductRecord->orders_id == $orderRecord->orders_id) {
                        if (\common\helpers\OrderProduct::isValidAllocated($orderProductRecord) != true) {
                            unset($orderProductRecord);
                            return false;
                        }
                    } else {
                        $productAllocated->delete();
                    }
                    unset($orderProductRecord);
                }
            }
            unset($orderProductSkipList);
            unset($productAllocated);
        }
        unset($orderRecord);
        return true;
    }

    /**
     * Get Order Product Allocation array
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @param boolean $asArray switching return type between array of arrays or array of instances of OrdersProductsAllocate
     * @return array array of mixed depending on $asArray parameter
     */
    public static function getAllocatedArray($orderRecord = 0, $asArray = true)
    {
        $return = [];
        $orderRecord = self::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            foreach ((\common\models\OrdersProductsAllocate::find()
                ->where(['orders_id' => (int)$orderRecord->orders_id])
                ->asArray($asArray)->all())
                    as $opAllocateRecord
            ) {
                $return[] = $opAllocateRecord;
            }
            unset($opAllocateRecord);
        }
        unset($orderRecord);
        unset($asArray);
        return $return;
    }

    /**
     * Automatically update Order Status based on Order Product statuses
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @param int $orderStatusPreferred preferred order status if two or more statuses are bonded to same order evaluation state. Default to current order status
     * @return mixed false on error or current Order Status Id
     */
    public static function evaluate($orderRecord = 0, $orderStatusPreferred = 0)
    {
        $return = false;
        $orderRecord = self::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            if (self::isValidAllocated($orderRecord) != true) {
                return $return;
            }
            $orderStatus = (int)$orderRecord->orders_status;
            $orderStatusPreferred = (int)(((int)$orderStatusPreferred <= 0) ? $orderStatus : $orderStatusPreferred);
            $orderProductStatusArray = array_fill_keys(array_keys(\common\helpers\OrderProduct::getStatusArray()), 0);
            foreach (\common\models\OrdersProducts::findAll(['orders_id' => $orderRecord->orders_id]) as $orderProductRecord) {
                $orderProductStatusArray[$orderProductRecord->orders_products_status] += (int)$orderProductRecord->products_quantity;
            }
            unset($orderProductRecord);
            if ($orderProductStatusArray[\common\helpers\OrderProduct::OPS_CANCELLED] > 0) {
                $return = self::OES_CANCELLED;
            }
            if ($orderProductStatusArray[\common\helpers\OrderProduct::OPS_DELIVERED] > 0) {
                $return = self::OES_DELIVERED;
            }
            if ($orderProductStatusArray[\common\helpers\OrderProduct::OPS_DISPATCHED] > 0) {
                $return = self::OES_DISPATCHED;
            }
            if ($orderProductStatusArray[\common\helpers\OrderProduct::OPS_RECEIVED] > 0) {
                $return = self::OES_RECEIVED;
            }
            if ($orderProductStatusArray[\common\helpers\OrderProduct::OPS_STOCK_DEFICIT] > 0
                OR $orderProductStatusArray[\common\helpers\OrderProduct::OPS_STOCK_ORDERED] > 0
            ) {
                $return = self::OES_PROCESSING;
            }
            if ($orderProductStatusArray[\common\helpers\OrderProduct::OPS_QUOTED] > 0) {
                if ($return == false) {
                    $return = self::OES_PENDING;
                } else {
                    $return = self::OES_PROCESSING;
                }
            }
            unset($orderProductStatusArray);
            $orderStatusRecord = \common\models\OrdersStatus::getDefaultByOrderEvaluationState($return, $orderStatusPreferred);
            if (!($orderStatusRecord instanceof \common\models\OrdersStatus) AND $return == self::OES_DELIVERED) {
                $return = self::OES_DISPATCHED;
                $orderStatusRecord = \common\models\OrdersStatus::getDefaultByOrderEvaluationState($return, $orderStatusPreferred);
            }
            if (!($orderStatusRecord instanceof \common\models\OrdersStatus) AND $return == self::OES_DISPATCHED) {
                $return = self::OES_RECEIVED;
                $orderStatusRecord = \common\models\OrdersStatus::getDefaultByOrderEvaluationState($return, $orderStatusPreferred);
            }
            if (!($orderStatusRecord instanceof \common\models\OrdersStatus) AND $return == self::OES_RECEIVED) {
                $return = self::OES_PROCESSING;
                $orderStatusRecord = \common\models\OrdersStatus::getDefaultByOrderEvaluationState($return, $orderStatusPreferred);
            }
            /* UNCOMMENT IN CASE OF FULLY AUTOMATIC STATUS CHANGE MODE ONLY!
            if (!($orderStatusRecord instanceof \common\models\OrdersStatus) AND $return == self::OES_PROCESSING) {
                $return = self::OES_PENDING;
                $orderStatusRecord = \common\models\OrdersStatus::getDefaultByOrderEvaluationState($return, $orderStatusPreferred);
            }
            EOF UNCOMMENT IN CASE OF FULLY AUTOMATIC STATUS CHANGE MODE ONLY! */
            $return = $orderStatus;
            if (($orderStatusRecord instanceof \common\models\OrdersStatus) AND $orderStatusRecord->orders_status_id != $return) {
                $isHistory = false;
                try {
                    $orderRecord->orders_status = (int)$orderStatusRecord->orders_status_id;
                    $orderRecord->last_modified = date('Y-m-d H:i:s');
                    $orderRecord->save();
                    $isHistory = true;
                } catch (\Exception $exc) {
                    $orderRecord->orders_status = $return;
                }
                $return = (int)$orderRecord->orders_status;
                if ($isHistory == true) {
                    \common\models\OrdersStatusHistory::write(
                        $orderRecord,
                        $return,
                        TEXT_ORDER_STATUS_AUTO_EVALUATE,
                        0,
                        ''
                    );
                }
                unset($isHistory);
            }
            unset($orderStatusRecord);
            unset($orderStatus);
        }
        unset($orderRecord);
        return $return;
    }

    /**
     * Update allocation allowance status for Order
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @param integer $allocateAllow allowance status value or based on Order Status value by default
     * @return mixed allocation allowance status or false on error
     */
    public static function updateAllocateAllow($orderRecord = 0, $allocateAllow = -1)
    {
        $return = false;
        $allocateAllow = (int)$allocateAllow;
        $orderRecord = self::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            $return = $orderRecord->orders_allocate_allow;
            if ($allocateAllow < 0) {
                $orderStatusRecord = \common\models\OrdersStatus::findOne(['orders_status_id' => $orderRecord->orders_status]);
                if ($orderStatusRecord instanceof \common\models\OrdersStatus) {
                    if ($orderStatusRecord->orders_status_allocate_allow > 0 AND $orderRecord->orders_allocate_allow != $orderStatusRecord->orders_status_allocate_allow) {
                        try {
                            $orderRecord->orders_allocate_allow = $orderStatusRecord->orders_status_allocate_allow;
                            $orderRecord->save();
                            $return = $orderRecord->orders_allocate_allow;
                        } catch (\Exception $exc) {}
                    }
                }
                unset($orderStatusRecord);
            } elseif ($orderRecord->orders_allocate_allow != $allocateAllow) {
                try {
                    $orderRecord->orders_allocate_allow = $allocateAllow;
                    $orderRecord->save();
                    $return = $orderRecord->orders_allocate_allow;
                } catch (\Exception $exc) {}
            }
        }
        unset($allocateAllow);
        unset($orderRecord);
        return $return;
    }

    /**
     * Check is order stock should be allocated as temporary
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @return boolean allocate as temporary
     */
    public static function isAllocateTemporary($orderRecord = 0)
    {
        $return = false;
        $orderRecord = self::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            $orderStatusRecord = \common\models\OrdersStatus::findOne(['orders_status_id' => $orderRecord->orders_status]);
            if ($orderStatusRecord instanceof \common\models\OrdersStatus) {
                $orderStatusGroupRecord = \common\models\OrdersStatusGroups::findOne(['orders_status_groups_id' => $orderStatusRecord->orders_status_groups_id]);
                if ($orderStatusGroupRecord instanceof \common\models\OrdersStatusGroups) {
                    $return = ((int)$orderStatusGroupRecord->orders_status_groups_store_temporary > 0);
                }
            }
            unset($orderStatusRecord);
        }
        unset($orderRecord);
        return $return;
    }

    /**
     * Set Order status (triggering binded order evaluation state update).
     * Behaviour $isAlternativeBehaviour:
     * OES_PENDING - defines should Cancelled quantity be reset to 0;
     * OES_PROCESSING - none;
     * OES_CANCELLED - defines should Dispatched quantity be returned to stock;
     * OES_DISPATCHED - defines should be Order products set as Dispatched even if there is no stock available;
     * OES_DELIVERED - defines should be Order products set as Delivered even if there is quantity awaiting for Dispatch
     * @param integer|\common\models\Orders $orderRecord Order Id or instance of Orders model
     * @param integer $orderStatus desired order status
     * @param array $historyArray Order Status History record parameters
     * @param boolean $isIgnoreBindEvaluationState if true - Order Evaluation State event binded to Order Status wouldn't be triggered
     * @param boolean $isAlternativeBehaviour switch order processing behaviour depending on binded order status evaluation state
     * @return mixed false on error or current order status
     */
    public static function setStatus($orderRecord = 0, $orderStatus = 0, $historyArray = [], $isIgnoreBindEvaluationState = false, $isAlternativeBehaviour = false)
    {
        $__orders_id = 0;
        $return = false;
        $orderRecord = self::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            $__orders_id = $orderRecord->orders_id;
            $isHistory = false;
            $orderStatus = (int)$orderStatus;
            $return = (int)$orderRecord->orders_status;
            $isAlternativeBehaviour = ((int)$isAlternativeBehaviour > 0);
            $historyArray = (is_array($historyArray) ? $historyArray : []);
            $isIgnoreBindEvaluationState = ((int)$isIgnoreBindEvaluationState > 0);
            $orderStatusRecord = \common\models\OrdersStatus::findOne(['orders_status_id' => $orderStatus]);
            if ($orderStatusRecord instanceof \common\models\OrdersStatus) {
              if ($isIgnoreBindEvaluationState == false) {
                    if ($orderStatusRecord->order_evaluation_state_id == self::OES_PENDING) {
                        self::doPendent($orderRecord, $isAlternativeBehaviour, $orderStatus);
                    } elseif ($orderStatusRecord->order_evaluation_state_id == self::OES_PROCESSING) {
                        self::doProcess($orderRecord, $isAlternativeBehaviour, $orderStatus);
                    } elseif ($orderStatusRecord->order_evaluation_state_id == self::OES_CANCELLED) {
                        self::doCancel($orderRecord, $isAlternativeBehaviour, $orderStatus);
                    } elseif ($orderStatusRecord->order_evaluation_state_id == self::OES_DISPATCHED) {
                        self::doDispatch($orderRecord, $isAlternativeBehaviour, $orderStatus);
                    } elseif ($orderStatusRecord->order_evaluation_state_id == self::OES_DELIVERED) {
                        self::doDeliver($orderRecord, $isAlternativeBehaviour, $orderStatus);
                    }
                }
                self::doRefresh($orderRecord, $isAlternativeBehaviour, $orderStatus);
                $return = (int)$orderRecord->orders_status;
                if ($return != $orderStatus) {
                    try {
                        $orderRecord->orders_status = $orderStatus;
                        $orderRecord->last_modified = date('Y-m-d H:i:s');
                        $orderRecord->save();
                        $isHistory = true;
                    } catch (\Exception $exc) {
                        $orderRecord->orders_status = $return;
                        \Yii::warning($exc->getMessage() . ' ' . $exc->getTraceAsString());
                    }
                    $return = (int)$orderRecord->orders_status;
                }

                if ($orderStatusRecord->orders_status_release_deferred == 1) {
                    try {
                        $orderPaymentRecordArray = \common\models\OrdersPayment::find()
                            ->where(['orders_payment_order_id' => $__orders_id])
                            ->andWhere(['deferred' => 1])
                            ->orderBy(['orders_payment_date_create' => SORT_DESC, 'orders_payment_id' => SORT_DESC])
                            ->all();
                        if (is_array($orderPaymentRecordArray) && count($orderPaymentRecordArray) > 0) {
                            $manager = \common\services\OrderManager::loadManager();
                            foreach ($orderPaymentRecordArray as $orderPaymentRecord) {
                                $payment = $manager->getPaymentCollection($orderPaymentRecord['orders_payment_module'])->getSelectedPayment();
                                if (is_object($payment) && method_exists($payment, 'release')){
                                    $payment->release($orderPaymentRecord['orders_payment_transaction_id'], $orderStatus);
                                }
                                unset($payment);
                            }
                            unset($orderPaymentRecord);
                            unset($manager);
                        }
                        unset($orderPaymentRecordArray);
                    } catch (\Exception $exc) {
                        \Yii::warning($exc->getMessage() . ' ' . $exc->getTraceAsString());
                    }
                }
            }
            unset($orderStatusRecord);
            $comments = trim(isset($historyArray['comments']) ? $historyArray['comments'] : '');
            $smscomments = trim(isset($historyArray['smscomments']) ? $historyArray['smscomments'] : '');
            $dateAdded = isset($historyArray['date_added'])?$historyArray['date_added']:null;
            $isNotified = (isset($historyArray['customer_notified']) ? (((int)$historyArray['customer_notified'] > 0) ? 1 : 0) : 0);
            if (($isHistory == true) OR ($comments != '') OR ($smscomments != '') OR ($isNotified > 0)) {
                \common\models\OrdersStatusHistory::write(
                    $orderRecord,
                    $return,
                    $comments,
                    $isNotified,
                    $smscomments,
                    $dateAdded
                );
            }
            unset($isNotified);
            unset($isHistory);
            unset($comments);
            self::doRefresh($orderRecord, $isAlternativeBehaviour, $return);

            foreach (\common\helpers\Hooks::getList('orders/after-setstatus') as $filename) {
                include($filename);
            }
        }
        unset($isIgnoreBindEvaluationState);
        unset($isAlternativeBehaviour);
        unset($historyArray);
        unset($orderStatus);
        unset($orderRecord);


        return $return;
    }

    /**
     * Search and cancel expired temporary stock allocation. Order is cancelled too if possible.
     * Cancel expired orders in status from "Temporary allocate order products" flag enabled status groups.
     * Behaviour: ORDER_STATUS_TEMPORARY_ALLOCATION_EXPIRED_DURATION >= 1
     * @return boolean always true
     */
    public static function doCancelAllocatedTemporaryExpired()
    {
        \common\helpers\Translation::init('admin/main');
        $orderStatusExpired = (int)\common\helpers\Configuration::get_configuration_key_value('ORDER_STATUS_TEMPORARY_ALLOCATION_EXPIRED');
        $orderStatusExpiredDurationHours = (int)\common\helpers\Configuration::get_configuration_key_value('ORDER_STATUS_TEMPORARY_ALLOCATION_EXPIRED_DURATION');
        if ($orderStatusExpiredDurationHours < 1) {
            $orderStatusExpiredDurationHours = 1;
        }
        foreach (\common\models\OrdersProductsAllocate::find()
            ->select(['orders_id'])
            ->where(['is_temporary' => 1])
            ->andWhere(['<', 'datetime', date('Y-m-d H:i:s', strtotime("-{$orderStatusExpiredDurationHours} hours"))])
            ->groupBy('orders_id')
            ->asArray(true)
            ->all() as $orderId
        ) {
            $orderId = (int)$orderId['orders_id'];
            $isExpired = null;
            $isTemporary = null;
            $opaRecordArray = [];
            foreach (\common\models\OrdersProductsAllocate::find()
                ->andWhere(['orders_id' => $orderId])
                ->asArray(false)
                ->all() as $opaRecord
            ) {
                $isTemporary = (is_null($isTemporary) ? true : $isTemporary);
                if ($opaRecord->is_temporary <= 0) {
                    $isTemporary = false;
                    continue;
                }
                $isExpired = false;
                if (strtotime($opaRecord->datetime) < strtotime("-{$orderStatusExpiredDurationHours} hours")) {
                    $isExpired = true;
                }
                if ($isExpired === false) {
                    break;
                }
                $opaRecordArray[] = $opaRecord;
            }
            unset($opaRecord);
            if ($isExpired === true) {
                try {
                    foreach ($opaRecordArray as $opaRecord) {
                        \common\helpers\OrderProduct::doCancel($opaRecord->orders_products_id, false);
                    }
                } catch (\Exception $exc) {}
                unset($opaRecord);
                try {
                    if ($isTemporary === true AND $orderStatusExpired > 0) {
                        self::setStatus($orderId, $orderStatusExpired, [], false, false);
                    } else {
                        self::evaluate($orderId);
                    }
                } catch (\Exception $exc) {}
            }
            unset($opaRecordArray);
            unset($isTemporary);
            unset($isExpired);
        }
        unset($orderId);
        $temporaryAllocateOrderStatusIdList = \common\models\OrdersStatusGroups::find()->alias('osg')
            ->leftJoin(\common\models\OrdersStatus::tableName() . ' os', 'os.orders_status_groups_id = osg.orders_status_groups_id AND os.language_id = osg.language_id')
            ->where(['osg.orders_status_groups_store_temporary' => 1])
            ->groupBy(['os.orders_status_id'])->asArray(true)->select('os.orders_status_id')->column();
        foreach (\common\models\Orders::find()
            ->where(['in', 'orders_status', $temporaryAllocateOrderStatusIdList])
            ->andWhere(['or',
                ['and',
                    ['!=', 'last_modified', '0000-00-00 00:00:00'],
                    ['<', 'last_modified', date('Y-m-d H:i:s', strtotime("-{$orderStatusExpiredDurationHours} hours"))]
                ],
                ['and',
                    ['last_modified' => '0000-00-00 00:00:00'],
                    ['<', 'date_purchased', date('Y-m-d H:i:s', strtotime("-{$orderStatusExpiredDurationHours} hours"))]
                ]
            ])
            ->asArray(true)->select('orders_id')->column() as $orderId
        ) {
            try {
                if ($orderStatusExpired > 0) {
                    self::setStatus($orderId, $orderStatusExpired, [], false, false);
                } else {
                    \common\helpers\Order::doCancel($orderId, false, 0);
                }
            } catch (\Exception $exc) {}
        }
        unset($temporaryAllocateOrderStatusIdList);
        unset($orderId);
        return true;
    }

    /**
     * Get Order record
     * @param mixed $orderId Order Id or instance of Orders model
     * @return mixed instance of Orders model or null
     */
    public static function getRecord($orderId = 0)
    {
        return ($orderId instanceof \common\models\Orders
            ? $orderId
            : \common\models\Orders::findOne(['orders_id' => (int)$orderId])
        );
    }

    /**
     * Get Order Product array
     * @param mixed $orderRecord Order Id or instance of Orders model
     * @param boolean $asArray switching return type between array of arrays or array of instances of OrdersProducts
     * @return array array of mixed depending on $asArray parameter
     */
    public static function getProductArray($orderRecord = 0, $asArray = true)
    {
        $return = [];
        $orderRecord = self::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            foreach ((\common\models\OrdersProducts::find()
                ->where(['orders_id' => (int)$orderRecord->orders_id])
                ->asArray($asArray)->all()) as $opRecord
            ) {
                $return[] = $opRecord;
            }
            unset($opRecord);
        }
        unset($orderRecord);
        unset($asArray);
        return $return;
    }

    /**
     * Get configuration array of possible automated evaluation states
     * @return array configuration array of possible automated evaluation states
     */
    public static function getEvaluationStateArray()
    {
        return [
            self::OES_PENDING => [
                'long' => 'Pending',
                'short' => 'Pndg',
                'key' => 'OES_PENDING'
            ],
            self::OES_PROCESSING => [
                'long' => 'Processing',
                'short' => 'Proc',
                'key' => 'OES_PROCESSING'
            ],
            self::OES_RECEIVED => [
                'long' => 'Received',
                'short' => 'Rcvd',
                'key' => 'OES_RECEIVED'
            ],
            self::OES_DISPATCHED => [
                'long' => 'Dispatched',
                'short' => 'Dspd',
                'key' => 'OES_DISPATCHED'
            ],
            self::OES_DELIVERED => [
                'long' => 'Delivered',
                'short' => 'Dlvd',
                'key' => 'OES_DELIVERED'
            ],
            self::OES_CANCELLED => [
                'long' => 'Cancelled',
                'short' => 'Cnld',
                'key' => 'OES_CANCELLED'
            ],
            self::OES_PARTIAL_CANCELLED => [
                'long' => 'Partially Cancelled',
                'short' => 'PartCnld',
                'key' => 'OES_PARTIAL_CANCELLED'
            ]
        ];
    }

    public static function getOrdersQuery(array $fields){
        $cQuery = \common\models\Orders::find()
                ->select(array_keys($fields))
                ->where('1=1');
        foreach($fields as $field => $value){
            if (is_array($value)){
                $cQuery->andWhere(['in', $field, $value]);
            } else if (is_string($value) && !empty($value)){
                $cQuery->andWhere(['like', $field, $value]);
            }
        }
        return $cQuery;
    }

    public static function getPurchaseOrderId(\common\classes\extended\OrderAbstract $order){
        return (!empty($order->info['purchase_order']) ? ' #'.$order->info['purchase_order'] : '');
    }

    public static function getOrderVolumeWeight(int $order_id)
    {
        $shipmentVolume = 0;
        $orderProducts = \common\models\OrdersProducts::find()->where(['orders_id' => $order_id])->asArray()->all();
        foreach ($orderProducts as $product) {
            $shipmentVolume += (\common\helpers\Product::get_products_volume((int)$product['products_id'], true) * $product['products_quantity']);
        }
        return $shipmentVolume;
    }

/**
 * query cost and profit amount on order. Ordered product should be allocated (assigned to supplier and its price)
 * @param int|array $orders_ids
 * @return array|null
 */
    public static function getProfit($orders_ids) {
      $ret = null;
      if (is_array($orders_ids)) {
        $orders_ids = array_map('intval', $orders_ids);
      }

      if ($orders_ids) {
         $q = (new \yii\db\Query())
            ->select([
              'sum(opa.allocate_received * opa.suppliers_price) as cost',
              'sum(opa.allocate_received * (op.final_price - opa.suppliers_price)) as profit',
              'sum(opa.allocate_received * (op.final_price - opa.suppliers_price)) / sum(opa.allocate_received * opa.suppliers_price) * 100 as profit_percent'])
            ->from(['op' => TABLE_ORDERS_PRODUCTS])
            ->leftJoin(['opa' => 'orders_products_allocate'], 'op.orders_products_id = opa.orders_products_id')
            ->andWhere(['op.orders_id' => $orders_ids])
            ->andWhere('opa.allocate_received > 0 and opa.suppliers_price > 0')
            ;
        if (is_array($orders_ids)) {
          $q->addSelect('op.orders_id')->groupBy('op.orders_id')->indexBy('orders_id');
          $ret = $q->all();
        } else {
          $ret = $q->one();
        }
      }
      return $ret;
    }

    public static function anonimizeOrder($orders_id, $table = ''){
      $removedId = \common\helpers\Customer::findCreateAnonymousCustomer();
      $sqlData = [
          'customers_id' => (int)$removedId,
          'basket_id' => 0,
          'customers_name' => 'removed',
          'customers_firstname' => 'removed',
          'customers_lastname' => 'removed',
          'customers_company' => '',
          'customers_company_vat' => '',
          'customers_customs_number' => '',
          'customers_street_address' => '',
          'customers_suburb' => '',
          'customers_city' => '',
          'customers_postcode' => '',
          //customers_state
          //customers_country
          'customers_telephone' => '',
          'customers_email_address' => 'removed',
          'delivery_gender' => '',
          'delivery_name' => 'removed',
          'delivery_firstname' => 'removed',
          'delivery_lastname' => 'removed',
          'delivery_company' => '',
          'delivery_street_address' => '',
          'delivery_suburb' => '',
          'delivery_city' => '',
          'delivery_postcode' => '',
          //delivery_state
          //delivery_country
          'delivery_address_book_id' => 0,
          'billing_gender' => '',
          'billing_name' => 'removed',
          'billing_firstname' => 'removed',
          'billing_lastname' => 'removed',
          'billing_company' => '',
          'billing_street_address' => '',
          'billing_suburb' => '',
          'billing_city' => '',
          'billing_postcode' => '',
          //billing_state
          //billing_country
          'billing_address_book_id' => 0,
      ];

      $statusCheckWhere = "";
      if (defined('GDPR_CUSTOMER_DELETE_OPEN_ORDER_STATUSES') && !empty(trim(GDPR_CUSTOMER_DELETE_OPEN_ORDER_STATUSES))) {
        $tmp = array_map('intval', explode(',', GDPR_CUSTOMER_DELETE_OPEN_ORDER_STATUSES));
        if (is_array($tmp ) && !empty($tmp )) {
          $statusCheckWhere = " and orders_status not in (" . implode(",", $tmp) . ")";
        }
      }
      if (empty($table)) {
        $table = TABLE_ORDERS;
      } elseif (!in_array($table, [TABLE_ORDERS, 'quote_'. TABLE_ORDERS, 'sample_' . TABLE_ORDERS, 'tmp_' . TABLE_ORDERS, TABLE_SUBSCRIPTION])) {
        $table = false;
      }
      foreach (\common\helpers\Hooks::getList('orders/order-anonymize') as $filename) {
        include($filename);
      }
    }

    public static function getStatusesDetails($typeId = 1) {
      $languages_id = \Yii::$app->settings->get('languages_id');

      $ret = \common\models\OrdersStatus::find()->alias('os')
          ->leftJoin(['osg' => \common\models\OrdersStatusGroups::tableName()], 'os.orders_status_groups_id=osg.orders_status_groups_id')
          ->select('os.*, osg.*')
          ->andWhere(['orders_status_type_id' => $typeId])
          ->andWhere(['os.language_id' => $languages_id])
          ->andWhere(['osg.language_id' => $languages_id])
          ;
      //echo $ret ->createCommand()->rawSql; die;
         $ret = $ret->asArray()->indexBy('orders_status_id')->all()
          ;
      return $ret;
    }

}
