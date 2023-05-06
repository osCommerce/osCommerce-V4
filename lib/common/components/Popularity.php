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

namespace common\components;

use Yii;
use common\models\Products;
use common\models\ProductsPopularity;

class Popularity {

  public $popularity_conf = ['POPULARITY_WEIGHT_NEW_PRODUCT', 'POPULARITY_WEIGHT_PUCHASE_WEEK', 'POPULARITY_WEIGHT_PUCHASE_MONTH', 'POPULARITY_WEIGHT_PUCHASE', 'POPULARITY_WEIGHT_WISHLIST', 'POPULARITY_WEIGHT_VIEW_WEEK', 'POPULARITY_WEIGHT_VIEW_MONTH', 'POPULARITY_WEIGHT_VIEW'];
  private $days = 30;

  public function __construct() {
    if (defined('POPULARITY_DAYS')) {
      $this->days = intval(POPULARITY_DAYS);
    }
    ProductsPopularity::deleteAll("viewed_date < now() - interval {$this->days} day");
  }

  public function clearCurrentPopularity() {
    Products::updateAll(['products_popularity' => 0]);
  }

  public function process() {
    //$this->clearCurrentPopularity();
    self::calculatePopularity();
  }

  public function updateProductVisit($products_id) {
    if ($products_id && isset($_SESSION['viewed_products']) && !isset($_SESSION['viewed_products'][(int) $products_id])) {
      $todayVisit = ProductsPopularity::find()->where('products_id=:prid and viewed_date = :date', [':prid' => (int) $products_id, ':date' => date("Y-m-d")])->one();
      if ($todayVisit) {
        $todayVisit->updateCounters(['products_viewed' => 1]);
      } else {
        //recalculate total on 1st visit
        self::calculatePopularity($products_id);
        $todayVisit = new ProductsPopularity();
        $todayVisit->setAttributes([
          'products_viewed' => 1,
          'products_id' => (int) $products_id,
          'viewed_date' => date("Y-m-d"),
            ], false);
        $todayVisit->save(false);
      }
      $_SESSION['viewed_products'][(int) $products_id] = (int) $products_id;
      self::addPopularity($products_id, 'view');
    }
  }
  
/**
 *
 * @param int $products_id
 * @param string $what view|wish|buy
 */
  public static function addPopularity($products_id, $what) {
    $add = 0;
    switch ($what) {
      case 'view':
        if (defined('POPULARITY_WEIGHT_VIEW_WEEK') && POPULARITY_WEIGHT_VIEW_WEEK > 0) {
          $add += (float) POPULARITY_WEIGHT_VIEW_WEEK;
        }
        if (defined('POPULARITY_WEIGHT_VIEW_MONTH') && POPULARITY_WEIGHT_VIEW_MONTH > 0) {
          $add += (float) POPULARITY_WEIGHT_VIEW_MONTH;
        }
        if (defined('POPULARITY_WEIGHT_VIEW') && POPULARITY_WEIGHT_VIEW > 0) {
          $add += (float) POPULARITY_WEIGHT_VIEW;
        }
        break;
      case 'wish':
        if (defined('POPULARITY_WEIGHT_WISHLIST') && POPULARITY_WEIGHT_WISHLIST > 0) {
          $add += (float) POPULARITY_WEIGHT_WISHLIST;
        }
        break;
      case 'buy':
        if (defined('POPULARITY_WEIGHT_PUCHASE_WEEK') && POPULARITY_WEIGHT_PUCHASE_WEEK > 0) {
          $add += (float) POPULARITY_WEIGHT_PUCHASE_WEEK;
        }
        if (defined('POPULARITY_WEIGHT_PUCHASE_MONTH') && POPULARITY_WEIGHT_PUCHASE_MONTH > 0) {
          $add += (float) POPULARITY_WEIGHT_PUCHASE_MONTH;
        }
        if (defined('POPULARITY_WEIGHT_PUCHASE') && POPULARITY_WEIGHT_PUCHASE > 0) {
          $add += (float) POPULARITY_WEIGHT_PUCHASE;
        }
        break;
    }
    if ($add > 0) {
      tep_db_query("update " . TABLE_PRODUCTS . " set products_popularity = products_popularity+" . (float) $add . " where products_id='" . (int) $products_id . "'");
    }
  }

