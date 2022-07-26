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

use Yii;
use common\models\Coupons;

class Coupon {

    public static function get_coupon_name($cc_id) {
        $coupon = tep_db_fetch_array(tep_db_query("select coupon_code from " . TABLE_COUPONS . " where coupon_id = '" . $cc_id . "'"));
        return $coupon['coupon_code'];
    }

    /**
     *
     * @param string $code
     * @param bool $asArray
     * @return false|array|object
     */
    public static function getCouponByCode($code, $asArray = true)
    {
        if (empty($code)){
            return false;
        }
        $main_counter = \common\models\Coupons::find()
            ->alias('c')
            ->where(['c.coupon_code' => $code])
            ->asArray($asArray)->one();

        if (!empty($main_counter)) return $main_counter;

        $customer_restrict_counter = \common\models\Coupons::find()
            ->alias('c')
            ->join('inner join', \common\models\CouponsCustomerCodesList::tableName() . ' ccl', 'c.coupon_id=ccl.coupon_id')
            ->where(['ccl.coupon_code' => $code])
            ->asArray($asArray)->one();

        if (!empty($customer_restrict_counter)) return $customer_restrict_counter;

        return false;
    }

    public static function isCouponCodeExists($code)
    {
        $main_counter = \common\models\Coupons::find()
            ->alias('c')
            ->where(['c.coupon_code' => $code])
            ->count();

        if ($main_counter > 0) return true;

        $customer_restrict_counter = \common\models\CouponsCustomerCodesList::find()
            ->alias('ccl')
            ->join('inner join', \common\models\Coupons::tableName() . ' c', 'c.coupon_id=ccl.coupon_id')
            ->where(['ccl.coupon_code' => $code])
            ->count();

        if ($customer_restrict_counter > 0) return true;

        return false;
    }

    public static function create_prefixed_code($prefix)
    {
        return static::create_coupon_code(md5(date('c')), 8, $prefix);
    }

    public static function create_coupon_code($salt="secret", $length = 8, $prefix='') {
        $ccid = md5(uniqid("","salt"));
        $ccid .= md5(uniqid("","salt"));
        $ccid .= md5(uniqid("","salt"));
        $ccid .= md5(uniqid("","salt"));
        srand((double)microtime()*1000000); // seed the random number generator
        $random_start = @rand(0, (128-$length));
        $good_result = 0;
        $prefix = trim($prefix);
        while ($good_result == 0) {
            $id1 = $prefix . substr($ccid, $random_start, $length);
            //if (!Coupons::getCouponByCode($id1)) $good_result = 1; // too slow for unique check
            if (!static::isCouponCodeExists($id1)) $good_result = 1;
        }
        return $id1;
  }
  
