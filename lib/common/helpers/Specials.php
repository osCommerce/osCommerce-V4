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

use Yii;

class Specials {

  public static function validateSave(
      $products_id,
      $special_price,
      $prices,
      $specials_id = 0,
      $status = 1,
      $specials_start_date = '',
      $specials_expires_date = '',
      $specials_type_id = 0,
      $specials_disabled = 0,
      $specials_enabled  = 0,
      $promote_type = 0
      , $total_qty = 0, $max_per_order = 0
      ) {
      $validateError = false;

      if (defined('SALE_STRICT_DATE') && SALE_STRICT_DATE=='True') {
      //check update other specials
      //start date in other special range
        if (!empty($specials_start_date)) {
          $e = \common\models\Specials::find()->select('specials_id, start_date ')->andWhere("specials_id <> '" . (int) $specials_id . "'")->andWhere(['products_id' => $products_id])
              ->startBefore($specials_start_date)
              ->endAfter($specials_start_date)->asArray()->all();
          if (!empty($e)) {
            if (defined('ALLOW_SALES_UPDATE_DATES') && ALLOW_SALES_UPDATE_DATES == 'True') {
              $ids = \yii\helpers\ArrayHelper::getColumn($e, 'specials_id');
              $columns = [
                'start_date' => new \yii\db\Expression('if(start_date="' . tep_db_input($specials_start_date) . '", '
                    . ' DATE_SUB("' . tep_db_input($specials_start_date) . '", INTERVAL 1 SECOND), start_date)'),
                'specials_last_modified' => new \yii\db\Expression('now()'),
                'expires_date' => new \yii\db\Expression("DATE_SUB('" . tep_db_input($specials_start_date) . "', INTERVAL 1 SECOND)")
              ];
              \common\models\Specials::updateAll($columns, ['specials_id' => $ids]);
            } else {
             $validateError = true;
            }
          }
        }

        //end date in other special range
        if (!$validateError && !empty($specials_expires_date)) {
          $e = \common\models\Specials::find()->select('specials_id')->andWhere("specials_id <> '" . (int) $specials_id . "'")->andWhere(['products_id' => $products_id])
              ->startBefore($specials_expires_date)
              ->endAfter($specials_expires_date)->asArray()->all();
          if (!empty($e)) {
            if (defined('ALLOW_SALES_UPDATE_DATES') && ALLOW_SALES_UPDATE_DATES == 'True') {
              $ids = \yii\helpers\ArrayHelper::getColumn($e, 'specials_id');
              $columns = [
                'start_date' => new \yii\db\Expression(' DATE_ADD("' . tep_db_input($specials_expires_date) . '", INTERVAL 1 SECOND)'),
                'specials_last_modified' => new \yii\db\Expression('now()')
              ];
              \common\models\Specials::updateAll($columns, ['specials_id' => $ids]);
            } else {
             $validateError = true;
            }
          }
        }
      }

      if (!$validateError) {

        // fix possible errors with dates (set the same and deactivate).
        tep_db_query("update ". TABLE_SPECIALS . " set specials_last_modified = now(), /*expires_date=start_date,*/ status=0 where expires_date<start_date and expires_date>'1980-01-01' and products_id='" . (int)$products_id . "'");

        if ($specials_start_date > date(\common\helpers\Date::DATABASE_DATETIME_FORMAT) && !$specials_enabled) {
          $_status = 0;
        } else {
          $_status = $status;
        }

        if ((int)$specials_id > 0) {
          //date_status_change
          $date_status_change = '';
          $sp = \common\models\Specials::find()->andWhere(['specials_id' => $specials_id])->asArray()->one();
          if ($sp && $sp['status'] != $_status) {
            $date_status_change = "date_status_change=now(), ";
          }
          tep_db_query("update " . TABLE_SPECIALS . " set {$date_status_change} specials_new_products_price = '" . (float) $special_price . "', specials_last_modified = now(), expires_date = '" . tep_db_input($specials_expires_date) . "', start_date = '" . tep_db_input($specials_start_date) . "', status = '" . $_status . "', specials_type_id='" . (int)$specials_type_id . "', specials_disabled='" . (int)$specials_disabled . "', specials_enabled=" . (int)$specials_enabled . ", promote_type=" . (int)$promote_type . ", total_qty=" . (int)$total_qty . ", max_per_order=" . (int)$max_per_order . " where specials_id = '" . (int)$specials_id . "'");
          \common\models\SpecialsPrices::deleteAll(['specials_id' => (int)$specials_id]);
        } else {
          tep_db_query("insert into " . TABLE_SPECIALS . " set products_id = '" . (int) $products_id . "', specials_new_products_price = '" . (float) $special_price . "', specials_date_added = now(), expires_date = '" . tep_db_input($specials_expires_date) . "', start_date = '" . tep_db_input($specials_start_date) . "', status = '" . $_status . "', specials_type_id='" . (int)$specials_type_id . "', specials_disabled='" . (int)$specials_disabled . "', specials_enabled=" . (int)$specials_enabled . ", promote_type=" . (int) $promote_type . ", total_qty=" . (int)$total_qty . ", max_per_order=" . (int)$max_per_order . "");
          $specials_id = tep_db_insert_id();
        }
        if (is_array($prices)) {
          foreach ($prices as $price) {
            try {
              $price['specials_id'] = $specials_id;
              $m = new \common\models\SpecialsPrices();
              $m->loadDefaultValues();
              $m->setAttributes($price, false);
              $m->save(false);
            } catch (\Exception $e) {
              \Yii::warning($e->getMessage(), 'SPECIALPRICES_ERROR');
            }
          }
        }
      } else {
        $specials_id = false;
      }
      return $specials_id;

  }
/**
 * save specials (either on categories or sales page in admin.
 * @param int $products_id
 * @param bool $deleteInactive default false
 * @return bool|string  true|false|error message?
 */
  public static function saveFromPost($products_id, $deleteInactive = false) {
    $ret = false;
    $currencies = Yii::$container->get('currencies');
    $_def_curr_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];
    $specials_type_id = (int)Yii::$app->request->post('specials_type_id', 0);
    $promote_type = (int)Yii::$app->request->post('promote_type', 0);

