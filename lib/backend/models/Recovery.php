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

namespace backend\models;

use Yii;
use yii\web\Session;

class Recovery
{
  
  /*
  * check if customer is online
  */  
  public static function is_online($customers_id){
    $customer_query = tep_db_fetch_array(tep_db_query("select count(*) as count, ip_address from " . TABLE_WHOS_ONLINE . " where customer_id = '" . tep_db_input($customers_id) . "'"));
    if ($customer_query['count']){
      return $customer_query['ip_address'];
    }
    return false;
  }
  
  public function appendToHistory($order_id, $comments){
    global $login_id;
      if ($order_id > 0){
        $order_status = tep_db_fetch_array(tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id='" . (int)$order_id . "'"));
        if ($order_status['orders_status']){
          tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments, admin_id) values ('" . (int)$order_id . "', '" . tep_db_input($order_status['orders_status']) . "', now(), '1', '" . tep_db_input($comments)  . "', '" . (int)$login_id . "')");
        }
      } 
  }
  
  /*
  * all recovery coupons 
  */  
  public static function getRecoveryCoupons($id = 0){
    
    $query = tep_db_query("select * from ". TABLE_COUPONS . " where coupon_active = 'Y' and coupon_for_recovery_email = 1 /*and coupon_expire_date >= now()*/ " . ($id ? " and coupon_id = '" . (int)$id . "'" : ""));
    $coupons = [];
    if (tep_db_num_rows($query)){
      while($coupon = tep_db_fetch_array($query)){
        $coupon['expired'] = $coupon['coupon_expire_date'] < date("Y-m-d H:i:s") ? true : false;
        $coupons[$coupon['coupon_id']] = $coupon;
      }      
    }
    return $coupons;
  }
  
  /*
  * not yet sent customer coupons 
  */
  public static function getCustomerEmailCouponsNotSended($customer_id, $all_coupons, $basket_id){
    $query = tep_db_query("select coupon_id from " . TABLE_COUPON_EMAIL_TRACK . " where customer_id_sent = '" . (int)$customer_id . "' and basket_id = '" . $basket_id . "'");
    $coupons = [];
    if (tep_db_num_rows($query)){
      while($coupon = tep_db_fetch_array($query)){
        if (array_key_exists($coupon['coupon_id'], $all_coupons)){
          $coupons[$coupon['coupon_id']] = $coupon['coupon_id'];
        }
      }
      $coupons = array_diff_key($all_coupons, $coupons);
      return $coupons;
    }
    return $all_coupons;
  }
  
}