   public static function gv_account_update($customer_id, $gv_id) {
    $customer_gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
    $coupon_gv_query = tep_db_query("select coupon_code, coupon_amount, coupon_currency from " . TABLE_COUPONS . " where coupon_id = '" . (int)$gv_id . "' and coupon_active='Y'");
    $coupon_gv = tep_db_fetch_array($coupon_gv_query);
    if (tep_db_num_rows($customer_gv_query) > 0) {
      $customer_gv = tep_db_fetch_array($customer_gv_query);
      $currencies = Yii::$container->get('currencies');
      $new_gv_amount = $customer_gv['amount'] + $coupon_gv['coupon_amount']/* * $currencies->get_market_price_rate($coupon_gv['coupon_currency'], $customer_gv['currency'])*/;
   // new code bugfix
      tep_db_query("update " . TABLE_CUSTOMERS . " set credit_amount = '" . tep_db_input($new_gv_amount) . "' where customers_id = '" . (int)$customer_id . "'");
	 // original code $gv_query = tep_db_query("update " . TABLE_COUPON_GV_CUSTOMER . " set amount = '" . $new_gv_amount . "'");
      tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY, array(
        'customers_id' => $customer_id,
        'credit_prefix' => '+',
        'credit_amount' => $coupon_gv['coupon_amount'],
        'currency' => DEFAULT_CURRENCY,
        'currency_value' => $currencies->currencies[DEFAULT_CURRENCY]['value'],
        'customer_notified' => 0,
        'comments' => 'Redeem '.$customer_gv['coupon_code'],
        'date_added' => 'now()',
        'admin_id' => 0,
      ));
    }
  }
  
  public static function credit_order_check_state($order_id) {
    $release_statuses = array_map('intval',explode(',',MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID));
    $ordered_gv_r = tep_db_query(
      "SELECT o.orders_id, o.customers_id, o.orders_status, op.products_model, op.orders_products_id, op.final_price, op.products_tax, op.products_quantity, op.gv_state ".
      "FROM ".TABLE_ORDERS_PRODUCTS." op, ".TABLE_ORDERS." o ".
      "WHERE o.orders_id='".(int)$order_id."' AND op.orders_id=o.orders_id AND op.gv_state='pending' "
    );
    if ( tep_db_num_rows($ordered_gv_r)>0 ) {
      while( $ordered_gv = tep_db_fetch_array($ordered_gv_r) ) {
        if ($ordered_gv['gv_state']=='pending' && in_array((int)$ordered_gv['orders_status'], $release_statuses) ) {
          self::credit_order_product_release($ordered_gv);
        }
      }
    }
  }

  public static function credit_order_product_release($order_products) {
    $currencies = \Yii::$container->get('currencies');
    $release_info = false;
    if ( is_array($order_products) && array_key_exists('customers_id',$order_products) && array_key_exists('orders_products_id',$order_products) ) {
      $release_info = $order_products;
    }
    if ( is_numeric($order_products) ) {
      $ordered_gv_r = tep_db_query(
        "SELECT o.orders_id, o.customers_id, o.orders_status, op.products_model, op.orders_products_id, op.final_price, op.products_tax, op.products_quantity, op.gv_state ".
        "FROM ".TABLE_ORDERS_PRODUCTS." op, ".TABLE_ORDERS." o ".
        "WHERE op.orders_products_id='".(int)$order_products."' AND op.orders_id=o.orders_id "
      );
      if ( tep_db_num_rows($ordered_gv_r)>0 ) {
        $release_info = tep_db_fetch_array($ordered_gv_r);
      }
    }
    if ( is_array($release_info) && !empty($release_info['gv_state']) && $release_info['gv_state']!='released' && $release_info['gv_state']!='none' ){
      $gv_order_amount = ($release_info['final_price'] * $release_info['products_quantity']);
      if (MODULE_ORDER_TOTAL_GV_CREDIT_TAX=='true') $gv_order_amount = $gv_order_amount * (100 + $release_info['products_tax']) / 100;
      $gv_order_amount = $gv_order_amount * 100 / 100;

      tep_db_query("update " . TABLE_CUSTOMERS . " set credit_amount = credit_amount + '" . tep_db_input($gv_order_amount) . "' where customers_id = '" . (int)$release_info['customers_id'] . "'");
      tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY, array(
        'customers_id' => $release_info['customers_id'],
        'credit_prefix' => '+',
        'credit_amount' => $gv_order_amount,
        'currency' => DEFAULT_CURRENCY,
        'currency_value' => $currencies->currencies[DEFAULT_CURRENCY]['value'],
        'customer_notified' => 0,
        'comments' => 'Order '. $release_info['products_model'].' order #'.$release_info['orders_id'],
        'date_added' => 'now()',
        'admin_id' => 0,
      ));
      tep_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS." SET gv_state='released' WHERE orders_products_id='".$release_info['orders_products_id']."' ");
    }
  }
  
  public static function credit_order_manual_update_state($orders_products_id, $new_state) {
    $ordered_gv_r = tep_db_query(
      "SELECT o.orders_id, o.customers_id, o.orders_status, op.products_model, op.orders_products_id, op.final_price, op.products_tax, op.products_quantity, op.gv_state ".
      "FROM ".TABLE_ORDERS_PRODUCTS." op, ".TABLE_ORDERS." o ".
      "WHERE op.orders_id=o.orders_id AND op.gv_state!='none' ".
      " AND op.orders_products_id='".(int)$orders_products_id."' "
    );
    if ( tep_db_num_rows($ordered_gv_r)>0 ) {
      while( $ordered_gv = tep_db_fetch_array($ordered_gv_r) ) {
        if ( $ordered_gv['gv_state']=='released' ) continue;
        if ($new_state=='released' && ($ordered_gv['gv_state']=='pending' || $ordered_gv['gv_state']=='canceled') ) {
          credit_order_product_release($ordered_gv);
        }elseif ($new_state=='pending' && $ordered_gv['gv_state']=='canceled') {
          tep_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS." SET gv_state='{$new_state}' WHERE orders_products_id='".$ordered_gv['orders_products_id']."' ");
        }elseif ($new_state=='canceled' && $ordered_gv['gv_state']=='pending') {
          tep_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS." SET gv_state='{$new_state}' WHERE orders_products_id='".$ordered_gv['orders_products_id']."' ");
        }
      }
    }
  }

    public static function generate_customer_gvcc($coupon_id, $mail, $amount, $currency, $customer_id = 0, $basket_id = 0){
	  $currencies = new \common\classes\Currencies();
    if (!$coupon_id){
        $id1 = \common\helpers\Coupon::create_coupon_code($mail);
        // Now create the coupon main and email entry
        
        $insert_query = tep_db_query("insert into " . TABLE_COUPONS . " (coupon_code, coupon_type, coupon_amount, coupon_currency, date_created) values ('" . $id1 . "', 'G', '" . $amount . "', '" . tep_db_input($currency) . "', now())");
        $insert_id = tep_db_insert_id();      
        $amount = $currencies->format($amount, false, $currency);
        $type = "G";
    } else {
        $coupon = tep_db_fetch_array(tep_db_query("select coupon_code, coupon_amount, coupon_currency, coupon_type from " . TABLE_COUPONS . " where coupon_id = '" . (int)$coupon_id . "'"));
        $id1 = $coupon['coupon_code'];
        $insert_id = $coupon_id;
        if ($coupon['coupon_type'] == 'P') {
            $amount = number_format($coupon['coupon_amount'], 2) . '%';
        } else {
            $amount = $currencies->format($coupon['coupon_amount'], false, $coupon['coupon_currency']);
        }        
        $type = "C";
    }
    $admin_fname = 'Admin';
    $admin_lname = '';
    if (class_exists('\backend\models\Admin')){
      $admin = new \backend\models\Admin();
      $admin_fname = $admin->getInfo('admin_firstname');
      $admin_lname = $admin->getInfo('admin_lastname');
    }
      
	  $insert_query = tep_db_query("insert into " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, basket_id, sent_firstname, sent_lastname, emailed_to, date_sent) values ('" . $insert_id ."', '" . (int)$customer_id . "', '" . (int)$basket_id . "','" . tep_db_input($admin_fname) . "', '" . tep_db_input($admin_lname) . "', '" . $mail . "', now() )"); 
    return ['id1' => $id1, 'amount' => $amount, 'type' => $type];
  }
  
 /**
 * returns array of active coupons (for HTML pulldown menu)
 * @param int $selected
 * @return array
 */
  public static function getActiveList($selected = ''){

    $coupons = Coupons::find()->where(['coupon_active' => 'Y'])->orderBy('coupon_code')->all();
    $ret = [];
    $ret[] = [
      'name' => TEXT_ALL,
      'value' => '',
      'selected' => '',
    ];

    if (is_array($coupons)) {
      foreach($coupons as $coupon) {
        $ret[] = [
          'name' => $coupon->coupon_code, //full_name,
          'value' => $coupon->coupon_id,
          'selected' => ($selected && $selected==$coupon->coupon_id?'selected':''),
        ];
      }
    }
    unset($coupons);
    return $ret;
  }
  
  public static function getOrderedList(){
    $coupons = \common\models\Coupons::find()->innerJoinWith([
        'redeemTrack' => function(\yii\db\ActiveQuery $query){
            $query->innerJoinWith(['order'])
                  ->where(['!=', 'order_id', 0]);
        }
        ])->asArray()->all();
    return $coupons;
  }
  
    public static function saveCSVCustomersCoupons($couponCSVLoadedCouponFileName, $cid)
    {
        $path = \Yii::getAlias('@webroot');
        $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        $tmp_name = $path . $couponCSVLoadedCouponFileName;
        if(file_exists($tmp_name)) {
            
            @ini_set("auto_detect_line_endings", true);
            $dataToSave = [];
            $row = 0;
            if (($handle = fopen($tmp_name, "r")) !== false) {
                $validator = new \yii\validators\EmailValidator();
                while (($data = fgetcsv($handle, 300, ',', '"')) !== false) {
                    if ($row==0) {
                        $row++;
                        continue;
                    }                   
                    if (count($data)!=2) {
                        fclose($handle);
                        //@unlink($tmp_name);
                        //return "Unable to import. CSV file is not valid.";
                        return;
                    }
                    
                    if(empty($data[0]) || empty($data[1])) {
                        continue;
                    }
                    $email = trim($data[0]);
                    $code = trim($data[1]);
                    if(!$validator->validate($email)) {
                        continue;
                    }
                    if (Coupons::getCouponByCode($code, true)) { //exists, and active
                      continue;
                    }
                    $dataToSave[] = [$cid, $code, $email, date('Y-m-d')];
                }
                
                if (count($dataToSave)) {
                    \Yii::$app->db->createCommand()->batchInsert(
                        \common\models\CouponsCustomerCodesList::tableName(), 
                        ['coupon_id', 'coupon_code', 'only_for_customer', 'date_added'], 
                        $dataToSave
                    )->execute();
                }
                fclose($handle);
            }            
            @unlink($tmp_name);
        }
        return !empty($dataToSave);
    }
    
    public static function getCustomersCouponsEmailsList($cid)
    {
        if (!$cid) {
            return '';
        }
        
        $records = \common\models\CouponsCustomerCodesList::find()
            ->andWhere(['coupon_id' => (int) $cid])
            ->asArray()
            ->all();
        
        if (empty($records)) {
            return '';
        }
        
        $emails = [];
        foreach ($records as $value) {
            $emails[] = $value['only_for_customer'];
        }
        
        return implode(', ', $emails);
    }

    public static function couponUsedBy($coupon_id, $email) {
       return \common\models\CouponRedeemTrack::find()->alias('crt')
          ->innerJoin(TABLE_ORDERS . ' o', 'crt.order_id = o.orders_id')
          ->andWhere(['coupon_id' => $coupon_id])
          ->andWhere(['o.customers_email_address' => $email])
          ->count();
    }


}