    $specials_id = (int) \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays(['db' => 'specials_id', 'dbdef' => 0, 'post' => 'specials_id'], $_def_curr_id, 0);
    //$status = (int) \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays(['db' => 'status', 'dbdef' => 0, 'post' => 'special_status'], $_def_curr_id, 0);
    $status = (int)Yii::$app->request->post('special_status', 0);

    if (!$status && $deleteInactive ) {
      $s = \common\models\Specials::findOne(['specials_id' => $specials_id]);
      if ($s) {
        $s->delete();
        $ret = true;
      }
    } else {
      //$specials_expires_date =  \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays(['db' => 'expires_date', 'dbdef' => '', 'post' => 'special_expires_date'], $_def_curr_id, 0);
      $specials_expires_date = Yii::$app->request->post('special_expires_date', '');
      $specials_expires_date = \common\helpers\Date::prepareInputDate($specials_expires_date, true);
      //$specials_start_date =  \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays(['db' => 'start_date', 'dbdef' => 'NULL', 'post' => 'special_start_date'], $_def_curr_id, 0);
      $specials_start_date = Yii::$app->request->post('special_start_date', '');
      $specials_start_date = \common\helpers\Date::prepareInputDate($specials_start_date, true);
      $special_price =  \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays(['db' => 'specials_new_products_price', 'dbdef' => '-1', 'post' => 'special_price'], $_def_curr_id, 0);
      $total_qty = (int)Yii::$app->request->post('total_qty', 0);
      $max_per_order = (int)Yii::$app->request->post('max_per_order', 0);
      if ($status == -1) {
        $specials_disabled = 1;
        $status = 0;
      } elseif ($status == 1) {
        $specials_disabled = 0;
        $specials_enabled = 1;
      } else {
        if ($status > 1) {
          $status = 1;
        }
        $specials_disabled = 0;
        $specials_enabled = 0;
      }

      if (!$specials_expires_date || $specials_expires_date=='' || $specials_expires_date=='NULL') {
  /*
          if ($special_price>0) {
            $dateFormat = date_create("+30 days");
            $specials_expires_date = $dateFormat?$dateFormat->format(\common\helpers\Date::DATABASE_DATETIME_FORMAT):'';
          } else {
            $status = 0;
          }
  */
      }

      if (!$specials_start_date || $specials_start_date=='' || $specials_start_date=='NULL') {
        $dateFormat = date_create();
        $specials_start_date = $dateFormat?$dateFormat->format(\common\helpers\Date::DATABASE_DATETIME_FORMAT):'';
      }

      $prices = $currencies_ids = $groups = $groups_price = [];
      if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
        $groups = $ext::getGroupsArray();
        if (!isset($groups['0'])) {
          $groups['0'] = ['groups_id' => 0, 'per_product_price' => 1];
        }
        $groups_price = array_filter($groups, function($e) { return $e['per_product_price'];} );
        if ($groups_price==$groups) {
          unset($groups_price);
        }
      }