  public static function calculatePopularity($products_id = []) {
    $grouped = false;
    $q = (new \yii\db\Query);

    $p_expression = ' 0 ';
    if ((defined('POPULARITY_WEIGHT_VIEW_WEEK') && (float) POPULARITY_WEIGHT_VIEW_WEEK > 0 ) ||
        (defined('POPULARITY_WEIGHT_VIEW_MONTH') && (float) POPULARITY_WEIGHT_VIEW_MONTH > 0)) {
      $p_expression .= "+ (select 0 ";
      if ((defined('POPULARITY_WEIGHT_VIEW_WEEK') && (float) POPULARITY_WEIGHT_VIEW_WEEK > 0)) {
        $p_expression .= " +ifnull(sum(if(pp.viewed_date > now() - interval 7 day, 0, ifnull(pp.products_viewed, 0))),0) * " . (float) POPULARITY_WEIGHT_VIEW_WEEK;
      }
      $tmp = '';
      if (defined('POPULARITY_WEIGHT_VIEW_MONTH') && (float) POPULARITY_WEIGHT_VIEW_MONTH > 0) {
        $p_expression .= " + ifnull(sum(ifnull(pp.products_viewed, 0)),0) * " . (float) POPULARITY_WEIGHT_VIEW_MONTH;
      } elseif ((defined('POPULARITY_WEIGHT_VIEW_WEEK') && (float) POPULARITY_WEIGHT_VIEW_WEEK > 0)) {
        $tmp = " and (pp.viewed_date > now() - interval 7 day) ";
      }
      $p_expression .= " from products_popularity pp where p.products_id = pp.products_id) {$tmp}";
    }

    if (defined('POPULARITY_WEIGHT_VIEW') && (float) POPULARITY_WEIGHT_VIEW > 0) {
      $p_expression .= "+ (select sum(products_viewed) * " . (float) POPULARITY_WEIGHT_VIEW;
      $p_expression .= " from products_description pd where p.products_id = pd.products_id)";
    }

//    if (defined('POPULARITY_WEIGHT_WISHLIST') && (float) POPULARITY_WEIGHT_WISHLIST > 0) {
//      $p_expression .= "+ (select ifnull(sum(products_quantity),0) * " . (float) POPULARITY_WEIGHT_WISHLIST;
//      $p_expression .= " from customers_wishlist wl where p.products_id = wl.products_id)"; //vl2check int=string
//    }

    if ((defined('POPULARITY_WEIGHT_PUCHASE_WEEK') && (float) POPULARITY_WEIGHT_PUCHASE_WEEK > 0 ) ||
        (defined('POPULARITY_WEIGHT_PUCHASE_MONTH') && (float) POPULARITY_WEIGHT_PUCHASE_MONTH > 0)) {

      $q->leftJoin('orders_products op', 'op.products_id = p.products_id')
          ->leftJoin('orders o', 'o.orders_id = op.orders_id and o.date_purchased >= now() - interval :days day',
              [':days' => (defined('POPULARITY_WEIGHT_PUCHASE_MONTH') && (float) POPULARITY_WEIGHT_PUCHASE_MONTH > 0) ? 30 : 7])
          ->groupBy('p.products_id');
      $grouped = true;

      if (defined('POPULARITY_WEIGHT_PUCHASE_WEEK') && POPULARITY_WEIGHT_PUCHASE_WEEK > 0) {
        $p_expression .= " +ifnull(sum(if(o.date_purchased > (now() - interval 7 day), 0, op.products_quantity)),0) * " . (float) POPULARITY_WEIGHT_PUCHASE_WEEK;
      }
      if (defined('POPULARITY_WEIGHT_PUCHASE_MONTH') && POPULARITY_WEIGHT_PUCHASE_MONTH > 0) {
        $p_expression .= " +ifnull(sum(if(o.date_purchased > (now() - interval 7 day), 0, op.products_quantity)),0) * " . (float) POPULARITY_WEIGHT_PUCHASE_MONTH;
      }
    }

    $sp = '0';

    if (defined('POPULARITY_WEIGHT_NEW_PRODUCT') && (float) POPULARITY_WEIGHT_NEW_PRODUCT > 0) {
      //if (defined('NEW_MARK_UNTIL_DAYS') && intval(constant('NEW_MARK_UNTIL_DAYS')) > 0) {
        $sp .= '+ if(p.products_new_until>="' . date(\common\helpers\Date::DATABASE_DATE_FORMAT) . '", ' . (float) POPULARITY_WEIGHT_NEW_PRODUCT . ', 0)';
      //}
    }
    if (defined('POPULARITY_WEIGHT_PUCHASE') && (float) POPULARITY_WEIGHT_PUCHASE > 0) {
      $sp .= '+ p.products_ordered* ' . (float) POPULARITY_WEIGHT_PUCHASE . '';
    }

    if (!empty($products_id)) {
      if (is_array($products_id)) {
        $products_id = array_map('intval', $products_id);
      } else {
        $products_id = [(int) $products_id];
      }
      $q->andWhere(['p.products_id' => $products_id]);
    } else {
      $q->andWhere('p.products_status=1');
    }

    if ($grouped) {
      // create extra subquery
      $q->select('p.products_id')->from('products p');
      $q->addSelect([
        'popularity' => new \yii\db\Expression($p_expression)
      ]);
      $sql = $q->createCommand()->rawSql;
      $query = "update products p, ({$sql}) stat set p.products_popularity={$sp}+ stat.popularity where p.products_id=stat.products_id";
    } else {
      $query = "update products p set p.products_popularity={$sp}+ {$p_expression} where ";
      if (!empty($products_id)) {
        $query .= 'p.products_id in (' . implode(', ', $products_id) . ')';
      } else {
        $query .= 'p.products_status=1';
      }
    }
    tep_db_query($query);
  }

}
