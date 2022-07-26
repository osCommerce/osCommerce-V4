<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\helpers;

use common\models\EcommerceTracking;

/**
 * EcommerceTracking function for GA e commerce tracking
 *
 * @author vlad
 */
class EcommerceTrackingHelper {

  public static function import($date = '') {
    if (empty($date)) {
      $dateStart = strtotime('2 day ago');
    } else {
      $dateStart = strtotime($date);
    }
    $yearAgo = strtotime('1 year ago');
    if ($dateStart < $yearAgo) {
      $dateStart = $yearAgo;
    }
    $gess = new \common\components\google\GoogleEcommerceSS();
    foreach (\common\classes\platform::getList(false) as $platform) {
      $gess->setPlatformId($platform['id']);
      $orders = $gess->getTransactionsReport([date('Y-m-d', $dateStart)]);
      if (is_array($orders)) {
        $oids = Orders::find()->select('orders_id')->where(['orders_id' => array_map('intval', array_keys($orders))])->asArray()->column();
        foreach ($orders as $orders_id => $order) {
          if (in_array($orders_id, $oids)) {
            \common\helpers\EcommerceTracking::saveET($orders_id, $order);
          }
        }
      }
    }
  }

  /**
   *
   * @param int $orders_id
   * @param array $order GA metrics
   */
  public static function saveET($orders_id, $order) {
    $et = EcommerceTracking::findOne(['orders_id' => $orders_id]);
    if ($et) {
      EcommerceTracking::updateAll([
        'verified' => new \yii\db\Expression('now()'),
        'verified_amount' => (float) $order['ga:transactionRevenue']
          ], ['orders_id' => $orders_id]
      );
    } else {
      $et = new EcommerceTracking();
      $et->setAttributes([
        'orders_id' => (int) $orders_id,
        'via' => 'report',
        'date_added' => new \yii\db\Expression('now()'),
        'verified' => new \yii\db\Expression('now()'),
        'verified_amount' => (float) $order['ga:transactionRevenue']
          ], false);
      $et->save(false);
    }
  }

  public static function updateStat($oid = false) {
    if ($oid) {
      $orderOnly = new \common\classes\Order($oid);
      $dateStart = strtotime($orderOnly->info['date_purchased']);
    } else {
      $q = \common\models\EcommerceTracking::find()->select('date_added')->where(['is', 'verified', null])->orderBy('date_added')->limit(1)->asArray()->one();
      if ($q) {
        $dateStart = strtotime($q['date_added']);
      }
    }
    $yearAgo = strtotime('1 year ago');
    if ($dateStart < $yearAgo) {
      $dateStart = $yearAgo;
    }
    $gess = new \common\components\google\GoogleEcommerceSS();
    //2do add for all platforms
    foreach (\common\classes\platform::getList(false) as $platform) {
      if ($orderOnly && $platform['id'] != $orderOnly->info['platform_id']) {
        continue;
      }
      $gess->setPlatformId($platform['id']);
      $orders = $gess->getTransactionsReport([date('Y-m-d', $dateStart)]);
      if (is_array($orders)) {
        foreach ($orders as $orders_id => $order) {
          if ($orderOnly && $orders_id != $oid) {
            //do not update other as there coud be incorrect data (total)
            continue;
          }
          EcommerceTracking::updateAll([
            'verified' => new \yii\db\Expression('now()'),
            'verified_amount' => (float) $order['ga:transactionRevenue']
              ], ['orders_id' => $orders_id]
          );
        }
      }
    }
  }

}