      if (USE_MARKET_PRICES == 'True') {
        foreach ($currencies->currencies as $key => $value)  {
          $currencies_ids[$currencies->currencies[$key]['id']] = $currencies->currencies[$key]['id'];
        }
      } else {
        $currencies_ids[$_def_curr_id] = '0'; /// here is the post and db currencies_id are different.
      }
      if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
        foreach ($currencies_ids as $post_currencies_id => $currencies_id) {
          foreach ((($groups_price??null)?$groups_price:$groups) as $groups_id => $non) {
            $prices[] = [
              'currencies_id' => $currencies_id,
              'groups_id' => $groups_id,
              'specials_new_products_price' =>  \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays(['db' => 'specials_new_products_price', 'dbdef' => -2, 'post' => 'special_price', 'f' => ['self', 'defGroupPrice']], $currencies_id, $groups_id)
              ];
          }
        }
      }

      $ret = self::validateSave($products_id, $special_price, $prices, $specials_id, $status, $specials_start_date, $specials_expires_date, $specials_type_id, $specials_disabled, $specials_enabled??null, $promote_type, $total_qty, $max_per_order);

      if ($ret) {
        tep_db_perform(TABLE_PRODUCTS, array(
                'products_last_modified' => 'now()',
            ), 'update', "products_id='" . (int)$products_id . "'");
      }
    }
    return $ret;
  }
  
  public static function specials_cleanup() {
    try {
      \common\models\Specials::deleteAll(
        ['and',
          ['status' => 0],
          ['or',
            ['<', 'start_date', new \yii\db\Expression('now()')],
            ['is', 'start_date',  new \yii\db\Expression('null')]
          ]
        ])
        ;
      //quick as DeleteAll doesn't trigger before Delete
        \common\models\SpecialsPrices::cleanup();
    } catch (\Exception $e) {
      \Yii::warning($e->getMessage(), 'specials');
      echo $e->getMessage();
    }
  }

/**
 * incorrect name (copied out) Activates scheduled and disable expired sales (special prices)
 * @param bool $force - ignore admin "by_cron" setting
 */
  public static function tep_expire_specials($force=false) {
    if ($force || !defined('EXPIRE_SPECIALS_BY_CRON') || EXPIRE_SPECIALS_BY_CRON == 'False' || date('H:i') == '00:03') {
      //enable
      tep_db_query("update " . TABLE_SPECIALS . " set status=1, date_status_change=now() "
          . " where specials_disabled=0 and status=0 "
          . " and (specials_enabled=1 or now() >= start_date or start_date is null or start_date='" . \common\models\queries\SpecialsQuery::$startEpoch . "')" //started
          . " and (specials_enabled=1 or expires_date is null or expires_date='" . \common\models\queries\SpecialsQuery::$startEpoch . "' or now() < expires_date)"); // not expired
      //disable (expire)
      tep_db_query("update " . TABLE_SPECIALS . " set status = 0, date_status_change = now() where status>0 and specials_enabled=0 and (specials_disabled=1 or (now() >= expires_date and expires_date > 0))") ;
    }
  }


 /**
  * for admin part only text according 3 flags and date
  * @param bool $enabled
  * @param bool $disabled
  * @param bool $expired
  * @param bool $scheduled
  * @return string
  */
  public static function statusDescriptionText($enabled, $disabled, $expired, $scheduled) {
    $cur_status = '<span class="sales-active">' . TEXT_ACTIVE . '</span>';
    if ($enabled) {
      $cur_status = '<span class="sales-active sales-manual">' . TEXT_MANUALLY_ACTIVATED . '</span>';
    } elseif ($disabled){
     $cur_status = '<span class="sales-inactive sales-manual">' . TEXT_MANUALLY_DISABLED . '</span>';
    } else {
      if ($expired) {
        $cur_status = '<span class="sales-inactive">' . TEXT_EXPIRED . '</span>';
      } elseif ($scheduled) {
        $cur_status = '<span class="sales-scheduled">' . TEXT_SCHEDULED . '</span>';
      }
    }
    return $cur_status;
  }


/**
 * description by specials_id admin only - 2do group
 * @param integer $specials_id
 * @param integer $tax
 * @param integer $group_id
 * @param integer $currencies_id
 * @return array
 */
  public static function getStatus($specials_id, $tax=0, $group_id=0, $currencies_id=0) {
    $ret = [];
    $expired  = $scheduled = false;
    $specials_id = intval($specials_id);
    $currencies = Yii::$container->get('currencies');
    $_def_curr_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];

    $listQuery = \common\models\Specials::find()->select(\common\models\Specials::tableName() . '.*');
    $listQuery->andWhere(['specials_id' => $specials_id]);
    //$listQuery->joinWith(['backendProductDescription', 'specialsType'])->addSelect('products_name, products_price, specials_type_name');

// echo $listQuery->createCommand()->rawSql; die;
    $special = $listQuery->one();
    if (!empty($special)) {
      if ($special['start_date'] > '1980-01-01') {
        if ($special['start_date'] > date("Y-m-d H:i:s") && !$special['status'] ) {
          $scheduled = true;
        }
      }
      if ($special['expires_date'] > '1980-01-01') {
        if ($special['expires_date'] < date("Y-m-d H:i:s")) {
          $expired = true;
        }
      }
      $ret['description'] = self::statusDescriptionText($special['specials_enabled'], $special['specials_disabled'], $expired, $scheduled);
      $prices = self::getPrices($special, $tax);
      if (!empty($prices)) {
        foreach ($prices as $cid => $value) {
          if ($cid != $currencies_id && !($cid==0 && $_def_curr_id==$currencies_id ) ) { continue; }
          foreach ($value as $gid => $price) {
            if ($gid != $group_id ) { continue; }
            $ret['prices'] = $price;
            break;
          }
          break;
        }
      }
    }
    return $ret;
  }

/**
 * Description by products_id - active, scheduled, disabled not expired - admin only - 2do group
 * @param integer $products_id
 * @param integer $group_id
 * @param integer $currencies_id
 * @return array
 */
  public static function getProductStatus($products_id,  $tax=0, $group_id=0, $currencies_id=0) {
    $listQuery = \common\models\Specials::find()->select(\common\models\Specials::tableName() . '.*');
    $listQuery->andWhere(['products_id' => $products_id]);
    $listQuery->orderBy('status desc, start_date<now(), start_date, specials_disabled  desc')->limit(1);//vl2check active, scheduled, disabled not expired
    $special = $listQuery->one();

    $currencies = Yii::$container->get('currencies');
    $_def_curr_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];

    $ret = [];
    $expired = $scheduled = false;
    if (!empty($special)) {
      if ($special['start_date'] > '1980-01-01' && $special['start_date'] > date("Y-m-d H:i:s") && !$special['status'] ) {
          $scheduled = true;
      }
      if ($special['expires_date'] > '1980-01-01'  && $special['expires_date'] < date("Y-m-d H:i:s")) {
          $expired = true;
      }
      $ret = [
        'description' => self::statusDescriptionText($special['specials_enabled'], $special['specials_disabled'], $expired, $scheduled),
        'start_date' => Date::datetime_short($special['start_date']),
        'expires_date' => Date::datetime_short($special['expires_date']),
        'total_qty' => $special['total_qty'],
        'max_per_order' => $special['max_per_order'],
        'sold' => (!empty($special['total_qty'])?self::getSoldOnlyQty(['specials_id' => $special['specials_id']]):0),
        'id' => $special['specials_id']
        ];

      $prices = self::getPrices($special, $tax);
      if (!empty($prices)) {
        foreach ($prices as $cid => $value) {
          if ($cid != $currencies_id && !($cid==0 && $_def_curr_id==$currencies_id ) ) { continue; }
          foreach ($value as $gid => $price) {
            if ($gid != $group_id ) { continue; }
            $ret['prices'] = $price;
            break;
          }
          break;
        }

      } 
      if (empty($ret['prices'])) {
        $ret['description'] .= ' ' . TEXT_SALES_DISABLED_GROUP;
      }
    } else {
      $ret = [
        'description' =>  '<span class="sales-active">' . TEXT_NOT_SET_UP_CLICK_MORE . '</span>'
        ];
    }
    return $ret;

  }

/**
 * get array of prices of specified special
 * @staticvar array $cPrices cache...
 * @param \common\models\Specials $special
 * @param float $tax ex 20
 * @return array [currency_id][group_id][value*, text* ...]
 */
  public static function getPrices($special, $tax = 0)  {
    static $cPrices = null;
    
    if ($special instanceof \common\models\Specials) {

      if (isset($cPrices[$special->specials_id])) {
        return $cPrices[$special->specials_id];
      }

      $sPrices = $special->prices; //if there isn't records for group/currency the main price is applied to def group, currency.
      if (empty($sPrices) || (!\common\helpers\Extensions::isCustomerGroupsAllowed() && USE_MARKET_PRICES != 'True')) {
        $sPrices[0] = $special->attributes;
        $sPrices[0]['groups_id'] = $sPrices[0]['currencies_id'] = 0;
      } elseif (!empty($sPrices) && is_array($sPrices)) {
        /// if no record sales_price for def cur/group and exists for other - admin shows incorrectly disabled for main and show on frontend
        $defCurrencyId = \common\helpers\Currencies::getCurrencyId(DEFAULT_CURRENCY);
        $missed = true;
        foreach ($sPrices as $priceInfo) {
          if ($priceInfo['groups_id']==0 && ($priceInfo['currencies_id']==0 || $priceInfo['currencies_id'] == $defCurrencyId)) {
            $missed = false;
            break;
          }
        }
        if ($missed) {
          $tmp = $special->attributes;
          $tmp['groups_id'] = $tmp['currencies_id'] = 0;
          $sPrices[] = $tmp;
        }
      }
      
      $cPrices[$special->specials_id] = $prices = self::calculatePrices($sPrices, $tax);
    }
    return $prices;
  }


  public static function calculatePrices($sPrices, $tax) {
    $prices = [];
    $defPrice = null;
    if (is_array($sPrices)) {
  /** @var \common\classes\Currencies $currencies */
      $currencies = Yii::$container->get('currencies');
      $groups = \common\helpers\Group::get_customer_groups();
      $defCurrencyId = \common\helpers\Currencies::getCurrencyId(DEFAULT_CURRENCY);

      foreach ($sPrices as $priceInfo) {

        if ($priceInfo['specials_new_products_price']>0 || $priceInfo['specials_new_products_price']==-2) {
          if ($priceInfo['currencies_id']==0 || $priceInfo['currencies_id'] == $defCurrencyId) {
            $cCode = DEFAULT_CURRENCY;
            $cId = 0;
          } else {
            $cCode = \common\helpers\Currencies::getCurrencyCode($priceInfo['currencies_id']);
            $cId = $priceInfo['currencies_id'];
          }
          $price = false;

          if ($priceInfo['specials_new_products_price']==-2) {
            //def price could be not first in the $sPrices array
            if (is_null($defPrice) && is_array($sPrices)) {
              if (!empty($prices[0][0])) {
                $defPrice = $prices[0][0];
              } else {
                $defPrice = false;
                foreach($sPrices as $_p) {
                  if ($_p['groups_id']==0 && $_p['currencies_id']==0) {
                    $defPrice = $_p;
                    $price = $_p['specials_new_products_price'];
                    if ($price>0) {
                      $prices[0][0] = [
                        'value' => $price,
                        'text' => $currencies->format($currencies->calculate_price($price, 0, 1, $cCode), true, $cCode),
                        'value_inc' => $currencies->calculate_price($price, $tax, 1, $cCode),
                        'text_inc' => $currencies->format($currencies->calculate_price($price, $tax, 1, $cCode), true, $cCode),
                        //'currency_code' => $cCode,
                        'group_name' => TEXT_MAIN
                        ];
                    }
                    $price = false;
                    break;
                  }
                }
              }
            }

            if (is_array($groups[$priceInfo['groups_id']] ?? null) && isset($prices[0][0]['value']) && $prices[0][0]['value']>0 && is_numeric($groups[$priceInfo['groups_id']]['groups_discount'])) {
              if ($groups[$priceInfo['groups_id']]['apply_groups_discount_to_specials']) {
                $price = $prices[0][0]['value'] * (1-$groups[$priceInfo['groups_id']]['groups_discount']/100);
              } else {
                $price = $prices[0][0]['value'];
              }
            }
          } else {
            $price = $priceInfo['specials_new_products_price'];
          }

          if (!empty($price)) {
            $prices[$cId][$priceInfo['groups_id']] = [
              'value' => $price,
              'text' => $currencies->format($currencies->calculate_price($price, 0, 1, $cCode), true, $cCode),
              'value_inc' => $currencies->calculate_price($price, $tax, 1, $cCode),
              'text_inc' => $currencies->format($currencies->calculate_price($price, $tax, 1, $cCode), true, $cCode),
              //'currency_code' => $cCode,
              'group_name' => ($priceInfo['groups_id']==0?TEXT_MAIN:$groups[$priceInfo['groups_id']]['groups_name'])
              ];
          }
          if (is_null($defPrice) && !empty($prices[0][0])) {
            $defPrice = $prices[0][0];
          }
        }
      }
    }
    return $prices;
  }

  /**
   * compare sold and allocated products with special price with allowed total_qty
   * @param array $params [specials_id, total_qty]
   * @return boolean
   */
  public static function checkSoldOut($params, $qty=1) {
    $ret = false;
    if (!empty($params['total_qty']) && !empty($params['specials_id'])) {
      $ret = ($params['total_qty'] < self::getSoldQty($params) + $qty );
    }
    
    return $ret;
  }

  /**
   * get sold and allocated products with special price
   * @param array $params [specials_id]
   * @return int
   */
  public static function getSoldQty($params) {
    $ret = 0;
    static $cache = [];
    if (!empty($params['specials_id'])) {
      if (!isset($cache[$params['specials_id']])) {
        $soldQty = self::getSoldOnlyQty($params);
        $temporaryStockQty = 0;

        if (defined('USE_TEMP_STOCK_ON_SPECIALS_CAP') && USE_TEMP_STOCK_ON_SPECIALS_CAP == 'True') {
          if (!(($ext = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $ext::isFreezed())) {
            if (\Yii::$app->id=='app-console') {
              $guid = \Yii::$app->storage->get('guid');
            } else {
              $guid = tep_session_id();
            }
            $temporaryStockQty = (int)\common\models\OrdersProductsTemporaryStock::find()
              ->andWhere(['!=', 'session_id', $guid])
              ->andWhere([
                'specials_id' => $params['specials_id']
              ])->sum('temporary_stock_quantity');
          }
        }
        $cache[$params['specials_id']] = $soldQty+$temporaryStockQty;
      }
      $ret = $cache[$params['specials_id']];
    }

    return $ret;
  }

  /**
   * get sold products with special price
   * @param array $params [specials_id]
   * @return int
   */
  public static function getSoldOnlyQty($params) {
    $ret = 0;
    static $cache = [];
    if (!empty($params['specials_id'])) {
      if (!isset($cache[$params['specials_id']])) {
        $exclude_order_statuses_array = \common\helpers\Order::extractStatuses(DASHBOARD_EXCLUDE_ORDER_STATUSES);
        $soldQty = (int)\common\models\OrdersProducts::find()->joinWith('order', false)
            ->andWhere(['not in', 'orders_status', $exclude_order_statuses_array])
            ->andWhere([
              'specials_id' => $params['specials_id']
            ])->sum('products_quantity');
        $temporaryStockQty = 0;

        $cache[$params['specials_id']] = $soldQty+$temporaryStockQty;
      }
      $ret = $cache[$params['specials_id']];
    }

    return $ret;
  }

/**
 * get sales description by ID
 * @param int $specials_id
 * @return string
 */
  public static function getDescription($specials_id=0, $promo_id=0)  {
    $ret = '';
    if ((int)$specials_id>0) {
      $ret .= sprintf(defined('TEXT_SPECIAL_DESCRIPTION')? TEXT_SPECIAL_DESCRIPTION: 'Special %s %s', '', '');
    }
    if ((int)$promo_id>0) {
      $ret .=  ' ' . (defined('TEXT_PROMOTIONS')? TEXT_PROMOTIONS: 'Promotion');
    }
    try {
      if (\common\helpers\Extensions::isCustomerGroupsAllowed() && !\Yii::$app->user->isGuest && \Yii::$app->storage->has('customer_groups_id')) {
          $check = false;
        /** @var \common\extensions\PersonalDiscount\PersonalDiscount  $personalDiscount */
        if ($personalDiscount = \common\helpers\Acl::checkExtensionAllowed('PersonalDiscount', 'allowed')){
            $check = $personalDiscount::getPersonalDiscountPercent();
            if ($check) {
                $ret = (defined('TEXT_PERSONAL_DISCOUNT')?constant('TEXT_PERSONAL_DISCOUNT'):'') . ' ' . $check . ' ' . $ret;
            }
        }

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        if (!$check && $customer_groups_id>0) {
          $groups = Group::get_customer_groups();
          if (!empty($groups[$customer_groups_id])) {
            $ret = $groups[$customer_groups_id]['groups_name'] . ' ' . $ret;
          }

        }
      }
    } catch (\Exception $e) {
      \Yii::warning(" #### " .print_r($e->getMessage(), true), 'TLDEBUG-specials');
    }
    return $ret;
  }

  public static function getSpecialId($productDetails, $qty)  {
    $specials_id = 0;
    if (!empty($productDetails)) {
      $products_id = $productDetails['products_id'];
      if (isset($productDetails['parent']) && $productDetails['parent'] != '') {
        if ($productDetails['products_pctemplates_id']) {
          // if parent is configurator
          $priceInstance = \common\models\Product\ConfiguratorPrice::getInstance($products_id);
          $special_price = $priceInstance->getConfiguratorSpecialPrice(['qty' => $qty]);
          if ($special_price !== false) {
            $_spd = $priceInstance->getSpecialPriceDetails(['qty' => $qty]);
            if (self::qtyInRange($_spd, $qty)) {
              $specials_id = $_spd['specials_id']??0;
            }
          }
        } else {
          if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {

          }
        }
      } else {
        $priceInstance = \common\models\Product\Price::getInstance($products_id);
        $special_price = $priceInstance->getInventorySpecialPrice(['qty' => $qty]);
        if ( $special_price !== false) {
          $_spd = $priceInstance->getSpecialPriceDetails(['qty' => $qty]);
          if (self::qtyInRange($_spd, $qty)) {
            $specials_id = $_spd['specials_id']??0;
          }
        }
 
      }     
    }
    return $specials_id;
  }

  /**
   * check specials total_qty and max_per_order with $qty
   * @param int|array|object $special
   * @param int $qty
   * @return bool|null
   */
  public static function qtyInRange($special, $qty) {

    if ($special instanceof \common\models\Specials) {
      $special = $special->attributes();

    } elseif (is_array($special) && !empty($special['specials_id']) ) {
      if (!isset($special['total_qty']) || !isset($special['max_per_order'])) {
        $special = \common\models\Specials::find()->where(['specials_id' => $special['specials_id']])->asArray()->one();
      }

    } elseif (is_scalar($special)) {
      $special = \common\models\Specials::find()->where(['specials_id' => $special])->asArray()->one();
    } else {
      $special = false;
    }

    if (!empty($special)) {
      $ret = true;
      if (!empty($special['total_qty']) && \common\helpers\Specials::checkSoldOut($special)) {
        $ret = false;
      }
      if (!empty($special['max_per_order']) && $special['max_per_order']<$qty) {
        $ret = false;
      }
    } else {
      $ret = null;
    }

    return $ret;
  }

  public static function getLinkAdmin($specials_id, $description) {
    $ret = $description;
    if ((int)$specials_id>0) {
      $check = \common\models\Specials::find()->where(['specials_id' => $specials_id])->exists();
      if ($check) {
        $ret = '<a target="top" href="' . Yii::$app->urlManager->createUrl(['specials/specialedit', 'id' => (int)$specials_id]) . '" class="sales-link">' . $ret . '</a>';
      }
    }
    return $ret;
  }


}
